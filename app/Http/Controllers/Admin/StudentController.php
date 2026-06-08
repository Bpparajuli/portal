<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CreateStudentAction;
use App\Actions\DeleteStudentAction;
use App\Actions\UpdateStudentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Application;
use App\Models\ApplicationStatus;
use App\Models\Course;
use App\Models\Setting;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
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

        if ($agentId = $request->get('agent')) {
            $baseQuery->where('agent_id', $agentId);
        }
        if ($status = $request->get('status')) {
            $baseQuery->whereHas('applications', fn($q) => $q->where('application_status_id', $status));
        }
        if ($university = $request->get('university')) {
            $baseQuery->whereHas('applications', fn($q) => $q->where('university_id', $university));
        }
        if ($country = $request->get('country')) {
            $baseQuery->where('preferred_country', $country);
        }

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
            ->whereNotNull('universities.country')
            ->groupBy('universities.country')
            ->orderBy('application_count', 'DESC')
            ->get())
            ->map(fn($item) => (object) ['name' => $item->country, 'count' => (int) $item->application_count]);

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

        return view('admin.students.index', compact(
            'table1Students', 'table2Students', 'agents', 'universities',
            'applicationStatuses', 'sortBy', 'order', 'countries'
        ) + ['totalRequiredDocs' => count(Student::REQUIRED_DOCUMENTS)]);
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

    public function create()
    {
        return view('admin.students.create', [
            'student' => new Student(),
            'agents' => User::agents()->orderBy('business_name')->get(),
            'universities' => University::orderBy('name')->get(),
            'courses' => Course::orderBy('title')->get(),
        ]);
    }

    public function store(StoreStudentRequest $request)
    {
        $student = $this->createStudentAction->execute($request);

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Student created successfully.');
    }

    public function show(Student $student)
    {
        $student->load(['agent', 'documents', 'applications.university', 'applications.course', 'applications.messages.user', 'currentStage']);
        $documentStats = $student->getDocumentStats();

        return view('admin.students.show', [
            'student' => $student,
            'applications' => $student->applications,
            'documentStats' => $documentStats,
            'totalRequiredDocs' => count(Student::REQUIRED_DOCUMENTS),
        ]);
    }

    public function edit(Student $student)
    {
        return view('admin.students.edit', [
            'student' => $student,
            'agents' => User::agents()->orderBy('business_name')->get(),
            'universities' => University::orderBy('name')->get(),
            'courses' => Course::orderBy('title')->get(),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $student = $this->updateStudentAction->execute($student, $request);

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        $this->deleteStudentAction->execute($student);

        return redirect()->route('admin.students.index')
            ->with('success', 'Student deleted successfully.');
    }

    public function applications(Student $student)
    {
        $student->load(['applications.university', 'applications.course']);
        return view('admin.students.applications', compact('student'));
    }
}
