<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\User;
use App\Models\University;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\StudentAdded;
use App\Notifications\StudentDeleted;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\FileUploadService;

class StudentController extends Controller
{
    // -------------------------------
    // List students with filters
    // -------------------------------
    public function index(Request $request)
    {
        $filters = $request->only([
            'search',
            'country',
            'university',
            'application_status',
            'document_status'
        ]);

        $query = Student::with(['applications', 'documents'])
            ->where('agent_id', Auth::id());

        // 🔍 Search
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('first_name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('last_name', 'like', '%' . $filters['search'] . '%');
            });
        }

        // 🌍 Country filter
        if (!empty($filters['country'])) {
            $query->where('preferred_country', $filters['country']);
        }

        // 🎓 University filter
        if (!empty($filters['university'])) {
            $query->whereHas('applications', function ($q) use ($filters) {
                $q->where('university_id', $filters['university']);
            });
        }

        // 📊 Application status
        if (!empty($filters['application_status'])) {
            $query->whereHas('applications', function ($q) use ($filters) {
                $q->where('application_status', $filters['application_status']);
            });
        }

        $perPage = 15;

        // 📂 Document status filter (custom logic)
        if (!empty($filters['document_status'])) {

            $all = $query->orderBy('created_at', 'desc')->get();

            $filtered = $all->filter(function ($student) use ($filters) {

                $requiredDocs = ['passport', 'id', 'transcript', 'financial', 'other'];

                $uploadedTypes = $student->documents->pluck('document_type')
                    ->map(fn($t) => strtolower(str_replace(' ', '', $t)))
                    ->toArray();

                $allUploaded = count(array_diff($requiredDocs, $uploadedTypes)) === 0;
                $completedCount = $student->documents->where('status', 'completed')->count();

                $status = ($allUploaded && $completedCount == count($requiredDocs))
                    ? 'Completed'
                    : (count($uploadedTypes) == 0 ? 'Not Uploaded' : 'Incomplete');

                return $status === $filters['document_status'];
            });

            $page = LengthAwarePaginator::resolveCurrentPage();

            $students = new LengthAwarePaginator(
                $filtered->slice(($page - 1) * $perPage, $perPage)->values(),
                $filtered->count(),
                $perPage,
                $page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'query' => $request->query()
                ]
            );
        } else {
            $students = $query->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->withQueryString();
        }

        // 🌍 Country list
        $countries = Student::where('agent_id', Auth::id())
            ->select('preferred_country')
            ->distinct()
            ->pluck('preferred_country')
            ->filter()
            ->values();

        $universities = University::orderBy('name')->get();

        return view('agent.students.index', compact('students', 'countries', 'universities'));
    }

    // -------------------------------
    // Create
    // -------------------------------
    public function create()
    {
        return view('agent.students.create', [
            'student' => new Student(),
            'universities' => University::all(),
            'courses' => Course::all(),
            'statuses' => Student::STATUS
        ]);
    }

    // -------------------------------
    // Store
    // -------------------------------
    public function store(Request $request)
    {
        $data = $this->validateStudent($request);
        $data['agent_id'] = Auth::id();

        // ✅ Create student first
        $student = Student::create($data);

        // ✅ Upload photo via service
        if ($request->hasFile('students_photo')) {
            $student->students_photo = FileUploadService::uploadStudentFile(
                $request->file('students_photo'),
                Auth::user(),
                $student,
                'photo'
            );
            $student->save();
        }

        // 🔔 Notify Admin (adjust ID later)
        $admin = User::find(6);
        if ($admin) {
            Notification::send($admin, new StudentAdded(Auth::user(), $student));
        }

        return redirect()->route('agent.students.index')
            ->with('success', 'Student created successfully.');
    }

    // -------------------------------
    // Show
    // -------------------------------
    public function show(Student $student)
    {
        $this->authorizeStudent($student);

        $documents = $student->documents;
        $applications = $student->applications;

        return view('agent.students.show', compact('student', 'documents', 'applications'));
    }

    // -------------------------------
    // Edit
    // -------------------------------
    public function edit(Student $student)
    {
        $this->authorizeStudent($student);

        return view('agent.students.edit', [
            'student' => $student,
            'universities' => University::all(),
            'courses' => Course::all(),
            'statuses' => Student::STATUS
        ]);
    }

    // -------------------------------
    // Update
    // -------------------------------
    public function update(Request $request, Student $student)
    {
        $this->authorizeStudent($student);

        $data = $this->validateStudent($request, $student->id);
        $student->update($data);

        // ✅ Update photo using service
        if ($request->hasFile('students_photo')) {
            $student->students_photo = FileUploadService::uploadStudentFile(
                $request->file('students_photo'),
                Auth::user(),
                $student,
                'photo'
            );
            $student->save();
        }

        return redirect()->route('agent.students.show', $student->id)
            ->with('success', 'Student updated successfully.');
    }

    // -------------------------------
    // Delete
    // -------------------------------
    public function destroy(Student $student)
    {
        $this->authorizeStudent($student);

        // 🔔 Notify Admin
        $admin = User::find(6);
        if ($admin) {
            Notification::send($admin, new StudentDeleted(Auth::user(), $student));
        }

        // ✅ Delete entire student folder
        $agentSlug = Auth::user()->slug;

        $studentName = strtolower(str_replace(' ', '-', $student->first_name . '-' . $student->last_name));

        $folder = "agents/{$agentSlug}/{$studentName}";

        if (Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->deleteDirectory($folder);
        }

        $student->delete();

        return redirect()->route('agent.students.index')
            ->with('success', 'Student deleted successfully.');
    }

    // -------------------------------
    // Validation
    // -------------------------------
    private function validateStudent(Request $request, $id = null)
    {
        return $request->validate([
            'first_name'        => 'required|string|max:255',
            'last_name'         => 'required|string|max:255',
            'dob'               => 'nullable|date',
            'gender'            => 'nullable|string|max:50',
            'email'             => 'nullable|email|unique:students,email' . ($id ? ',' . $id : ''),
            'phone_number'      => 'nullable|string|max:20',
            'permanent_address' => 'nullable|string|max:255',
            'temporary_address' => 'nullable|string|max:255',
            'nationality'       => 'nullable|string|max:100',
            'passport_number'   => 'nullable|string|max:50',
            'passport_expiry'   => 'nullable|date',
            'marital_status'    => 'nullable|string|max:50',
            'qualification'     => 'nullable|string|max:255',
            'passed_year'       => 'nullable|integer',
            'gap'               => 'nullable|string|max:255',
            'last_grades'       => 'nullable|string|max:50',
            'education_board'   => 'nullable|string|max:255',
            'preferred_country' => 'nullable|string|max:100',
            'preferred_course'  => 'nullable|string|max:255',
            'university_id'     => 'nullable|exists:universities,id',
            'course_id'         => 'nullable|exists:courses,id',
            'student_status'    => 'nullable|in:' . implode(',', Student::STATUS),
            'notes'             => 'nullable|string',
            'follow_up_date'    => 'nullable|date',
            'students_photo'    => 'nullable|image|mimes:jpg,jpeg,png|max:5120'
        ]);
    }

    // -------------------------------
    // Authorization
    // -------------------------------
    private function authorizeStudent(Student $student)
    {
        if ($student->agent_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }
    }
}
