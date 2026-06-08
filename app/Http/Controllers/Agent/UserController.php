<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AgreementSubmitted;
use App\Contracts\FileUploadServiceInterface;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function __construct(
        private readonly FileUploadServiceInterface $fileUploadService,
    ) {}
    /**
     * Get user by slug
     */
    private function getUserBySlug($slug)
    {
        return User::where('slug', $slug)->firstOrFail();
    }

    /**
     * Show profile (Agent's own profile + staff list)
     */
    public function show($slug)
    {
        $auth = Auth::user();
        $user = $this->getUserBySlug($slug);

        if ($auth->id != $user->id) {
            abort(403, 'Unauthorized access.');
        }

        // Get staff members created by this agent
        $staffMembers = User::where('parent_id', $user->id)
            ->where('role', 'staff')
            ->orderBy('created_at', 'desc')
            ->get();

        $studentActivities = Activity::where('user_id', $user->id)
            ->whereIn('type', ['student_added', 'student_deleted'])
            ->latest()->take(10)->get();

        $documentActivities = Activity::where('user_id', $user->id)
            ->whereIn('type', ['document_uploaded', 'document_deleted'])
            ->latest()->take(10)->get();

        $applicationActivities = Activity::where('user_id', $user->id)
            ->whereIn('type', ['application_submitted', 'application_withdrawn'])
            ->latest()->take(10)->get();

        return view('agent.users.show', compact(
            'user',
            'staffMembers',
            'studentActivities',
            'documentActivities',
            'applicationActivities'
        ));
    }

    /**
     * Edit profile (Agent's own profile)
     */
    public function edit($slug)
    {
        $user = $this->getUserBySlug($slug);

        if (Auth::id() != $user->id) {
            abort(403, 'Unauthorized.');
        }

        return view('agent.users.edit', compact('user'));
    }

    /**
     * Update profile (Agent's own profile)
     * Note: Agreement file cannot be uploaded from here (read-only in form)
     */
    public function update(Request $request, $slug)
    {
        $user = $this->getUserBySlug($slug);

        if (Auth::id() != $user->id) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'email'          => 'required|email|max:255|unique:users,email,' . $user->id,
            'contact'        => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:255',
            'password'       => 'nullable|string|min:6|confirmed',
            'business_logo'  => 'nullable|file|mimes:jpg,jpeg,png|max:20480',
            'registration'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
            'pan'            => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
            // agreement_file is NOT included - it's read-only in the form
        ]);

        // Track changes for logging
        $oldData = [
            'email' => $user->email,
            'contact' => $user->contact,
            'address' => $user->address,
        ];

        // Update basic info
        $user->email   = $request->email;
        $user->contact = $request->contact;
        $user->address = $request->address;

        // Update password if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Ensure slug exists
        if (!$user->slug) {
            $user->slug = strtolower(str_replace(' ', '-', $user->business_name));
        }

        $user->save();

        // Upload files (logo, registration, pan only - NOT agreement)
        $user->business_logo = $this->fileUploadService->uploadAgentFile($request, $user, 'business_logo', 'logo');
        $user->registration  = $this->fileUploadService->uploadAgentFile($request, $user, 'registration', 'registration');
        $user->pan           = $this->fileUploadService->uploadAgentFile($request, $user, 'pan', 'pan');

        $user->save();

        // Log profile update activity if anything changed
        $changes = [];
        if ($oldData['email'] != $user->email) $changes[] = 'email';
        if ($oldData['contact'] != $user->contact) $changes[] = 'contact';
        if ($oldData['address'] != $user->address) $changes[] = 'address';

        if (
            !empty($changes) || $request->filled('password') || $request->hasFile('business_logo') ||
            $request->hasFile('registration') || $request->hasFile('pan')
        ) {

            Activity::create([
                'user_id' => $user->id,
                'type' => 'profile_updated',
                'description' => "Profile updated by {$user->business_name}" . (!empty($changes) ? ". Changes: " . implode(', ', $changes) : ""),
                'notifiable_id' => $user->id,
                'notifiable_type' => User::class,
                'link' => route('agent.users.show', $user->slug),
            ]);
        }

        return redirect()->route('agent.users.show', $user->slug)
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Reset password (Agent's own password)
     */
    public function resetPassword($slug)
    {
        $user = $this->getUserBySlug($slug);

        if (Auth::id() != $user->id) {
            abort(403, 'Unauthorized.');
        }

        $newPassword = Str::random(10);

        $user->password = Hash::make($newPassword);
        $user->save();

        // Log password reset
        Activity::create([
            'user_id' => $user->id,
            'type' => 'password_reset',
            'description' => "Password reset for {$user->business_name}",
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
        ]);

        return back()->with('success', "Password reset successfully. New password: $newPassword");
    }

    // ============================================
    // STAFF MANAGEMENT METHODS
    // ============================================

    /**
     * Show create staff form
     */
    public function createStaff()
    {
        $agent = Auth::user();

        // Check staff limit (global setting for now)
        $staffCount = User::where('parent_id', $agent->id)
            ->where('role', 'staff')
            ->count();

        $staffLimit = 1; // Default limit, will make configurable later

        if ($staffCount >= $staffLimit) {
            return redirect()->route('agent.users.show', $agent->slug)
                ->with('error', "You have reached the maximum staff limit ({$staffLimit}). Please contact admin to increase limit.");
        }

        return view('agent.users.create-staff', compact('agent'));
    }

    /**
     * Store new staff member
     */
    public function storeStaff(Request $request)
    {
        $agent = Auth::user();

        // Check staff limit again
        $staffCount = User::where('parent_id', $agent->id)
            ->where('role', 'staff')
            ->count();

        $staffLimit = 1; // Default limit

        if ($staffCount >= $staffLimit) {
            return redirect()->route('agent.users.show', $agent->slug)
                ->with('error', "Staff limit reached. Cannot create more staff.");
        }

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'contact'   => 'nullable|string|max:20',
            'address'   => 'nullable|string|max:500',
            'password'  => 'required|string|min:6|confirmed',
        ]);

        // Create staff user
        $staff = new User();
        $staff->name = $request->name;
        $staff->email = $request->email;
        $staff->contact = $request->contact;
        $staff->address = $request->address;
        $staff->password = Hash::make($request->password);
        $staff->role = 'staff';
        $staff->parent_id = $agent->id;
        $staff->active = 1; // Staff active by default
        $staff->business_name = $agent->business_name . ' - Staff';
        $staff->owner_name = $agent->owner_name;

        // Generate slug
        $slug = Str::slug($staff->name);
        $originalSlug = $slug;
        $counter = 1;
        while (User::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }
        $staff->slug = $slug;

        $staff->save();

        // Log activity
        Activity::create([
            'user_id' => $agent->id,
            'type' => 'staff_created',
            'description' => "Staff member {$staff->name} created",
            'notifiable_id' => $staff->id,
            'notifiable_type' => User::class,
            'link' => route('agent.users.show-staff', $staff->slug),
        ]);

        Log::info('Staff member created', [
            'agent_id' => $agent->id,
            'staff_id' => $staff->id,
            'staff_email' => $staff->email,
            'ip' => $request->ip()
        ]);

        return redirect()->route('agent.users.show', $agent->slug)
            ->with('success', "Staff member {$staff->name} created successfully.");
    }

    /**
     * Edit staff member
     */
    public function editStaff($slug)
    {
        $agent = Auth::user();
        $staff = $this->getUserBySlug($slug);

        // Ensure this staff belongs to this agent
        if ($staff->parent_id != $agent->id || $staff->role != 'staff') {
            abort(403, 'Unauthorized access.');
        }

        return view('agent.users.edit-staff', compact('staff', 'agent'));
    }

    /**
     * Update staff member
     */
    public function updateStaff(Request $request, $slug)
    {
        $agent = Auth::user();
        $staff = $this->getUserBySlug($slug);

        // Ensure this staff belongs to this agent
        if ($staff->parent_id != $agent->id || $staff->role != 'staff') {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $staff->id,
            'contact'   => 'nullable|string|max:20',
            'address'   => 'nullable|string|max:500',
            'password'  => 'nullable|string|min:6|confirmed',
            'status'    => 'nullable|boolean',
        ]);

        // Track changes
        $oldData = [
            'name' => $staff->name,
            'email' => $staff->email,
            'active' => $staff->active,
        ];

        $staff->name = $request->name;
        $staff->email = $request->email;
        $staff->contact = $request->contact;
        $staff->address = $request->address;
        $staff->active = $request->has('status') ? 1 : 0;

        if ($request->filled('password')) {
            $staff->password = Hash::make($request->password);
        }

        $staff->save();

        // Log activity
        $changes = [];
        if ($oldData['name'] != $staff->name) $changes[] = 'name';
        if ($oldData['email'] != $staff->email) $changes[] = 'email';
        if ($oldData['active'] != $staff->active) $changes[] = 'status';

        Activity::create([
            'user_id' => $agent->id,
            'type' => 'staff_updated',
            'description' => "Staff member {$staff->name} updated" . (!empty($changes) ? " (" . implode(', ', $changes) . ")" : ""),
            'notifiable_id' => $staff->id,
            'notifiable_type' => User::class,
            'link' => route('agent.users.show-staff', $staff->slug),
        ]);

        return redirect()->route('agent.users.show', $agent->slug)
            ->with('success', "Staff member {$staff->name} updated successfully.");
    }

    /**
     * Delete staff member
     */
    public function destroyStaff($slug)
    {
        $agent = Auth::user();
        $staff = $this->getUserBySlug($slug);

        // Ensure this staff belongs to this agent
        if ($staff->parent_id != $agent->id || $staff->role != 'staff') {
            abort(403, 'Unauthorized access.');
        }

        $staffName = $staff->name;
        $staffId = $staff->id;

        // Check if staff has any related data (students, applications, etc.)
        $hasStudents = $staff->students()->exists();
        $hasApplications = $staff->applications()->exists();

        if ($hasStudents || $hasApplications) {
            return redirect()->route('agent.users.show', $agent->slug)
                ->with('error', "Cannot delete {$staffName}. They have associated students or applications.");
        }

        $staff->delete();

        // Log activity
        Activity::create([
            'user_id' => $agent->id,
            'type' => 'staff_deleted',
            'description' => "Staff member {$staffName} deleted",
            'notifiable_id' => $staffId,
            'notifiable_type' => User::class,
        ]);

        Log::info('Staff member deleted', [
            'agent_id' => $agent->id,
            'staff_id' => $staffId,
            'staff_name' => $staffName
        ]);

        return redirect()->route('agent.users.show', $agent->slug)
            ->with('success', "Staff member {$staffName} deleted successfully.");
    }

    /**
     * Show staff details (optional - if you want a separate view)
     */
    public function showStaff($slug)
    {
        $agent = Auth::user();
        $staff = $this->getUserBySlug($slug);

        // Ensure this staff belongs to this agent
        if ($staff->parent_id != $agent->id || $staff->role != 'staff') {
            abort(403, 'Unauthorized access.');
        }

        return view('agent.users.show-staff', compact('staff', 'agent'));
    }
}
