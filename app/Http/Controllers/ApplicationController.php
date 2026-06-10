<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\ApplicationStatus;
use App\Models\Course;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use App\Notifications\ApplicationMessageAdded;
use App\Notifications\ApplicationStatusUpdated;
use App\Notifications\ApplicationSubmitted;
use App\Notifications\ApplicationWithdrawn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ApplicationController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Application::class, 'application');
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Application::with(['student', 'university', 'course', 'status']);

        if ($user->is_agent) {
            $query->where('agent_id', $user->id);
        } elseif ($user->is_staff) {
            $query->accessible();
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', fn($q2) => $q2->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('course', fn($q3) => $q3->where('title', 'like', "%{$search}%"))
                    ->orWhereHas('university', fn($q4) => $q4->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) $query->where('application_status_id', $request->status);
        if ($request->filled('status_filter')) $query->where('application_status_id', $request->status_filter);
        if ($request->filled('university_filter')) $query->where('university_id', $request->university_filter);
        if ($request->filled('agent_filter') && ($user->is_admin || $user->is_admin_staff)) $query->where('agent_id', $request->agent_filter);
        if ($request->filled('country_filter')) {
            $query->whereHas('university', fn($q) => $q->where('country', $request->country_filter));
        }

        $statuses = ApplicationStatus::where('is_active', 1)
            ->orderBy('sort_order')->get();

        $universities = University::whereHas('applications', fn($q) => $user->is_agent ? $q->where('agent_id', $user->id) : $q)
            ->withCount(['applications' => fn($q) => $user->is_agent ? $q->where('agent_id', $user->id) : $q])
            ->orderBy('name')->get();

        $agents = ($user->is_admin || $user->is_admin_staff)
            ? User::agents()->whereHas('students')->withCount('students')->orderBy('business_name')->get()
            : collect();

        $countries = collect(Application::whereHas('student')->whereNotNull('university_id')
            ->join('universities', 'applications.university_id', '=', 'universities.id')
            ->select('universities.country', \DB::raw('COUNT(*) as application_count'))
            ->whereNotNull('universities.country')
            ->when($user->is_agent, fn($q) => $q->where('applications.agent_id', $user->id))
            ->groupBy('universities.country')->orderBy('application_count', 'DESC')->get())
            ->map(fn($i) => (object) ['name' => $i->country, 'count' => (int) $i->application_count]);

        $approvedStatusId = ApplicationStatus::where('name', 'Visa Approved')->value('id');
        $rejectedStatusId = ApplicationStatus::where('name', 'Visa Rejected')->value('id');
        $lostStatusId = ApplicationStatus::where('name', 'Lost')->value('id');
        $baseCount = fn($id) => ($user->is_agent ? Application::where('agent_id', $user->id) : Application::query())->where('application_status_id', $id)->count();
        $acceptedCount = $baseCount($approvedStatusId);
        $rejectedCount = $baseCount($rejectedStatusId);
        $lostCount = $baseCount($lostStatusId);

        $applications = $query->latest()->paginate(20)->withQueryString();

        $role = $user->role;

        return view('shared.applications.index', compact(
            'applications', 'statuses', 'universities', 'agents', 'countries',
            'acceptedCount', 'rejectedCount', 'lostCount', 'role'
        ));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $universities = University::with('courses')->get();

        if ($user->is_agent) {
            $selectedUniversityId = session('quick_start_university_id') ?? $request->query('university_id');
            $selectedCourseId = session('quick_start_course_id') ?? $request->query('course_id');
            session()->forget(['quick_start_university_id', 'quick_start_course_id']);

            $courses = $selectedUniversityId ? Course::where('university_id', $selectedUniversityId)->get() : collect();

            if ($selectedCourseId && !$selectedUniversityId) {
                $course = Course::find($selectedCourseId);
                if ($course) {
                    $selectedUniversityId = $course->university_id;
                    $courses = Course::where('university_id', $selectedUniversityId)->get();
                }
            }

            if ($request->has('student_id')) {
                $student = Student::where('id', $request->student_id)->where('agent_id', $user->id)->firstOrFail();
                return view('shared.applications.create', compact('student', 'universities', 'courses', 'selectedUniversityId', 'selectedCourseId'));
            }

            $requiredTypes = \App\Http\Controllers\DocumentController::allDocumentTypes();
            $students = Student::where('agent_id', $user->id)
                ->with(['documents' => fn($q) => $q->whereIn('document_type', $requiredTypes)])
                ->get()->filter(fn($s) => empty(array_diff($requiredTypes, $s->documents->pluck('document_type')->toArray())));

            return view('shared.applications.create', compact('students', 'courses', 'universities', 'selectedUniversityId', 'selectedCourseId'));
        }

        $selectedStudent = $request->has('student_id') ? Student::find($request->student_id) : null;
        $students = Student::whereHas('documents')->get();
        $courses = collect();

        return view('shared.applications.create', compact('students', 'universities', 'courses', 'selectedStudent'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'student_id' => 'required|exists:students,id',
            'university_id' => 'required|exists:universities,id',
            'course_id' => 'nullable|exists:courses,id',
            'sop_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:15360',
        ]);

        $student = $user->is_agent
            ? Student::where('agent_id', $user->id)->findOrFail($request->student_id)
            : Student::findOrFail($request->student_id);

        if (Application::where('student_id', $student->id)->where('university_id', $request->university_id)->where('course_id', $request->course_id)->exists()) {
            return back()->withErrors(['course_id' => 'Duplicate application.'])->withInput();
        }

        $defaultStatus = ApplicationStatus::orderBy('sort_order')->first();

        $application = Application::create([
            'student_id' => $student->id,
            'university_id' => $request->university_id,
            'course_id' => $request->course_id,
            'agent_id' => $student->agent_id,
            'application_status_id' => $defaultStatus?->id,
        ]);

        $application->application_number = $student->agent_id . '-' . $student->id . '-' . $request->university_id . '-' . ($request->course_id ?? 0) . '-' . $application->id;

        if ($request->hasFile('sop_file')) {
            $folder = "agents/{$student->agent_id}/{$student->id}/applications";
            $application->sop_file = $request->file('sop_file')->storeAs($folder, 'sop_' . time() . '.' . $request->file('sop_file')->getClientOriginalExtension(), 'public');
        }

        $application->save();

        if ($user->is_agent) {
            User::admins()->first()?->notify(new ApplicationSubmitted($application));
        }

        return redirect()->route($user->role . '.applications.index')
            ->with('success', 'Application created successfully.');
    }

    public function show(Application $application)
    {
        $statuses = ApplicationStatus::where('is_active', 1)->orderBy('sort_order')->get();
        $application->load(['student', 'university', 'course', 'documents', 'messages.user', 'status']);

        return view('shared.applications.show', compact('application', 'statuses'));
    }

    public function edit(Application $application)
    {
        $user = Auth::user();
        $application->load(['student', 'university', 'course', 'status']);

        $universities = University::all();
        $statuses = ($user->is_admin || $user->is_admin_staff) ? ApplicationStatus::orderBy('sort_order')->get() : collect();

        if ($user->is_agent) {
            $courses = $application->university_id ? Course::where('university_id', $application->university_id)->get() : collect();
            $students = Student::where('agent_id', $user->id)->get();
            return view('shared.applications.edit', compact('application', 'universities', 'students', 'courses', 'statuses'));
        }

        $courses = Course::all();
        return view('shared.applications.edit', compact('application', 'universities', 'courses', 'statuses'));
    }

    public function update(Request $request, Application $application)
    {
        $user = Auth::user();

        $rules = [
            'university_id' => 'required|exists:universities,id',
            'course_id' => 'nullable|exists:courses,id',
            'sop_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:15360',
        ];

        $canManageStatus = $user->is_admin || $user->is_admin_staff;

        if ($canManageStatus) {
            $rules['application_status_id'] = 'required|exists:application_statuses,id';
        }

        if ($user->is_agent) {
            $rules['student_id'] = 'required|exists:students,id';
        }

        $data = $request->validate($rules);
        $oldStatus = $application->application_status_id;

        $application->update([
            'university_id' => $request->university_id,
            'course_id' => $request->course_id,
            'application_status_id' => $canManageStatus ? $request->application_status_id : $application->application_status_id,
        ]);

        if ($user->is_agent && $request->has('student_id')) {
            $application->student_id = $request->student_id;
        }

        if ($request->hasFile('sop_file')) {
            if ($application->sop_file && Storage::disk('public')->exists($application->sop_file)) {
                Storage::disk('public')->delete($application->sop_file);
            }
            $student = $application->student;
            $folder = "agents/{$student->agent_id}/{$student->id}/applications";
            $application->sop_file = $request->file('sop_file')->storeAs($folder, 'sop_' . time() . '.' . $request->file('sop_file')->getClientOriginalExtension(), 'public');
        }

        $application->save();

        if ($canManageStatus && $oldStatus != $request->application_status_id) {
            User::notifyAgent($application->student->agent_id, new ApplicationStatusUpdated($application, $user));
        }

        return back()->with('success', 'Application updated successfully.');
    }

    public function updateStatus(Request $request, Application $application)
    {
        $this->authorize('updateStatus', $application);

        $request->validate(['application_status_id' => 'required|exists:application_statuses,id']);
        $oldStatus = $application->application_status_id;
        $application->update(['application_status_id' => $request->application_status_id]);

        \App\Models\ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'from_status_id' => $oldStatus,
            'to_status_id' => $request->application_status_id,
            'changed_by' => Auth::id(),
            'reason' => $request->reason,
        ]);

        if ($oldStatus != $request->application_status_id) {
            User::notifyAgent($application->student->agent_id, new ApplicationStatusUpdated($application, Auth::user()));
        }

        return back()->with('success', 'Status updated.');
    }

    public function withdraw(Request $request, Application $application)
    {
        $this->authorize('withdraw', $application);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $lostStatusId = ApplicationStatus::where('name', 'Lost')->value('id');
        $application->update([
            'withdrawn_at' => now(),
            'withdraw_reason' => $request->reason ?? 'No reason given',
            'application_status' => 'Withdrawn',
            'application_status_id' => $lostStatusId,
        ]);

        $admin = User::admins()->first();
        $admin?->notify(new ApplicationWithdrawn($application));

        return redirect()->route(Auth::user()->role . '.applications.index')
            ->with('success', 'Application withdrawn.');
    }

    public function addMessage(Request $request, Application $application)
    {
        $request->validate([
            'message' => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|max:10240',
        ]);

        if (!$request->message && !$request->hasFile('attachment')) {
            return back()->withErrors('Write a message or attach a file.');
        }

        $user = Auth::user();
        $type = $user->is_admin ? 'admin' : 'agent';
        $filePath = null;

        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->storeAs(
                'application_messages/' . $application->id,
                time() . '_' . $request->file('attachment')->getClientOriginalName(),
                'public'
            );
        }

        $message = $application->messages()->create([
            'user_id' => $user->id,
            'type' => $type,
            'message' => $request->message,
            'file_path' => $filePath,
        ]);

        if ($type === 'agent') {
            User::notifyAdmins(new ApplicationMessageAdded($application, $message));
        } else {
            $application->agent?->notify(new ApplicationMessageAdded($application, $message));
        }

        return back()->with('success', 'Message sent.');
    }

    public function deleteMessage(Application $application, $messageId)
    {
        $message = $application->messages()->findOrFail($messageId);

        $user = Auth::user();
        if (!$user->is_admin && $user->id !== $message->user_id) {
            abort(403);
        }

        $message->delete();
        return back()->with('success', 'Message deleted.');
    }

    public function destroy(Application $application)
    {
        if ($application->sop_file && Storage::disk('public')->exists($application->sop_file)) {
            Storage::disk('public')->delete($application->sop_file);
        }
        $application->delete();

        return redirect()->route(Auth::user()->role . '.applications.index')
            ->with('success', 'Application deleted.');
    }

    public function export(Request $request)
    {
        $query = Application::with('student', 'university', 'course', 'status');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', fn($q2) => $q2->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"))
                    ->orWhereHas('course', fn($q3) => $q3->where('title', 'like', "%{$search}%"))
                    ->orWhereHas('university', fn($q4) => $q4->where('name', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('status')) $query->where('application_status_id', $request->status);
        if ($request->filled('university_filter')) $query->where('university_id', $request->university_filter);

        $apps = $query->get();
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="applications-' . now()->format('Y-m-d') . '.csv"'];
        $callback = function () use ($apps) {
            $f = fopen('php://output', 'w');
            fputs($f, "\xEF\xBB\xBF");
            fputcsv($f, ['ID', 'Application #', 'Student', 'Course', 'University', 'Status', 'Agent', 'Applied Date']);
            foreach ($apps as $a) {
                fputcsv($f, [$a->id, $a->application_number ?? '—', $a->student?->full_name ?? '—', $a->course?->title ?? '—', $a->university?->name ?? '—', $a->status?->name ?? '—', $a->agent?->business_name ?? '—', $a->created_at->format('Y-m-d')]);
            }
            fclose($f);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function getCourses($universityId)
    {
        return Course::where('university_id', $universityId)->select('id', 'title')->get();
    }

    public function forStudent(Student $student)
    {
        $user = Auth::user();
        if ($user->is_agent && $student->agent_id !== $user->id) {
            abort(403);
        }

        $statuses = ApplicationStatus::orderBy('sort_order')->get();
        $query = $student->applications()->with('university', 'course', 'status');

        if ($user->is_agent) {
            $query->where('agent_id', $user->id);
        }

        $applications = $query->latest()->get();

        return view('shared.applications.for_student', compact('student', 'applications', 'statuses'));
    }

    public function quickStart(Request $request)
    {
        if ($request->has('course_id')) {
            session(['quick_start_course_id' => $request->course_id]);
            $course = Course::find($request->course_id);
            if ($course) session(['quick_start_university_id' => $course->university_id]);
        } elseif ($request->has('university_id')) {
            session(['quick_start_university_id' => $request->university_id]);
            session()->forget('quick_start_course_id');
        }

        return redirect()->route('agent.applications.create');
    }
}
