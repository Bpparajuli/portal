<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Application;
use App\Models\Student;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Notifications\UserApproved;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        $search = $request->input('search');
        $role = $request->input('role');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%");
            });
        }

        if ($role === 'admin') {
            $admins = User::where('is_admin', true)->paginate(5)->withQueryString();
            $agents = collect();
        } elseif ($role === 'agent') {
            $admins = collect();
            $agents = User::where('is_agent', true)
                ->withCount(['students', 'applications'])
                ->paginate(5)
                ->withQueryString();
        } else {
            $admins = User::where('is_admin', true)->paginate(5)->withQueryString();
            $agents = User::where('is_agent', true)
                ->withCount(['students', 'applications'])
                ->paginate(5)
                ->withQueryString();
        }

        return view('admin.users.index', compact('admins', 'agents', 'search', 'role'));
    }

    public function show(User $user)
    {
        if (!Auth::user()->is_admin && Auth::id() !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $notifications = $user->notifications()->latest()->get();
        $students = $user->students()->withCount('applications')->get();
        $applications = Application::whereIn('student_id', $students->pluck('id'))
            ->with(['student', 'course.university'])
            ->get();

        $user->students_count = $students->count();
        $user->applications_count = $applications->count();
        $user->pending_applications = $applications->where('status', 'pending')->count();

        return view('admin.users.show', compact('user', 'notifications', 'students', 'applications'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'owner_name'    => 'nullable|string|max:255',
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users',
            'contact'       => 'nullable|string|max:20',
            'address'       => 'nullable|string|max:255',
            'role'          => 'required|in:admin,agent',
            'status'        => 'required|in:0,1',
            'password'      => 'required|string|min:6|confirmed',
            'business_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $user = new User();
        $this->assignUserFields($user, $request);
        $user->password = Hash::make($request->password);
        $this->handleBusinessLogoUpload($request, $user);
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User added successfully.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'owner_name'    => 'nullable|string|max:255',
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email,' . $user->id,
            'contact'       => 'nullable|string|max:20',
            'address'       => 'nullable|string|max:255',
            'role'          => 'required|in:admin,agent',
            'status'        => 'required|in:0,1',
            'password'      => 'nullable|string|min:6|confirmed',
            'business_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $this->assignUserFields($user, $request);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $this->handleBusinessLogoUpload($request, $user);
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function destroy(User $user)
    {
        if (Auth::id() !== 1) {
            return back()->with('error', 'Only root admin can delete users.');
        }

        if ($user->id == 1) {
            return back()->with('error', 'Cannot delete root admin.');
        }

        if ($user->business_logo && Storage::disk('public')->exists($user->business_logo)) {
            Storage::disk('public')->delete($user->business_logo);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function waiting()
    {
        $users = User::where('active', 0)->paginate(10);
        $totalWaitingUsers = User::where('active', 0)->count();

        return view('admin.users.waiting', compact('users', 'totalWaitingUsers'));
    }

    public function approve(User $user)
    {
        $user->active = true;
        $user->save();
        $user->notify(new UserApproved());

        return back()->with('success', $user->business_name . ' has been approved and notified.');
    }

    private function assignUserFields(User $user, Request $request)
    {
        $user->business_name = $request->business_name;
        $user->owner_name    = $request->owner_name;
        $user->name          = $request->name;
        $user->email         = $request->email;
        $user->contact       = $request->contact;
        $user->address       = $request->address;
        $user->is_admin      = $request->role === 'admin';
        $user->is_agent      = $request->role === 'agent';
        $user->active        = (int) $request->status;
    }

    private function handleBusinessLogoUpload(Request $request, User $user)
    {
        if ($request->hasFile('business_logo')) {
            $safeBusinessName = str_replace(' ', '_', strtolower($request->business_name ?? 'agent'));
            $folderPath = 'agents/' . $safeBusinessName;
            $logoName = $safeBusinessName . '_logo.png';

            if ($user->business_logo && Storage::disk('public')->exists($user->business_logo)) {
                Storage::disk('public')->delete($user->business_logo);
            }

            $path = $request->file('business_logo')->storeAs($folderPath, $logoName, 'public');
            $user->business_logo = $path;
        }
    }

    /**
     * ✅ Admin view: students of an agent
     */
    public function students(User $agent, Request $request)
    {
        if (!$agent->is_agent) abort(404);

        $query = Student::withCount('applications')
            ->where('agent_id', $agent->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.students', compact('agent', 'students'));
    }



    /**
     * ✅ Admin view: applications of an agent
     */
    public function applications(User $agent, Request $request)
    {
        if (!$agent->is_agent) abort(404);

        $studentIds = $agent->students()->pluck('id');

        $query = Application::with(['student', 'course.university'])
            ->whereIn('student_id', $studentIds);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.users.applications', compact('agent', 'applications'));
    }
}
