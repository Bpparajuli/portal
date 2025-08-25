<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\University;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of the students with filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Start with a query builder instance
        $query = Student::with(['agent', 'university', 'course']);

        // Apply filters from the request
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($agent = $request->input('agent')) {
            $query->where('agent_id', $agent);
        }

        if ($university = $request->input('university')) {
            $query->where('university_id', $university);
        }

        if ($courseTitle = $request->input('course_title')) {
            // Filter by course title, needs to join the courses table
            $query->whereHas('course', function ($q) use ($courseTitle) {
                $q->where('title', $courseTitle);
            });
        }

        if ($status = $request->input('status')) {
            $query->where('student_status', $status);
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'DESC');
        $query->orderBy($sortBy, $sortOrder);

        // Get the paginated results
        $students = $query->paginate(10); // Use paginate() instead of get()

        // Fetch all necessary variables for the filter dropdowns
        $agents = User::where('is_agent', 1)->get();
        $universities = University::all();
        $courses = Course::all();

        // Pass all variables to the view
        return view('admin.students.index', compact('students', 'agents', 'universities', 'courses'));
    }

    /**
     * Show the form for creating a new student.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $agents = User::where('is_agent', 1)->get();
        $universities = University::all();
        $courses = Course::all();
        return view('admin.students.create', compact('agents', 'universities', 'courses'));
    }

    /**
     * Store a newly created student in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Add validation and storing logic here
        // ...
        return redirect()->route('admin.students.index')->with('success', 'Student created successfully.');
    }

    /**
     * Display the specified student.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $student = Student::with(['agent', 'university', 'course'])->findOrFail($id);
        return view('admin.students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified student.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $student = Student::with(['agent', 'university', 'course'])->findOrFail($id);
        $agents = User::where('is_agent', 1)->get();
        $universities = University::all();
        $courses = Course::all();
        return view('admin.students.edit', compact('student', 'agents', 'universities', 'courses'));
    }

    /**
     * Update the specified student in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Add validation and update logic here
        // ...
        return redirect()->route('admin.students.index')->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified student from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();
        return redirect()->route('admin.students.index')->with('success', 'Student deleted successfully.');
    }
}
