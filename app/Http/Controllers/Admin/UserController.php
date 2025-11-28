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

        // Base query for admins
        $adminQuery = User::query()->where('is_admin', 1);
        if ($search) {
            $adminQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%");
            });
        }

        // Base query for agents
        $agentQuery = User::query()->where('is_agent', 1);
        if ($search) {
            $agentQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($role === 'admin') {
            $admins = $adminQuery->withCount(['students', 'applications'])
                ->orderBy('created_at', 'asc')
                ->paginate(20, ['*'], 'admins_page')
                ->withQueryString();
            $agents = collect(); // empty
        } elseif ($role === 'agent') {
            $agents = $agentQuery->withCount(['students', 'applications'])
                ->orderBy('created_at', 'asc')
                ->paginate(20, ['*'], 'agents_page')
                ->withQueryString();
            $admins = collect(); // empty
        } else {
            // Both roles
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
     * Resolve slug → actual user
     */
    private function businessNameFromSlug($slug)
    {
        $name = str_replace('-', ' ', $slug);

        return User::whereRaw('LOWER(business_name) = ?', [strtolower($name)])
            ->firstOrFail();
    }

    /**
     * SHOW USER DETAILS
     */
    public function show($slug)
    {
        $user = $this->businessNameFromSlug($slug);

        if (!Auth::user()->is_admin) {
            abort(403, 'Unauthorized');
        }

        // Activities
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
            ->with(['student', 'course.university'])
            ->get();

        return view('admin.users.show', compact(
            'user',
            'students',
            'applications',
            'studentActivities',
            'documentActivities',
            'applicationActivities'
        ));
    }

    /**
     * CREATE USER PAGE
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * STORE NEW USER
     */
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
            'business_logo' => 'nullable|file|max:20480',
            'agreement_file'   => 'nullable|file|max:20000',
            'agreement_status' => 'nullable|in:not_uploaded,uploaded,verified',
        ]);

        $user = new User();
        $this->assignUserFields($user, $request);
        $user->password = Hash::make($request->password);
        $this->handleBusinessLogoUpload($request, $user);

        //  agreement values
        if ($request->hasFile('agreement_file')) {

            $safeName = str_replace(' ', '_', strtolower($user->business_name));
            $folder = "agents/$safeName";

            $ext = $request->file('agreement_file')->getClientOriginalExtension();
            $fileName = $safeName . '_agreement.' . $ext;

            // delete old agreement
            if ($user->agreement_file && Storage::disk('public')->exists($user->agreement_file)) {
                Storage::disk('public')->delete($user->agreement_file);
            }

            $path = $request->file('agreement_file')
                ->storeAs($folder, $fileName, 'public');

            $user->agreement_file = $path;
            $user->agreement_status = 'uploaded';
        }

        if (!$user->agreement_status) {
            $user->agreement_status = 'not_uploaded';
        }

        $user->save();

        // Notify main admin (ID=2)
        $admin = User::find(2);
        if ($admin) {
            Notification::send($admin, new UserRegistered($user));
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User added successfully.');
    }

    /**
     * EDIT PAGE
     */
    public function edit($slug)
    {
        $user = $this->businessNameFromSlug($slug);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * UPDATE USER (INCLUDING AGREEMENT FILE)
     */
    public function update(Request $request, $slug)
    {
        $user = $this->businessNameFromSlug($slug);

        $request->validate([
            'business_name'    => 'required|string|max:255',
            'owner_name'       => 'nullable|string|max:255',
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|max:255|unique:users,email,' . $user->id,
            'contact'          => 'nullable|string|max:20',
            'address'          => 'nullable|string|max:255',
            'role'             => 'required|in:admin,agent',
            'status'           => 'required|in:0,1',
            'password'         => 'nullable|string|min:6|confirmed',
            'business_logo'    => 'nullable|file|max:20480',
            'agreement_file'   => 'nullable|file|max:20000',
            'agreement_status' => 'nullable|in:not_uploaded,uploaded,verified',
        ]);

        $previousAgreement = $user->agreement_file;

        $this->assignUserFields($user, $request);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $this->handleBusinessLogoUpload($request, $user);

        /**
         * AGREEMENT FILE UPLOAD
         * Same logic as agent upload
         */
        if ($request->hasFile('agreement_file')) {

            $safeName = str_replace(' ', '_', strtolower($user->business_name));
            $folder = "agents/$safeName";

            $ext = $request->file('agreement_file')->getClientOriginalExtension();
            $fileName = $safeName . '_agreement.' . $ext;

            // delete old agreement
            if ($user->agreement_file && Storage::disk('public')->exists($user->agreement_file)) {
                Storage::disk('public')->delete($user->agreement_file);
            }

            $path = $request->file('agreement_file')
                ->storeAs($folder, $fileName, 'public');

            $user->agreement_file = $path;
            $user->agreement_status = 'uploaded';

            // Notify agent that admin updated agreement
            $user->notify(new AgreementSubmitted($user));
        }

        // Change status without file upload
        if ($request->agreement_status && !$request->hasFile('agreement_file')) {
            $user->agreement_status = $request->agreement_status;
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * DELETE USER
     */
    public function destroy($slug)
    {
        $user = $this->businessNameFromSlug($slug);

        if (Auth::id() !== 1) {
            return back()->with('error', 'Only main admin can delete users.');
        }

        if ($user->id == 1) {
            return back()->with('error', 'Cannot delete main admin.');
        }

        if ($user->business_logo && Storage::disk('public')->exists($user->business_logo)) {
            Storage::disk('public')->delete($user->business_logo);
        }

        if ($user->agreement_file && Storage::disk('public')->exists($user->agreement_file)) {
            Storage::disk('public')->delete($user->agreement_file);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * WAITING APPROVAL PAGE
     */
    public function waiting()
    {
        // List 1: Normal waiting approval
        $waitingUsers = User::where('active', 0)->get();

        // List 2: Agreement uploaded but not verified
        $agreementUsers = User::whereIn('agreement_status', ['uploaded', 'not_uploaded'])
            ->where('is_agent', 1)
            ->get();

        return view('admin.users.waiting', compact('waitingUsers', 'agreementUsers'));
    }

    /**
     * APPROVE USER
     */
    public function approve($slug)
    {
        $user = $this->businessNameFromSlug($slug);
        $user->active = true;
        $user->save();

        $user->notify(new UserApproved());

        return back()->with('success', $user->business_name . ' approved successfully.');
    }
    // List users with uploaded agreement
    // public function agreementApproval()
    // {
    //     $users = User::where('agreement_status', 'uploaded')->paginate(10);
    //     return view('admin.users.agreement_approval', compact('users'));
    // }
    public function deleteAgreement(User $user)
    {
        // Delete file if exists
        if ($user->agreement_file && Storage::disk('public')->exists($user->agreement_file)) {
            Storage::disk('public')->delete($user->agreement_file);
        }

        // Reset database fields
        $user->update([
            'agreement_file' => null,
            'agreement_status' => 'not_uploaded',
        ]);

        return back()->with('success', 'Agreement file deleted successfully.');
    }


    // Verify agreement
    public function verifyAgreement($slug)
    {
        $user = $this->businessNameFromSlug($slug);
        $user->agreement_status = 'verified';
        $user->save();

        $user->notify(new AgreementVerified($user));

        return back()->with('success', 'Agreement verified successfully.');
    }

    /**
     * Shared Fields
     */
    private function assignUserFields(User $user, Request $request)
    {
        $user->business_name = $request->business_name;
        $user->owner_name    = $request->owner_name;
        $user->name          = $request->name;
        $user->email         = $request->email;
        $user->contact       = $request->contact;
        $user->address       = $request->address;

        $user->is_admin = $request->role === 'admin';
        $user->is_agent = $request->role === 'agent';

        $user->active   = (int) $request->status;
    }

    /**
     * Business Logo Upload
     */
    private function handleBusinessLogoUpload(Request $request, User $user)
    {
        if ($request->hasFile('business_logo')) {

            $safeName = str_replace(' ', '_', strtolower($request->business_name ?? 'agent'));
            $folder = "agents/$safeName";

            $ext = $request->file('business_logo')->getClientOriginalExtension();
            $fileName = $safeName . '_logo.' . $ext;

            if ($user->business_logo && Storage::disk('public')->exists($user->business_logo)) {
                Storage::disk('public')->delete($user->business_logo);
            }

            $path = $request->file('business_logo')->storeAs($folder, $fileName, 'public');
            $user->business_logo = $path;
        }
    }
    /**
     * List all applications of a given agent
     */
    public function applications($slug, Request $request)
    {
        $agent = $this->businessNameFromSlug($slug);
        if (!$agent->is_agent) abort(404);

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
    /**
     * List all students of a given agent
     */
    public function students($slug, Request $request)
    {
        $agent = $this->businessNameFromSlug($slug);
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
}
