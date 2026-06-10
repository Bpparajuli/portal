<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\University;
use App\Models\Course;
use App\Models\Student;
use App\Models\Activity;
use App\Models\ApplicationStatus;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        $agentId = Auth::id();

        // ---------- BASIC METRICS ----------
        $totalStudents = Student::where('agent_id', $agentId)->count();
        $totalApplications = Application::where('agent_id', $agentId)->count();
        $today = Carbon::today();

        $thisMonthStudents = Student::where('agent_id', $agentId)
            ->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        $thisMonthApps = Application::where('agent_id', $agentId)
            ->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        // ---------- VISA CONVERSION ----------
        $visaApprovedStatusId = ApplicationStatus::where('name', 'Visa Approved')->value('id');
        $visaApproved = Application::where('agent_id', $agentId)
            ->where('application_status_id', $visaApprovedStatusId)->count();
        $visaConversionPercent = $totalApplications > 0
            ? round(($visaApproved / $totalApplications) * 100, 1) : 0;

        // ---------- GROWTH / TRENDS ----------
        $appGrowth = $this->dashboardService->applicationGrowth($agentId);
        $weeklyData = $this->dashboardService->weeklyTrendData($agentId);
        $weeklyLabels = $weeklyData['labels'];
        $weeklyAppsData = $weeklyData['applications'];
        $weeklyStudentsData = $weeklyData['students'];

        // ---------- STATUS (ONLY WITH APPLICATIONS) ----------
        $statuses = ApplicationStatus::where('is_active', 1)
            ->whereHas('applications', fn($q) => $q->where('agent_id', $agentId))
            ->withCount(['applications' => fn($q) => $q->where('agent_id', $agentId)])
            ->orderBy('sort_order')
            ->get();

        $statusLabels = $statuses->pluck('name')->values();
        $statusCounts = $statuses->pluck('applications_count')->values();
        $statusColors = $statuses->map(fn($s) => $s->bg_color ?? '#6b7280')->values();

        $statusChartData = $this->dashboardService->applicationsByStatusChart($agentId);

        // ---------- CHARTS ----------
        $universityChartData = $this->dashboardService->applicationsByUniversityChart($agentId, 10);
        $monthlyApps = $this->dashboardService->monthlyApplicationsChart(null, $agentId);

        // ---------- TOP LISTS ----------
        $topUniversities = University::select('universities.id', 'universities.name', 'universities.short_name')
            ->join('applications', 'applications.university_id', '=', 'universities.id')
            ->where('applications.agent_id', $agentId)
            ->selectRaw('COUNT(applications.id) as applications_count')
            ->groupBy('universities.id', 'universities.name', 'universities.short_name')
            ->orderByDesc('applications_count')
            ->limit(5)
            ->get();

        $topCourses = Course::whereHas('applications', fn($q) => $q->where('agent_id', $agentId))
            ->withCount(['applications' => fn($q) => $q->where('agent_id', $agentId)])
            ->orderByDesc('applications_count')
            ->take(5)
            ->get();

        // ---------- LATEST APPLICATIONS ----------
        $latestApplications = Application::with(['student', 'course', 'university', 'status'])
            ->where('agent_id', $agentId)->latest()->take(10)->get();

        // ---------- ACTIVITIES (grouped by type+student+date) ----------
        $baseTypes = ['student_added','student_deleted'];
        $appTypes  = ['application_submitted','application_withdrawn','application_status_changed'];
        $docTypes  = ['document_uploaded','document_deleted'];

        $studentGroups = $this->dashboardService->groupActivities(
            Activity::with('student', 'user')->where('user_id', $agentId)
                ->whereIn('type', $baseTypes)->latest()->take(50)->get()
        );
        $studentActivities = $studentGroups->take(7)->map(function ($g) {
            return (object) [
                'link' => $g['notifiable_id'] && in_array($g['type'], ['student_added','student_deleted'])
                    ? route('agent.students.show', $g['notifiable_id']) : '#',
                'description' => $this->dashboardService->formatGroupedDescription($g, 'agent'),
                'user' => $g['user'],
                'created_at' => $g['latest'],
            ];
        });

        $appGroups = $this->dashboardService->groupActivities(
            Activity::with('application', 'user')->where('user_id', $agentId)
                ->whereIn('type', $appTypes)->latest()->take(50)->get()
        );
        $applicationActivities = $appGroups->take(7)->map(function ($g) {
            return (object) [
                'link' => $g['notifiable_id']
                    ? route('agent.applications.show', $g['notifiable_id']) : '#',
                'description' => $this->dashboardService->formatGroupedDescription($g, 'agent'),
                'user' => $g['user'],
                'created_at' => $g['latest'],
            ];
        });

        $docGroups = $this->dashboardService->groupActivities(
            Activity::with('student', 'user')->where('user_id', $agentId)
                ->whereIn('type', $docTypes)->latest()->take(50)->get()
        );
        $documentActivities = $docGroups->take(7)->map(function ($g) {
            return (object) [
                'link' => '#',
                'description' => $this->dashboardService->formatGroupedDescription($g, 'agent'),
                'user' => $g['user'],
                'created_at' => $g['latest'],
            ];
        });

        $todayActivitiesCount = Activity::where('user_id', $agentId)
            ->whereIn('type', array_merge($baseTypes, $appTypes, $docTypes))
            ->whereDate('created_at', $today)->count();

        // ---------- FILTERS (university list) ----------
        $countries = University::select('country')->distinct()->pluck('country');
        $query = University::with('courses');
        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%{$keyword}%")
                ->orWhereHas('courses', fn($qc) => $qc->where('title', 'like', "%{$keyword}%")));
        }
        if ($request->filled('country')) $query->where('country', $request->country);
        if ($request->filled('city')) $query->where('city', $request->city);
        if ($request->filled('university_id')) $query->where('id', $request->university_id);
        if ($request->filled('course_id'))
            $query->whereHas('courses', fn($q) => $q->where('id', $request->course_id));
        $universities = $query->paginate(10)->withQueryString();

        return view('dashboard.agent-dashboard', compact(
            'totalStudents','totalApplications',
            'thisMonthStudents','thisMonthApps',
            'visaApproved','visaConversionPercent',
            'appGrowth',
            'weeklyLabels','weeklyAppsData','weeklyStudentsData',
            'statuses','statusLabels','statusCounts','statusColors','statusChartData',
            'universityChartData','monthlyApps',
            'topUniversities','topCourses',
            'latestApplications',
            'studentActivities','documentActivities','applicationActivities',
            'todayActivitiesCount',
            'countries','universities'
        ));
    }

    // Chart methods extracted to App\Services\DashboardService


    // ---------- AJAX FILTERS ----------
    public function getCities($country)
    {
        $cities = University::where('country', $country)->select('city')->distinct()->pluck('city');
        return response()->json($cities);
    }

    public function getUniversities($city)
    {
        $unis = University::where('city', $city)->select('id', 'name')->get();
        return response()->json($unis);
    }

    public function getCourses($universityId)
    {
        $courses = Course::where('university_id', $universityId)->select('id', 'title')->get();
        return response()->json($courses);
    }
}
