<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\University;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['agent', 'university', 'course']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        if ($agent = $request->input('agent')) $query->where('agent_id', $agent);
        if ($university = $request->input('university')) $query->where('university_id', $university);
        if ($courseTitle = $request->input('course_title')) {
            $query->whereHas('course', fn($q) => $q->where('title', $courseTitle));
        }
        if ($status = $request->input('status')) $query->where('student_status', $status);

        // Apply sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'DESC');
        $query->orderBy($sortBy, $sortOrder);

        // Get the paginated results
        $students = $query->orderBy(
            $request->input('sort_by', 'created_at'),
            $request->input('sort_order', 'DESC')
        )->paginate(10);

        // Fetch all necessary variables for the filter dropdowns
        $agents = User::where('is_agent', 1)->get();
        $universities = University::all();
        $courses = Course::all();

        return view('admin.students.index', compact('students', 'agents', 'universities', 'courses'));
    }

    public function create()
    {
        $universities = University::all();
        $courses = Course::all();
        $statuses = Student::STATUSES;

        $agents = [];
        if (auth()->user()->is_admin) {
            $agents = User::where('is_agent', 1)->get();
        }

        return view('admin.students.create', compact('universities', 'courses', 'statuses', 'agents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'        => 'required|string|max:100',
            'last_name'         => 'required|string|max:100',
            'dob'               => 'nullable|date',
            'gender'            => ['nullable', Rule::in(Student::GENDERS)],
            'email'             => 'required|email|unique:students,email',
            'phone_number'      => 'nullable|string|max:20',
            'address'           => 'nullable|string',
            'passport_number'   => 'nullable|string|max:50',
            'preferred_country' => 'nullable|string|max:100',
            'nationality'       => 'nullable|string|max:100',
            'university_id'     => 'nullable|exists:universities,id',
            'course_id'         => 'nullable|exists:courses,id',
            'student_status'    => ['required', Rule::in(Student::STATUSES)],
            'agent_id'          => ['nullable', 'exists:users,id'], // admin selects
        ]);

        if (auth()->user()->is_agent) {
            $validated['agent_id'] = auth()->id();
        }

        Student::create($validated);

        return redirect()->route('admin.students.index')->with('success', 'Student created successfully.');
    }

    public function edit($id)
    {
        $student = Student::findOrFail($id);
        $universities = University::all();
        $courses = Course::all();
        $statuses = Student::STATUSES;

        $agents = [];
        if (auth()->user()->is_admin) {
            $agents = User::where('is_agent', 1)->get();
        }

        return view('admin.students.edit', compact('student', 'universities', 'courses', 'statuses', 'agents'));
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'first_name'        => 'required|string|max:100',
            'last_name'         => 'required|string|max:100',
            'dob'               => 'nullable|date',
            'gender'            => ['nullable', Rule::in(Student::GENDERS)],
            'email'             => ['required', 'email', Rule::unique('students')->ignore($student->id)],
            'phone_number'      => 'nullable|string|max:20',
            'address'           => 'nullable|string',
            'passport_number'   => 'nullable|string|max:50',
            'preferred_country' => 'nullable|string|max:100',
            'nationality'       => 'nullable|string|max:100',
            'university_id'     => 'nullable|exists:universities,id',
            'course_id'         => 'nullable|exists:courses,id',
            'student_status'    => ['required', Rule::in(Student::STATUSES)],
            'agent_id'          => ['nullable', 'exists:users,id'], // admin selects
        ]);

        if (auth()->user()->is_agent) {
            $validated['agent_id'] = $student->agent_id; // cannot reassign
        }

        $student->update($validated);

        return redirect()->route('admin.students.index')->with('success', 'Student updated successfully.');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();
        return redirect()->route('admin.students.index')->with('success', 'Student deleted successfully.');
    }
    /**
     * Display the specified student.
     */
    public function show($id)
    {
        $student = Student::with(['agent', 'university', 'course'])->findOrFail($id);
        return view('admin.students.show', compact('student'));
    }
}
