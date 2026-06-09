<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Activity;
use App\Models\Student;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use App\Contracts\FileUploadServiceInterface;

use App\Notifications\UserRegistered;
use App\Notifications\UserApproved;
use App\Notifications\AgreementSubmitted;
use App\Notifications\AgreementVerified;

class UserController extends Controller
{
    public function __construct(
        private readonly FileUploadServiceInterface $fileUploadService,
    ) {}

    private function getUserBySlug($slug)
    {
        return User::where('slug', $slug)->firstOrFail();
    }

    private function assignUserFields(User $user, Request $request)
    {
        $user->business_name = $request->business_name;
        $user->owner_name = $request->owner_name;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        $slug = strtolower(str_replace(' ', '-', $request->business_name));
        $originalSlug = $slug;
        $counter = 1;
        while (User::where('slug', $slug)->where('id', '!=', $user->id)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }
        $user->slug = $slug;
    }

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

                fputs($file, "\xEF\xBB\xBF");

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

                fputs($file, "\xEF\xBB\xBF");

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
            $html = view('exports.users_pdf', compact('users'))->render();
            $pdf = app('dompdf.wrapper');
            $pdf->loadHTML($html);
            return $pdf->download($filename . '.pdf');
        }

        return redirect()->back()->with('error', 'Export type not supported');
    }

    public function index(Request $request)
    {
        if (!Auth::user()->is_admin) {
            abort(403);
        }

        $query = User::query();
        $query->withCount(['students', 'applications']);

        $activeFilters = [];

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

        if ($request->filled('role')) {
            $activeFilters['role'] = $request->role;
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $activeFilters['status'] = $request->status;
            $query->where('active', $request->status === 'active' ? 1 : 0);
        }

        if ($request->filled('agreement')) {
            $activeFilters['agreement'] = $request->agreement;
            $query->where('agreement_status', $request->agreement);
        }

        if ($request->filled('date_from')) {
            $activeFilters['date_from'] = $request->date_from;
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $activeFilters['date_to'] = $request->date_to;
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('min_students')) {
            $activeFilters['min_students'] = $request->min_students;
            $query->has('students', '>=', $request->min_students);
        }

        if ($request->filled('min_applications')) {
            $activeFilters['min_applications'] = $request->min_applications;
            $query->has('applications', '>=', $request->min_applications);
        }

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
                $query->orderByRaw('(COALESCE(students_count, 0) + COALESCE(applications_count, 0)) DESC');
        }

        if ($request->filled('export')) {
            $users = $query->get();
            return $this->exportUsers($users, $request->export);
        }

        $totalUsers = (clone $query)->count();

        $parents = User::whereIn('role', ['admin', 'agent'])
            ->where('active', 1)
            ->orderBy('business_name')
            ->get(['id', 'business_name', 'name', 'role']);

        $hasFilters = !empty($activeFilters);

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
                ->paginate(100, ['*'], 'agent_page')
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
            $admins = (clone $query)->where('role', 'admin')
                ->withCount(['students', 'applications'])
                ->paginate(20, ['*'], 'admin_page')
                ->withQueryString();

            $agents = (clone $query)->where('role', 'agent')
                ->withCount(['students', 'applications'])
                ->paginate(100, ['*'], 'agent_page')
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

    public function show(User $user)
    {
        $auth = Auth::user();

        if (!$auth->is_admin) {
            if ($auth->id !== $user->id) {
                abort(403, 'Unauthorized access.');
            }

            $students = $user->is_staff
                ? $user->parent->students()->withCount('applications')->get()
                : $user->students()->withCount('applications')->get();

            $applications = Application::whereIn('student_id', $students->pluck('id'))
                ->with(['student', 'course.university'])
                ->get();

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

            return view('shared.users.show', compact(
                'user',
                'students',
                'applications',
                'staffMembers',
                'studentActivities',
                'documentActivities',
                'applicationActivities'
            ));
        }

        $students = $user->students()->withCount('applications')->get();

        $applications = Application::whereIn('student_id', $students->pluck('id'))
            ->with(['student', 'course.university'])
            ->get();

        $studentActivities = Activity::where('user_id', $user->id)
            ->whereIn('type', ['student_added', 'student_deleted'])
            ->latest()->take(5)->get();

        $documentActivities = Activity::where('user_id', $user->id)
            ->whereIn('type', ['document_uploaded', 'document_deleted'])
            ->latest()->take(5)->get();

        $applicationActivities = Activity::where('user_id', $user->id)
            ->whereIn('type', ['application_submitted', 'application_withdrawn'])
            ->latest()->take(5)->get();

        $staffMembers = User::where('parent_id', $user->id)
            ->where('role', 'staff')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('shared.users.show', compact(
            'user',
            'students',
            'applications',
            'studentActivities',
            'documentActivities',
            'applicationActivities',
            'staffMembers'
        ));
    }

    public function create()
    {
        if (!Auth::user()->is_admin) {
            abort(403);
        }

        $parents = User::whereIn('role', ['admin', 'agent'])
            ->where('active', 1)
            ->orderBy('business_name')
            ->get();

        return view('shared.users.create', compact('parents'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->is_admin) {
            abort(403);
        }

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

        if ($request->hasFile('business_logo')) {
            $user->business_logo = $this->fileUploadService->uploadAgentFile($request, $user, 'business_logo', 'logo');
        }

        if ($request->hasFile('registration')) {
            $user->registration = $this->fileUploadService->uploadAgentFile($request, $user, 'registration', 'registration');
        }

        if ($request->hasFile('pan')) {
            $user->pan = $this->fileUploadService->uploadAgentFile($request, $user, 'pan', 'pan');
        }

        if ($request->hasFile('agreement_file')) {
            $user->agreement_file = $this->fileUploadService->uploadAgentFile($request, $user, 'agreement_file', 'agreement');
            $user->agreement_status = 'uploaded';
        } else {
            $user->agreement_status = $request->agreement_status ?? 'not_uploaded';
        }

        $user->save();

        $admin = User::admins()->first();
        if ($admin) {
            Notification::send($admin, new UserRegistered($user));
        }

        return redirect()->route('admin.users.show', $user->slug)
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $auth = Auth::user();

        if (!$auth->is_admin) {
            if ($auth->id !== $user->id) {
                abort(403, 'Unauthorized.');
            }
            return view('shared.users.edit', compact('user'));
        }

        $parents = User::whereIn('role', ['admin', 'agent'])
            ->where('active', 1)
            ->where('id', '!=', $user->id)
            ->orderBy('business_name')
            ->get(['id', 'business_name', 'name', 'role']);

        return view('shared.users.edit', compact('user', 'parents'));
    }

    public function update(Request $request, User $user)
    {
        $auth = Auth::user();

        if (!$auth->is_admin) {
            if ($auth->id !== $user->id) {
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
            ]);

            $oldData = [
                'email' => $user->email,
                'contact' => $user->contact,
                'address' => $user->address,
            ];

            $user->email   = $request->email;
            $user->contact = $request->contact;
            $user->address = $request->address;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            if (!$user->slug) {
                $user->slug = strtolower(str_replace(' ', '-', $user->business_name));
            }

            $user->save();

            $user->business_logo = $this->fileUploadService->uploadAgentFile($request, $user, 'business_logo', 'logo');
            $user->registration  = $this->fileUploadService->uploadAgentFile($request, $user, 'registration', 'registration');
            $user->pan           = $this->fileUploadService->uploadAgentFile($request, $user, 'pan', 'pan');

            $user->save();

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

        $user->active = $request->input('status', 0) == 1 ? 1 : 0;

        if ($request->filled('contact')) {
            $user->contact = $request->contact;
        }
        if ($request->filled('address')) {
            $user->address = $request->address;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->role === 'staff' && $request->filled('parent_id')) {
            $user->parent_id = $request->parent_id;
        } elseif ($request->role !== 'staff') {
            $user->parent_id = null;
        }

        if ($request->hasFile('business_logo')) {
            $oldFile = $user->business_logo;
            $user->business_logo = $this->fileUploadService->uploadAgentFile($request, $user, 'business_logo', 'logo');
            if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                Storage::disk('public')->delete($oldFile);
            }
        }

        if ($request->hasFile('registration')) {
            $oldFile = $user->registration;
            $user->registration = $this->fileUploadService->uploadAgentFile($request, $user, 'registration', 'registration');
            if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                Storage::disk('public')->delete($oldFile);
            }
        }

        if ($request->hasFile('pan')) {
            $oldFile = $user->pan;
            $user->pan = $this->fileUploadService->uploadAgentFile($request, $user, 'pan', 'pan');
            if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                Storage::disk('public')->delete($oldFile);
            }
        }

        if ($request->hasFile('agreement_file')) {
            $oldFile = $user->agreement_file;
            $user->agreement_file = $this->fileUploadService->uploadAgentFile($request, $user, 'agreement_file', 'agreement');
            $user->agreement_status = 'uploaded';
            if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                Storage::disk('public')->delete($oldFile);
            }
        } elseif ($request->has('agreement_status') && !$request->hasFile('agreement_file')) {
            $user->agreement_status = $request->agreement_status;
        }

        $user->save();

        return redirect()->route('admin.users.show', $user->slug)
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if (!Auth::user()->is_admin) {
            return back()->with('error', 'Unauthorized action.');
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->is_admin) {
            return back()->with('error', 'Super admin cannot be deleted.');
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

    // ================================================
    // ADMIN-ONLY METHODS
    // ================================================

    public function waiting()
    {
        if (!Auth::user()->is_admin) {
            abort(403);
        }

        $waitingUsers = User::where('active', 0)->get();

        $agreementUsers = User::where('role', 'agent')
            ->whereIn('agreement_status', ['uploaded', 'not_uploaded'])
            ->get();

        return view('admin.users.waiting', compact('waitingUsers', 'agreementUsers'));
    }

    public function approve(User $user)
    {
        if (!Auth::user()->is_admin) {
            abort(403);
        }

        $user->active = 1;
        $user->save();

        $user->notify(new UserApproved());

        return back()->with('success', 'User approved.');
    }

    public function deleteAgreement(User $user)
    {
        if (!Auth::user()->is_admin) {
            abort(403);
        }

        try {
            if ($user->agreement_file && Storage::disk('public')->exists($user->agreement_file)) {
                Storage::disk('public')->delete($user->agreement_file);
            }

            $user->update([
                'agreement_file' => null,
                'agreement_status' => 'not_uploaded'
            ]);

            return redirect()->back()->with('success', 'Agreement deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Agreement deletion error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Error deleting agreement: ' . $e->getMessage());
        }
    }

    public function verifyAgreement(User $user)
    {
        if (!Auth::user()->is_admin) {
            abort(403);
        }

        $user->agreement_status = 'verified';
        $user->save();

        $user->notify(new AgreementVerified($user));

        return redirect()->route('admin.users.show', $user->slug)
            ->with('success', 'Agreement verified.');
    }

    public function students(User $agent)
    {
        if (!Auth::user()->is_admin) {
            abort(403);
        }

        if ($agent->role !== 'agent') abort(404);

        $students = Student::where('agent_id', $agent->id)
            ->withCount('applications')
            ->latest()
            ->paginate(10);

        return view('admin.users.students', compact('agent', 'students'));
    }

    public function applications(User $agent)
    {
        if (!Auth::user()->is_admin) {
            abort(403);
        }

        if ($agent->role !== 'agent') abort(404);

        $studentIds = $agent->students()->pluck('id');

        $applications = Application::whereIn('student_id', $studentIds)
            ->with(['student', 'course.university'])
            ->latest()
            ->paginate(10);

        return view('admin.users.applications', compact('agent', 'applications'));
    }

    public function getParents()
    {
        if (!Auth::user()->is_admin) {
            abort(403);
        }

        try {
            $parents = User::whereIn('role', ['admin', 'agent'])
                ->where('active', 1)
                ->select('id', 'name', 'business_name', 'owner_name', 'role', 'email')
                ->orderBy('role')
                ->orderBy('business_name')
                ->get();

            return response()->json(['parents' => $parents]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ================================================
    // AGENT-ONLY METHODS
    // ================================================

    public function resetPassword(User $user)
    {
        if (Auth::user()->is_admin) {
            abort(403);
        }

        if (Auth::id() != $user->id) {
            abort(403, 'Unauthorized.');
        }

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

        return back()->with('success', "Password reset successfully. New password: $newPassword");
    }

    public function createStaff()
    {
        if (Auth::user()->is_admin) {
            abort(403);
        }

        $agent = Auth::user();

        $staffCount = User::where('parent_id', $agent->id)
            ->where('role', 'staff')
            ->count();

        $staffLimit = 1;

        if ($staffCount >= $staffLimit) {
            return redirect()->route('agent.users.show', $agent->slug)
                ->with('error', "You have reached the maximum staff limit ({$staffLimit}). Please contact admin to increase limit.");
        }

        return view('shared.staff.create', compact('agent'));
    }

    public function storeStaff(Request $request)
    {
        if (Auth::user()->is_admin) {
            abort(403);
        }

        $agent = Auth::user();

        $staffCount = User::where('parent_id', $agent->id)
            ->where('role', 'staff')
            ->count();

        $staffLimit = 1;

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

        $staff = new User();
        $staff->name = $request->name;
        $staff->email = $request->email;
        $staff->contact = $request->contact;
        $staff->address = $request->address;
        $staff->password = Hash::make($request->password);
        $staff->role = 'staff';
        $staff->parent_id = $agent->id;
        $staff->active = 1;
        $staff->business_name = $agent->business_name . ' - Staff';
        $staff->owner_name = $agent->owner_name;

        $slug = Str::slug($staff->name);
        $originalSlug = $slug;
        $counter = 1;
        while (User::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }
        $staff->slug = $slug;

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
            'agent_id' => $agent->id,
            'staff_id' => $staff->id,
            'staff_email' => $staff->email,
            'ip' => $request->ip()
        ]);

        return redirect()->route('agent.users.show', $agent->slug)
            ->with('success', "Staff member {$staff->name} created successfully.");
    }

    public function editStaff(User $staff)
    {
        if (Auth::user()->is_admin) {
            abort(403);
        }

        $agent = Auth::user();

        if ($staff->parent_id != $agent->id || $staff->role != 'staff') {
            abort(403, 'Unauthorized access.');
        }

        return view('shared.staff.edit', compact('staff', 'agent'));
    }

    public function updateStaff(Request $request, User $staff)
    {
        if (Auth::user()->is_admin) {
            abort(403);
        }

        $agent = Auth::user();

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
            'link' => route('agent.staff.show', $staff->slug),
        ]);

        return redirect()->route('agent.users.show', $agent->slug)
            ->with('success', "Staff member {$staff->name} updated successfully.");
    }

    public function destroyStaff(User $staff)
    {
        if (Auth::user()->is_admin) {
            abort(403);
        }

        $agent = Auth::user();

        if ($staff->parent_id != $agent->id || $staff->role != 'staff') {
            abort(403, 'Unauthorized access.');
        }

        $staffName = $staff->name;
        $staffId = $staff->id;

        $hasStudents = $staff->students()->exists();
        $hasApplications = $staff->applications()->exists();

        if ($hasStudents || $hasApplications) {
            return redirect()->route('agent.users.show', $agent->slug)
                ->with('error', "Cannot delete {$staffName}. They have associated students or applications.");
        }

        $staff->delete();

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

    public function showStaff(User $staff)
    {
        if (Auth::user()->is_admin) {
            abort(403);
        }

        $agent = Auth::user();

        if ($staff->parent_id != $agent->id || $staff->role != 'staff') {
            abort(403, 'Unauthorized access.');
        }

        $activities = Activity::where('user_id', $staff->id)
            ->latest()
            ->take(20)
            ->get();

        return view('shared.staff.show', compact('staff', 'agent', 'activities'));
    }
}
