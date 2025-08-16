<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\University;
use App\Models\Course;
use App\Models\StudentApplication;
use App\Models\ApplicationDocument;
use App\Models\ApplicationChat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class StudentController extends Controller
{
    // Student List for current agent
    public function list()
    {
        $students = Student::where('agent_id', Auth::id())->get();
        $agents = User::where('is_agent', 1)->get();
        return view('student.list', compact('students', 'agents'));
    }

    // Student Index with filters and pagination
    public function index(Request $request)
    {
        $query = Student::query();

        if (!Auth::user()->is_admin) {
            $query->where('agent_id', Auth::id());
        }

        if ($request->filled('status')) {
            $query->where('student_status', $request->status);
        }

        if ($request->filled('university_id')) {
            $query->where('university_id', $request->university_id);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                    ->orWhere('last_name', 'like', '%' . $request->search . '%')
                    ->orWhere('agent_student_id', 'like', '%' . $request->search . '%');
            });
        }

        $students = $query->with(['university', 'course', 'applications' => fn($q) => $q->latest()])->paginate(15);
        $universities = University::all();
        $courses = Course::all();
        return view('student.index', compact('students', 'universities', 'courses'));
    }

    // Show create form
    public function create()
    {
        $universities = University::all();
        $courses = Course::all();
        return view('student.create', compact('universities', 'courses'));
    }

    // Store student
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dob' => 'required|date',
            'gender' => 'required|in:Male,Female,Other',
            'email' => 'required|email|unique:students,email',
            'phone_number' => 'required',
            'address' => 'required',
            'passport_number' => 'nullable|string',
            'preferred_country' => 'required|string',
            'nationality' => 'required|string',
            'university_id' => 'nullable|exists:universities,id',
            'course_id' => 'nullable|exists:courses,id',
            'academic_background' => 'nullable|string',
            'english_proficiency' => 'nullable|string',
            'financial_proof' => 'nullable|string',
            'agent_student_id' => 'nullable|string|unique:students,agent_student_id',
            'notes' => 'nullable|string',
        ]);

        $student = Student::create(array_merge($request->only([
            'first_name',
            'last_name',
            'dob',
            'gender',
            'email',
            'phone_number',
            'address',
            'passport_number',
            'preferred_country',
            'nationality',
            'university_id',
            'course_id',
            'academic_background',
            'english_proficiency',
            'financial_proof',
            'agent_student_id',
            'notes'
        ]), ['agent_id' => Auth::id(), 'student_status' => 'pending']));

        $admins = User::where('is_admin', 1)->get();
        Notification::send($admins, new \App\Notifications\NewStudentNotification($student));

        return redirect()->route('students.show', $student->id)->with('success', 'Student created successfully.');
    }

    // Show edit form
    public function edit(Student $student)
    {
        $universities = University::all();
        $courses = Course::all();
        return view('student.edit', compact('student', 'universities', 'courses'));
    }

    // Update student
    public function update(Request $request, Student $student)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dob' => 'required|date',
            'gender' => 'required|in:Male,Female,Other',
            'email' => 'required|email|unique:students,email,' . $student->id,
            'phone_number' => 'required',
            'address' => 'required',
            'passport_number' => 'nullable|string',
            'preferred_country' => 'required|string',
            'nationality' => 'required|string',
            'university_id' => 'nullable|exists:universities,id',
            'course_id' => 'nullable|exists:courses,id',
            'academic_background' => 'nullable|string',
            'english_proficiency' => 'nullable|string',
            'financial_proof' => 'nullable|string',
            'agent_student_id' => 'nullable|string|unique:students,agent_student_id,' . $student->id,
            'notes' => 'nullable|string',
            'student_status' => Auth::user()->is_admin ? 'required|in:pending,approved,rejected' : 'nullable'
        ]);

        $student->update($request->only([
            'first_name',
            'last_name',
            'dob',
            'gender',
            'email',
            'phone_number',
            'address',
            'passport_number',
            'preferred_country',
            'nationality',
            'university_id',
            'course_id',
            'academic_background',
            'english_proficiency',
            'financial_proof',
            'agent_student_id',
            'notes',
            'student_status'
        ]));

        return redirect()->route('students.show', $student->id)->with('success', 'Student updated successfully.');
    }

    // Show student details
    public function show(Student $student)
    {
        $student->load(['applications.documents', 'applications.chats', 'university', 'course']);
        return view('student.show', compact('student'));
    }

    // Apply Now form
    public function apply(Student $student)
    {
        $universities = University::all();
        $courses = Course::all();
        return view('student.apply', compact('student', 'universities', 'courses'));
    }

    // Submit application
    public function submitApplication(Request $request, Student $student)
    {
        $request->validate([
            'university_id' => 'required|exists:universities,id',
            'course_id' => 'required|exists:courses,id',
            'documents.*' => 'required|file|max:10240',
        ]);

        $application = $student->applications()->create([
            'university_id' => $request->university_id,
            'course_id' => $request->course_id,
            'application_status' => 'pending',
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store('student_documents', 'public');
                $application->documents()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'uploaded_by' => Auth::id()
                ]);
            }
        }

        $admins = User::where('is_admin', 1)->get();
        Notification::send($admins, new \App\Notifications\NewApplicationNotification($application));

        return redirect()->route('students.show', $student->id)->with('success', 'Application submitted successfully.');
    }

    // Documents page
    public function documents($applicationId)
    {
        $application = StudentApplication::with('documents', 'student')->findOrFail($applicationId);
        return view('student.documents', compact('application'));
    }

    // Store uploaded documents
    public function storeDocuments(Request $request, $applicationId)
    {
        $application = StudentApplication::findOrFail($applicationId);
        $request->validate(['documents.*' => 'required|file|max:10240']);

        foreach ($request->file('documents') as $file) {
            $path = $file->store('student_documents', 'public');
            $application->documents()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'uploaded_by' => Auth::id()
            ]);
        }

        $admins = User::where('is_admin', 1)->get();
        Notification::send($admins, new \App\Notifications\NewDocumentUploaded($application));

        return back()->with('success', 'Documents uploaded successfully.');
    }

    // Chat page
    public function chat($applicationId)
    {
        $application = StudentApplication::with('chats.user', 'student')->findOrFail($applicationId);
        return view('student.chat', compact('application'));
    }

    // Store chat message
    public function storeChat(Request $request, $applicationId)
    {
        $application = StudentApplication::findOrFail($applicationId);
        $request->validate(['message' => 'required|string']);

        $application->chats()->create([
            'user_id' => Auth::id(),
            'message' => $request->message
        ]);

        return back()->with('success', 'Message sent.');
    }

    // Profile page
    public function profile()
    {
        return view('student.profile', ['user' => Auth::user()]);
    }
}
