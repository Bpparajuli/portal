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
use App\Notifications\StudentDeleted;

class StudentController extends Controller
{
    // List students with filters
    public function index(Request $request)
    {
        $filters = $request->only([
            'search',
            'agent',
            'university',
            'course_title',
            'status',
            'sort_by',
            'sort_order',
            'quick_filter'
        ]);

        // Base query with relationships
        $studentsQuery = Student::query()->with(['agent', 'university', 'course', 'applications']);

        // Apply filters as before
        if (!empty($filters['search'])) {
            $studentsQuery->where(fn($q) => $q->where('first_name', 'like', "%{$filters['search']}%")
                ->orWhere('last_name', 'like', "%{$filters['search']}%")
                ->orWhere('email', 'like', "%{$filters['search']}%"));
        }
        if (!empty($filters['agent'])) $studentsQuery->where('agent_id', $filters['agent']);
        if (!empty($filters['university'])) $studentsQuery->where('university_id', $filters['university']);
        if (!empty($filters['course_title'])) {
            $studentsQuery->whereHas('course', fn($q) => $q->where('title', $filters['course_title']));
        }
        if (!empty($filters['status'])) {
            $studentsQuery->whereHas('applications', function ($q) use ($filters) {
                $q->where('application_status', $filters['status']);
            });
        }
        if (!empty($filters['quick_filter'])) {
            if ($filters['quick_filter'] === 'applied') {
                $studentsQuery->whereHas('applications');
            } elseif ($filters['quick_filter'] === 'not_applied') {
                $studentsQuery->doesntHave('applications');
            }
        }
        $studentsQuery->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_order'] ?? 'DESC');

        // Split queries for two tables
        $specialAgentIds = [11, 12];

        $table2Query = clone $studentsQuery;
        $table2Students = $table2Query->whereIn('agent_id', $specialAgentIds)->paginate(10, ['*'], 'table2')->withQueryString();

        $table1Query = clone $studentsQuery;
        $table1Students = $table1Query->whereNotIn('agent_id', $specialAgentIds)->paginate(15, ['*'], 'table1')->withQueryString();

        return view('admin.students.index', [
            'table1Students' => $table1Students,
            'table2Students' => $table2Students,
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
        if ($student->agent_id && $agent = User::find($student->agent_id)) {
            Notification::send($agent, new StudentAdded($agent, $student));
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
        // Notify agent before deletion
        if ($student->agent) {
            Notification::send($student->agent, new StudentDeleted(Auth::user(), $student));
        }

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
        if (!file_exists(public_path($path))) {
            mkdir(public_path($path), 0755, true);
        }
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
