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
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->is_admin) {
            abort(403, 'Unauthorized');
        }

        // === KPI COUNTS ===
        $totalAgents       = User::where('is_agent', 1)->count();
        $totalAdmins       = User::where('is_admin', 1)->count();
        $totalStudents     = Student::count();
        $totalUniversities = University::count();
        $totalCourses      = Course::count();
        $totalApplications = Application::count();
        $totalWaitingUsers = User::where('active', 0)->count();
        $totalActiveUsers  = User::where('active', 1)->count();


        // === Latest records ===
        $latestAgents       = User::where('is_agent', 1)->latest()->take(5)->get();
        $activities         = Activity::with('user')->latest()->take(5)->get();
        $latestApplications = Application::with(['student.agent', 'course', 'university'])->latest()->take(5)->get();

        // === Monthly Applications Chart ===
        $applicationsChartData = $this->monthlyApplicationsChart();

        // === Applications by Status dynamically from DB ===
        $statusChartData = $this->applicationsByStatusChart();

        // === Applications by Country dynamically from DB ===
        $countryChartData = $this->applicationsByCountryChart();

        // === Top Agents, Courses, Universities ===
        $topAgents       = User::where('is_agent', 1)->withCount('applications')->orderByDesc('applications_count')->take(5)->get();
        $topCourses      = Course::withCount('applications')->orderByDesc('applications_count')->take(5)->get();
        $topUniversities = University::withCount('applications')->orderByDesc('applications_count')->take(5)->get();

        // ---------- RECENT ACTIVITIES ----------
        // Last 5 student activities (added/deleted) across all users
        $studentActivities = Activity::with('student', 'user')
            ->whereIn('type', ['student_added', 'student_deleted'])
            ->latest()
            ->take(7)
            ->get();

        $documentActivities = Activity::with('student', 'document', 'user')
            ->whereIn('type', ['document_uploaded', 'document_deleted'])
            ->latest()
            ->take(5)
            ->get();

        $applicationActivities = Activity::with('application', 'user')
            ->whereIn('type', ['application_submitted', 'application_withdrawn'])
            ->latest()
            ->take(5)
            ->get();


        // ---------- TODAY'S COUNTS ----------
        $today = Carbon::today();

        // Count of student activities today
        $todayStudentCount = Activity::whereIn('type', ['student_added', 'student_deleted'])
            ->whereDate('created_at', $today)
            ->count();

        // Count of document activities today
        $todayDocumentCount = Activity::whereIn('type', ['document_uploaded', 'document_deleted'])
            ->whereDate('created_at', $today)
            ->count();

        // Count of application activities today
        $todayApplicationCount = Activity::whereIn('type', ['application_submitted', 'application_withdrawn'])
            ->whereDate('created_at', $today)
            ->count();



        return view('admin.dashboard', compact(
            'totalAgents',
            'totalAdmins',
            'totalStudents',
            'totalUniversities',
            'totalCourses',
            'totalWaitingUsers',
            'totalActiveUsers',
            'totalApplications',
            'latestAgents',
            'activities',
            'applicationsChartData',
            'statusChartData',
            'countryChartData',
            'latestApplications',
            'topAgents',
            'topCourses',
            'topUniversities',
            'studentActivities',
            'documentActivities',
            'applicationActivities',
            'todayStudentCount',
            'todayDocumentCount',
            'todayApplicationCount'
        ));
    }

    // === Helper to calculate % change vs last month ===
    private function percentChange($model, $column = null)
    {
        $query = $model::query();
        if ($column) $query->where($column, 1);

        $totalNow  = $query->count();
        $totalPrev = $query->whereMonth('created_at', now()->subMonth()->month)->count();

        return $totalPrev ? round(($totalNow - $totalPrev) / $totalPrev * 100, 1) : 0;
    }

    // === Monthly Applications Chart Data ===
    private function monthlyApplicationsChart()
    {
        $monthlyApplications = Application::select(
            DB::raw('COUNT(*) as count'),
            DB::raw('MONTH(created_at) as month')
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $data = array_fill(0, 12, 0);
        foreach ($monthlyApplications as $m) {
            $data[$m->month - 1] = $m->count;
        }

        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'datasets' => [[
                'label' => 'Applications',
                'data' => $data,
                'backgroundColor' => '#1a0262',
                'borderColor' => '#820b5c',
                'borderWidth' => 2,
                'borderRadius' => 4
            ]]
        ];
    }

    // === Applications by Status dynamically with base colors ===
    private function applicationsByStatusChart()
    {
        $statusCounts = Application::select('application_status', DB::raw('COUNT(*) as count'))
            ->groupBy('application_status')
            ->pluck('count', 'application_status')
            ->toArray();

        $statuses = array_keys($statusCounts);

        // Base color palette (repeats if more statuses exist)
        $baseColors = [
            '#3b82f6',
            '#60a5fa',
            '#818cf8',
            '#ef4444',
            '#facc15',
            '#22c55e',
            '#8b5cf6',
            '#f97316',
            '#0ea5e9',
            '#16a34a',
            '#b91c1c',
            '#6b7280'
        ];

        $statusColors = [];
        foreach ($statuses as $i => $status) {
            $statusColors[] = $baseColors[$i % count($baseColors)];
        }

        return [
            'labels' => $statuses,
            'datasets' => [[
                'label' => 'Applications',
                'data' => array_values($statusCounts),
                'backgroundColor' => $statusColors,
                'borderColor' => '#fff',
                'borderWidth' => 2
            ]]
        ];
    }

    // === Applications by Country dynamically with base colors ===
    private function applicationsByCountryChart()
    {
        $countryCounts = University::select('country')
            ->distinct()
            ->get()
            ->map(function ($uni) {
                $count = Application::whereHas('university', fn($q) => $q->where('country', $uni->country))->count();
                return [
                    'country' => $uni->country,
                    'count' => $count
                ];
            });

        $countries = $countryCounts->pluck('country')->toArray();
        $counts    = $countryCounts->pluck('count')->toArray();

        // Base color palette (repeat if more countries)
        $baseColors = [
            '#3b82f6',
            '#60a5fa',
            '#818cf8',
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

        $colors = [];
        foreach ($countries as $i => $country) {
            $colors[] = $baseColors[$i % count($baseColors)];
        }

        return [
            'labels' => $countries,
            'datasets' => [[
                'label' => 'Applications',
                'data' => $counts,
                'backgroundColor' => $colors,
                'borderColor' => '#fff',
                'borderWidth' => 2
            ]]
        ];
    }

    /** Fetch cities by country (AJAX) */
    public function getCities($country)
    {
        $cities = University::where('country', $country)->select('city')->distinct()->pluck('city');
        return response()->json($cities);
    }

    /** Fetch universities by city (AJAX) */
    public function getUniversities($city)
    {
        $unis = University::where('city', $city)->select('id', 'name')->get();
        return response()->json($unis);
    }

    /** Fetch courses by university (AJAX) */
    public function getCourses($universityId)
    {
        $courses = Course::where('university_id', $universityId)->select('id', 'title')->get();
        return response()->json($courses);
    }
}
