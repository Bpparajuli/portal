<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Application;
use App\Models\ApplicationStatus;
use App\Models\CrmTasks;
use App\Models\Revenue;
use App\Models\Student;
use App\Models\StudentNote;
use App\Models\StudentStage;
use App\Models\StudentStageHistory;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $user = Auth::user();
        $myStudentIds = Student::where('agent_id', $user->id)->pluck('id');

        // ── MONTHLY TASK REPORT (last 12 months, my tasks only) ──
        $monthlyRows = CrmTasks::where('created_by', $user->id)
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw("SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) as cancelled"))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $monthlyLabels = [];
        $monthlyPending = [];
        $monthlyCompleted = [];
        $monthlyCancelled = [];
        for ($i = 11; $i >= 0; $i--) {
            $label = now()->subMonths($i)->format('M Y');
            $key = now()->subMonths($i)->format('Y-m');
            $monthlyLabels[] = $label;
            $row = $monthlyRows->get($key);
            $monthlyPending[] = (int) ($row->pending ?? 0);
            $monthlyCompleted[] = (int) ($row->completed ?? 0);
            $monthlyCancelled[] = (int) ($row->cancelled ?? 0);
        }

        // ── STUDENTS BY STAGE (my students only) ──
        $stages = StudentStage::active()->ordered()->get();
        $stageLabels = [];
        $stageCounts = [];
        $stageColors = [];
        $studentsByStage = [];
        foreach ($stages as $stage) {
            $count = Student::whereIn('id', $myStudentIds)
                ->where('current_stage_id', $stage->id)->count();
            $stageLabels[] = $stage->name;
            $stageCounts[] = $count;
            $stageColors[] = $stage->color;
            $studentsByStage[] = [
                'id' => $stage->id,
                'name' => $stage->name,
                'color' => $stage->color,
                'count' => $count,
            ];
        }

        // ── STUDENTS BY APPLICATION STATUS (my students only, >0 count) ──
        $statuses = ApplicationStatus::active()->ordered()->get()
            ->map(fn($s) => (object) [
                'id' => $s->id, 'name' => $s->name,
                'bg_color' => $s->bg_color, 'text_color' => $s->text_color,
                'count' => Application::whereIn('student_id', $myStudentIds)
                    ->where('application_status_id', $s->id)->count(),
            ])
            ->filter(fn($s) => $s->count > 0)
            ->values();

        $statusChartData = [
            'labels' => $statuses->pluck('name'),
            'datasets' => [[
                'data' => $statuses->pluck('count'),
                'backgroundColor' => $statuses->pluck('bg_color'),
                'borderWidth' => 0,
            ]],
        ];

        // ── WEEKLY TASK REPORT (last 7 days, my tasks only) ──
        $weekStart = now()->subDays(6)->startOfDay();
        $weeklyTasks = CrmTasks::where('created_by', $user->id)
            ->where('created_at', '>=', $weekStart)
            ->select(DB::raw('DATE(created_at) as date'), 'status', DB::raw('COUNT(*) as count'))
            ->groupBy('date', 'status')
            ->get()
            ->keyBy(fn($i) => $i->date . '_' . $i->status);

        $weeklyLabels = [];
        $weeklyPending = [];
        $weeklyCompleted = [];
        $weeklyCancelled = [];
        $weeklyTotals = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->format('Y-m-d');
            $weeklyLabels[] = $date->format('D');
            $p = (int) optional($weeklyTasks->get($key . '_pending'))->count;
            $c = (int) optional($weeklyTasks->get($key . '_completed'))->count;
            $x = (int) optional($weeklyTasks->get($key . '_cancelled'))->count;
            $weeklyPending[] = $p;
            $weeklyCompleted[] = $c;
            $weeklyCancelled[] = $x;
            $weeklyTotals[] = $p + $c + $x;
        }

        // ── MY ACTIVITY (all tasks done by me, no limit) ──
        $recentActivities = $this->buildActivityFeed($user->id, 200);

        return view('dashboard.staff-dashboard', compact(
            'monthlyLabels', 'monthlyPending', 'monthlyCompleted', 'monthlyCancelled',
            'stageLabels', 'stageCounts', 'stageColors', 'studentsByStage',
            'statuses',
            'weeklyLabels', 'weeklyPending', 'weeklyCompleted', 'weeklyCancelled', 'weeklyTotals',
            'recentActivities',
        ));
    }

    public function activities()
    {
        $perPage = 25;
        $page = request()->get('page', 1);
        $all = $this->buildActivityFeed(Auth::user()->id, 9999);

        $total = $all->count();
        $items = $all->forPage($page, $perPage)->values();

        $activities = new LengthAwarePaginator(
            $items, $total, $perPage, $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('staff.activities', compact('activities'));
    }

    private function buildActivityFeed($userId, $take)
    {
        $completedTasks = CrmTasks::where('completed_by', $userId)
            ->with('student')
            ->latest('completed_at')
            ->take($take * 3)->get()
            ->map(fn($t) => $this->makeActivity('task_completed',
                'Task completed: ' . ($t->title ?? '') . ' for ' . $this->studentName($t->student),
                $t->student, $t->completed_at ?? $t->updated_at));

        $tasksCreated = CrmTasks::where('created_by', $userId)
            ->where('created_by', '!=', DB::raw('COALESCE(completed_by, 0)'))
            ->with('student', 'assignee')
            ->latest()->take($take * 3)->get()
            ->map(fn($t) => $this->makeActivity('task_created',
                ($t->assignee && $t->assignee->id === $userId
                    ? 'Self-assigned task'
                    : 'Task assigned to ' . optional($t->assignee)->name)
                . ': ' . ($t->title ?? '') . ' for ' . $this->studentName($t->student),
                $t->student, $t->created_at));

        $notes = StudentNote::where('created_by', $userId)
            ->with('student')
            ->latest()->take($take * 3)->get()
            ->map(fn($n) => $this->makeActivity('note_added',
                'Note added to ' . $this->studentName($n->student)
                . ($n->title ? ': ' . $n->title : ''),
                $n->student, $n->created_at));

        $revenues = Revenue::where('created_by', $userId)
            ->with('student')
            ->latest()->take($take * 3)->get()
            ->map(fn($r) => $this->makeActivity('revenue_added',
                'Revenue $' . number_format($r->amount, 2) . ' added to ' . $this->studentName($r->student),
                $r->student, $r->created_at));

        $stageChanges = StudentStageHistory::where('changed_by', $userId)
            ->with('student', 'fromStage', 'toStage')
            ->latest()->take($take * 3)->get()
            ->map(fn($h) => $this->makeActivity('stage_changed',
                'Stage changed: ' . (optional($h->fromStage)->name ?? 'Initial')
                . ' \u{2192} ' . optional($h->toStage)->name
                . ' for ' . $this->studentName($h->student),
                $h->student, $h->created_at));

        $docUploads = Activity::where('user_id', $userId)
            ->where('type', 'document_uploaded')
            ->latest()->take($take * 3)->get()
            ->map(fn($a) => $this->makeActivity('document_uploaded',
                $a->description ?: 'Document uploaded for student',
                null, $a->created_at, $a->link));

        return collect()
            ->merge($completedTasks)->merge($tasksCreated)->merge($notes)
            ->merge($revenues)->merge($stageChanges)->merge($docUploads)
            ->sortByDesc('created_at')
            ->take($take)
            ->values();
    }

    private function studentName($student): string
    {
        if (!$student) return 'Unknown Student';
        return trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
    }

    private function makeActivity(string $type, string $description, $student = null, $createdAt = null, ?string $link = null): object
    {
        return (object) [
            'type' => $type,
            'description' => $description,
            'student' => $student,
            'created_at' => $createdAt ? Carbon::parse($createdAt) : now(),
            'link' => $link ?: ($student ? route('staff.students.show', $student->id) : null),
        ];
    }
}
