<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\User;
use App\Models\University;
use App\Models\Course;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\StudentAdded;
use App\Notifications\StudentStatusUpdated;

class StudentController extends Controller
{
    // List students with filters
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'agent', 'university', 'course_title', 'status', 'sort_by', 'sort_order']);
        $students = Student::query()->with(['agent', 'university', 'course']);

        if (!empty($filters['search'])) {
            $students->where(fn($q) => $q->where('first_name', 'like', "%{$filters['search']}%")
                ->orWhere('last_name', 'like', "%{$filters['search']}%")
                ->orWhere('email', 'like', "%{$filters['search']}%"));
        }

        if (!empty($filters['agent'])) $students->where('agent_id', $filters['agent']);
        if (!empty($filters['university'])) $students->where('university_id', $filters['university']);
        if (!empty($filters['course_title'])) {
            $students->whereHas('course', fn($q) => $q->where('title', $filters['course_title']));
        }
        if (!empty($filters['status'])) $students->where('student_status', $filters['status']);

        $students->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_order'] ?? 'DESC');

        return view('admin.students.index', [
            'students' => $students->paginate(15)->withQueryString(),
            'agents' => User::where('is_agent', 1)->get(),
            'universities' => University::all(),
            'courses' => Course::all(),
        ]);
    }

    // Show form to create student
    public function create()
    {
        return view('admin.students.create', [
            'student' => new Student(),
            'agents' => User::where('is_agent', 1)->get(),
            'universities' => University::all(),
            'courses' => Course::all(),
            'statuses' => Student::STATUS,
        ]);
    }

    // Store new student
    public function store(Request $request)
    {
        $data = $this->validateStudent($request);

        $student = Student::create($data);

        if ($request->hasFile('students_photo')) {
            $student->students_photo = $this->uploadPhoto($request->file('students_photo'), $student, $request->agent_id);
            $student->save();
        }

        // Notify agent if assigned
        if ($student->agent_id) {
            Notification::send(User::find($student->agent_id), new StudentAdded($agent, $student));
        }

        return redirect()->route('admin.students.index')->with('success', 'Student created successfully.');
    }

    // Show student details
    public function show(Student $student)
    {
        $documents = $student->documents;
        return view('admin.students.show', compact('student', 'documents'));
    }

    // Show form to edit student
    public function edit(Student $student)
    {
        return view('admin.students.edit', [
            'student' => $student,
            'agents' => User::where('is_agent', 1)->get(),
            'universities' => University::all(),
            'courses' => Course::all(),
            'statuses' => Student::STATUS,
        ]);
    }

    // Update student
    public function update(Request $request, Student $student)
    {
        $data = $this->validateStudent($request, $student->id);

        $student->update($data);

        if ($request->hasFile('students_photo')) {
            $student->students_photo = $this->uploadPhoto($request->file('students_photo'), $student, $request->agent_id);
            $student->save();
        }

        // Notify agent if student status changed
        if ($student->wasChanged('student_status') && $student->agent) {
            Notification::send($student->agent, new StudentStatusUpdated($student));
        }

        return redirect()->route('admin.students.show', $student->id)->with('success', 'Student updated successfully.');
    }

    // Delete student
    public function destroy(Student $student)
    {
        // Delete student photo
        if ($student->students_photo && file_exists(public_path($student->students_photo))) {
            unlink(public_path($student->students_photo));
        }

        $student->delete();
        return redirect()->route('admin.students.index')->with('success', 'Student deleted successfully.');
    }

    // Upload student photo
    private function uploadPhoto($file, Student $student, $agent_id = null)
    {
        $agent = $agent_id ? User::find($agent_id) : Auth::user();
        $agent_name = $agent ? ($agent->username ?? $agent->business_name) : 'unknown_agent';
        $student_name = $student->first_name . '_' . $student->last_name;
        $path = 'images/agents/' . $agent_name . '/' . $student_name;
        $filename = $student_name . '_photo.' . $file->getClientOriginalExtension();
        $file->move(public_path($path), $filename);
        return $path . '/' . $filename;
    }

    // Validate student request
    private function validateStudent(Request $request, $id = null)
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dob' => 'nullable|date',
            'gender' => 'required|in:' . implode(',', Student::GENDERS),
            'email' => 'required|email|unique:students,email' . ($id ? ',' . $id : ''),
            'phone_number' => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'temporary_address' => 'nullable|string',
            'nationality' => 'nullable|string',
            'passport_number' => 'nullable|string',
            'passport_expiry' => 'nullable|date',
            'marital_status' => 'nullable|in:Single,Married,Other',
            'qualification' => 'nullable|string',
            'passed_year' => 'nullable|numeric',
            'gap' => 'nullable|numeric',
            'last_grades' => 'nullable|string',
            'education_board' => 'nullable|string',
            'preferred_country' => 'nullable|string',
            'agent_id' => 'nullable|exists:users,id',
            'university_id' => 'nullable|exists:universities,id',
            'course_id' => 'nullable|exists:courses,id',
            'student_status' => 'nullable|in:' . implode(',', Student::STATUS),
            'notes' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
            'students_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ];

        return $request->validate($rules);
    }
}
