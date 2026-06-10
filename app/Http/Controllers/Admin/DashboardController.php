<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Student;
use App\Models\University;
use App\Models\Course;
use App\Models\Application;
use App\Models\Activity;
use App\Models\ApplicationStatus;
use App\Models\Setting;
use Carbon\Carbon;

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
        if (!$user || !$user->is_admin) {
            abort(403, 'Unauthorized');
        }

        // ========== KPI COUNTS ==========
        $totalAgents       = User::agents()->count();
        $activeAgents      = User::agents()->where('agreement_status', 'verified')->count();
        $totalStudents     = Student::count();
        $totalUniversities = University::count();
        $totalCourses      = Course::count();
        $totalApplications = Application::count();
        $totalWaitingUsers = User::where('active', 0)->count();

        // This month counts
        $thisMonthStudents = Student::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        $thisMonthApps = Application::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // ========== TODAY'S ACTIVITY COUNTS ==========
        $today = Carbon::today();

        $todayStudentCount = Activity::whereIn('type', ['student_added', 'student_deleted'])
            ->whereDate('created_at', $today)
            ->count();

        $todayDocumentCount = Activity::whereIn('type', ['document_uploaded', 'document_deleted'])
            ->whereDate('created_at', $today)
            ->count();

        $todayApplicationCount = Activity::whereIn('type', ['application_submitted', 'application_withdrawn'])
            ->whereDate('created_at', $today)
            ->count();

        // ========== 7-DAY TREND DATA ==========
        $weeklyData = $this->dashboardService->weeklyTrendData();
        $weeklyLabels = $weeklyData['labels'];
        $weeklyAppsData = $weeklyData['applications'];
        $weeklyStudentsData = $weeklyData['students'];

        // Growth percentage vs last month
        $appGrowth = $this->dashboardService->applicationGrowth();

        // ========== PIPELINE SUMMARY (Application Statuses) ==========
        $pipelineSummary = ApplicationStatus::where('is_active', 1)
            ->withCount('applications')
            ->orderBy('sort_order')
            ->get();

        // ========== MONTHLY APPLICATIONS CHART ==========
        $applicationsChartData = $this->dashboardService->monthlyApplicationsChart();

        // ========== APPLICATIONS BY STATUS CHART ==========
        $statusChartData = $this->dashboardService->applicationsByStatusChart();

        // ========== APPLICATIONS BY UNIVERSITY CHART ==========
        $universityChartData = $this->dashboardService->applicationsByUniversityChart();

        // ========== TOP LISTS ==========
        $topAgents = User::agents()
            ->withCount('applications')
            ->orderByDesc('applications_count')
            ->take(10)
            ->get()
            ->filter(fn($u) => $u->applications_count > 0)
            ->take(5)
            ->values();

        $topCourses = Course::withCount('applications')
            ->orderByDesc('applications_count')
            ->take(10)
            ->get()
            ->filter(fn($c) => $c->applications_count > 0)
            ->take(5)
            ->values();

        $topUniversities = University::withCount('applications')
            ->orderByDesc('applications_count')
            ->take(10)
            ->get()
            ->filter(fn($u) => $u->applications_count > 0)
            ->take(5)
            ->values();

        // ========== PENDING AGENTS ==========
        $pendingAgents = User::agents()
            ->where(function ($q) {
                $q->where('active', 0)
                    ->orWhere('agreement_status', '!=', 'verified')
                    ->orWhereNull('agreement_status');
            })
            ->latest()
            ->take(10)
            ->get();

        // ========== LATEST APPLICATIONS ==========
        $latestApplications = Application::with(['student', 'agent', 'course', 'university', 'status'])
            ->latest()
            ->take(10)
            ->get();

        // ========== ACTIVITY FEEDS (grouped by type+student+date) ==========
        $studentGroups = $this->dashboardService->groupActivities(
            Activity::with('student', 'user')
                ->whereIn('type', ['student_added', 'student_deleted', 'student_updated'])
                ->latest()->take(50)->get()
        );
        $studentActivities = $studentGroups->take(7)->map(function ($g) {
            return (object) [
                'link' => $g['notifiable_id'] ? route('admin.students.show', $g['notifiable_id']) : '#',
                'description' => $this->dashboardService->formatGroupedDescription($g, 'admin'),
                'user' => $g['user'],
                'created_at' => $g['latest'],
            ];
        });

        $appGroups = $this->dashboardService->groupActivities(
            Activity::with('application', 'user')
                ->whereIn('type', ['application_submitted', 'application_withdrawn', 'application_updated', 'application_status_changed'])
                ->latest()->take(50)->get()
        );
        $applicationActivities = $appGroups->take(7)->map(function ($g) {
            return (object) [
                'link' => $g['notifiable_id'] ? route('admin.applications.show', $g['notifiable_id']) : '#',
                'description' => $this->dashboardService->formatGroupedDescription($g, 'admin'),
                'user' => $g['user'],
                'created_at' => $g['latest'],
            ];
        });

        $docGroups = $this->dashboardService->groupActivities(
            Activity::with('student', 'user')
                ->whereIn('type', ['document_uploaded', 'document_deleted', 'document_updated'])
                ->latest()->take(50)->get()
        );
        $documentActivities = $docGroups->take(7)->map(function ($g) {
            return (object) [
                'link' => '#',
                'description' => $this->dashboardService->formatGroupedDescription($g, 'admin'),
                'user' => $g['user'],
                'created_at' => $g['latest'],
            ];
        });

        $widgets = Setting::getValue('dashboard_widgets', []);
        $welcomeMessage = Setting::getValue('welcome_message', 'Welcome to the admin dashboard.');

        return view('dashboard.admin-dashboard', compact(
            // KPI
            'totalAgents',
            'activeAgents',
            'totalStudents',
            'totalUniversities',
            'totalCourses',
            'totalApplications',
            'totalWaitingUsers',
            'thisMonthStudents',
            'thisMonthApps',

            // Today counts
            'todayStudentCount',
            'todayDocumentCount',
            'todayApplicationCount',

            // Weekly trend
            'weeklyLabels',
            'weeklyAppsData',
            'weeklyStudentsData',
            'appGrowth',

            // Pipeline
            'pipelineSummary',

            // Charts
            'applicationsChartData',
            'statusChartData',
            'universityChartData',

            // Top lists
            'topAgents',
            'topCourses',
            'topUniversities',

            // Pending
            'pendingAgents',

            // Latest records
            'latestApplications',

            // Activity feeds
            'studentActivities',
            'applicationActivities',
            'documentActivities',

            // Widget config
            'widgets',
            'welcomeMessage'
        ));
    }

    // Private chart/activity methods extracted to App\Services\DashboardService

    /**
     * Get cities by country (AJAX)
     */
    public function getCities($country)
    {
        $cities = University::where('country', $country)
            ->select('city')
            ->distinct()
            ->pluck('city');
        return response()->json($cities);
    }

    /**
     * Get universities by city (AJAX)
     */
    public function getUniversities($city)
    {
        $universities = University::where('city', $city)
            ->select('id', 'name')
            ->get();
        return response()->json($universities);
    }

    /**
     * Get courses by university (AJAX)
     */
    public function getCourses($universityId)
    {
        $courses = Course::where('university_id', $universityId)
            ->select('id', 'title')
            ->get();
        return response()->json($courses);
    }

    /**
     * Get dashboard summary for API/widget (optional)
     */
    public function summary()
    {
        return response()->json([
            'total_applications' => Application::count(),
            'total_students' => Student::count(),
            'total_universities' => University::count(),
            'total_courses' => Course::count(),
            'pending_users' => User::where('active', 0)->count(),
            'applications_this_month' => Application::whereMonth('created_at', Carbon::now()->month)->count(),
        ]);
    }
}
