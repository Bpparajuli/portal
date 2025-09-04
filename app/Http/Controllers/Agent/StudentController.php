<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Student;

use App\Models\University;
use App\Models\Course;
use App\Notifications\NewStudentAdded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Models\Activity;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'university', 'course_title', 'status', 'sort_by', 'sort_order']);

        $students = Student::with(['agent', 'university', 'course'])
            ->where('agent_id', Auth::id()); // only this agent's students

        if (!empty($filters['search'])) {
            $students->where(fn($q) => $q->where('first_name', 'like', "%{$filters['search']}%")
                ->orWhere('last_name', 'like', "%{$filters['search']}%")
                ->orWhere('email', 'like', "%{$filters['search']}%"));
        }

        if (!empty($filters['university'])) $students->where('university_id', $filters['university']);
        if (!empty($filters['course_title'])) {
            $students->whereHas('course', fn($q) => $q->where('title', $filters['course_title']));
        }
        if (!empty($filters['status'])) $students->where('student_status', $filters['status']);

        // Apply sorting if needed
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $students->orderBy($sortBy, $sortOrder);

        $students = $students->paginate(15)->withQueryString(); // ✅ paginate on query builder

        return view('agent.students.index', [
            'students' => $students,
            'universities' => University::all(),
            'courses' => Course::all(),
        ]);
    }


    public function create()
    {
        $universities = University::all();
        $courses = Course::all();
        return view('agent.students.create', compact('universities', 'courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
            'students_photo' => 'nullable|image|max:2048',
            'university_id' => 'nullable|exists:universities,id',
            'course_id' => 'nullable|exists:courses,id',
            // add other validations as needed
        ]);

        $data = $request->all();
        $data['agent_id'] = Auth::id(); // always current agent
        $data['student_status'] = 'created'; // agent cannot set status

        // handle photo upload
        if ($request->hasFile('students_photo')) {
            $path = $request->file('students_photo')->store('students', 'public');
            $data['students_photo'] = $path;
        }

        $student = Student::create($data);

        // notify admin
        $admins = \App\Models\User::where('is_admin', 1)->get();
        Notification::send($admins, new NewStudentAdded($student));

        return redirect()->route('agent.students.index')->with('success', 'Student created successfully.');
    }

    public function edit(Student $student)
    {
        // Only allow editing own students
        if ($student->agent_id != Auth::id()) {
            abort(403);
        }

        $universities = University::all();
        $courses = Course::all();
        return view('agent.students.edit', compact('student', 'universities', 'courses'));
    }

    public function update(Request $request, Student $student)
    {
        if ($student->agent_id != Auth::id()) {
            abort(403);
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
            'students_photo' => 'nullable|image|max:2048',
            'university_id' => 'required|exists:universities,id',
            'course_id' => 'required|exists:courses,id',
            // add other validations as needed
        ]);

        $data = $request->all();
        $data['agent_id'] = $student->agent_id; // cannot change agent
        unset($data['student_status']); // agent cannot change status

        // handle photo upload
        if ($request->hasFile('students_photo')) {
            $path = $request->file('students_photo')->store('students', 'public');
            $data['students_photo'] = $path;
        }

        $student->update($data);

        return redirect()->route('agent.students.index')->with('success', 'Student updated successfully.');
    }

    public function show(Student $student)
    {
        if ($student->agent_id != Auth::id()) {
            abort(403);
        }

        return view('agent.students.show', compact('student'));
    }
}
