<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Student;
use App\Models\University;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewApplicationSubmitted;

class ApplicationController extends Controller
{
    // List agent’s applications
    public function index()
    {
        $applications = Application::where('agent_id', Auth::id())
            ->with(['student', 'university', 'course'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('agent.applications.index', compact('applications'));
    }

    // Show create form
    public function create(Request $request)
    {
        $agentId = Auth::id();

        $students = Student::where('agent_id', $agentId)->get();
        $universities = University::all();
        $courses = Course::all();

        // Preselect values if coming from Student/University page
        $selectedStudent = $request->get('student_id');
        $selectedUniversity = $request->get('university_id');

        return view('agent.applications.create', compact(
            'students',
            'universities',
            'courses',
            'selectedStudent',
            'selectedUniversity'
        ));
    }

    // Show edit form
    public function edit(Application $application)
    {
        // Ensure the agent owns this application
        if ($application->agent_id != Auth::id()) {
            abort(403);
        }

        $students = Student::where('agent_id', Auth::id())->get();
        $universities = University::all();
        $courses = Course::all();

        return view('agent.applications.edit', compact('application', 'students', 'universities', 'courses'));
    }

    // Update application
    public function update(Request $request, Application $application)
    {
        if ($application->agent_id != Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'university_id' => 'required|exists:universities,id',
            'course_id' => 'nullable|exists:courses,id',
            'remarks' => 'nullable|string',
        ]);

        // Prevent duplicate
        $exists = Application::where('student_id', $validated['student_id'])
            ->where('university_id', $validated['university_id'])
            ->where('course_id', $validated['course_id'])
            ->where('id', '!=', $application->id) // exclude current one
            ->exists();

        if ($exists) {
            return back()->withErrors('This student already has an application for this university and course.');
        }

        // Keep status unchanged (agent cannot update status)
        unset($validated['application_status']);

        $application->update($validated);

        return redirect()->route('agent.applications.index')
            ->with('success', 'Application updated successfully.');
    }

    // Store application
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'university_id' => 'required|exists:universities,id',
            'course_id' => 'nullable|exists:courses,id',
            'remarks' => 'nullable|string',
        ]);

        // Prevent duplicate
        $exists = Application::where('student_id', $validated['student_id'])
            ->where('university_id', $validated['university_id'])
            ->where('course_id', $validated['course_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors('This student already has an application for this university and course.');
        }

        $validated['agent_id'] = Auth::id();
        $validated['application_status'] = 'Application created';

        $application = Application::create($validated);

        // Notify admins
        $admins = User::where('is_admin', 1)->get();
        Notification::send($admins, new NewApplicationSubmitted($application));

        return redirect()->route('agent.applications.index')
            ->with('success', 'Application submitted and admin notified.');
    }

    // Show one application
    public function show(Application $application)
    {
        if ($application->agent_id != Auth::id()) {
            abort(403);
        }

        return view('agent.applications.show', compact('application'));
    }
}
