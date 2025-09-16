<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Student;
use App\Models\Document;
use App\Models\University;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use App\Notifications\NewApplicationSubmitted;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    public function index()
    {
        $applications = Application::where('agent_id', Auth::id())->with(['student', 'university', 'course'])->get();
        return view('agent.applications.index', compact('applications'));
    }

    public function create()
    {
        $students = Student::where('agent_id', Auth::id())->get();
        $universities = University::all();
        $courses = Course::all();
        return view('agent.applications.create', compact('students', 'universities', 'courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'university_id' => 'required|exists:universities,id',
            'course_id' => 'nullable|exists:courses,id',
            'remarks' => 'nullable|string',
            'sop' => 'required|file|mimes:pdf,doc,docx|max:15360'
        ]);

        $student = Student::findOrFail($request->student_id);

        $exists = Application::where('student_id', $request->student_id)
            ->where('university_id', $request->university_id)
            ->where('course_id', $request->course_id)
            ->exists();

        if ($exists) return back()->withErrors('Application already exists.');

        $application = Application::create([
            'student_id' => $student->id,
            'university_id' => $request->university_id,
            'course_id' => $request->course_id,
            'agent_id' => Auth::id(),
            'remarks' => $request->remarks,
            'application_status' => 'Application created'
        ]);

        // store SOP
        $file = $request->file('sop');
        $folder = "agents/" . Str::slug($student->id) . "/applications/" . $application->id;
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs($folder, $filename, 'public');

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

        // notify admin
        $admins = User::where('is_admin', 1)->get();
        Notification::send($admins, new NewApplicationSubmitted($application));

        return redirect()->route('agent.applications.index')->with('success', 'Application created with SOP uploaded.');
    }
}
