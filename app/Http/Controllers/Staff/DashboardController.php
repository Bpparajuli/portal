<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationStatus;
use App\Models\Student;
use App\Models\University;
use App\Models\Course;
use App\Models\User;
use App\Models\Activity;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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
        $today = Carbon::today();

        // ── Scope-aware query builders ──
        $studentQuery = Student::query()->accessible();
        $applicationQuery = Application::query();

        if ($user->is_agent_staff) {
            $applicationQuery->where('agent_id', $user->parent_id);
        }

        // ---------- KPI COUNTS ----------
        $totalStudents = (clone $studentQuery)->count();
        $totalApplications = (clone $applicationQuery)->count();
        $totalUniversities = University::count();

        $thisMonthStudents = (clone $studentQuery)
            ->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        $thisMonthApps = (clone $applicationQuery)
            ->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        // ---------- DOC COMPLETION ----------
        $studentsWithDocs = (clone $studentQuery)->whereHas('documents')->count();
        $docCompletionRate = $totalStudents > 0 ? round(($studentsWithDocs / $totalStudents) * 100) : 0;

        // ---------- TRENDS ----------
        $appGrowth = $this->dashboardService->applicationGrowth();
        $weeklyData = $this->dashboardService->weeklyTrendData();
        $weeklyLabels = $weeklyData['labels'];
        $weeklyAppsData = $weeklyData['applications'];
        $weeklyStudentsData = $weeklyData['students'];

        // ---------- PIPELINE ----------
        $statuses = ApplicationStatus::where('is_active', 1)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($status) use ($applicationQuery) {
                $q = clone $applicationQuery;
                $status->count = $q->where('application_status_id', $status->id)->count();
                return $status;
            });

        // ---------- CHARTS ----------
        $applicationsChartData = $this->dashboardService->monthlyApplicationsChart();
        $statusChartData = $this->dashboardService->applicationsByStatusChart();
        $universityChartData = $this->dashboardService->applicationsByUniversityChart();

        // ---------- TOP LISTS ----------
        $topAgents = User::agents()
            ->withCount('applications')
            ->orderByDesc('applications_count')
            ->take(10)->get()
            ->filter(fn($u) => $u->applications_count > 0)
            ->take(5)->values();

        $topCourses = Course::withCount('applications')
            ->orderByDesc('applications_count')
            ->take(10)->get()
            ->filter(fn($c) => $c->applications_count > 0)
            ->take(5)->values();

        $topUniversities = University::withCount('applications')
            ->orderByDesc('applications_count')
            ->take(10)->get()
            ->filter(fn($u) => $u->applications_count > 0)
            ->take(5)->values();

        // ---------- LATEST APPLICATIONS ----------
        $latestApplications = (clone $applicationQuery)
            ->with(['student', 'course', 'university', 'status'])
            ->latest()->take(10)->get();

        // ---------- ACTIVITIES (grouped by type+student+date) ----------
        $studentGroups = $this->dashboardService->groupActivities(
            Activity::with('user', 'student')
                ->whereIn('type', ['student_added','student_deleted','student_updated'])
                ->latest()->take(50)->get()
        );
        $studentActivities = $studentGroups->take(7)->map(function ($g) {
            return (object) [
                'link' => $g['notifiable_id'] ? route('staff.students.show', $g['notifiable_id']) : '#',
                'description' => $this->dashboardService->formatGroupedDescription($g, 'staff'),
                'user' => $g['user'],
                'created_at' => $g['latest'],
            ];
        });

        $appGroups = $this->dashboardService->groupActivities(
            Activity::with('user')
                ->whereIn('type', ['application_submitted','application_withdrawn','application_updated','application_status_changed'])
                ->latest()->take(50)->get()
        );
        $applicationActivities = $appGroups->take(7)->map(function ($g) {
            return (object) [
                'link' => $g['notifiable_id'] ? route('staff.applications.show', $g['notifiable_id']) : '#',
                'description' => $this->dashboardService->formatGroupedDescription($g, 'staff'),
                'user' => $g['user'],
                'created_at' => $g['latest'],
            ];
        });

        $docGroups = $this->dashboardService->groupActivities(
            Activity::with('user', 'student')
                ->whereIn('type', ['document_uploaded','document_deleted','document_updated'])
                ->latest()->take(50)->get()
        );
        $documentActivities = $docGroups->take(7)->map(function ($g) {
            return (object) [
                'link' => '#',
                'description' => $this->dashboardService->formatGroupedDescription($g, 'staff'),
                'user' => $g['user'],
                'created_at' => $g['latest'],
            ];
        });

        return view('dashboard.staff-dashboard', compact(
            'totalStudents','totalApplications','totalUniversities',
            'thisMonthStudents','thisMonthApps',
            'studentsWithDocs','docCompletionRate',
            'appGrowth',
            'weeklyLabels','weeklyAppsData','weeklyStudentsData',
            'statuses',
            'applicationsChartData','statusChartData','universityChartData',
            'topAgents','topCourses','topUniversities',
            'latestApplications',
            'studentActivities','applicationActivities','documentActivities'
        ));
    }
}
