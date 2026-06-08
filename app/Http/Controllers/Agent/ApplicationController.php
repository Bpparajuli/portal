<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Student;
use App\Models\University;
use App\Models\Course;
use App\Models\User;
use App\Models\ApplicationMessage;
use App\Models\ApplicationStatus;
use App\Notifications\ApplicationSubmitted;
use App\Notifications\ApplicationWithdrawn;
use App\Notifications\ApplicationMessageAdded;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Contracts\FileUploadServiceInterface;

class ApplicationController extends Controller
{
    public function __construct(
        private readonly FileUploadServiceInterface $fileUploadService,
    ) {}

    // -------------------------------
    // List applications
    // -------------------------------
    public function index(Request $request)
    {
        $query = Application::where('agent_id', Auth::id())
            ->with([
                'student',
                'university',
                'course',
                'documents',
                'messages.user',
                'status'
            ])
            ->latest();

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
    | Status Filters
    |--------------------------------------------------------------------------
    */
        if ($request->filled('status')) {
            $query->where('application_status_id', $request->status);
        }

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
    | Status List (ONLY this agent's applications)
    |--------------------------------------------------------------------------
    */
        $statuses = ApplicationStatus::where('is_active', 1)
            ->whereHas('applications', function ($q) {
                $q->where('agent_id', Auth::id());
            })
            ->withCount(['applications' => function ($q) {
                $q->where('agent_id', Auth::id());
            }])
            ->orderBy('sort_order')
            ->get();

        /*
    |--------------------------------------------------------------------------
    | Universities List (ONLY this agent's applications)
    |--------------------------------------------------------------------------
    */
        $universities = University::whereHas('applications', function ($q) {
            $q->where('agent_id', Auth::id());
        })
            ->withCount(['applications' => function ($q) {
                $q->where('agent_id', Auth::id());
            }])
            ->orderBy('name')
            ->get();

        /*
    |--------------------------------------------------------------------------
    | Statistics Cards (ONLY this agent)
    |--------------------------------------------------------------------------
    */
        $approvedStatusId = ApplicationStatus::where('name', 'Visa Approved')->value('id');
        $rejectedStatusId = ApplicationStatus::where('name', 'Visa Rejected')->value('id');
        $lostStatusId = ApplicationStatus::where('name', 'Lost')->value('id');

        $acceptedCount = Application::where('agent_id', Auth::id())
            ->where('application_status_id', $approvedStatusId)
            ->count();

        $rejectedCount = Application::where('agent_id', Auth::id())
            ->where('application_status_id', $rejectedStatusId)
            ->count();

        $lostCount = Application::where('agent_id', Auth::id())
            ->where('application_status_id', $lostStatusId)
            ->count();

        /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
        $applications = $query->paginate(20)->withQueryString();

        return view('agent.applications.index', compact(
            'applications',
            'statuses',
            'universities',
            'acceptedCount',
            'rejectedCount',
            'lostCount'
        ));
    }


    // -------------------------------
    // Required document types
    // -------------------------------
    private function allDocumentTypes(): array
    {
        return [
            'passport',
            '10th_certificate',
            '10th_transcript',
            '11th_transcript',
            '12th_certificate',
            '12th_transcript',
            'cv',
            'moi',
            'lor',
            'ielts_pte_language_certificate',
        ];
    }

    // -------------------------------
    // Quick Start - Redirect to create with session data
    // -------------------------------
    public function quickStart(Request $request)
    {
        // Store the pre-selected values in session
        if ($request->has('course_id')) {
            session(['quick_start_course_id' => $request->course_id]);

            // Also get the university from this course
            $course = Course::find($request->course_id);
            if ($course) {
                session(['quick_start_university_id' => $course->university_id]);
            }
        } elseif ($request->has('university_id')) {
            session(['quick_start_university_id' => $request->university_id]);
            session()->forget('quick_start_course_id');
        }

        return redirect()->route('agent.applications.create');
    }

    // -------------------------------
    // Create form
    // -------------------------------
    public function create(Request $request)
    {
        $universities = University::with('courses')->get();

        // Get pre-selected values from session or request
        $selectedUniversityId = session('quick_start_university_id') ?? $request->query('university_id');
        $selectedCourseId = session('quick_start_course_id') ?? $request->query('course_id');

        // Clear session data after retrieving
        if (session()->has('quick_start_university_id')) {
            session()->forget('quick_start_university_id');
        }
        if (session()->has('quick_start_course_id')) {
            session()->forget('quick_start_course_id');
        }

        // Get courses if university is selected
        $courses = collect();
        if ($selectedUniversityId) {
            $courses = Course::where('university_id', $selectedUniversityId)->get();
        }

        // If only course_id is provided (without university)
        if ($selectedCourseId && !$selectedUniversityId) {
            $courses = Course::where('id', $selectedCourseId)->get();
            $course = $courses->first();
            if ($course) {
                $selectedUniversityId = $course->university_id;
                // Reload courses with the correct university
                $courses = Course::where('university_id', $selectedUniversityId)->get();
            }
        }

        // If coming from student page
        if ($request->has('student_id')) {
            $student = Student::where('id', $request->student_id)
                ->where('agent_id', Auth::id())
                ->firstOrFail();

            return view('agent.applications.create', compact(
                'student',
                'universities',
                'courses',
                'selectedUniversityId',
                'selectedCourseId'
            ));
        }

        // Get students with required documents
        $requiredTypes = $this->allDocumentTypes();

        // Alternative: Get all students and check documents in view
        $students = Student::where('agent_id', Auth::id())
            ->with(['documents' => function ($query) use ($requiredTypes) {
                $query->whereIn('document_type', $requiredTypes);
            }])
            ->get();

        // Filter students who have all required documents
        $students = $students->filter(function ($student) use ($requiredTypes) {
            $studentDocTypes = $student->documents->pluck('document_type')->toArray();
            $missingDocs = array_diff($requiredTypes, $studentDocTypes);
            return empty($missingDocs);
        });

        return view('agent.applications.create', compact(
            'students',
            'courses',
            'universities',
            'selectedUniversityId',
            'selectedCourseId'
        ));
    }

    // -------------------------------
    // Store application
    // -------------------------------
    public function store(Request $request)
    {
        $request->validate([
            'student_id'    => 'required|exists:students,id',
            'university_id' => 'required|exists:universities,id',
            'course_id'     => 'required|exists:courses,id',
            'sop_file'      => 'required|file|mimes:jpeg,jpg,png,pdf,doc,docx|max:15360',
        ]);

        $student = Student::where('agent_id', Auth::id())
            ->findOrFail($request->student_id);

        // Prevent duplicate application
        if (Application::where('student_id', $student->id)
            ->where('university_id', $request->university_id)
            ->where('course_id', $request->course_id)
            ->exists()
        ) {
            return back()->withErrors([
                'course_id' => 'This student has already applied to this course.'
            ])->withInput();
        }

        // Create application
        $application = Application::create([
            'student_id' => $student->id,
            'university_id' => $request->university_id,
            'course_id' => $request->course_id,
            'agent_id' => Auth::id(),
            'application_status' => 'Application started',
            'application_status_id' => 1,
        ]);

        // Generate application number
        $application->application_number =
            Auth::id() . '-' .
            $student->id . '-' .
            $request->university_id . '-' .
            ($request->course_id ?? 0) . '-' .
            $application->id;

        // ✅ SOP Upload using service
        if ($request->hasFile('sop_file')) {
            $application->sop_file = $this->fileUploadService->uploadStudentSOP(
                $request->file('sop_file'),
                Auth::user(),
                $student
            );
        }

        $application->save();

        // Notify admin
        $admin = User::admins()->first();
        if ($admin) {
            Notification::send($admin, new ApplicationSubmitted($application));
        }

        return redirect()->route('agent.applications.index')
            ->with('success', 'Application created successfully.');
    }

    // -------------------------------
    // Show
    // -------------------------------
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


        return view('agent.applications.show', compact('application', 'statuses'));
    }


