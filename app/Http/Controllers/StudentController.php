<?php

namespace App\Http\Controllers;

use App\Actions\CreateStudentAction;
use App\Actions\DeleteStudentAction;
use App\Actions\UpdateStudentAction;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Application;
use App\Models\ApplicationStatus;
use App\Models\Course;
use App\Models\Document;
use App\Models\Setting;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function __construct(
        private readonly CreateStudentAction $createStudentAction,
        private readonly UpdateStudentAction $updateStudentAction,
        private readonly DeleteStudentAction $deleteStudentAction,
    ) {
        $this->authorizeResource(Student::class, 'student');
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->is_admin || $user->is_admin_staff) {
            return $this->adminIndex($request);
        }

        if ($user->is_agent || $user->is_agent_staff) {
            return $this->agentIndex($request);
        }

        abort(403);
    }

    private function adminIndex(Request $request)
    {
        $baseQuery = Student::with(['agent', 'documents', 'applications']);

        if ($search = $request->get('search')) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('agent', fn($q) => $q->where('business_name', 'like', "%{$search}%"))
                    ->orWhereHas('applications.university', fn($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('applications.course', fn($q) => $q->where('title', 'like', "%{$search}%"));
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $order = $request->get('order', 'desc');
        $baseQuery->orderBy($sortBy, $order);

        if ($agentId = $request->get('agent')) $baseQuery->where('agent_id', $agentId);
        if ($status = $request->get('status')) $baseQuery->whereHas('applications', fn($q) => $q->where('application_status_id', $status));
        if ($university = $request->get('university')) $baseQuery->whereHas('applications', fn($q) => $q->where('university_id', $university));
        if ($country = $request->get('country')) $baseQuery->where('preferred_country', $country);

        match ($request->get('quick_filter')) {
            'applied' => $baseQuery->whereHas('applications'),
            'not_applied' => $baseQuery->whereDoesntHave('applications'),
            default => null,
        };

        $allQuery = clone $baseQuery;
        $partnerQuery = clone $baseQuery;
        $specialAgentIds = Setting::getValue('partner_agent_ids', [11, 12]);

        $universities = University::whereHas('applications')->withCount('applications')->orderBy('name')->get();
        $agents = User::agents()->whereHas('students')->withCount('students')->orderBy('business_name')->get();
        $applicationStatuses = ApplicationStatus::withCount('applications')->get()->filter(fn($s) => $s->applications_count > 0)->values();

        $countries = collect(Application::whereHas('student')->whereNotNull('university_id')
            ->join('universities', 'applications.university_id', '=', 'universities.id')
            ->select('universities.country', DB::raw('COUNT(*) as application_count'))
            ->whereNotNull('universities.country')->groupBy('universities.country')
            ->orderBy('application_count', 'DESC')->get())
            ->map(fn($i) => (object) ['name' => $i->country, 'count' => (int) $i->application_count]);

        $table1Students = $allQuery->when(!empty($specialAgentIds), fn($q) => $q->whereNotIn('agent_id', $specialAgentIds))
            ->paginate(15, ['*'], 'page1')->withQueryString();
        $table2Students = $partnerQuery->whereIn('agent_id', $specialAgentIds)
            ->paginate(15, ['*'], 'page2')->withQueryString();

        foreach ([$table1Students, $table2Students] as $collection) {
            foreach ($collection as $student) {
                $stats = $student->getDocumentStats();
                $student->document_status = $stats['status'];
                $student->document_progress = $stats['progress'];
                $student->uploaded_count = $stats['uploaded_count'];
            }
        }

        return view('shared.students.index', compact(
            'table1Students', 'table2Students', 'agents', 'universities',
            'applicationStatuses', 'sortBy', 'order', 'countries'
        ) + ['totalRequiredDocs' => count(Student::REQUIRED_DOCUMENTS)]);
    }

    private function agentIndex(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->integer('per_page', 15);
        $agentId = $user->is_agent ? $user->id : $user->parent_id;

        $query = Student::with(['applications.university', 'applications.status', 'documents'])
            ->accessible();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($country = $request->get('country')) {
            $query->whereHas('applications.university', fn($q) => $q->where('country', $country));
        }

        if ($university = $request->get('university')) {
            $query->whereHas('applications', fn($q) => $q->where('university_id', $university));
        }

        if ($appStatusId = $request->get('application_status_id')) {
            $query->whereHas('applications', fn($q) => $q->where('application_status_id', $appStatusId));
        }

        if ($docStatus = $request->get('document_status')) {
            $required = Student::REQUIRED_DOCUMENTS;
            $totalRequired = count($required);
            $query->addSelect([
                'required_doc_count' => Document::selectRaw('COUNT(DISTINCT document_type)')
                    ->whereColumn('student_id', 'students.id')->whereIn('document_type', $required),
            ]);
            match ($docStatus) {
                'Completed' => $query->having('required_doc_count', '=', $totalRequired),
                'Not Uploaded' => $query->havingRaw('COALESCE(required_doc_count, 0) = 0'),
                'Incomplete' => $query->havingRaw("COALESCE(required_doc_count, 0) > 0 AND COALESCE(required_doc_count, 0) < {$totalRequired}"),
                default => null,
            };
        }

        $universities = University::whereHas('applications', fn($q) => $q->where('agent_id', $agentId))
            ->withCount(['applications' => fn($q) => $q->where('agent_id', $agentId)])
            ->orderBy('name')->get();

        $applicationStatuses = ApplicationStatus::where('is_active', 1)->orderBy('sort_order')->get()
            ->map(function ($status) use ($agentId) {
                $status->applications_count = Application::where('application_status_id', $status->id)
                    ->whereHas('student', fn($q) => $q->where('agent_id', $agentId))->count();
                return $status;
            })->filter(fn($s) => $s->applications_count > 0)->values();

        $students = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        foreach ($students as $student) {
            $stats = $student->getDocumentStats();
            $student->document_status = $stats['status'];
            $student->document_progress = $stats['progress'];
            $student->uploaded_count = $stats['uploaded_count'];

            $latestApp = $student->applications->sortByDesc('created_at')->first();
            if ($latestApp && $latestApp->status) {
                $student->latest_status_name = $latestApp->status->name;
                $student->latest_status_bg_color = $latestApp->status->bg_color;
                $student->latest_status_text_color = $latestApp->status->text_color;
            }
        }

        $dashboardStats = Cache::remember('agent_dashboard_stats_' . $agentId, 300, function () use ($agentId) {
            return [
                'totalStudents' => Student::where('agent_id', $agentId)->count(),
                'totalApplied' => Student::where('agent_id', $agentId)->whereHas('applications')->count(),
                'admittedEnrolled' => Student::where('agent_id', $agentId)
                    ->whereHas('applications', fn($q) => $q->whereIn('application_status_id', function ($sub) {
                        $sub->select('id')->from('application_statuses')
                            ->whereIn('name', ['Accepted by the University', 'Visa Approved']);
                    }))->count(),
                'documentCompleted' => Student::countStudentsWithAllCompulsoryDocuments($agentId),
            ];
        });

        $countries = Cache::remember('agent_application_countries_' . $agentId, 3600, function () use ($agentId) {
            return Application::where('agent_id', $agentId)->whereNotNull('university_id')
                ->join('universities', 'applications.university_id', '=', 'universities.id')
                ->select('universities.country', DB::raw('COUNT(*) as application_count'))
                ->whereNotNull('universities.country')->groupBy('universities.country')
                ->orderBy('application_count', 'DESC')->get()
                ->map(fn($i) => (object) ['name' => $i->country, 'count' => (int) $i->application_count]);
        });

        if ($countries->isNotEmpty() && is_string($countries->first())) {
            Cache::forget('agent_application_countries_' . $agentId);
            $countries = collect();
        }

        return view('shared.students.index', array_merge(
            compact('students', 'countries', 'universities', 'applicationStatuses'),
            ['totalRequiredDocs' => count(Student::REQUIRED_DOCUMENTS)],
            $dashboardStats
        ));
    }

    public function create()
    {
        $user = Auth::user();

        if ($user->is_admin || $user->is_admin_staff) {
            return view('shared.students.create', [
                'student' => new Student(),
                'agents' => User::agents()->orderBy('business_name')->get(),
                'universities' => University::orderBy('name')->get(),
                'courses' => Course::orderBy('title')->get(),
            ]);
        }

        if ($user->is_staff && !$user->is_admin_staff) {
            return view('shared.students.create', ['student' => new Student()]);
        }

        return view('shared.students.create', ['student' => new Student()]);
    }

    public function store(StoreStudentRequest $request)
    {
        $user = Auth::user();
        if ($user->is_agent || $user->is_agent_staff) {
            $request->merge(['agent_id' => $user->id]);
        }

        $student = $this->createStudentAction->execute($request);

        if ($user->is_agent) {
            Cache::forget('agent_dashboard_stats_' . $user->id);
            Cache::forget('agent_application_countries_' . $user->id);
        }

        return redirect()->route($user->role . '.students.show', $student)
            ->with('success', 'Student created successfully.');
    }

    public function show(Student $student)
    {
        $user = Auth::user();

        $student->load(['agent', 'documents', 'applications.university', 'applications.course', 'applications.messages.user', 'currentStage']);
        $documentStats = $student->getDocumentStats();

        return view('shared.students.show', [
            'student' => $student,
            'applications' => $student->applications,
            'documentStats' => $documentStats,
            'totalRequiredDocs' => count(Student::REQUIRED_DOCUMENTS),
        ]);
    }

    public function edit(Student $student)
    {
        $user = Auth::user();

        // All roles use the same shared edit view
        $data = ['student' => $student];

        if ($user->is_admin || $user->is_admin_staff) {
            $data['agents'] = User::agents()->orderBy('business_name')->get();
            $data['universities'] = University::orderBy('name')->get();
            $data['courses'] = Course::orderBy('title')->get();
        } elseif ($user->is_staff && !$user->is_admin_staff) {
            $data['agents'] = User::agents()->orderBy('business_name')->get();
        }

        return view('shared.students.edit', $data);
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $this->updateStudentAction->execute($student, $request);

        $user = Auth::user();
        if ($user->is_agent) {
            Cache::forget('agent_dashboard_stats_' . $user->id);
        }

        return redirect()->route($user->role . '.students.show', $student)
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        $this->deleteStudentAction->execute($student);

        $user = Auth::user();
        if ($user->is_agent) {
            Cache::forget('agent_dashboard_stats_' . $user->id);
        }

        return redirect()->route($user->role . '.students.index')
            ->with('success', 'Student deleted successfully.');
    }

    public function export(Request $request)
    {
        $query = Student::with('agent');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $students = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($students) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Agent', 'Passport Number', 'Nationality', 'DOB', 'Created At']);
            foreach ($students as $s) {
                fputcsv($file, [
                    $s->id, $s->first_name, $s->last_name, $s->email ?? '—', $s->phone_number ?? '—',
                    $s->agent?->business_name ?? '—', $s->passport_number ?? '—', $s->nationality ?? '—',
                    $s->date_of_birth ?? '—', $s->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function chat()
    {
        return view('shared.chat.index');
    }
}
