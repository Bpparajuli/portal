<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Student;
use App\Models\University;
use App\Models\Course;
use App\Models\User;
use App\Notifications\ApplicationSubmittedNotification;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function create(Student $student)
    {
        if ($student->agent_id !== auth()->id()) {
            abort(403);
        }
        $universities = University::all();
        return view('agent.applications.create', compact('student', 'universities'));
    }

    public function store(Request $request, Student $student)
    {
        if ($student->agent_id !== auth()->id()) {
            abort(403);
        }
        $validatedData = $request->validate([
            'university_id' => 'required|exists:universities,id',
            'course_id' => 'required|exists:courses,id',
            // ... other validation rules
        ]);

        $application = $student->applications()->create($validatedData);

        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            $admin->notify(new ApplicationSubmittedNotification($application));
        }

        return redirect()->route('agent.applications.show', $application)->with('success', 'Application submitted successfully.');
    }

    public function show(Application $application)
    {
        if ($application->student->agent_id !== auth()->id()) {
            abort(403);
        }
        return view('agent.applications.show', compact('application'));
    }
}
