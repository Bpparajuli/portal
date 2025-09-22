<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Student;
use App\Models\University;
use App\Models\Course;
use App\Models\Document;
use App\Models\ApplicationRemark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ApplicationController extends Controller
{
    public function index()
    {
        $applications = Application::where('agent_id', Auth::id())
            ->with(['student', 'university', 'course', 'documents', 'comments.user'])
            ->latest()
            ->paginate(20);

        return view('agent.applications.index', compact('applications'));
    }

    public function create(Request $request)
    {
        $universities = University::all();

        // Redirected from student page
        if ($request->has('student_id')) {
            $student = Student::where('id', $request->student_id)
                ->where('agent_id', Auth::id())
                ->firstOrFail();

            return view('agent.applications.create', compact('student', 'universities'));
        }

        // Normal create page: students with uploaded documents
        $students = Student::where('agent_id', Auth::id())
            ->whereHas('documents', function ($q) {
                $q->whereNotNull('file_path');
            })
            ->get();

        return view('agent.applications.create', compact('students', 'universities'));
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
        $duplicate = Application::where('student_id', $student->id)
            ->where('university_id', $request->university_id)
            ->where('course_id', $request->course_id)
            ->exists();

        if ($duplicate) {
            return back()->withErrors([
                'course_id' => 'This student has already applied to the selected university and course.'
            ])->withInput();
        }

        // Create application
        $application = Application::create([
            'student_id'        => $student->id,
            'university_id'     => $request->university_id,
            'course_id'         => $request->course_id,
            'agent_id'          => Auth::id(),
            'application_status' => 'Application started',
        ]);

        // Generate application number
        $application->application_number = Auth::id() . '-' . $student->id . '-' . $request->university_id . '-' . ($request->course_id ?? 0) . '-' . $application->id;
        $application->save();

        // Store SOP document
        $file = $request->file('sop');
        $folder = "agents/" . Auth::id() . "/{$student->id}/applications/{$application->id}";
        $filename = 'sop_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($folder, $filename, 'public');

        Document::create([
            'student_id'    => $student->id,
            'uploaded_by'   => Auth::id(),
            'file_name'     => $file->getClientOriginalName(),
            'file_path'     => $path,
            'file_type'     => $file->getClientMimeType(),
            'file_size'     => $file->getSize(),
            'document_type' => 'SOP'
        ]);

        return redirect()->route('agent.applications.index')->with('success', 'Application created successfully.');
    }

    public function show(Application $application)
    {
        $this->authorizeAgent($application);
        $application->load(['student', 'university', 'course', 'documents', 'comments.user']);
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


        // Update SOP
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
            'withdrawn_at'        => now(),
            'withdraw_reason'     => $request->reason ?? 'No reason given',
            'application_status'  => 'Withdrawn'
        ]);

        return redirect()->route('agent.applications.index')->with('success', 'Application withdrawn successfully.');
    }

    public function addComment(Request $request, Application $application)
    {
        $request->validate([
            'comment' => 'required|string|max:2000'
        ]);

        $userType = Auth::user()->is_admin ? 'admin' : 'agent';

        $application->comments()->create([
            'user_id' => Auth::id(),
            'type'    => $userType,
            'comment' => $request->comment, // ✅ must match column name
            'application_id' => $application->id, // if not automatically added
        ]);

        return redirect()->back()->with('success', 'Comment added successfully.');
    }


    protected function authorizeAgent(Application $application)
    {
        if ($application->agent_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
