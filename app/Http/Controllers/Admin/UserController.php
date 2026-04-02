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
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

// ✅ Service
use App\Services\FileUploadService;

// Notifications
use App\Notifications\UserRegistered;
use App\Notifications\UserApproved;
use App\Notifications\AgreementSubmitted;
use App\Notifications\AgreementVerified;

class UserController extends Controller
{
    /**
     * LIST USERS with Advanced Filtering, Sorting, and Export
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Track active filters
        $activeFilters = [];

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $activeFilters['search'] = $search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('owner_name', 'like', "%{$search}%")
                    ->orWhere('contact', 'like', "%{$search}%");
            });
        }

        // Apply role filter
        if ($request->filled('role')) {
            $activeFilters['role'] = $request->role;
            $query->where('role', $request->role);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $activeFilters['status'] = $request->status;
            $query->where('active', $request->status === 'active' ? 1 : 0);
        }

        // Apply agreement status filter
        if ($request->filled('agreement')) {
            $activeFilters['agreement'] = $request->agreement;
            $query->where('agreement_status', $request->agreement);
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $activeFilters['date_from'] = $request->date_from;
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $activeFilters['date_to'] = $request->date_to;
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Apply minimum students filter
        if ($request->filled('min_students')) {
            $activeFilters['min_students'] = $request->min_students;
            $query->has('students', '>=', $request->min_students);
        }

        // Apply minimum applications filter
        if ($request->filled('min_applications')) {
            $activeFilters['min_applications'] = $request->min_applications;
            $query->has('applications', '>=', $request->min_applications);
        }

        // Apply sorting
        switch ($request->sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'business_asc':
                $query->orderBy('business_name', 'asc');
                break;
            case 'business_desc':
                $query->orderBy('business_name', 'desc');
                break;
            case 'created_at_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'created_at_desc':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        // Handle export
        if ($request->filled('export')) {
            $users = $query->get();
            return $this->exportUsers($users, $request->export);
        }

        // Get total count for stats
        $totalUsers = (clone $query)->count();

        // Get parents for filter (admins and agents)
        $parents = User::whereIn('role', ['admin', 'agent'])
            ->where('active', 1)
            ->orderBy('business_name')
            ->get(['id', 'business_name', 'name', 'role']);

        // Check if any filters are applied
        $hasFilters = !empty($activeFilters);

        // Paginate by role
        $role = $request->role;

        if ($role === 'admin') {
            $admins = (clone $query)->where('role', 'admin')
                ->withCount(['students', 'applications'])
                ->paginate(20, ['*'], 'admin_page')
                ->withQueryString();
            $agents = collect();
            $staffs = collect();
        } elseif ($role === 'agent') {
            $agents = (clone $query)->where('role', 'agent')
                ->withCount(['students', 'applications'])
                ->paginate(20, ['*'], 'agent_page')
                ->withQueryString();
            $admins = collect();
            $staffs = collect();
        } elseif ($role === 'staff') {
            $staffs = (clone $query)->where('role', 'staff')
                ->withCount(['students', 'applications'])
                ->paginate(20, ['*'], 'staff_page')
                ->withQueryString();
            $admins = collect();
            $agents = collect();
        } else {
            // Get all roles separately with their own pagination
            $admins = (clone $query)->where('role', 'admin')
                ->withCount(['students', 'applications'])
                ->paginate(20, ['*'], 'admin_page')
                ->withQueryString();

            $agents = (clone $query)->where('role', 'agent')
                ->withCount(['students', 'applications'])
                ->paginate(20, ['*'], 'agent_page')
                ->withQueryString();

            $staffs = (clone $query)->where('role', 'staff')
                ->withCount(['students', 'applications'])
                ->paginate(20, ['*'], 'staff_page')
                ->withQueryString();
        }

        return view('admin.users.index', compact(
            'admins',
            'agents',
            'staffs',
            'parents',
            'totalUsers',
            'hasFilters',
            'activeFilters'
        ));
    }

    /**
     * Export Users to CSV, Excel, or PDF
     */
    private function exportUsers($users, $type)
    {
        $filename = 'users_' . date('Y-m-d_H-i-s');

        if ($type === 'csv') {
            $filename .= '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            $callback = function () use ($users) {
                $file = fopen('php://output', 'w');

                // Add UTF-8 BOM for Excel compatibility
                fputs($file, "\xEF\xBB\xBF");

                // Add headers
                fputcsv($file, [
                    'ID',
                    'Business Name',
                    'Owner Name',
                    'User Name',
                    'Email',
                    'Contact',
                    'Address',
                    'Role',
                    'Status',
                    'Agreement Status',
                    'Parent Company',
                    'Students Count',
                    'Applications Count',
                    'Created At',
                    'Last Updated'
                ]);

                // Add data rows
                foreach ($users as $user) {
                    fputcsv($file, [
                        $user->id,
                        $user->business_name,
                        $user->owner_name ?? 'N/A',
                        $user->name,
                        $user->email,
                        $user->contact ?? 'N/A',
                        $user->address ?? 'N/A',
                        ucfirst($user->role),
                        $user->active ? 'Active' : 'Inactive',
                        str_replace('_', ' ', ucfirst($user->agreement_status)),
                        $user->parent ? $user->parent->business_name : 'N/A',
                        $user->students_count ?? $user->students()->count(),
                        $user->applications_count ?? $user->applications()->count(),
                        $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : 'N/A',
                        $user->updated_at ? $user->updated_at->format('Y-m-d H:i:s') : 'N/A'
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        if ($type === 'excel') {
            $filename .= '.xls';
            $headers = [
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            $callback = function () use ($users) {
                $file = fopen('php://output', 'w');

                // Add UTF-8 BOM
                fputs($file, "\xEF\xBB\xBF");

                // Add headers with tab separation for better Excel compatibility
                fputcsv($file, [
                    'ID',
                    'Business Name',
                    'Owner Name',
                    'User Name',
                    'Email',
                    'Contact',
                    'Address',
                    'Role',
                    'Status',
                    'Agreement Status',
                    'Parent Company',
                    'Students Count',
                    'Applications Count',
                    'Created At',
                    'Last Updated'
                ], "\t");

                // Add data rows
                foreach ($users as $user) {
                    fputcsv($file, [
                        $user->id,
                        $user->business_name,
                        $user->owner_name ?? 'N/A',
                        $user->name,
                        $user->email,
                        $user->contact ?? 'N/A',
                        $user->address ?? 'N/A',
                        ucfirst($user->role),
                        $user->active ? 'Active' : 'Inactive',
                        str_replace('_', ' ', ucfirst($user->agreement_status)),
                        $user->parent ? $user->parent->business_name : 'N/A',
                        $user->students_count ?? $user->students()->count(),
                        $user->applications_count ?? $user->applications()->count(),
                        $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : 'N/A',
                        $user->updated_at ? $user->updated_at->format('Y-m-d H:i:s') : 'N/A'
                    ], "\t");
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        if ($type === 'pdf') {
            // For PDF export, you'll need to install: composer require barryvdh/laravel-dompdf
            $html = view('exports.users_pdf', compact('users'))->render();
            $pdf = app('dompdf.wrapper');
            $pdf->loadHTML($html);
            return $pdf->download($filename . '.pdf');
        }

        return redirect()->back()->with('error', 'Export type not supported');
    }

    /**
     * SHOW USER DETAILS
     */
    public function show(User $user)
    {
        if (!in_array(Auth::user()->role, ['admin', 'superadmin'])) {
            abort(403);
        }

        $students = $user->students()->withCount('applications')->get();

        $applications = Application::whereIn('student_id', $students->pluck('id'))
            ->with(['student', 'course.university'])
            ->get();

        $studentActivities = Activity::where('user_id', $user->id)
            ->whereIn('type', ['student_added', 'student_deleted'])
            ->latest()->take(7)->get();

        $documentActivities = Activity::where('user_id', $user->id)
            ->whereIn('type', ['document_uploaded', 'document_deleted'])
            ->latest()->take(5)->get();

        $applicationActivities = Activity::where('user_id', $user->id)
            ->whereIn('type', ['application_submitted', 'application_withdrawn'])
            ->latest()->take(5)->get();

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
     * CREATE
     */
    public function create()
    {
        $parents = User::whereIn('role', ['admin', 'agent'])
            ->where('active', 1)
            ->orderBy('business_name')
            ->get();

        return view('admin.users.create', compact('parents'));
    }

    /**
     * STORE USER
     */
    public function store(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'contact' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role' => 'required|in:admin,agent,staff,university,student',
            'status' => 'nullable|boolean',
            'password' => 'required|min:6|confirmed',
            'parent_id' => 'required_if:role,staff|nullable|exists:users,id',
            'agreement_status' => 'nullable|in:not_uploaded,uploaded,verified',

            'business_logo' => 'nullable|file|max:20480',
            'registration'  => 'nullable|file|max:20480',
            'pan'           => 'nullable|file|max:20480',
            'agreement_file' => 'nullable|file|max:20480',
        ]);

        $user = new User();

        $this->assignUserFields($user, $request);

        $user->password = Hash::make($request->password);
        $user->active = $request->has('status') ? 1 : 0;

        // Set contact and address
        if ($request->filled('contact')) {
            $user->contact = $request->contact;
        }
        if ($request->filled('address')) {
            $user->address = $request->address;
        }

        // Set parent only for staff role
        if ($request->role === 'staff' && $request->filled('parent_id')) {
            $user->parent_id = $request->parent_id;
        }

        // Save first to generate ID
        $user->save();

        // Upload files via service
        if ($request->hasFile('business_logo')) {
            $user->business_logo = FileUploadService::uploadAgentFile($request, $user, 'business_logo', 'logo');
        }

        if ($request->hasFile('registration')) {
            $user->registration = FileUploadService::uploadAgentFile($request, $user, 'registration', 'registration');
        }

        if ($request->hasFile('pan')) {
            $user->pan = FileUploadService::uploadAgentFile($request, $user, 'pan', 'pan');
        }

        if ($request->hasFile('agreement_file')) {
            $user->agreement_file = FileUploadService::uploadAgentFile($request, $user, 'agreement_file', 'agreement');
            $user->agreement_status = 'uploaded';
            $user->notify(new AgreementSubmitted($user));
        } else {
            $user->agreement_status = $request->agreement_status ?? 'not_uploaded';
        }

        $user->save();

        // Notify admin (user with ID 2)
        $admin = User::find(2);
        if ($admin) {
            Notification::send($admin, new UserRegistered($user));
        }

        return redirect()->route('admin.users.show', $user->slug)
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        // Get all active admins and agents for parent selection (excluding current user)
        $parents = User::whereIn('role', ['admin', 'agent'])
            ->where('active', 1)
            ->where('id', '!=', $user->id)
            ->orderBy('business_name')
            ->get(['id', 'business_name', 'name', 'role']);

        return view('admin.users.edit', compact('user', 'parents'));
    }

    /**
     * UPDATE USER
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'contact' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role' => 'required|in:admin,agent,staff,university,student',
            'status' => 'nullable|boolean',
            'password' => 'nullable|min:6|confirmed',
            'parent_id' => 'nullable|exists:users,id',
            'agreement_status' => 'nullable|in:not_uploaded,uploaded,verified',

            'business_logo' => 'nullable|file|max:20480',
            'registration'  => 'nullable|file|max:20480',
            'pan'           => 'nullable|file|max:20480',
            'agreement_file' => 'nullable|file|max:20480',
        ]);

        $this->assignUserFields($user, $request);

        $user->active = $request->has('status') ? 1 : 0;

        // Update contact and address
        if ($request->filled('contact')) {
            $user->contact = $request->contact;
        }
        if ($request->filled('address')) {
            $user->address = $request->address;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Update parent only for staff and if provided
        if ($request->role === 'staff' && $request->filled('parent_id')) {
            $user->parent_id = $request->parent_id;
        } elseif ($request->role !== 'staff') {
            $user->parent_id = null;
        }

        // Upload via service
        if ($request->hasFile('business_logo')) {
            $oldFile = $user->business_logo;
            $user->business_logo = FileUploadService::uploadAgentFile($request, $user, 'business_logo', 'logo');
            if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                Storage::disk('public')->delete($oldFile);
            }
        }

        if ($request->hasFile('registration')) {
            $oldFile = $user->registration;
            $user->registration = FileUploadService::uploadAgentFile($request, $user, 'registration', 'registration');
            if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                Storage::disk('public')->delete($oldFile);
            }
        }

        if ($request->hasFile('pan')) {
            $oldFile = $user->pan;
            $user->pan = FileUploadService::uploadAgentFile($request, $user, 'pan', 'pan');
            if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                Storage::disk('public')->delete($oldFile);
            }
        }

        if ($request->hasFile('agreement_file')) {
            $oldFile = $user->agreement_file;
            $user->agreement_file = FileUploadService::uploadAgentFile($request, $user, 'agreement_file', 'agreement');
            $user->agreement_status = 'uploaded';
            if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                Storage::disk('public')->delete($oldFile);
            }
            $user->notify(new AgreementSubmitted($user));
        } elseif ($request->has('agreement_status') && !$request->hasFile('agreement_file')) {
            $user->agreement_status = $request->agreement_status;
        }

        $user->save();

        return redirect()->route('admin.users.show', $user->slug)
            ->with('success', 'User updated successfully.');
    }

    /**
     * DELETE USER
     */
    public function destroy(User $user)
    {
        if (!in_array(Auth::id(), [1])) {
            return back()->with('error', 'Only super admin can delete.');
        }

        // Prevent self-deletion
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        foreach (['business_logo', 'registration', 'pan', 'agreement_file'] as $file) {
            if ($user->$file && Storage::disk('public')->exists($user->$file)) {
                Storage::disk('public')->delete($user->$file);
            }
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * WAITING USERS
     */
    public function waiting()
    {
        $waitingUsers = User::where('active', 0)->get();

        $agreementUsers = User::where('role', 'agent')
            ->whereIn('agreement_status', ['uploaded', 'not_uploaded'])
            ->get();

        return view('admin.users.waiting', compact('waitingUsers', 'agreementUsers'));
    }

    /**
     * APPROVE USER
     */
    public function approve(User $user)
    {
        $user->active = 1;
        $user->save();

        $user->notify(new UserApproved());

        return back()->with('success', 'User approved.');
    }

    /**
     * DELETE AGREEMENT
     */
    public function deleteAgreement(User $user)
    {
        if ($user->agreement_file && Storage::disk('public')->exists($user->agreement_file)) {
            Storage::disk('public')->delete($user->agreement_file);
        }

        $user->update([
            'agreement_file' => null,
            'agreement_status' => 'not_uploaded'
        ]);

        return response()->json(['success' => true, 'message' => 'Agreement deleted successfully.']);
    }

    /**
     * VERIFY AGREEMENT
     */
    public function verifyAgreement(User $user)
    {
        $user->agreement_status = 'verified';
        $user->save();

        $user->notify(new AgreementVerified($user));

        return redirect()->route('admin.users.waiting')->with('success', 'Agreement verified.');
    }

    /**
     * AGENT STUDENTS
     */
    public function students(User $agent)
    {
        if ($agent->role !== 'agent') abort(404);

        $students = Student::where('agent_id', $agent->id)
            ->withCount('applications')
            ->latest()
            ->paginate(10);

        return view('admin.users.students', compact('agent', 'students'));
    }

    /**
     * AGENT APPLICATIONS
     */
    public function applications(User $agent)
    {
        if ($agent->role !== 'agent') abort(404);

        $studentIds = $agent->students()->pluck('id');

        $applications = Application::whereIn('student_id', $studentIds)
            ->with(['student', 'course.university'])
            ->latest()
            ->paginate(10);

        return view('admin.users.applications', compact('agent', 'applications'));
    }

    /**
     * Assign Fields
     */
    private function assignUserFields(User $user, Request $request)
    {
        $user->business_name = $request->business_name;
        $user->owner_name = $request->owner_name;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        // Generate slug if business name changed
        $slug = strtolower(str_replace(' ', '-', $request->business_name));
        // Add unique identifier if needed
        $originalSlug = $slug;
        $counter = 1;
        while (User::where('slug', $slug)->where('id', '!=', $user->id)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }
        $user->slug = $slug;
    }

    /**
     * Get parent users for staff creation (admins and agents only)
     */
    public function getParents()
    {
        try {
            $parents = User::whereIn('role', ['admin', 'agent'])
                ->where('active', 1)
                ->select('id', 'name', 'business_name', 'role', 'email')
                ->orderBy('role')
                ->orderBy('business_name')
                ->get();

            return response()->json(['parents' => $parents]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
