<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Notifications\StudentCreatedNotification;

class StudentController extends Controller
{
    public function index()
    {
        $students = auth()->user()->students;
        return view('agent.students.index', compact('students'));
    }

    public function create()
    {
        return view('agent.students.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            // ... other student fields
        ]);

        $student = auth()->user()->students()->create($validatedData);

        // Notify the admin
        // Assuming there is at least one admin
        $admin = \App\Models\User::where('role', 'admin')->first();
        if ($admin) {
            $admin->notify(new StudentCreatedNotification($student));
        }

        return redirect()->route('agent.students.show', $student)->with('success', 'Student created successfully.');
    }

    public function show(Student $student)
    {
        // Ensure the agent can only view their own students
        if ($student->agent_id !== auth()->id()) {
            abort(403);
        }
        return view('agent.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        if ($student->agent_id !== auth()->id()) {
            abort(403);
        }
        return view('agent.students.edit', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        if ($student->agent_id !== auth()->id()) {
            abort(403);
        }
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            // ... other update validation rules
        ]);
        $student->update($validatedData);
        return redirect()->route('agent.students.show', $student)->with('success', 'Student updated successfully.');
    }
}