    // -------------------------------
    // Edit
    // -------------------------------
    public function edit($id)
    {
        $application = Application::where('agent_id', Auth::id())
            ->with(['student', 'university', 'course', 'documents'])
            ->findOrFail($id);

        $universities = University::with('courses')->get();
        $students = Student::where('agent_id', Auth::id())->get();

        $selectedUniversityId = $application->university_id;

        $courses = $selectedUniversityId
            ? Course::where('university_id', $selectedUniversityId)->select('id', 'title')->get()
            : collect();

        return view('agent.applications.edit', compact(
            'application',
            'universities',
            'students',
            'courses',
            'selectedUniversityId'
        ));
    }

    // -------------------------------
    // Update
    // -------------------------------
    public function update(Request $request, $id)
    {
        $application = Application::where('agent_id', Auth::id())
            ->findOrFail($id);

        $request->validate([
            'student_id'    => 'required|exists:students,id',
            'university_id' => 'required|exists:universities,id',
            'course_id'     => 'required|exists:courses,id',
            'sop_file'      => 'nullable|file|mimes:jpeg,jpg,png,pdf,doc,docx|max:15360',
        ]);

        // SOP update
        if ($request->hasFile('sop_file')) {

            // Delete old SOP
            if ($application->sop_file && Storage::disk('public')->exists($application->sop_file)) {
                Storage::disk('public')->delete($application->sop_file);
            }

            $application->sop_file = $this->fileUploadService->uploadStudentSOP(
                $request->file('sop_file'),
                Auth::user(),
                $application->student
            );
        }

        $application->update([
            'student_id' => $request->student_id,
            'university_id' => $request->university_id,
            'course_id' => $request->course_id,
        ]);

        $application->save();

        return redirect()->route('agent.applications.index')
            ->with('success', 'Application updated successfully.');
    }

