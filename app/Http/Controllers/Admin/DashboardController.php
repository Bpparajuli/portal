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

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with full dynamic data.
     */
    public function index()
    {
        // Ensure only admins can access
        $user = Auth::user();
        if (!$user || !$user->is_admin) {
            abort(403, 'Unauthorized');
        }

        // === COUNTS ===
        $totalAgents        = User::where('is_agent', 1)->count();
        $totalAdmins        = User::where('is_admin', 1)->count();
        $totalStudents      = Student::count();
        $totalUniversities  = University::count();
        $totalCourses       = Course::count();
        $totalApplications  = Application::count();
        $totalWaitingUsers  = User::where('active', 0)->count();

        // === LATEST RECORDS ===
        $latestAgents = User::where('is_agent', 1)
            ->latest()
            ->take(5)
            ->get();

        $activities = Activity::with('user')
            ->latest()
            ->take(10)
            ->get();

        $latestApplications = Application::with(['student.agent', 'course', 'university'])
            ->latest()
            ->take(5)
            ->get();

        // === MONTHLY APPLICATIONS ===
        $monthlyApplications = Application::select(
            DB::raw('COUNT(*) as count'),
            DB::raw('MONTH(created_at) as month')
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $applicationsChartData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'datasets' => [[
                'label'           => 'New Applications',
                'data'            => array_fill(0, 12, 0),
                'backgroundColor' => '#1a0262',
                'borderColor'     => '#820b5c',
                'borderWidth'     => 2,
                'borderRadius'    => 4,
            ]]
        ];

        foreach ($monthlyApplications as $m) {
            $applicationsChartData['datasets'][0]['data'][$m->month - 1] = $m->count;
        }

        // === APPLICATIONS BY STATUS (Dynamic from Model) ===
        $statusCounts = Application::select('application_status', DB::raw('COUNT(*) as count'))
            ->groupBy('application_status')
            ->pluck('count', 'application_status')
            ->toArray();

        $statusLabels = Application::STATUSES;
        $statusValues = [];
        $statusColors = [
            'rgba(59,130,246,0.7)',  // Blue
            'rgba(16,185,129,0.7)',  // Green
            'rgba(234,179,8,0.7)',   // Yellow
            'rgba(239,68,68,0.7)',   // Red
            'rgba(139,92,246,0.7)',  // Purple
            'rgba(244,63,94,0.7)',   // Pink
            'rgba(34,197,94,0.7)',   // Emerald
            'rgba(249,115,22,0.7)',  // Orange
            'rgba(14,165,233,0.7)',  // Sky
            'rgba(132,204,22,0.7)',  // Lime
            'rgba(202,138,4,0.7)',   // Amber
            'rgba(100,116,139,0.7)', // Slate
        ];

        foreach ($statusLabels as $label) {
            $statusValues[] = $statusCounts[$label] ?? 0;
        }

        $statusChartData = [
            'labels' => $statusLabels,
            'datasets' => [[
                'label' => 'No of applications',
                'data' => $statusValues,
                'backgroundColor' => $statusColors,
                'borderColor' => '#fff',
                'borderWidth' => 2,
            ]]
        ];

        // === APPLICATIONS BY COUNTRY ===
        $countryCounts = Application::join('universities', 'applications.university_id', '=', 'universities.id')
            ->select('universities.country', DB::raw('COUNT(applications.id) as count'))
            ->groupBy('universities.country')
            ->orderByDesc('count')
            ->get();

        $countryChartData = [
            'labels' => $countryCounts->pluck('country'),
            'datasets' => [[
                'label' => 'Applications',
                'data' => $countryCounts->pluck('count'),
                'backgroundColor' => [
                    'rgba(106, 103, 241, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(34, 197, 94, 0.7)',
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(244, 63, 94, 0.7)',
                ],
                'borderColor' => '#fff',
                'borderWidth' => 2
            ]]
        ];

        // === TOP AGENTS BY APPLICATION COUNT ===
        $topAgents = User::where('is_agent', 1)
            ->withCount('applications')
            ->orderByDesc('applications_count')
            ->take(5)
            ->get();

        // Return dashboard view with compact data
        return view('admin.dashboard', compact(
            'totalAgents',
            'totalAdmins',
            'totalStudents',
            'totalUniversities',
            'totalCourses',
            'totalWaitingUsers',
            'totalApplications',
            'latestAgents',
            'activities',
            'applicationsChartData',
            'statusChartData',
            'countryChartData',
            'latestApplications',
            'topAgents'
        ));
    }

    /** Fetch cities by country (AJAX) */
    public function getCities($country)
    {
        $cities = University::where('country', $country)
            ->select('city')
            ->distinct()
            ->pluck('city');
        return response()->json($cities);
    }

    /** Fetch universities by city (AJAX) */
    public function getUniversities($city)
    {
        $unis = University::where('city', $city)
            ->select('id', 'name')
            ->get();
        return response()->json($unis);
    }

    /** Fetch courses by university (AJAX) */
    public function getCourses($universityId)
    {
        $courses = Course::where('university_id', $universityId)
            ->select('id', 'title')
            ->get();
        return response()->json($courses);
    }
}
