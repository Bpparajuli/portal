<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Student;
use App\Models\University;
use App\Models\Course;
use App\Models\User;
use App\Models\Document;
use App\Notifications\ApplicationSubmitted;
use App\Notifications\ApplicationWithdrawn;
use App\Notifications\ApplicationMessageAdded;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index()
    {
        $applications = Application::where('agent_id', Auth::id())
            ->with(['student', 'university', 'course', 'documents', 'messages.user'])
            ->latest()
            ->paginate(20);

        return view('agent.applications.index', compact('applications'));
    }

    public function create(Request $request)
    {
        $universities = University::with('courses')->get();
        $selectedUniversityId = $request->query('university_id');
        $selectedCourseId = $request->query('course_id');

        // Redirected from student page
        if ($request->has('student_id')) {
            $student = Student::where('id', $request->student_id)
                ->where('agent_id', Auth::id())
                ->firstOrFail();

            return view('agent.applications.create', compact(
                'student',
                'universities',
                'selectedUniversityId',
                'selectedCourseId'
            ));
        }

        // ✅ Only students with all required document types
        $requiredTypes = ['education', 'identification', 'ward', 'financial'];
        $students = Student::where('agent_id', Auth::id())
            ->whereHas('documents', function ($q) use ($requiredTypes) {
                $q->whereIn('document_type', $requiredTypes);
            }, '=', count($requiredTypes))
            ->get();

        return view('agent.applications.create', compact(
            'students',
            'universities',
            'selectedUniversityId',
            'selectedCourseId'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id'    => 'required|exists:students,id',
            'university_id' => 'required|exists:universities,id',
            'course_id'     => 'nullable|exists:courses,id',
            'sop'           => 'required|file|mimes:jpeg,jpg,png,pdf,doc,docx,txt,xls,xlsx,ppt,pptx,zip,rar|max:15360',
        ]);

        $student = Student::where('agent_id', Auth::id())->findOrFail($request->student_id);

        // Prevent duplicate applications
        if (Application::where('student_id', $student->id)
            ->where('university_id', $request->university_id)
            ->where('course_id', $request->course_id)
            ->exists()
        ) {
            return back()->withErrors([
                'course_id' => 'This student has already applied to the selected university and course.'
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
        $application->save();

        // Store SOP
        $file = $request->file('sop');
        $folder = "agents/" . Auth::id() . "/{$student->id}/applications/{$application->id}";
        $filename = 'sop_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($folder, $filename, 'public');

        Document::create([
            'student_id' => $student->id,
            'uploaded_by' => Auth::id(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'document_type' => 'SOP'
        ]);

        // Notify admin (ID=1)
        $admin = User::find(1);
        Notification::send($admin, new ApplicationSubmitted($application));

        return redirect()->route('agent.applications.index')->with('success', 'Application created successfully.');
    }

    public function show(Application $application)
    {
        $this->authorizeAgent($application);
        $application->load(['student', 'university', 'course', 'documents', 'messages.user']);
        return view('agent.applications.show', compact('application'));
    }

    public function edit($id)
    {
        $application = Application::where('agent_id', Auth::id())
            ->with(['student', 'university', 'course', 'documents'])
            ->findOrFail($id);

        return view('agent.applications.edit', compact('application'));
    }

    public function update(Request $request, $id)
    {
        $application = Application::where('agent_id', Auth::id())->findOrFail($id);

        $request->validate([
            'sop' => 'nullable|file|mimes:jpeg,jpg,png,pdf,doc,docx,txt,xls,xlsx,ppt,pptx,zip,rar|max:15360',
        ]);

        if ($request->hasFile('sop')) {
            $student = $application->student;
            $file = $request->file('sop');
            $folder = "agents/" . Auth::id() . "/{$student->id}/applications/{$application->id}";
            $filename = "sop_" . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($folder, $filename, 'public');

            $document = Document::where('student_id', $student->id)
                ->where('document_type', 'SOP')
                ->first();

            if ($document) {
                $document->update([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            } else {
                Document::create([
                    'student_id' => $student->id,
                    'uploaded_by' => Auth::id(),
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'document_type' => 'SOP'
                ]);
            }
        }

        return redirect()->route('agent.applications.index')->with('success', 'Application updated successfully.');
    }

    public function getCourses($universityId)
    {
        $courses = Course::where('university_id', $universityId)
            ->select('id', 'title')
            ->get()
            ->map(fn($course) => ['id' => $course->id, 'name' => $course->title]);

        return response()->json($courses);
    }

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

        $admin = User::find(1);
        Notification::send($admin, new ApplicationWithdrawn($application));

        return redirect()->route('agent.applications.index')->with('success', 'Application withdrawn successfully.');
    }

    public function addMessage(Request $request, Application $application)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $user = Auth::user();
        $userType = $user->is_admin ? 'admin' : 'agent';

        $message = $application->messages()->create([
            'user_id' => $user->id,
            'type'    => $userType,
            'message' => $request->message,
        ]);

        // Notify opposite role
        if ($userType === 'agent') {
            $admins = User::where('is_admin', true)->get();
            Notification::send($admins, new ApplicationMessageAdded($application, $message));
        } else {
            $application->agent?->notify(new ApplicationMessageAdded($application, $message));
        }

        return back()->with('success', 'Message added and notification sent.');
    }

    protected function authorizeAgent(Application $application)
    {
        if ($application->agent_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
