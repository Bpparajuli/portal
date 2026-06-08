<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Application;
use App\Models\ApplicationStatus;
use App\Models\Student;
use App\Models\University;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Notifications\ApplicationMessageAdded;
use App\Notifications\ApplicationStatusUpdated;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    /**
     * LIST
     */
    public function index(Request $request)
    {
        $query = Application::with([
            'student',
            'university',
            'course',
            'agent',
            'status'
        ])->latest();

        /*
    |--------------------------------------------------------------------------
    | Search Filter
    |--------------------------------------------------------------------------
    */
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($q2) use ($search) {
                    $q2->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })

                    ->orWhereHas('course', function ($q3) use ($search) {
                        $q3->where('title', 'like', "%{$search}%");
                    })

                    ->orWhereHas('university', function ($q4) use ($search) {
                        $q4->where('name', 'like', "%{$search}%");
                    });
            });
        }

        /*
    |--------------------------------------------------------------------------
    | Status Card Filter (Approved / Rejected / Lost)
    |--------------------------------------------------------------------------
    */
        if ($request->filled('status')) {
            $query->where('application_status_id', $request->status);
        }

        /*
    |--------------------------------------------------------------------------
    | Dropdown Status Filter
    |--------------------------------------------------------------------------
    */
        if ($request->filled('status_filter')) {
            $query->where('application_status_id', $request->status_filter);
        }

        /*
    |--------------------------------------------------------------------------
    | University Filter
    |--------------------------------------------------------------------------
    */
        if ($request->filled('university_filter')) {
            $query->where('university_id', $request->university_filter);
        }

        /*
    |--------------------------------------------------------------------------
    | Agent Filter
    |--------------------------------------------------------------------------
    */
        if ($request->filled('agent_filter')) {
            $query->where('agent_id', $request->agent_filter);
        }

        /*
    |--------------------------------------------------------------------------
    | Applied University Country Filter
    |--------------------------------------------------------------------------
    */
        if ($request->filled('country_filter')) {
            $query->whereHas('university', function ($q) use ($request) {
                $q->where('country', $request->country_filter);
            });
        }

        /*
    |--------------------------------------------------------------------------
    | Only show statuses having applications
    |--------------------------------------------------------------------------
    */
        $statuses = ApplicationStatus::where('is_active', 1)
            ->whereHas('applications')
            ->withCount('applications')
            ->orderBy('sort_order')
            ->get();

        /*
    |--------------------------------------------------------------------------
    | Only show universities having applications
    |--------------------------------------------------------------------------
    */
        $universities = University::whereHas('applications')
            ->withCount('applications')
            ->orderBy('name')
            ->get();

        /*
    |--------------------------------------------------------------------------
    | Get all agents (users with agent role) for filter
    |--------------------------------------------------------------------------
    */

        $agents = User::agents()
            ->whereHas('students')
            ->withCount('students')
            ->orderBy('business_name')
            ->get();
        /*
    |--------------------------------------------------------------------------
    | Get countries where universities have applications
    |--------------------------------------------------------------------------
    */
        // Get countries with application counts - UPDATED
        $countries = collect(Application::whereHas('student')
            ->whereNotNull('university_id')
            ->join('universities', 'applications.university_id', '=', 'universities.id')
            ->select('universities.country', DB::raw('COUNT(*) as application_count'))
            ->whereNotNull('universities.country')
            ->groupBy('universities.country')
            ->orderBy('application_count', 'DESC')
            ->get())
            ->map(function ($item) {
                return (object) [
                    'name' => $item->country,
                    'count' => (int) $item->application_count
                ];
            });
        /*
    |--------------------------------------------------------------------------
    | Statistics Count Cards
    |--------------------------------------------------------------------------
    */
        $approvedStatusId = ApplicationStatus::where('name', 'Visa Approved')->value('id');
        $rejectedStatusId = ApplicationStatus::where('name', 'Visa Rejected')->value('id');
        $lostStatusId = ApplicationStatus::where('name', 'Lost')->value('id');

        $acceptedCount = Application::where('application_status_id', $approvedStatusId)->count();
        $rejectedCount = Application::where('application_status_id', $rejectedStatusId)->count();
        $lostCount = Application::where('application_status_id', $lostStatusId)->count();

        /*
    |--------------------------------------------------------------------------
    | Paginated Results
    |--------------------------------------------------------------------------
    */
        $applications = $query
            ->paginate(20)
            ->withQueryString();

        return view('admin.applications.index', compact(
            'applications',
            'statuses',
            'universities',
            'agents',
            'countries',
            'acceptedCount',
            'rejectedCount',
            'lostCount'
        ));
    }
    public function export(Request $request)
    {
        $query = Application::with('student', 'university', 'course', 'status');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', fn($q2) => $q2->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"))
                    ->orWhereHas('course', fn($q3) => $q3->where('title', 'like', "%{$search}%"))
                    ->orWhereHas('university', fn($q4) => $q4->where('name', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('status')) {
            $query->where('application_status_id', $request->status);
        }
        if ($request->filled('university_filter')) {
            $query->where('university_id', $request->university_filter);
        }

        $apps = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="applications-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($apps) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, ['ID', 'Application #', 'Student', 'Course', 'University', 'Status', 'Agent', 'Applied Date']);
            foreach ($apps as $a) {
                fputcsv($file, [
                    $a->id, $a->application_number ?? '—',
                    $a->student?->full_name ?? '—',
                    $a->course?->title ?? '—',
                    $a->university?->name ?? '—',
                    $a->status?->name ?? '—',
                    $a->agent?->business_name ?? '—',
                    $a->created_at->format('Y-m-d'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * CREATE FORM
     */
    public function create(Request $request)
    {
        $selectedStudent = null;

        if ($request->has('student_id')) {
            $selectedStudent = Student::find($request->student_id);
        }

        return view('admin.applications.create', [
            'students' => Student::whereHas('documents')->get(),
            'universities' => University::all(),
            'courses' => Course::all(),
            'selectedStudent' => $selectedStudent,
        ]);
    }

    /**
     * STORE
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'university_id' => 'required|exists:universities,id',
            'course_id' => 'nullable|exists:courses,id',
            'sop_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:15360',
        ]);

        $student = Student::findOrFail($request->student_id);


        // DEFAULT STATUS = first by sort_order
        $defaultStatus = ApplicationStatus::orderBy('sort_order')->first();

        $application = new Application();
        $application->student_id = $student->id;
        $application->agent_id = $student->agent_id;
        $application->university_id = $request->university_id;
        $application->course_id = $request->course_id;
        $application->application_status_id = $defaultStatus?->id;

        // SOP file
        if ($request->hasFile('sop_file')) {
            $file = $request->file('sop_file');
            $folder = "agents/{$student->agent_id}/{$student->id}/applications";
            $filename = 'sop_' . time() . '.' . $file->getClientOriginalExtension();

            $application->sop_file = $file->storeAs($folder, $filename, 'public');
        }

        $application->save();

        return redirect()->route('admin.applications.index')
            ->with('success', 'Application created successfully.');
    }

    /**
     * SHOW
     */
    public function show(Application $application)
    {
        $statuses = ApplicationStatus::orderBy('sort_order')
            ->where('is_active', 1)
            ->get();
        $application->load([
            'student',
            'university',
            'course',
            'documents',
            'messages.user',
            'status'
        ]);


        return view('admin.applications.show', compact('application', 'statuses'));
    }

    /**
     * EDIT
     */
    public function edit($id)
    {
        $application = Application::with([
            'student',
            'university',
            'course',
            'status'
        ])->findOrFail($id);

        return view('admin.applications.edit', [
            'application' => $application,
            'universities' => University::all(),
            'courses' => Course::all(),
            'statuses' => ApplicationStatus::orderBy('sort_order')->get(),
        ]);
    }

    /**
     * UPDATE
     */
    public function update(Request $request, $id)
    {
        $application = Application::findOrFail($id);

        $request->validate([
            'university_id' => 'required|exists:universities,id',
            'course_id' => 'nullable|exists:courses,id',
            'application_status_id' => 'required|exists:application_statuses,id',
            'sop_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:15360',
        ]);

        $oldStatus = $application->application_status_id;

        $application->update([
            'university_id' => $request->university_id,
            'course_id' => $request->course_id,
            'application_status_id' => $request->application_status_id,
        ]);

        // SOP update
        if ($request->hasFile('sop_file')) {

            $file = $request->file('sop_file');
            $student = $application->student;

            $folder = "agents/{$student->agent_id}/{$student->id}/applications";
            $filename = 'sop_' . time() . '.' . $file->getClientOriginalExtension();

            if ($application->sop_file && Storage::disk('public')->exists($application->sop_file)) {
                Storage::disk('public')->delete($application->sop_file);
            }

            $application->update([
                'sop_file' => $file->storeAs($folder, $filename, 'public')
            ]);
        }

        // STATUS CHANGE NOTIFICATION
        if ($oldStatus != $request->application_status_id) {
            User::notifyAgent(
                $application->student->agent_id,
                new ApplicationStatusUpdated($application, Auth::user())
            );
        }

        return back()->with('success', 'Application updated successfully.');
    }

    /**
     * MESSAGE SYSTEM (UNCHANGED LOGIC)
     */
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
        $userType = $user->is_admin ? 'admin' : 'agent';

        $filePath = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');

            $filePath = $file->storeAs(
                'application_messages/' . $application->id,
                time() . '_' . $file->getClientOriginalName(),
                'public'
            );
        }

        $message = $application->messages()->create([
            'user_id' => $user->id,
            'type' => $userType,
            'message' => $request->message,
            'file_path' => $filePath,
        ]);

        if ($userType === 'agent') {
            User::notifyAdmins(new ApplicationMessageAdded($application, $message));
        } else {
            $application->agent?->notify(new ApplicationMessageAdded($application, $message));
        }

        return back()->with('success', 'Message sent.');
    }

    /**
     * DELETE MESSAGE
     */
    public function deleteMessage(Application $application, $messageId)
    {
        $message = $application->messages()->findOrFail($messageId);

        if (Auth::user()->is_admin && Auth::id() !== $message->user_id) {
            abort(403);
        }

        $message->delete();

        return back()->with('success', 'Message deleted successfully.');
    }

    /**
     * DELETE APPLICATION
     */
    public function withdraw(Application $application)
    {
        $lostStatusId = ApplicationStatus::where('name', 'Lost')->value('id');
        $application->update([
            'withdrawn_at' => now(),
            'application_status_id' => $lostStatusId,
        ]);

        $student = $application->student;
        if ($student && $student->agent_id) {
            $agent = User::find($student->agent_id);
            if ($agent) {
                $agent->notify(new \App\Notifications\ApplicationWithdrawn($application));
            }
        }

        return redirect()->back()->with('success', 'Application withdrawn successfully.');
    }

    public function destroy(Application $application)
    {
        if ($application->sop_file) {
            Storage::disk('public')->delete($application->sop_file);
        }

        $application->delete();

        return redirect()->route('admin.applications.index')
            ->with('success', 'Application deleted successfully.');
    }

    /**
     * AJAX COURSES
     */
    public function getCourses($universityId)
    {
        return Course::where('university_id', $universityId)
            ->select('id', 'title')
            ->get();
    }

    /**
     * STUDENT APPLICATIONS
     */
    public function forStudent(Student $student)
    {
        $statuses = ApplicationStatus::orderBy('sort_order')->get();

        $applications = $student->applications()
            ->with('university', 'course', 'status')
            ->latest()
            ->get();

        return view('admin.applications.for_student', compact('student', 'applications', 'statuses'));
    }
}
