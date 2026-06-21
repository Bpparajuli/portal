<?php
// app/Services/UserService.php
//
// Consolidated User business logic extracted from UserController (1074 lines).
// Handles: CRUD, filtering/sorting, export, staff management, agreement workflow,
// password reset, parent/user queries.
//
// Controllers call this service and handle HTTP-layer concerns (validation, auth, response).

namespace App\Services;

use App\Models\Activity;
use App\Models\Application;
use App\Models\Student;
use App\Models\User;
use App\Services\FileUploadService;
use App\Notifications\UserRegistered;
use App\Notifications\UserApproved;
use App\Notifications\AgreementVerified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserService
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {}

    // -----------------------------------------------------------------------
    //  FILTERING & PAGINATION
    // -----------------------------------------------------------------------

    /**
     * Build a filtered, sorted User query with counts for the admin index page.
     *
     * Applies search (name/email/business/owner/contact), role, status,
     * agreement_status, date range, min students/applications filters,
     * and a configurable sort order.
     *
     * @param  Request $request  Incoming request with filter/sort parameters.
     * @return \Illuminate\Database\Eloquent\Builder  Configured query (not executed).
     */
    public function buildFilteredUserQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = User::query()->withCount(['students', 'applications']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%")
                  ->orWhere('owner_name', 'like', "%{$search}%")
                  ->orWhere('contact', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active' ? 1 : 0);
        }

        if ($request->filled('agreement')) {
            $query->where('agreement_status', $request->agreement);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('min_students')) {
            $query->has('students', '>=', (int) $request->min_students);
        }

        if ($request->filled('min_applications')) {
            $query->has('applications', '>=', (int) $request->min_applications);
        }

        match ($request->sort) {
            'name_asc'       => $query->orderBy('name', 'asc'),
            'name_desc'      => $query->orderBy('name', 'desc'),
            'business_asc'   => $query->orderBy('business_name', 'asc'),
            'business_desc'  => $query->orderBy('business_name', 'desc'),
            'created_at_asc' => $query->orderBy('created_at', 'asc'),
            'created_at_desc'=> $query->orderBy('created_at', 'desc'),
            default           => $query->orderByRaw('(COALESCE(students_count, 0) + COALESCE(applications_count, 0)) DESC'),
        };

        return $query;
    }

    /**
     * Get active users suitable as parent (admin/agent) for form dropdowns.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getParentOptions(): \Illuminate\Support\Collection
    {
        return User::whereIn('role', ['admin', 'agent'])
            ->where('active', 1)
            ->orderBy('business_name')
            ->get(['id', 'business_name', 'name', 'role']);
    }

    // -----------------------------------------------------------------------
    //  CRUD
    // -----------------------------------------------------------------------

    /**
     * Assign common fields (business_name, owner_name, name, email, role, slug)
     * from a request to a user model. Slug is auto-generated from business_name
     * and uniquified.
     */
    public function assignFields(User $user, Request $request): void
    {
        $user->business_name = $request->business_name;
        $user->owner_name    = $request->owner_name;
        $user->name          = $request->name;
        $user->email         = $request->email;
        $user->role          = $request->role;
        $user->max_staff         = $request->input('max_staff', $user->max_staff ?? 1);
        $user->max_students      = $request->input('max_students', $user->max_students ?? 0);
        $user->paid_crm          = $request->boolean('paid_crm');
        $user->subscription_plan = $request->input('subscription_plan', $user->subscription_plan ?? '');

        $slug = strtolower(str_replace(' ', '-',
            $request->role === 'staff' ? $request->name : $request->business_name
        ));
        $originalSlug = $slug;
        $counter = 1;
        while (User::where('slug', $slug)->where('id', '!=', $user->id)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }
        $user->slug = $slug;
    }

    /**
     * Create a new user with all associated file uploads.
     *
     * Handles password hashing, status, parent_id, agreement, and file uploads
     * (business_logo, registration, pan, agreement_file).
     * Notifies the first admin about the new registration.
     *
     * @return User  Freshly created user.
     */
    public function createUser(Request $request): User
    {
        $user = new User();
        $this->assignFields($user, $request);

        $user->password = Hash::make($request->password);
        $user->active   = $request->has('status') ? 1 : 0;

        if ($request->filled('contact')) {
            $user->contact = $request->contact;
        }
        if ($request->filled('address')) {
            $user->address = $request->address;
        }
        if ($request->role === 'staff' && $request->filled('parent_id')) {
            $user->parent_id = $request->parent_id;
        }

        $user->save();

        $this->handleFileUploads($user, $request);
        $this->notifyAdminsOfNewUser($user);

        return $user;
    }

    /**
     * Update an existing user's core fields, password, status, and files.
     *
     * Handles admin-update (all fields) and self-update (limited fields).
     * Deletes old files when replaced. Auto-generates slug if missing.
     *
     * @param  User    $user    The user to update.
     * @param  Request $request Validated request data.
     * @return User             Freshly reloaded user.
     */
    public function updateUser(User $user, Request $request): User
    {
        $isAdmin = Auth::user()->is_admin;

        if ($isAdmin) {
            $this->assignFields($user, $request);
            $user->active = $request->input('status', 0) == 1 ? 1 : 0;

            if ($request->filled('contact')) $user->contact = $request->contact;
            if ($request->filled('address')) $user->address = $request->address;

            if ($request->role === 'staff' && $request->filled('parent_id')) {
                $user->parent_id = $request->parent_id;
            } elseif ($request->role !== 'staff') {
                $user->parent_id = null;
            }
        } else {
            $user->name    = $request->name;
            $user->email   = $request->email;
            $user->contact = $request->contact;
            $user->address = $request->address;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if (!$user->slug) {
            $user->slug = Str::slug(
                $user->role === 'staff' ? $user->name : ($user->business_name ?: $user->name)
            );
        }

        $user->save();
        $this->handleFileUploads($user, $request);

        return $user->fresh();
    }

    /**
     * Delete a user and their associated files from storage.
     *
     * Removes: business_logo, registration, pan, agreement_file.
     * Performs a soft-delete on the user record.
     */
    public function deleteUser(User $user): void
    {
        foreach (['business_logo', 'registration', 'pan', 'agreement_file'] as $file) {
            if ($user->$file && Storage::disk('public')->exists($user->$file)) {
                Storage::disk('public')->delete($user->$file);
            }
        }
        $user->delete();
    }

    // -----------------------------------------------------------------------
    //  FILE UPLOADS (shared between create and update)
    // -----------------------------------------------------------------------

    /**
     * Process file uploads (business_logo, registration, pan, agreement_file).
     *
     * Uploads each present file via FileUploadService and deletes the old file
     * on disk before replacing the path.
     */
    private function handleFileUploads(User $user, Request $request): void
    {
        $files = ['business_logo', 'registration', 'pan', 'agreement_file'];
        $types = ['business_logo' => 'logo', 'registration' => 'registration', 'pan' => 'pan', 'agreement_file' => 'agreement'];

        foreach ($files as $inputName) {
            if ($request->hasFile($inputName)) {
                $oldFile = $user->$inputName;
                $user->$inputName = $this->fileUploadService->uploadAgentFile($request, $user, $inputName, $types[$inputName]);
                if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }
            }
        }

        if ($request->hasFile('agreement_file')) {
            $user->agreement_status = 'uploaded';
        } elseif ($request->has('agreement_status') && !$request->hasFile('agreement_file')) {
            $user->agreement_status = $request->agreement_status;
        }

        $user->save();
    }

    // -----------------------------------------------------------------------
    //  USER DATA FOR SHOW VIEW
    // -----------------------------------------------------------------------

    /**
     * Load a user's profile data for the show view.
     *
     * ROLE SCOPING:
     * - Admin: sees the user's students, applications, staff, and activities.
     * - Non-admin (self-view): sees their own scoped data.
     *
     * @param  User      $user   The user whose profile to show.
     * @param  User|null $auth   The authenticated viewer (defaults to current).
     * @return array             Keys: students, applications, staffMembers, activities.
     */
    public function getProfileData(User $user, ?User $auth = null): array
    {
        $auth = $auth ?? Auth::user();

        if ($auth->is_admin) {
            $students = $user->students()->withCount('applications')->get();
            $staffMembers = User::where('parent_id', $user->id)
                ->where('role', 'staff')->orderBy('created_at', 'desc')->get();
        } else {
            $students = $user->is_staff
                ? ($user->parent?->students()->withCount('applications')->get() ?? collect())
                : $user->students()->withCount('applications')->get();
            $staffMembers = User::where('parent_id', $user->id)
                ->where('role', 'staff')->orderBy('created_at', 'desc')->get();
        }

        $applications = Application::whereIn('student_id', $students->pluck('id'))
            ->with(['student', 'course.university', 'status'])->get();

        $studentActivities = Activity::where('user_id', $user->id)
            ->whereIn('type', ['student_added', 'student_deleted'])
            ->latest()->take($auth->is_admin ? 5 : 10)->get();

        $documentActivities = Activity::where('user_id', $user->id)
            ->whereIn('type', ['document_uploaded', 'document_deleted'])
            ->latest()->take($auth->is_admin ? 5 : 10)->get();

        $applicationActivities = Activity::where('user_id', $user->id)
            ->whereIn('type', ['application_submitted', 'application_withdrawn'])
            ->latest()->take($auth->is_admin ? 5 : 10)->get();

        return compact('students', 'applications', 'staffMembers', 'studentActivities', 'documentActivities', 'applicationActivities');
    }

    // -----------------------------------------------------------------------
    //  EXPORT
    // -----------------------------------------------------------------------

    /**
     * Export a collection of users in the requested format.
     *
     * Supported formats: csv, excel (tab-separated), pdf.
     *
     * @param  \Illuminate\Support\Collection $users  The users to export.
     * @param  string                         $type   Export format (csv|excel|pdf).
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function exportUsers($users, string $type)
    {
        $filename = 'users_' . date('Y-m-d_H-i-s');
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename.csv\"",
            'Pragma' => 'no-cache', 'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0', 'Expires' => '0'];

        if ($type === 'csv') {
            $callback = function () use ($users) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, ['ID', 'Business Name', 'Owner Name', 'User Name', 'Email', 'Contact', 'Address',
                    'Role', 'Status', 'Agreement Status', 'Parent Company', 'Students Count', 'Applications Count',
                    'Created At', 'Last Updated']);
                foreach ($users as $user) {
                    fputcsv($file, [
                        $user->id, $user->business_name, $user->owner_name ?? 'N/A', $user->name, $user->email,
                        $user->contact ?? 'N/A', $user->address ?? 'N/A', ucfirst($user->role),
                        $user->active ? 'Active' : 'Inactive',
                        str_replace('_', ' ', ucfirst($user->agreement_status)),
                        $user->parent ? $user->parent->business_name : 'N/A',
                        $user->students_count ?? $user->students()->count(),
                        $user->applications_count ?? $user->applications()->count(),
                        $user->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
                        $user->updated_at?->format('Y-m-d H:i:s') ?? 'N/A',
                    ]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        if ($type === 'excel') {
            $headers['Content-Type'] = 'application/vnd.ms-excel';
            $filename = str_replace('.csv', '.xls', $headers['Content-Disposition']);
            $headers['Content-Disposition'] = "attachment; filename=\"$filename\"";
            $callback = function () use ($users) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, ['ID', 'Business Name', 'Owner Name', 'User Name', 'Email', 'Contact', 'Address',
                    'Role', 'Status', 'Agreement Status', 'Parent Company', 'Students Count', 'Applications Count',
                    'Created At', 'Last Updated'], "\t");
                foreach ($users as $user) {
                    fputcsv($file, [
                        $user->id, $user->business_name, $user->owner_name ?? 'N/A', $user->name, $user->email,
                        $user->contact ?? 'N/A', $user->address ?? 'N/A', ucfirst($user->role),
                        $user->active ? 'Active' : 'Inactive',
                        str_replace('_', ' ', ucfirst($user->agreement_status)),
                        $user->parent ? $user->parent->business_name : 'N/A',
                        $user->students_count ?? $user->students()->count(),
                        $user->applications_count ?? $user->applications()->count(),
                        $user->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
                        $user->updated_at?->format('Y-m-d H:i:s') ?? 'N/A',
                    ], "\t");
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        if ($type === 'pdf') {
            $html = view('exports.users_pdf', compact('users'))->render();
            $pdf = app('dompdf.wrapper');
            $pdf->loadHTML($html);
            return $pdf->download($filename . '.pdf');
        }

        throw new \InvalidArgumentException("Unsupported export type: {$type}");
    }

    // -----------------------------------------------------------------------
    //  AGREEMENT WORKFLOW
    // -----------------------------------------------------------------------

    /**
     * Delete a user's agreement file from storage and reset status to not_uploaded.
     */
    public function deleteAgreement(User $user): void
    {
        if ($user->agreement_file && Storage::disk('public')->exists($user->agreement_file)) {
            Storage::disk('public')->delete($user->agreement_file);
        }
        $user->update(['agreement_file' => null, 'agreement_status' => 'not_uploaded']);
    }

    /**
     * Mark a user's agreement as verified and notify them.
     */
    public function verifyAgreement(User $user): void
    {
        $user->agreement_status = 'verified';
        $user->save();
        $user->notify(new AgreementVerified($user));
    }

    // -----------------------------------------------------------------------
    //  APPROVAL / WAITING
    // -----------------------------------------------------------------------

    /**
     * Get waiting and agreement-pending users for the approval page.
     *
     * @return array  Keys: waitingUsers (inactive), agreementUsers (agents with pending agreements).
     */
    public function getWaitingUsersData(): array
    {
        $waitingUsers   = User::where('active', 0)->get();
        $agreementUsers = User::where('role', 'agent')
            ->whereIn('agreement_status', ['uploaded', 'not_uploaded'])
            ->get();

        return compact('waitingUsers', 'agreementUsers');
    }

    /**
     * Approve a user (set active=1) and send the UserApproved notification.
     */
    public function approveUser(User $user): void
    {
        $user->active = 1;
        $user->save();
        $user->notify(new UserApproved());
    }

    // -----------------------------------------------------------------------
    //  AGENT CHILD VIEWS
    // -----------------------------------------------------------------------

    /**
     * Get paginated students for an agent (admin view).
     */
    public function getAgentStudents(User $agent): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Student::where('agent_id', $agent->id)
            ->withCount('applications')
            ->latest()
            ->paginate(10);
    }

    /**
     * Get paginated applications for an agent (admin view).
     */
    public function getAgentApplications(User $agent): \Illuminate\Pagination\LengthAwarePaginator
    {
        $studentIds = $agent->students()->pluck('id');
        return Application::whereIn('student_id', $studentIds)
            ->with(['student', 'course.university'])
            ->latest()
            ->paginate(10);
    }

    // -----------------------------------------------------------------------
    //  PASSWORD RESET
    // -----------------------------------------------------------------------

    /**
     * Reset a user's password to a random 10-char string.
     *
     * Logs the reset as an Activity entry.
     *
     * @return string  The new plain-text password.
     */
    public function resetPassword(User $user): string
    {
        $newPassword = Str::random(10);
        $user->password = Hash::make($newPassword);
        $user->save();

        Activity::create([
            'user_id' => $user->id,
            'type' => 'password_reset',
            'description' => "Password reset for {$user->business_name}",
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
        ]);

        return $newPassword;
    }

    // -----------------------------------------------------------------------
    //  STAFF MANAGEMENT (Agent creates/manages their staff)
    // -----------------------------------------------------------------------

    /**
     * Create a staff member under the given agent.
     *
     * Checks staff limit (hardcoded to 1) before creating.
     * Generates a unique slug from the staff's name.
     * Logs Activity and a Laravel log entry.
     *
     * @param  User    $agent   The parent agent.
     * @param  Request $request Validated staff data (name, email, contact, address, password).
     * @return User             The newly created staff.
     * @throws \RuntimeException If staff limit reached.
     */
    public function createStaff(User $agent, Request $request): User
    {
        $staffCount = User::where('parent_id', $agent->id)
            ->where('role', 'staff')->count();

        $staffLimit = $agent->max_staff ?? 1;
        if ($staffCount >= $staffLimit) {
            throw new \RuntimeException("Staff limit ({$staffLimit}) reached. Cannot create more staff.");
        }

        $staff = new User();
        $staff->name         = $request->name;
        $staff->email        = $request->email;
        $staff->contact      = $request->contact;
        $staff->address      = $request->address;
        $staff->password     = Hash::make($request->password);
        $staff->role         = 'staff';
        $staff->parent_id    = $agent->id;
        $staff->active       = 1;
        $staff->business_name = $agent->business_name . ' - Staff';
        $staff->owner_name   = $agent->owner_name;
        $staff->slug         = $this->uniqueSlug(Str::slug($staff->name));
        $staff->save();

        Activity::create([
            'user_id' => $agent->id,
            'type' => 'staff_created',
            'description' => "Staff member {$staff->name} created",
            'notifiable_id' => $staff->id,
            'notifiable_type' => User::class,
            'link' => route('agent.staff.show', $staff->slug),
        ]);

        Log::info('Staff member created', [
            'agent_id' => $agent->id, 'staff_id' => $staff->id,
            'staff_email' => $staff->email, 'ip' => $request->ip(),
        ]);

        return $staff;
    }

    /**
     * Update an existing staff member's fields under the given agent.
     *
     * Detects changes and logs an Activity entry.
     *
     * @return User  Updated staff.
     */
    public function updateStaff(User $agent, User $staff, Request $request): User
    {
        $oldData = ['name' => $staff->name, 'email' => $staff->email, 'active' => $staff->active];

        $staff->name    = $request->name;
        $staff->email   = $request->email;
        $staff->contact = $request->contact;
        $staff->address = $request->address;
        $staff->active  = $request->has('status') ? 1 : 0;

        if ($request->filled('password')) {
            $staff->password = Hash::make($request->password);
        }
        $staff->save();

        $changes = [];
        if ($oldData['name'] != $staff->name) $changes[] = 'name';
        if ($oldData['email'] != $staff->email) $changes[] = 'email';
        if ($oldData['active'] != $staff->active) $changes[] = 'status';

        Activity::create([
            'user_id' => $agent->id,
            'type' => 'staff_updated',
            'description' => "Staff member {$staff->name} updated" . ($changes ? ' (' . implode(', ', $changes) . ')' : ''),
            'notifiable_id' => $staff->id,
            'notifiable_type' => User::class,
            'link' => route('agent.staff.show', $staff->slug),
        ]);

        return $staff;
    }

    /**
     * Delete a staff member after checking they have no associated students/apps.
     *
     * @throws \RuntimeException If staff has associated students or applications.
     */
    public function destroyStaff(User $agent, User $staff): void
    {
        if ($staff->students()->exists() || $staff->applications()->exists()) {
            throw new \RuntimeException(
                "Cannot delete {$staff->name}. They have associated students or applications."
            );
        }

        $staffName = $staff->name;
        $staffId = $staff->id;
        $staff->delete();

        Activity::create([
            'user_id' => $agent->id,
            'type' => 'staff_deleted',
            'description' => "Staff member {$staffName} deleted",
            'notifiable_id' => $staffId,
            'notifiable_type' => User::class,
        ]);

        Log::info('Staff member deleted', [
            'agent_id' => $agent->id, 'staff_id' => $staffId, 'staff_name' => $staffName,
        ]);
    }

    /**
     * Get activity log entries for a staff member.
     *
     * @return \Illuminate\Support\Collection  Up to 20 most recent activities.
     */
    public function getStaffActivities(User $staff): \Illuminate\Support\Collection
    {
        return Activity::where('user_id', $staff->id)->latest()->take(20)->get();
    }

    // -----------------------------------------------------------------------
    //  UTILITY
    // -----------------------------------------------------------------------

    /**
     * Get parent users as JSON (for admin AJAX select).
     */
    public function getParentsJson(): array
    {
        $parents = User::whereIn('role', ['admin', 'agent'])
            ->where('active', 1)
            ->select('id', 'name', 'business_name', 'owner_name', 'role', 'email')
            ->orderBy('role')->orderBy('business_name')
            ->get();
        return ['parents' => $parents];
    }

    /**
     * Check if an agent can create more staff members.
     *
     * @return bool True if under the limit.
     */
    public function canCreateStaff(User $agent): bool
    {
        $staffLimit = $agent->max_staff ?? 1;
        $staffCount = User::where('parent_id', $agent->id)
            ->where('role', 'staff')->count();
        return $staffCount < $staffLimit;
    }

    // -----------------------------------------------------------------------
    //  INTERNAL HELPERS
    // -----------------------------------------------------------------------

    /**
     * Generate a unique slug by appending a counter if needed.
     */
    private function uniqueSlug(string $slug): string
    {
        $original = $slug;
        $counter = 1;
        while (User::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $counter++;
        }
        return $slug;
    }

    /**
     * Notify the first admin about a newly registered user.
     */
    private function notifyAdminsOfNewUser(User $user): void
    {
        $admin = User::admins()->first();
        if ($admin) {
            $admin->notify(new UserRegistered($user));
        }
    }
}
