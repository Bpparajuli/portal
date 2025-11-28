<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Student;
use App\Models\University;
use App\Models\Course;
use App\Models\User;
use App\Models\Document;
use App\Models\ApplicationMessage;
use App\Notifications\ApplicationSubmitted;
use App\Notifications\ApplicationWithdrawn;
use App\Notifications\ApplicationMessageAdded;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class ApplicationController extends Controller
{
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

    /** Show all applications */
    public function index()
    {
        $applications = Application::where('agent_id', Auth::id())
            ->with(['student', 'university', 'course', 'documents', 'messages.user'])
            ->latest()
            ->paginate(20);

        return view('agent.applications.index', compact('applications'));
    }

    /** Show create form */
    public function create(Request $request)
    {
        $universities = University::with('courses')->get();
        $courses = Course::all();
        $selectedUniversityId = $request->query('university_id');
        $selectedCourseId = $request->query('course_id');

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

        $requiredTypes = $this->allDocumentTypes();

        $students = Student::where('agent_id', Auth::id())
            ->where(function ($query) use ($requiredTypes) {
                foreach ($requiredTypes as $type) {
                    $query->whereHas('documents', function ($q) use ($type) {
                        $q->where('document_type', $type);
                    });
                }
            })
            ->get();

        return view('agent.applications.create', compact(
            'students',
            'courses',
            'universities',
            'selectedUniversityId',
            'selectedCourseId'
        ));
    }

    /** Store new application */
    public function store(Request $request)
    {
        $request->validate([
            'student_id'    => 'required|exists:students,id',
            'university_id' => 'required|exists:universities,id',
            'course_id'     => 'required|exists:courses,id',
            'sop_file'      => 'required|file|mimes:jpeg,jpg,png,pdf,doc,docx|max:15360',
        ]);

        $student = Student::where('agent_id', Auth::id())->findOrFail($request->student_id);

        if (Application::where('student_id', $student->id)
            ->where('university_id', $request->university_id)
            ->where('course_id', $request->course_id)
            ->exists()
        ) {
            return back()->withErrors([
                'course_id' => 'This student has already applied to this course.'
            ])->withInput();
        }

        $application = Application::create([
            'student_id' => $student->id,
            'university_id' => $request->university_id,
            'course_id' => $request->course_id,
            'agent_id' => Auth::id(),
            'application_status' => 'Application started',
        ]);

        $application->application_number = Auth::id() . '-' . $student->id . '-' . $request->university_id . '-' . ($request->course_id ?? 0) . '-' . $application->id;

        // ✅ Handle SOP upload
        if ($request->hasFile('sop_file')) {
            $sopPath = $this->getSopStoragePath($request->file('sop_file'), $student, 'sop_file');
            $application->sop_file = $sopPath;
        }

        $application->save();

        // Notify admin(s)
        $admin = User::find(4);
        Notification::send($admin, new ApplicationSubmitted($application));

        return redirect()->route('agent.applications.index')->with('success', 'Application created successfully.');
    }

    /** Show single application */
    public function show(Application $application)
    {
        $this->authorizeAgent($application);
        $application->load(['student', 'university', 'course', 'documents', 'messages.user']);

        $status = Application::STATUSES;
        $statusColors = Application::STATUS_COLORS;

        return view('agent.applications.show', compact('application', 'status', 'statusColors'));
    }


    /** Edit application */
    public function edit($id)
    {
        $application = Application::where('agent_id', Auth::id())
            ->with(['student', 'university', 'course', 'documents'])
            ->findOrFail($id);

        $universities = University::with('courses')->get();
        $students = Student::where('agent_id', Auth::id())->get();

        $selectedUniversityId = $application->university_id;
        $courses = $selectedUniversityId ? Course::where('university_id', $selectedUniversityId)->select('id', 'title')->get() : collect();

        return view('agent.applications.edit', compact(
            'application',
            'universities',
            'students',
            'courses',
            'selectedUniversityId'
        ));
    }

    /** Update application (SOP optional) */
    public function update(Request $request, $id)
    {
        $application = Application::where('agent_id', Auth::id())->findOrFail($id);

        $request->validate([
            'student_id'    => 'required|exists:students,id',
            'university_id' => 'required|exists:universities,id',
            'course_id'     => 'required|exists:courses,id',
            'sop_file'      => 'nullable|file|mimes:jpeg,jpg,png,pdf,doc,docx|max:15360',
        ]);

        if ($request->hasFile('sop_file')) {
            if ($application->sop_file && Storage::disk('public')->exists($application->sop_file)) {
                Storage::disk('public')->delete($application->sop_file);
            }

            $sopPath = $this->getSopStoragePath($request->file('sop_file'), $application->student, 'sop_file');
            $application->sop_file = $sopPath;
        }

        $application->update([
            'student_id' => $request->student_id,
            'university_id' => $request->university_id,
            'course_id' => $request->course_id,
        ]);

        $application->save();

        return redirect()->route('agent.applications.index')->with('success', 'Application updated successfully.');
    }

    /** Get courses by university (AJAX) */
    public function getCourses($universityId)
    {
        $courses = Course::where('university_id', $universityId)
            ->select('id', 'title')
            ->get();

        return response()->json($courses);
    }

    /** Withdraw application */
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

        $admin = User::find(4);
        Notification::send($admin, new ApplicationWithdrawn($application));

        return redirect()->route('agent.applications.index')->with('success', 'Application withdrawn successfully.');
    }

    /** Add message to application */
    public function addMessage(Request $request, Application $application)
    {
        $request->validate(['message' => 'required|string|max:2000']);

        $user = Auth::user();
        $userType = $user->is_admin ? 'admin' : 'agent';

        $message = $application->messages()->create([
            'user_id' => $user->id,
            'type'    => $userType,
            'message' => $request->message,
        ]);

        $application->load('student');
        $message->load('user');

        if ($userType === 'agent') {
            User::notifyAdmins(new ApplicationMessageAdded($application, $message));
        } else {
            $application->agent?->notify(new ApplicationMessageAdded($application, $message));
        }

        return back()->with('success', 'Message added and notification sent.');
    }

    public function deleteMessage(Application $application, ApplicationMessage $message)
    {
        // Make sure message belongs to this application
        if ($message->application_id !== $application->id) {
            abort(403, 'Unauthorized action.');
        }
        // Same logic as your addMessage method
        $user = Auth::user();
        $userType = $user->is_admin ? 'admin' : 'agent';
        // Only admin or message owner can delete
        if ($userType !== 'admin' && $user->id !== $message->user_id) {
            abort(403, 'Unauthorized action.');
        }
        // Delete the message
        $message->delete();
        return back()->with('success', 'Message deleted successfully.');
    }

    /** Authorization check */
    protected function authorizeAgent(Application $application)
    {
        if ($application->agent_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }

    /** Generate SOP file path */
    private function getSopStoragePath($file, $student, $documentType = 'sop_file')
    {
        $agent = Auth::user();
        $safeAgent = preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower($agent->business_name ?? 'agent'));
        $rawStudentName = trim(($student->first_name ?? '') . '_' . ($student->last_name ?? ''));
        $safeStudent = empty($rawStudentName) || $rawStudentName === '_'
            ? 'student_' . $student->id
            : preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower($rawStudentName));

        $fileName = $documentType . '.' . $file->getClientOriginalExtension();
        $folderPath = "agents/{$safeAgent}/{$safeStudent}";

        return $file->storeAs($folderPath, $fileName, 'public');
    }

    /** Applications FOr student */
    public function forStudent($studentId)
    {
        $student = Student::with(['applications.university', 'applications.course'])
            ->where('agent_id', Auth::id())
            ->findOrFail($studentId);

        return view('agent.applications.for_student', compact('student'));
    }
}
