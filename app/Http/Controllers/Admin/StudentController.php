<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationStatus;
use App\Models\Course;
use App\Models\Document;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use App\Notifications\StudentAdded;
use App\Notifications\StudentDeleted;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------
    public function index(Request $request)
    {
        $baseQuery = Student::with(['agent', 'documents', 'applications']);

        // Search
        if ($search = $request->get('search')) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('agent', function ($q) use ($search) {
                        $q->where('business_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('applications.university', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('applications.course', function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%");
                    });
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $order  = $request->get('order', 'desc');

        $baseQuery->orderBy($sortBy, $order);

        // Agent filter
        if ($agentId = $request->get('agent')) {
            $baseQuery->where('agent_id', $agentId);
        }

        // Application status filter
        if ($status = $request->get('status')) {
            $baseQuery->whereHas('applications', function ($q) use ($status) {
                $q->where('application_status_id', $status);
            });
        }

        if ($university = $request->get('university')) {
            $baseQuery->whereHas('applications', function ($q) use ($university) {
                $q->where('university_id', $university);
            });
        }

        // Preferred country filter
        if ($country = $request->get('country')) {
            $baseQuery->where('preferred_country', $country);
        }

        // Quick filter
        match ($request->get('quick_filter')) {
            'applied'     => $baseQuery->whereHas('applications'),
            'not_applied' => $baseQuery->whereDoesntHave('applications'),
            default       => null,
        };

        // Clone query before applying agent scope split
        $allQuery     = clone $baseQuery;
        $partnerQuery = clone $baseQuery;

        // Split queries for two tables
        $specialAgentIds = [11, 12];

        $universities = University::whereHas('applications')
            ->withCount('applications')
            ->orderBy('name')
            ->get();

        $agents = User::agents()
            ->whereHas('students')
            ->withCount('students')
            ->orderBy('business_name')
            ->get();

        $applicationStatuses = ApplicationStatus::withCount('applications')
            ->having('applications_count', '>', 0)
            ->get();

        // Get countries with application counts - UPDATED
        $countries = collect(Application::whereHas('student')
            ->whereNotNull('university_id')
            ->join('universities', 'applications.university_id', '=', 'universities.id')
            ->select('universities.country', DB::raw('COUNT(*) as application_count'))
            ->whereNotNull('universities.country')
            ->groupBy('universities.country')
            ->orderBy('application_count', 'DESC')
            ->get())
            ->map(function ($item) {
                return (object) [
                    'name' => $item->country,
                    'count' => (int) $item->application_count
                ];
            });

        $table1Students = $allQuery
            ->when(!empty($specialAgentIds), function ($q) use ($specialAgentIds) {
                $q->whereNotIn('agent_id', array_values($specialAgentIds));
            })
            ->paginate(15, ['*'], 'page1')
            ->withQueryString();

        $table2Students = $partnerQuery
            ->whereIn('agent_id', $specialAgentIds)
            ->paginate(15, ['*'], 'page2')
            ->withQueryString();

        // Attach doc stats
        foreach ([$table1Students, $table2Students] as $collection) {
            foreach ($collection as $student) {
                $stats                     = $student->getDocumentStats();
                $student->document_status   = $stats['status'];
                $student->document_progress = $stats['progress'];
                $student->uploaded_count    = $stats['uploaded_count'];
            }
        }

        return view('admin.students.index', [
            'table1Students'      => $table1Students,
            'table2Students'      => $table2Students,
            'agents'              => $agents,
            'universities'        => $universities,
            'totalRequiredDocs'   => count(Student::REQUIRED_DOCUMENTS),
            'sort_by'             => $sortBy,
            'order'               => $order,
            'countries'           => $countries,
            'applicationStatuses' => $applicationStatuses,
        ]);
    }
    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function create()
    {
        return view('admin.students.create', [
            'student'      => new Student(),
            'agents'       => User::agents()->orderBy('business_name')->get(),
            'universities' => University::orderBy('name')->get(),
            'courses'      => Course::orderBy('title')->get(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function store(Request $request)
    {
        $data         = $this->validateStudent($request);
        $data['tags'] = $this->parseTags($request->get('tags', ''));

        $student = Student::create($data);

        if ($request->hasFile('students_photo')) {
            $student->students_photo = FileUploadService::uploadStudentFile(
                $request->file('students_photo'),
                Auth::user(),
                $student,
                'photo'
            );
            $student->saveQuietly();
        }

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Student created successfully.');
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function show(Student $student)
    {
        $student->load([
            'agent',
            'documents',
            'applications.university',
            'applications.course',
            'applications.messages.user',
            'currentStage',
        ]);
        $applications = $student->applications;

        $documentStats = $student->getDocumentStats();

        // same logic as index (important for consistency)
        $student->uploaded_count = $documentStats['uploaded_count'];
        return view('admin.students.show', [
            'student'            => $student,
            'applications'       => $applications,
            'documentStats'      => $documentStats,
            'totalRequiredDocs'  => count(Student::REQUIRED_DOCUMENTS),
        ]);
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function edit(Student $student)
    {
        return view('admin.students.edit', [
            'student'      => $student,
            'agents'       => User::agents()->orderBy('business_name')->get(),
            'universities' => University::orderBy('name')->get(),
            'courses'      => Course::orderBy('title')->get(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function update(Request $request, Student $student)
    {
        $data         = $this->validateStudent($request, $student->id);
        $data['tags'] = $this->parseTags($request->get('tags', ''));

        $student->update($data);

        if ($request->hasFile('students_photo')) {
            $student->students_photo = FileUploadService::uploadStudentFile(
                $request->file('students_photo'),
                Auth::user(),
                $student,
                'photo'
            );
            $student->saveQuietly();
        }

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Student updated successfully.');
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function destroy(Student $student)
    {
        $folder = sprintf(
            'agents/%s/%s',
            optional($student->agent)->slug ?? 'unknown',
            strtolower(str_replace(' ', '-', "{$student->first_name}-{$student->last_name}"))
        );

        if (Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->deleteDirectory($folder);
        }

        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Student deleted successfully.');
    }

    // -------------------------------------------------------------------------
    // Student Applications listing
    // -------------------------------------------------------------------------

    public function applications(Student $student)
    {
        $student->load(['applications.university', 'applications.course']);

        return view('admin.students.applications', compact('student'));
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    private function validateStudent(Request $request, ?int $excludeId = null): array
    {
        $emailRule = 'nullable|email|unique:students,email' . ($excludeId ? ",{$excludeId}" : '');

        return $request->validate([
            'agent_id'            => 'nullable|exists:users,id',
            'first_name'          => 'required|string|max:255',
            'last_name'           => 'required|string|max:255',
            'dob'                 => 'nullable|date',
            'gender'              => ['nullable', 'in:' . implode(',', Student::GENDERS)],
            'email'               => $emailRule,
            'phone_number'        => 'nullable|string|max:50',
            'permanent_address'   => 'nullable|string|max:255',
            'temporary_address'   => 'nullable|string|max:255',
            'nationality'         => 'nullable|string|max:100',
            'passport_number'     => 'nullable|string|max:100',
            'passport_expiry'     => 'nullable|date',
            'marital_status'      => ['nullable', 'in:' . implode(',', Student::MARITAL_STATUSES)],
            'qualification'       => 'nullable|string|max:255',
            'passed_year'         => 'nullable|integer|min:1900|max:' . date('Y'),
            'gap'                 => 'nullable|integer|min:0|max:20',
            'last_grades'         => 'nullable|string|max:50',
            'education_board'     => 'nullable|string|max:100',
            'preferred_country'   => 'nullable|string|max:100',
            'preferred_city'      => 'nullable|string|max:100',
            'preferred_course'    => 'nullable|string|max:255',
            'preferred_university' => 'nullable|string|max:255',
            'remarks'             => 'nullable|string',
            'follow_up_date'      => 'nullable|date',
            'students_photo'      => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);
    }

    private function parseTags(string $raw): array
    {
        if (empty(trim($raw))) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
}
