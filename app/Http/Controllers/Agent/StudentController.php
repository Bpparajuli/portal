<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Course;
use App\Models\University;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    // Display all students for this agent
    public function index(Request $request)
    {
        $query = Student::where('agent_id', Auth::id());

        // Optional: filter by university if needed
        if ($request->filled('university_id')) {
            $query->where('university_id', $request->university_id);
        }

        $students = $query->get();
        $universities = University::all(); // for dropdown filter if needed
        $courses = Course::all(); // <- add this

        return view('agent.students.index', compact('students', 'universities'));
    }

    // Show create form
    public function create()
    {
        $universities = University::all();
        $courses = Course::all(); // <- add this
        return view('agent.students.create', compact('universities'));
    }

    // Store a new student
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'university_id' => 'nullable|exists:universities,id',
            'course_id' => 'nullable|exists:courses,id',
        ]);

        Student::create([
            'name' => $request->name,
            'email' => $request->email,
            'university_id' => $request->university_id,
            'course_id' => $request->course_id,
            'agent_id' => Auth::id(),
        ]);

        return redirect()->route('agent.students.index')->with('success', 'Student added successfully.');
    }

    // Show edit form
    public function edit(Student $student)
    {
        // Only allow agent to edit their own students
        if ($student->agent_id !== Auth::id()) {
            abort(403);
        }

        $universities = University::all();
        $courses = Course::all(); // <- add this
        return view('agent.students.edit', compact('student', 'universities'));
    }

    // Update student
    public function update(Request $request, Student $student)
    {
        if ($student->agent_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email,' . $student->id,
            'university_id' => 'nullable|exists:universities,id',
            'course_id' => 'nullable|exists:courses,id',
        ]);

        $student->update([
            'name' => $request->name,
            'email' => $request->email,
            'university_id' => $request->university_id,
            'course_id' => $request->course_id,
        ]);

        return redirect()->route('agent.students.index')->with('success', 'Student updated successfully.');
    }

    // Delete student
    public function destroy(Student $student)
    {
        if ($student->agent_id !== Auth::id()) {
            abort(403);
        }

        $student->delete();

        return redirect()->route('agent.students.index')->with('success', 'Student deleted successfully.');
    }
}
