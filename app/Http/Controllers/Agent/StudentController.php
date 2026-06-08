<?php

namespace App\Http\Controllers\Agent;

use App\Actions\CreateStudentAction;
use App\Actions\DeleteStudentAction;
use App\Actions\UpdateStudentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Application;
use App\Models\ApplicationStatus;
use App\Models\Document;
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
    ) {}

    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 15);

        $query = Student::with(['applications.university', 'applications.status', 'documents'])
            ->where('agent_id', Auth::id());

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
                    ->whereColumn('student_id', 'students.id')
                    ->whereIn('document_type', $required),
            ]);

            match ($docStatus) {
                'Completed' => $query->having('required_doc_count', '=', $totalRequired),
                'Not Uploaded' => $query->havingRaw('COALESCE(required_doc_count, 0) = 0'),
                'Incomplete' => $query->havingRaw("COALESCE(required_doc_count, 0) > 0 AND COALESCE(required_doc_count, 0) < {$totalRequired}"),
                default => null,
            };
        }

        $universities = University::whereHas('applications', fn($q) => $q->where('agent_id', Auth::id()))
            ->withCount(['applications' => fn($q) => $q->where('agent_id', Auth::id())])
            ->orderBy('name')->get();

        $applicationStatuses = ApplicationStatus::where('is_active', 1)->orderBy('sort_order')->get()
            ->map(function ($status) {
                $status->applications_count = Application::where('application_status_id', $status->id)
                    ->whereHas('student', fn($q) => $q->where('agent_id', Auth::id()))->count();
                return $status;
            })
            ->filter(fn($status) => $status->applications_count > 0)
            ->values();

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

        $dashboardStats = Cache::remember('agent_dashboard_stats_' . Auth::id(), 300, function () {
            $agentId = Auth::id();
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

        $countries = Cache::remember('agent_application_countries_' . Auth::id(), 3600, function () {
            return Application::where('agent_id', Auth::id())->whereNotNull('university_id')
                ->join('universities', 'applications.university_id', '=', 'universities.id')
                ->select('universities.country', DB::raw('COUNT(*) as application_count'))
                ->whereNotNull('universities.country')->groupBy('universities.country')
                ->orderBy('application_count', 'DESC')->get()
                ->map(fn($item) => (object) ['name' => $item->country, 'count' => (int) $item->application_count]);
        });

        if ($countries->isNotEmpty() && is_string($countries->first())) {
            Cache::forget('agent_application_countries_' . Auth::id());
            $countries = collect();
        }

        return view('agent.students.index', array_merge(
            compact('students', 'countries', 'universities', 'applicationStatuses'),
            ['totalRequiredDocs' => count(Student::REQUIRED_DOCUMENTS)],
            $dashboardStats
        ));
    }

    public function create()
    {
        return view('agent.students.create', ['student' => new Student()]);
    }

    public function store(StoreStudentRequest $request)
    {
        $request->merge(['agent_id' => Auth::id()]);
        $student = $this->createStudentAction->execute($request);
        $this->clearAgentCache();

        return redirect()->route('agent.students.index')
            ->with('success', 'Student created successfully.');
    }

    public function show(Student $student)
    {
        $this->authorizeStudent($student);

        $student->load(['documents', 'applications.university', 'applications.course', 'applications.messages.user']);
        $documentStats = $student->getDocumentStats();

        return view('agent.students.show', [
            'student' => $student,
            'applications' => $student->applications,
            'documentStats' => $documentStats,
            'totalRequiredDocs' => count(Student::REQUIRED_DOCUMENTS),
        ]);
    }

    public function edit(Student $student)
    {
        $this->authorizeStudent($student);
        return view('agent.students.edit', ['student' => $student]);
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $this->authorizeStudent($student);
        $student = $this->updateStudentAction->execute($student, $request);
        $this->clearAgentCache();

        return redirect()->route('agent.students.show', $student)
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        $this->authorizeStudent($student);
        $this->deleteStudentAction->execute($student);
        $this->clearAgentCache();

        return redirect()->route('agent.students.index')
            ->with('success', 'Student deleted successfully.');
    }

    private function authorizeStudent(Student $student): void
    {
        if ((int) $student->agent_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403, 'Unauthorized');
        }
    }

    private function clearAgentCache(): void
    {
        Cache::forget('agent_dashboard_stats_' . Auth::id());
        Cache::forget('agent_application_countries_' . Auth::id());
    }
}
