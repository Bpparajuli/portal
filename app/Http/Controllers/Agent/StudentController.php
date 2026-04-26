<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Application;
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
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    // -------------------------------------------------------------------------
    // Index – list students with filters
    // -------------------------------------------------------------------------

    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 15);

        $query = Student::with(['applications.university', 'documents'])
            ->where('agent_id', Auth::id());

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        // Preferred country filter
        if ($country = $request->get('country')) {
            $query->where('preferred_country', $country);
        }

        // University filter (through applications)
        if ($university = $request->get('university')) {
            $query->whereHas('applications', function ($q) use ($university) {
                $q->where('university_id', $university);
            });
        }

        // Application status filter
        if ($appStatus = $request->get('application_status')) {
            $query->whereHas('applications', fn($q) => $q->where('application_status', $appStatus));
        }

        // Document status filter using subquery
        if ($docStatus = $request->get('document_status')) {
            $required      = Student::REQUIRED_DOCUMENTS;
            $totalRequired = count($required);

            $query->addSelect([
                'required_doc_count' => Document::selectRaw('COUNT(DISTINCT document_type)')
                    ->whereColumn('student_id', 'students.id')
                    ->whereIn('document_type', $required),
            ]);

            match ($docStatus) {
                'Completed'    => $query->having('required_doc_count', '=', $totalRequired),
                'Not Uploaded' => $query->havingRaw('COALESCE(required_doc_count, 0) = 0'),
                'Incomplete'   => $query->havingRaw("COALESCE(required_doc_count, 0) > 0 AND COALESCE(required_doc_count, 0) < {$totalRequired}"),
                default        => null,
            };
        }
        $universities = University::whereHas('applications', function ($q) {
            $q->where('agent_id', Auth::id());
        })
            ->withCount(['applications' => function ($q) {
                $q->where('agent_id', Auth::id());
            }])
            ->orderBy('name')
            ->get();

        $students = $query->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        // Attach document stats from already-loaded relationship (no extra queries)
        foreach ($students as $student) {
            $stats                     = $student->getDocumentStats();
            $student->document_status   = $stats['status'];
            $student->document_progress = $stats['progress'];
            $student->uploaded_count    = $stats['uploaded_count'];
        }

        // Dashboard stats (cached 5 min)
        $dashboardStats = Cache::remember('agent_dashboard_stats_' . Auth::id(), 300, function () {
            $agentId = Auth::id();

            return [
                'totalStudents'    => Student::where('agent_id', $agentId)->count(),
                'totalApplied'     => Student::where('agent_id', $agentId)->whereHas('applications')->count(),
                'admittedEnrolled' => Student::where('agent_id', $agentId)
                    ->whereHas('applications', fn($q) => $q->whereIn('application_status', ['Accepted by the University', 'Visa Approved']))
                    ->count(),
                'documentCompleted' => Student::countStudentsWithAllCompulsoryDocuments($agentId),
            ];
        });

        // Country dropdown (cached 1 hour)
        $countries = Cache::remember(
            'agent_countries_' . Auth::id(),
            3600,
            fn() =>
            Student::where('agent_id', Auth::id())
                ->whereNotNull('preferred_country')
                ->distinct()
                ->pluck('preferred_country')
                ->filter()
                ->values()
        );

        return view('agent.students.index', [
            'students'            => $students,
            'countries'           => $countries,
            'universities'        => $universities,
            'applicationStatuses' => Application::STATUSES,
            'totalRequiredDocs'   => count(Student::REQUIRED_DOCUMENTS),
            ...$dashboardStats,
        ]);
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function create()
    {
        return view('agent.students.create', [
            'student' => new Student(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function store(Request $request)
    {
        $data             = $this->validateStudent($request);
        $data['tags']     = $this->parseTags($request->get('tags', ''));
        $data['agent_id'] = Auth::id();

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

        $this->notifyAdmin(new StudentAdded(Auth::user(), $student));
        $this->clearAgentCache();

        return redirect()->route('agent.students.index')
            ->with('success', 'Student created successfully.');
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function show(Student $student)
    {
        $this->authorizeStudent($student);

        $student->load(['documents', 'applications.university', 'applications.course', 'applications.messages.user']);

        return view('agent.students.show', [
            'student'          => $student,
            'documentStats'    => $student->getDocumentStats(),
            'totalRequiredDocs' => count(Student::REQUIRED_DOCUMENTS),
        ]);
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function edit(Student $student)
    {
        $this->authorizeStudent($student);

        return view('agent.students.edit', [
            'student' => $student,
        ]);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function update(Request $request, Student $student)
    {
        $this->authorizeStudent($student);

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

        $this->clearAgentCache();

        return redirect()->route('agent.students.show', $student)
            ->with('success', 'Student updated successfully.');
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function destroy(Student $student)
    {
        $this->authorizeStudent($student);

        $this->notifyAdmin(new StudentDeleted(Auth::user(), $student));

        // Remove student folder from storage
        $folder = sprintf(
            'agents/%s/%s',
            Auth::user()->slug,
            strtolower(str_replace(' ', '-', "{$student->first_name}-{$student->last_name}"))
        );

        if (Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->deleteDirectory($folder);
        }

        $student->delete();
        $this->clearAgentCache();

        return redirect()->route('agent.students.index')
            ->with('success', 'Student deleted successfully.');
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    private function validateStudent(Request $request, ?int $excludeId = null): array
    {
        $emailRule = 'nullable|email|unique:students,email' . ($excludeId ? ",{$excludeId}" : '');

        return $request->validate([
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

    private function authorizeStudent(Student $student): void
    {
        if ((int) $student->agent_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
    }

    private function notifyAdmin(object $notification): void
    {
        $admin = User::find(config('app.admin_user_id', 6));
        if ($admin) {
            Notification::send($admin, $notification);
        }
    }

    private function clearAgentCache(): void
    {
        Cache::forget('agent_dashboard_stats_' . Auth::id());
        Cache::forget('agent_countries_' . Auth::id());
    }
}