    // -------------------------------
    // Get courses (AJAX)
    // -------------------------------
    public function getCourses($universityId)
    {
        $courses = Course::where('university_id', $universityId)
            ->select('id', 'title')
            ->get();

        return response()->json($courses);
    }

    // -------------------------------
    // Withdraw
    // -------------------------------
    public function withdraw(Request $request, Application $application)
    {
        $request->validate([
            'password' => 'required',
            'reason'   => 'nullable|string|max:255'
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        $application->update([
            'withdrawn_at' => now(),
            'withdraw_reason' => $request->reason ?? 'No reason given',
            'application_status' => 'Withdrawn'
        ]);

        $admin = User::admins()->first();
        if ($admin) {
            Notification::send($admin, new ApplicationWithdrawn($application));
        }

        return redirect()->route('agent.applications.index')
            ->with('success', 'Application withdrawn successfully.');
    }

    // -------------------------------
    // Add message
    // -------------------------------
    public function addMessage(Request $request, Application $application)
    {
        $request->validate([
            'message' => 'required|string|max:2000'
        ]);

        $user = Auth::user();
        $userType = $user->is_admin ? 'admin' : 'agent';

        $message = $application->messages()->create([
            'user_id' => $user->id,
            'type' => $userType,
            'message' => $request->message,
        ]);

        $application->load('student');
        $message->load('user');

        if ($userType === 'agent') {
            User::notifyAdmins(new ApplicationMessageAdded($application, $message));
        } else {
            $application->agent?->notify(new ApplicationMessageAdded($application, $message));
        }

        return back()->with('success', 'Message added.');
    }

    // -------------------------------
    // Delete message
    // -------------------------------
    public function deleteMessage(Application $application, ApplicationMessage $message)
    {
        if ($message->application_id !== $application->id) {
            abort(403);
        }

        $user = Auth::user();
        $userType = $user->is_admin ? 'admin' : 'agent';

        if ($userType !== 'admin' && $user->id !== $message->user_id) {
            abort(403);
        }

        $message->delete();

        return back()->with('success', 'Message deleted.');
    }

    // -------------------------------
    // Authorization
    // -------------------------------
    protected function authorizeAgent(Application $application)
    {
        if ($application->agent_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }

    // -------------------------------
    // Applications for student
    // -------------------------------
    public function forStudent($studentId)
    {
        $student = Student::with([
            'applications.university',
            'applications.course'
        ])
            ->where('agent_id', Auth::id())
            ->findOrFail($studentId);

        return view('agent.applications.for_student', compact('student'));
    }
}
