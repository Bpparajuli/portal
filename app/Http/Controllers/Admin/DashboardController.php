<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $weeklyData = $this->getWeeklyTrendData();
        $weeklyLabels = $weeklyData['labels'];
        $weeklyAppsData = $weeklyData['applications'];
        $weeklyStudentsData = $weeklyData['students'];

        // Growth percentage vs last month
        $appGrowth = $this->getApplicationGrowth();

        // ========== PIPELINE SUMMARY (Application Statuses) ==========
        $pipelineSummary = ApplicationStatus::where('is_active', 1)
            ->withCount('applications')
            ->orderBy('sort_order')
            ->get();

        // ========== MONTHLY APPLICATIONS CHART ==========
        $applicationsChartData = $this->monthlyApplicationsChart();

        // ========== APPLICATIONS BY STATUS CHART ==========
        $statusChartData = $this->applicationsByStatusChart();

        // ========== APPLICATIONS BY UNIVERSITY CHART ==========
        $universityChartData = $this->applicationsByUniversityChart();

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

        // ========== ACTIVITY FEEDS ==========
        $studentActivities = Activity::with('student', 'user')
            ->whereIn('type', ['student_added', 'student_deleted', 'student_updated'])
            ->latest()
            ->take(7)
            ->get()
            ->map(function ($activity) {
                $activity->link = $activity->student_id ? route('admin.students.show', $activity->student_id) : '#';
                $activity->description = $this->formatActivityDescription($activity);
                return $activity;
            });

        $applicationActivities = Activity::with('application', 'user')
            ->whereIn('type', ['application_submitted', 'application_withdrawn', 'application_updated', 'application_status_changed'])
            ->latest()
            ->take(7)
            ->get()
            ->map(function ($activity) {
                $activity->link = $activity->application_id ? route('admin.applications.show', $activity->application_id) : '#';
                $activity->description = $this->formatActivityDescription($activity);
                return $activity;
            });

        $documentActivities = Activity::with('student', 'document', 'user')
            ->whereIn('type', ['document_uploaded', 'document_deleted', 'document_updated'])
            ->latest()
            ->take(7)
            ->get()
            ->map(function ($activity) {
                $activity->link = $activity->document_id ? '#' : '#';
                $activity->description = $this->formatActivityDescription($activity);
                return $activity;
            });

        $widgets = Setting::getValue('dashboard_widgets', []);
        $welcomeMessage = Setting::getValue('welcome_message', 'Welcome to the admin dashboard.');

        return view('admin.dashboard', compact(
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

    /**
     * Get weekly trend data for last 7 days
     */
    private function getWeeklyTrendData(): array
    {
        $labels = [];
        $applications = [];
        $students = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('D, M d');

            // Applications count for this day
            $applications[] = Application::whereDate('created_at', $date)->count();

            // Students count for this day
            $students[] = Student::whereDate('created_at', $date)->count();
        }

        return [
            'labels' => $labels,
            'applications' => $applications,
            'students' => $students
        ];
    }

    /**
     * Calculate application growth percentage compared to last month
     */
    private function getApplicationGrowth(): float
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $lastMonth = Carbon::now()->subMonth()->month;
        $lastMonthYear = Carbon::now()->subMonth()->year;

        $currentMonthApps = Application::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();

        $lastMonthApps = Application::whereMonth('created_at', $lastMonth)
            ->whereYear('created_at', $lastMonthYear)
            ->count();

        if ($lastMonthApps == 0) {
            return $currentMonthApps > 0 ? 100 : 0;
        }

        return round((($currentMonthApps - $lastMonthApps) / $lastMonthApps) * 100, 1);
    }

    /**
     * Monthly Applications Chart Data
     */
    private function monthlyApplicationsChart(): array
    {
        $apps = Application::whereYear('created_at', now()->year)->get();
        $grouped = $apps->groupBy(fn($app) => (int) $app->created_at->format('n'));

        $data = array_fill(0, 12, 0);
        foreach ($grouped as $month => $items) {
            $data[$month - 1] = $items->count();
        }

        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'datasets' => [[
                'label' => 'Applications',
                'data' => $data,
                'borderColor' => '#820b5c',
                'backgroundColor' => 'rgba(130, 11, 92, 0.1)',
                'borderWidth' => 2,
                'pointBackgroundColor' => '#1a0262',
                'pointBorderColor' => '#fff',
                'pointRadius' => 4,
                'pointHoverRadius' => 6,
                'tension' => 0.3,
                'fill' => true
            ]]
        ];
    }

    /**
     * Applications by Status Chart Data
     */
    private function applicationsByStatusChart(): array
    {
        $data = Application::join('application_statuses', 'applications.application_status_id', '=', 'application_statuses.id')
            ->select(
                'application_statuses.id',
                'application_statuses.name',
                'application_statuses.bg_color',
                'application_statuses.text_color',
                'application_statuses.sort_order',
                DB::raw('COUNT(applications.id) as count')
            )
            ->where('application_statuses.is_active', 1)
            ->groupBy(
                'application_statuses.id',
                'application_statuses.name',
                'application_statuses.bg_color',
                'application_statuses.text_color',
                'application_statuses.sort_order'
            )
            ->orderBy('application_statuses.sort_order')
            ->get();

        $backgroundColors = $data->pluck('bg_color')->map(function ($color) {
            // Add transparency for better doughnut chart appearance
            return $color . 'cc';
        })->toArray();

        return [
            'labels' => $data->pluck('name')->toArray(),
            'datasets' => [[
                'label' => 'Applications',
                'data' => $data->pluck('count')->toArray(),
                'backgroundColor' => $backgroundColors,
                'borderColor' => '#fff',
                'borderWidth' => 2,
                'hoverOffset' => 8
            ]],
            'statuses' => $data

        ];
    }

    /**
     * Applications by University Chart Data (Top 12)
     */
    private function applicationsByUniversityChart(): array
    {
        $data = DB::table('applications')
            ->join('universities', 'applications.university_id', '=', 'universities.id')
            ->select(
                'universities.short_name',
                'universities.name as full_name',
                DB::raw('COUNT(applications.id) as count')
            )
            ->groupBy('universities.short_name', 'universities.name')
            ->orderByDesc('count')
            ->limit(12)
            ->get();

        $labels = $data->pluck('short_name')->toArray();
        $counts = $data->pluck('count')->toArray();

        // Generate distinct colors for each bar
        $baseColors = [
            '#1a0262',
            '#60a5fa',
            '#820b5c',
            '#facc15',
            '#22c55e',
            '#ef4444',
            '#8b5cf6',
            '#f97316',
            '#0ea5e9',
            '#16a34a',
            '#b91c1c',
            '#6b7280'
        ];

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Applications',
                'data' => $counts,
                'backgroundColor' => array_slice($baseColors, 0, count($labels)),
                'borderRadius' => 4,
                'barPercentage' => 0.7,
                'categoryPercentage' => 0.8
            ]]
        ];
    }

    /**
     * Format activity description for display
     */
    private function formatActivityDescription($activity): string
    {
        $userName = $activity->user->name ?? 'System';

        switch ($activity->type) {
            case 'student_added':
                $studentName = $activity->student->first_name ?? 'Student';
                return "<strong>{$userName}</strong> added student <strong>{$studentName}</strong>";
            case 'student_updated':
                $studentName = $activity->student->first_name ?? 'Student';
                return "<strong>{$userName}</strong> updated student <strong>{$studentName}</strong>";
            case 'student_deleted':
                return "<strong>{$userName}</strong> deleted a student record";
            case 'application_submitted':
                return "<strong>{$userName}</strong> submitted a new application";
            case 'application_updated':
                return "<strong>{$userName}</strong> updated an application";
            case 'application_status_changed':
                $oldStatus = $activity->old_value ?? 'Unknown';
                $newStatus = $activity->new_value ?? 'Unknown';
                return "<strong>{$userName}</strong> changed application status from {$oldStatus} to {$newStatus}";
            case 'application_withdrawn':
                return "<strong>{$userName}</strong> withdrew an application";
            case 'document_uploaded':
                return "<strong>{$userName}</strong> uploaded a new document";
            case 'document_updated':
                return "<strong>{$userName}</strong> updated a document";
            case 'document_deleted':
                return "<strong>{$userName}</strong> deleted a document";
            default:
                return "<strong>{$userName}</strong> performed {$activity->type}";
        }
    }

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
