<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Activity;
use App\Models\Student;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;

// Notifications
use App\Notifications\UserRegistered;
use App\Notifications\UserApproved;
use App\Notifications\AgreementSubmitted;
use App\Notifications\AgreementVerified;

class UserController extends Controller
{
    /**
     * LIST USERS (Admins + Agents)
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $role   = $request->role;

        $adminQuery = User::where('role', 'admin');
        $agentQuery = User::where('role', 'agent');

        if ($search) {
            $adminQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%");
            });

            $agentQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%");
            });
        }

        if ($role === 'admin') {
            $admins = $adminQuery->withCount(['students', 'applications'])
                ->orderBy('created_at', 'asc')
                ->paginate(20, ['*'], 'admins_page')
                ->withQueryString();
            $agents = collect();
        } elseif ($role === 'agent') {
            $agents = $agentQuery->withCount(['students', 'applications'])
                ->orderBy('created_at', 'desc')
                ->paginate(20, ['*'], 'agents_page')
                ->withQueryString();
            $admins = collect();
        } else {
            $admins = $adminQuery->withCount(['students', 'applications'])
                ->orderBy('created_at', 'asc')
                ->paginate(20, ['*'], 'admins_page')
                ->withQueryString();
            $agents = $agentQuery->withCount(['students', 'applications'])
                ->orderBy('created_at', 'desc')
                ->paginate(20, ['*'], 'agents_page')
                ->withQueryString();
        }

        return view('admin.users.index', compact('admins', 'agents', 'search', 'role'));
    }

    /**
     * SHOW USER DETAILS
     */
    public function show(User $user)
    {
        if (!in_array(Auth::user()->role, ['admin', 'superadmin'])) {
            abort(403, 'Unauthorized');
        }

        $studentActivities = Activity::with('student', 'user')
            ->where('user_id', $user->id)
            ->whereIn('type', ['student_added', 'student_deleted'])
            ->latest()->take(7)->get();

        $documentActivities = Activity::with('student', 'document', 'user')
            ->where('user_id', $user->id)
            ->whereIn('type', ['document_uploaded', 'document_deleted'])
            ->latest()->take(5)->get();

        $applicationActivities = Activity::with('application', 'user')
            ->where('user_id', $user->id)
            ->whereIn('type', ['application_submitted', 'application_withdrawn'])
            ->latest()->take(5)->get();

        $students = $user->students()->withCount('applications')->get();
        $applications = Application::whereIn('student_id', $students->pluck('id'))
            ->with(['student', 'course.university'])->get();

        return view('admin.users.show', compact(
            'user',
            'students',
            'applications',
            'studentActivities',
            'documentActivities',
            'applicationActivities'
        ));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'contact' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'role' => 'required|in:admin,agent',
            'status' => 'required|in:0,1',
            'password' => 'required|string|min:6|confirmed',
            'business_logo' => 'nullable|file|max:20480',
            'registration'  => 'required|file|max:20480',
            'pan'           => 'required|file|max:20480',
            'agreement_file' => 'nullable|file|max:20000',
            'agreement_status' => 'nullable|in:not_uploaded,uploaded,verified',
        ]);

        $user = new User();
        $this->assignUserFields($user, $request);
        $user->password = Hash::make($request->password);

        // Upload files
        $user->business_logo  = $this->uploadFile($request, $user, 'business_logo', 'logo');
        $user->registration   = $this->uploadFile($request, $user, 'registration', 'registration');
        $user->pan            = $this->uploadFile($request, $user, 'pan', 'pan');

        if ($request->hasFile('agreement_file')) {
            $user->agreement_file   = $this->uploadFile($request, $user, 'agreement_file', 'agreement');
            $user->agreement_status = 'uploaded';
        } else {
            $user->agreement_status = 'not_uploaded';
        }

        $user->save();

        $admin = User::find(2);
        if ($admin) Notification::send($admin, new UserRegistered($user));

        return redirect()->route('admin.users.index')->with('success', 'User added successfully.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'contact' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'role' => 'required|in:admin,agent',
            'status' => 'required|in:0,1',
            'password' => 'nullable|string|min:6|confirmed',
            'business_logo' => 'nullable|file|max:20480',
            'registration'  => 'nullable|file|max:20480',
            'pan'           => 'nullable|file|max:20480',
            'agreement_file' => 'nullable|file|max:20000',
            'agreement_status' => 'nullable|in:not_uploaded,uploaded,verified',
        ]);

        $this->assignUserFields($user, $request);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Upload/update files
        $user->business_logo  = $this->uploadFile($request, $user, 'business_logo', 'logo');
        $user->registration   = $this->uploadFile($request, $user, 'registration', 'registration');
        $user->pan            = $this->uploadFile($request, $user, 'pan', 'pan');

        if ($request->hasFile('agreement_file')) {
            $user->agreement_file   = $this->uploadFile($request, $user, 'agreement_file', 'agreement');
            $user->agreement_status = 'uploaded';
            $user->notify(new AgreementSubmitted($user));
        } elseif ($request->agreement_status) {
            $user->agreement_status = $request->agreement_status;
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if (Auth::id() != 1) return back()->with('error', 'Only main admin can delete users.');
        if ($user->id == 1) return back()->with('error', 'Cannot delete main admin.');

        foreach (['business_logo', 'agreement_file', 'registration', 'pan'] as $file) {
            if ($user->$file && Storage::disk('public')->exists($user->$file)) {
                Storage::disk('public')->delete($user->$file);
            }
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function waiting()
    {
        $waitingUsers = User::where('active', 0)->get();
        $agreementUsers = User::whereIn('agreement_status', ['uploaded', 'not_uploaded'])
            ->where('role', 'agent')
            ->get();

        return view('admin.users.waiting', compact('waitingUsers', 'agreementUsers'));
    }

    public function approve(User $user)
    {
        $user->active = true;
        $user->save();
        $user->notify(new UserApproved());
        return back()->with('success', $user->business_name . ' approved successfully.');
    }

    public function deleteAgreement(User $user)
    {
        if ($user->agreement_file && Storage::disk('public')->exists($user->agreement_file)) {
            Storage::disk('public')->delete($user->agreement_file);
        }

        $user->update([
            'agreement_file' => null,
            'agreement_status' => 'not_uploaded'
        ]);

        return back()->with('success', 'Agreement file deleted successfully.');
    }

    public function verifyAgreement(User $user)
    {
        $user->agreement_status = 'verified';
        $user->save();
        $user->notify(new AgreementVerified($user));

        // Redirect explicitly to the admin waiting page
        return redirect()->route('admin.users.waiting')
            ->with('success', 'Agreement verified successfully.');
    }



    public function students(User $agent, Request $request)
    {
        if ($agent->role !== 'agent') abort(404);

        $query = Student::withCount('applications')->where('agent_id', $agent->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        return view('admin.users.students', compact('agent', 'students'));
    }

    public function applications(User $agent, Request $request)
    {
        if ($agent->role !== 'agent') abort(404);

        $studentIds = $agent->students()->pluck('id');

        $query = Application::with(['student', 'course.university'])
            ->whereIn('student_id', $studentIds);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($q2) use ($search) {
                    $q2->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                })
                    ->orWhereHas('course', function ($q3) use ($search) {
                        $q3->where('title', 'like', "%{$search}%");
                    })
                    ->orWhereHas('course.university', function ($q4) use ($search) {
                        $q4->where('name', 'like', "%{$search}%");
                    });
            });
        }
        $applications = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        return view('admin.users.applications', compact('agent', 'applications'));
    }

    private function assignUserFields(User $user, Request $request)
    {
        $user->business_name = $request->business_name;
        $user->owner_name    = $request->owner_name;
        $user->name          = $request->name;
        $user->email         = $request->email;
        $user->contact       = $request->contact;
        $user->address       = $request->address;
        $user->role          = $request->role;
        $user->active        = (int)$request->status;

        // Slug for URL only
        $user->slug = strtolower(str_replace(' ', '-', $user->business_name));
    }

    private function uploadFile(Request $request, User $user, string $inputName, string $suffix)
    {
        if (!$request->hasFile($inputName)) return $user->$inputName;

        $safeName = str_replace([' ', '.', '-'], '_', strtolower($user->business_name));
        $folder   = "agents/$safeName";

        $file = $request->file($inputName);
        $fileName = $safeName . '_' . $suffix . '.' . $file->getClientOriginalExtension();

        if ($user->$inputName && Storage::disk('public')->exists($user->$inputName)) {
            Storage::disk('public')->delete($user->$inputName);
        }

        return $file->storeAs($folder, $fileName, 'public');
    }
}
