<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Application;
use App\Services\UserService;
use App\Services\NotificationDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly NotificationDispatcher $notifier,
    ) {}

    // ================================================
    //  INDEX + EXPORT
    // ================================================

    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->is_admin) abort(403);

        $query = $this->userService->buildFilteredUserQuery($request);

        $activeFilters = array_filter($request->only(['search', 'role', 'status', 'agreement', 'date_from', 'date_to', 'min_students', 'min_applications']));

        if ($request->filled('export')) {
            return $this->userService->exportUsers($query->get(), $request->export);
        }

        $totalUsers = (clone $query)->count();
        $parents = $this->userService->getParentOptions();
        $hasFilters = !empty($activeFilters);
        $role = $request->role;

        if ($role === 'admin') {
            $admins = (clone $query)->where('role', 'admin')->withCount(['students', 'applications'])->paginate(20, ['*'], 'admin_page')->withQueryString();
            $agents = collect(); $staffs = collect();
        } elseif ($role === 'agent') {
            $agents = (clone $query)->where('role', 'agent')->withCount(['students', 'applications'])->paginate(100, ['*'], 'agent_page')->withQueryString();
            $admins = collect(); $staffs = collect();
        } elseif ($role === 'staff') {
            $staffs = (clone $query)->where('role', 'staff')->withCount(['students', 'applications'])->paginate(20, ['*'], 'staff_page')->withQueryString();
            $admins = collect(); $agents = collect();
        } else {
            $admins  = (clone $query)->where('role', 'admin')->withCount(['students', 'applications'])->paginate(20, ['*'], 'admin_page')->withQueryString();
            $agents  = (clone $query)->where('role', 'agent')->withCount(['students', 'applications'])->paginate(100, ['*'], 'agent_page')->withQueryString();
            $staffs  = (clone $query)->where('role', 'staff')->withCount(['students', 'applications'])->paginate(20, ['*'], 'staff_page')->withQueryString();
        }

        return view('admin.users.index', compact('admins', 'agents', 'staffs', 'parents', 'totalUsers', 'hasFilters', 'activeFilters'));
    }

    // ================================================
    //  SHOW
    // ================================================

    public function show(User $user)
    {
        $auth = Auth::user();
        if (!$auth->is_admin && $auth->id !== $user->id) abort(403, 'Unauthorized access.');

        $data = $this->userService->getProfileData($user, $auth);
        $staffMembers = $data['staffMembers'];
        $students = $data['students'];
        $applications = $data['applications'];

        return view('shared.users.show', array_merge($data, compact('user')));
    }

    // ================================================
    //  CREATE
    // ================================================

    public function create()
    {
        if (!Auth::user()->is_admin) abort(403);
        $parents = $this->userService->getParentOptions();
        return view('shared.users.create', compact('parents'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->is_admin) abort(403);

        $request->validate([
            'business_name'   => 'required|string|max:255',
            'owner_name'      => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email'           => 'required|email|unique:users',
            'contact'         => 'nullable|string|max:20',
            'address'         => 'nullable|string|max:500',
            'role'            => 'required|in:admin,agent,staff,university,student',
            'status'          => 'nullable|boolean',
            'password'        => 'required|min:6|confirmed',
            'parent_id'       => 'required_if:role,staff|nullable|exists:users,id',
            'agreement_status'=> 'nullable|in:not_uploaded,uploaded,verified',
            'business_logo'   => 'nullable|file|max:20480',
            'registration'    => 'nullable|file|max:20480',
            'pan'             => 'nullable|file|max:20480',
            'agreement_file'  => 'nullable|file|max:20480',
            'max_staff'          => 'nullable|integer|min:0|max:100',
            'max_students'       => 'nullable|integer|min:0|max:10000',
            'paid_crm'           => 'nullable|boolean',
            'subscription_plan'  => 'nullable|string|max:50',
        ]);

        $user = $this->userService->createUser($request);

        return redirect()->route('admin.users.show', $user->slug)
            ->with('success', 'User created successfully.');
    }

    // ================================================
    //  EDIT / UPDATE
    // ================================================

    public function edit(User $user)
    {
        $auth = Auth::user();
        if (!$auth->is_admin && $auth->id !== $user->id) abort(403, 'Unauthorized.');

        if ($auth->is_admin) {
            $parents = User::whereIn('role', ['admin', 'agent'])->where('active', 1)
                ->where('id', '!=', $user->id)->orderBy('business_name')
                ->get(['id', 'business_name', 'name', 'role']);
            return view('shared.users.edit', compact('user', 'parents'));
        }

        return view('shared.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $auth = Auth::user();
        if (!$auth->is_admin && $auth->id !== $user->id) abort(403, 'Unauthorized.');

        if (!$auth->is_admin) {
            $request->validate([
                'name'           => 'required|string|max:255',
                'email'          => 'required|email|max:255|unique:users,email,' . $user->id,
                'contact'        => 'nullable|string|max:20',
                'address'        => 'nullable|string|max:255',
                'password'       => 'nullable|string|min:6|confirmed',
                'business_logo'  => 'nullable|file|mimes:jpg,jpeg,png|max:20480',
                'registration'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
                'pan'            => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
            ]);

            $oldData = ['email' => $user->email, 'contact' => $user->contact, 'address' => $user->address];
            $this->userService->updateUser($user, $request);

            $changes = [];
            if ($oldData['email'] != $user->email) $changes[] = 'email';
            if ($oldData['contact'] != $user->contact) $changes[] = 'contact';
            if ($oldData['address'] != $user->address) $changes[] = 'address';
            $routePrefix = $auth->is_agent ? 'agent' : 'staff';
            if ($request->filled('password') || $request->hasFile('business_logo') || $request->hasFile('registration') || $request->hasFile('pan')) {
                \App\Models\Activity::create([
                    'user_id' => $user->id, 'type' => 'profile_updated',
                    'description' => "Profile updated by {$user->business_name}" . ($changes ? ". Changes: " . implode(', ', $changes) : ""),
                    'notifiable_id' => $user->id, 'notifiable_type' => User::class,
                    'link' => route($routePrefix . '.users.show', $user->slug),
                ]);
            }
            return redirect()->route($routePrefix . '.users.show', $user->slug)->with('success', 'Profile updated successfully.');
        }

        $request->validate([
            'business_name'   => 'required|string|max:255',
            'owner_name'      => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email,' . $user->id,
            'contact'         => 'nullable|string|max:20',
            'address'         => 'nullable|string|max:500',
            'role'            => 'required|in:admin,agent,staff,university,student',
            'status'          => 'nullable|boolean',
            'password'        => 'nullable|min:6|confirmed',
            'parent_id'       => 'nullable|exists:users,id',
            'agreement_status'=> 'nullable|in:not_uploaded,uploaded,verified',
            'business_logo'   => 'nullable|file|max:20480',
            'registration'    => 'nullable|file|max:20480',
            'pan'             => 'nullable|file|max:20480',
            'agreement_file'  => 'nullable|file|max:20480',
            'max_staff'          => 'nullable|integer|min:0|max:100',
            'max_students'       => 'nullable|integer|min:0|max:10000',
            'paid_crm'           => 'nullable|boolean',
            'subscription_plan'  => 'nullable|string|max:50',
        ]);

        $this->userService->updateUser($user, $request);

        return redirect()->route('admin.users.show', $user->slug)
            ->with('success', 'User updated successfully.');
    }

    // ================================================
    //  DESTROY
    // ================================================

    public function destroy(User $user)
    {
        if (!Auth::user()->is_admin) return back()->with('error', 'Unauthorized action.');
        if ($user->id === Auth::id()) return back()->with('error', 'You cannot delete your own account.');
        if ($user->is_admin) return back()->with('error', 'Super admin cannot be deleted.');

        $this->userService->deleteUser($user);
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function changeRole(Request $request, User $user)
    {
        if (!Auth::user()->is_admin) abort(403);

        $request->validate([
            'role'             => 'required|in:admin,agent,staff,university,student',
            'status'           => 'nullable|boolean',
            'parent_id'        => 'nullable|exists:users,id|required_if:role,staff',
            'agreement_status' => 'nullable|in:not_uploaded,uploaded,verified',
            'max_staff'          => 'nullable|integer|min:0|max:100',
            'max_students'       => 'nullable|integer|min:0|max:10000',
            'paid_crm'           => 'nullable|boolean',
            'subscription_plan'  => 'nullable|string|max:50',
        ]);

        $user->role = $request->role;
        $user->active = $request->input('status', 0) == 1 ? 1 : 0;
        $user->agreement_status = $request->agreement_status ?? $user->agreement_status;
        $user->max_staff = $request->input('max_staff', $user->max_staff ?? 1);
        $user->max_students = $request->input('max_students', $user->max_students ?? 0);
        $user->paid_crm = $request->boolean('paid_crm');
        $user->subscription_plan = $request->input('subscription_plan', $user->subscription_plan ?? '');

        if ($request->role === 'staff' && $request->filled('parent_id')) {
            $user->parent_id = $request->parent_id;
        } elseif ($request->role !== 'staff') {
            $user->parent_id = null;
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', "{$user->business_name} role updated to " . ucfirst($request->role) . ".");
    }

    public function updatePlan(Request $request, User $user)
    {
        if (!Auth::user()->is_admin) abort(403);

        $request->validate([
            'max_staff'         => 'nullable|integer|min:0|max:100',
            'max_students'      => 'nullable|integer|min:0|max:10000',
            'paid_crm'          => 'nullable|boolean',
            'subscription_plan' => 'nullable|string|max:50',
        ]);

        $user->max_staff = $request->input('max_staff', $user->max_staff ?? 1);
        $user->max_students = $request->input('max_students', $user->max_students ?? 0);
        $user->paid_crm = $request->boolean('paid_crm');
        $user->subscription_plan = $request->input('subscription_plan', $user->subscription_plan ?? '');
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', "{$user->business_name} plan updated successfully.");
    }

    // ================================================
    //  ADMIN METHODS
    // ================================================

    public function waiting()
    {
        if (!Auth::user()->is_admin) abort(403);
        $data = $this->userService->getWaitingUsersData();
        return view('admin.users.waiting', $data);
    }

    public function approve(User $user)
    {
        if (!Auth::user()->is_admin) abort(403);
        $this->userService->approveUser($user);
        return back()->with('success', 'User approved.');
    }

    public function deleteAgreement(User $user)
    {
        if (!Auth::user()->is_admin) abort(403);
        try {
            $this->userService->deleteAgreement($user);
            return redirect()->back()->with('success', 'Agreement deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting agreement: ' . $e->getMessage());
        }
    }

    public function verifyAgreement(User $user)
    {
        if (!Auth::user()->is_admin) abort(403);
        $this->userService->verifyAgreement($user);
        return redirect()->route('admin.users.show', $user->slug)->with('success', 'Agreement verified.');
    }

    public function students(User $agent)
    {
        if (!Auth::user()->is_admin) abort(403);
        if ($agent->role !== 'agent') abort(404);
        $students = $this->userService->getAgentStudents($agent);
        return view('admin.users.students', compact('agent', 'students'));
    }

    public function applications(User $agent)
    {
        if (!Auth::user()->is_admin) abort(403);
        if ($agent->role !== 'agent') abort(404);
        $applications = $this->userService->getAgentApplications($agent);
        return view('admin.users.applications', compact('agent', 'applications'));
    }

    public function getParents()
    {
        if (!Auth::user()->is_admin) abort(403);
        try {
            return response()->json($this->userService->getParentsJson());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ================================================
    //  AGENT METHODS
    // ================================================

    public function resetPassword(User $user)
    {
        $auth = Auth::user();
        if ($auth->is_admin) abort(403);
        if ($auth->id !== $user->id) abort(403, 'Unauthorized.');

        $newPassword = $this->userService->resetPassword($user);
        return back()->with('success', "Password reset successfully. New password: $newPassword");
    }

    // Staff management

    public function createStaff()
    {
        if (Auth::user()->is_admin) abort(403);
        $agent = Auth::user();

        if (!$this->userService->canCreateStaff($agent)) {
            return redirect()->route('agent.users.show', $agent->slug)
                ->with('error', 'You have reached the maximum staff limit (1). Please contact admin.');
        }
        return view('shared.staff.create', compact('agent'));
    }

    public function storeStaff(Request $request)
    {
        if (Auth::user()->is_admin) abort(403);
        $agent = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'contact'  => 'nullable|string|max:20',
            'address'  => 'nullable|string|max:500',
            'password' => 'required|string|min:6|confirmed',
        ]);

        try {
            $this->userService->createStaff($agent, $request);
            return redirect()->route('agent.users.show', $agent->slug)
                ->with('success', 'Staff member created successfully.');
        } catch (\RuntimeException $e) {
            return redirect()->route('agent.users.show', $agent->slug)
                ->with('error', $e->getMessage());
        }
    }

    public function editStaff(User $staff)
    {
        if (Auth::user()->is_admin) abort(403);
        $agent = Auth::user();
        if ($staff->parent_id !== $agent->id || $staff->role !== 'staff') abort(403);
        return view('shared.staff.edit', compact('staff', 'agent'));
    }

    public function updateStaff(Request $request, User $staff)
    {
        if (Auth::user()->is_admin) abort(403);
        $agent = Auth::user();
        if ($staff->parent_id !== $agent->id || $staff->role !== 'staff') abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $staff->id,
            'contact'  => 'nullable|string|max:20',
            'address'  => 'nullable|string|max:500',
            'password' => 'nullable|string|min:6|confirmed',
            'status'   => 'nullable|boolean',
        ]);

        $this->userService->updateStaff($agent, $staff, $request);
        return redirect()->route('agent.users.show', $agent->slug)
            ->with('success', "Staff member {$staff->name} updated successfully.");
    }

    public function destroyStaff(User $staff)
    {
        if (Auth::user()->is_admin) abort(403);
        $agent = Auth::user();
        if ($staff->parent_id !== $agent->id || $staff->role !== 'staff') abort(403);

        try {
            $this->userService->destroyStaff($agent, $staff);
            return redirect()->route('agent.users.show', $agent->slug)
                ->with('success', 'Staff member deleted successfully.');
        } catch (\RuntimeException $e) {
            return redirect()->route('agent.users.show', $agent->slug)
                ->with('error', $e->getMessage());
        }
    }

    public function showStaff(User $staff)
    {
        if (Auth::user()->is_admin) abort(403);
        $agent = Auth::user();
        if ($staff->parent_id !== $agent->id || $staff->role !== 'staff') abort(403);

        $activities = $this->userService->getStaffActivities($staff);
        return view('shared.staff.show', compact('staff', 'agent', 'activities'));
    }
}
