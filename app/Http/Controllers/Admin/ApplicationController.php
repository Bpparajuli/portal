<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Application;
use App\Models\Student;
use App\Models\University;
use App\Models\Course;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Notifications\ApplicationMessageAdded;


class ApplicationController extends Controller
{
    public function index()
    {
        $applications = Application::with(['student', 'university', 'course', 'agent'])->latest()->paginate(20);
        return view('admin.applications.index', compact('applications'));
    }

    public function create(Request $request)
    {
        $students = Student::whereHas('documents')->get(); // only students with docs
        $universities = University::all();
        $courses = Course::all();

        return view('admin.applications.create', compact('students', 'universities', 'courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'university_id' => 'required|exists:universities,id',
            'course_id' => 'nullable|exists:courses,id',
            'sop' => 'required|file|mimes:pdf,doc,docx|max:15360',
        ]);

        $student = Student::findOrFail($request->student_id);

        $application = Application::create([
            'student_id' => $student->id,
            'university_id' => $request->university_id,
            'course_id' => $request->course_id,
            'agent_id' => $student->agent_id, // link to owning agent
            'remarks' => $request->remarks,
            'application_status' => 'Application started',
        ]);

        // Store SOP
        $file = $request->file('sop');
        $folder = "agents/{$student->agent_id}/{$student->id}/applications/{$application->id}";
        $filename = 'sop_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($folder, $filename, 'public');

        Document::create([
            'student_id' => $student->id,
            'file_name' => $filename,
            'uploaded_by' => Auth::id(),
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'document_type' => 'SOP',
            'application_id' => $application->id,
        ]);

        return redirect()->route('admin.applications.index')->with('success', 'Application created successfully.');
    }

    public function show(Application $application)
    {
        $application->load(['student', 'university', 'course', 'sop', 'agent']);
        return view('admin.applications.show', compact('application'));
    }

    public function edit($id)
    {
        $application = Application::with(['student', 'university', 'course'])->findOrFail($id);
        $universities = University::all();
        $courses = Course::all();

        return view('admin.applications.edit', compact('application', 'universities', 'courses'));
    }

    public function update(Request $request, $id)
    {
        $application = Application::findOrFail($id);

        $request->validate([
            'university_id' => 'required|exists:universities,id',
            'course_id' => 'nullable|exists:courses,id',
            'application_status' => 'required|string|in:' . implode(',', Application::STATUSES),
            'sop' => 'nullable|file|mimes:pdf,doc,docx|max:15360'
        ]);

        $application->update([
            'university_id' => $request->university_id,
            'course_id' => $request->course_id,
            'application_status' => $request->application_status,
        ]);

        // If SOP uploaded → replace
        if ($request->hasFile('sop')) {
            $student = $application->student;
            $file = $request->file('sop');

            $folder = "agents/" . $student->id . "/applications/" . $application->id;
            $filename = "sop_" . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($folder, $filename, 'public');

            $document = Document::where('application_id', $application->id)
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
                    'document_type' => 'SOP',
                    'application_id' => $application->id
                ]);
            }
        }

        return redirect()->route('admin.applications.index')->with('success', 'Application updated successfully.');
    }
    public function addMessage(Request $request, Application $application)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $user = Auth::user();
        $userType = $user->is_admin ? 'admin' : 'agent';

        // Create the message
        $message = $application->messages()->create([
            'user_id' => $user->id,
            'type'    => $userType,
            'message' => $request->message,
        ]);

        // Notify opposite role
        if ($userType === 'agent') {
            // Notify all admins using helper
            User::notifyAdmins(new ApplicationMessageAdded($application, $message));
        } else {
            // Notify the assigned agent of this application
            $application->agent?->notify(new ApplicationMessageAdded($application, $message));
        }

        return back()->with('success', 'Message added and notification sent.');
    }

    public function destroy(Application $application)
    {
        $application->delete();
        return redirect()->route('admin.applications.index')->with('success', 'Application deleted successfully.');
    }
}
