<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\User;
use App\Models\Application;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\UserRegistered;
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
            $admins = User::where('is_admin', true)->paginate(10)->withQueryString();
            $agents = collect();
        } elseif ($role === 'agent') {
            $admins = collect();
            $agents = User::where('is_agent', true)
                ->withCount(['students', 'applications'])
                ->paginate(10)
                ->withQueryString();
        } else {
            $admins = User::where('is_admin', true)->paginate(10)->withQueryString();
            $agents = User::where('is_agent', true)
                ->withCount(['students', 'applications'])
                ->paginate(10)
                ->withQueryString();
        }

        return view('admin.users.index', compact('admins', 'agents', 'search', 'role'));
    }

    public function show(User $user)
    {
        if (!Auth::user()->is_admin && Auth::id() !== $user->id) {
            abort(403, 'Unauthorized');
        }
        // ---------- RECENT ACTIVITIES ----------
        // Last 5 student activities (added/deleted) across all users
        $studentActivities = Activity::with('student', 'user')
            ->whereIn('type', ['student_added', 'student_deleted'])
            ->where('user_id', $user->id)       // <-- filter by selected user
            ->latest()
            ->take(7)
            ->get();

        $documentActivities = Activity::with('student', 'document', 'user')
            ->whereIn('type', ['document_uploaded', 'document_deleted'])
            ->where('user_id', $user->id)       // <-- filter by selected user
            ->latest()
            ->take(5)
            ->get();

        $applicationActivities = Activity::with('application', 'user')
            ->whereIn('type', ['application_submitted', 'application_withdrawn'])
            ->where('user_id', $user->id)       // <-- filter by selected user
            ->latest()
            ->take(5)
            ->get();


        $notifications = $user->notifications()->latest()->get();
        $students = $user->students()->withCount('applications')->get();
        $applications = Application::whereIn('student_id', $students->pluck('id'))
            ->with(['student', 'course.university'])
            ->get();

        $user->students_count = $students->count();
        $user->applications_count = $applications->count();
        $user->pending_applications = $applications->where('status', 'pending')->count();

        return view('admin.users.show', compact('user', 'notifications', 'students', 'applications', 'documentActivities', 'studentActivities', 'applicationActivities'));
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

        // Notify admin about new registration
        $admin = User::find(2); // or adjust your admin logic
        Notification::send($admin, new UserRegistered($user));


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
            return back()->with('error', 'Only Main admin can delete users.');
        }

        if ($user->id == 1) {
            return back()->with('error', 'Cannot delete Main admin.');
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

    public function applications(User $agent, Request $request)
    {
        if (!$agent->is_agent) abort(404);

        $studentIds = $agent->students()->pluck('id');

        $query = Application::with(['student', 'course.university'])
            ->whereIn('student_id', $studentIds);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                // Search by student first name or last name
                $q->whereHas('student', function ($q2) use ($search) {
                    $q2->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                })
                    // Search by course uniersity_name
                    ->orWhereHas('course', function ($q3) use ($search) {
                        $q3->where('title', 'like', "%{$search}%"); // use actual column
                    })
                    ->orWhereHas('course.university', function ($q4) use ($search) {
                        $q4->where('name', 'like', "%{$search}%"); // use actual column
                    });
            });
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.users.applications', compact('agent', 'applications'));
    }
}
