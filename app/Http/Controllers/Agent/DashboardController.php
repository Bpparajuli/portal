<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\University;
use App\Models\Course;
use App\Models\Student;
use App\Models\Activity;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $agentId = Auth::id();

        // ---------- METRICS ----------
        $totalStudents = Student::where('agent_id', $agentId)->count();
        $applications = Application::where('agent_id', $agentId)->get();
        $totalApplications = $applications->count();
        // Get the start and end of current month
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();


        // Only this user's applications this month
        $recentApplications = Application::where('agent_id', $agentId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get();

        $visaApproved = $applications->where('application_status', 'Visa Approved')->count();
        $visaConversionPercent = $totalApplications > 0 ? round(($visaApproved / $totalApplications) * 100, 1) : 0;

        $totalApproved = $applications->whereIn('application_status', ['Accepted by the University', 'Visa Approved'])->count();
        $totalRejected = $applications->whereIn('application_status', ['Rejected by the University', 'Visa Rejected', 'Lost'])->count();
        $totalUniversities = University::count();

        // ---------- APPLICATION STATUS COUNTS ----------
        $statuses = [
            'Application started',
            'Application viewed by Admin',
            'Applied to University',
            'Need to give the test',
            'Accepted by the University',
            'Rejected by the University',
            'Applied to another university',
            'Application forwarded to embassy',
            'Is on waiting list on Embassy',
            'Visa Approved',
            'Visa Rejected',
            'Lost'
        ];

        $applicationStatusCounts = $applications
            ->groupBy('application_status')
            ->map(fn($group) => $group->count());

        $statusCounts = [];
        foreach ($statuses as $s) {
            $statusCounts[] = $applicationStatusCounts->get($s, 0);
        }

        $statusColors = [
            '#3b82f6', // Application started - Blue
            '#60a5fa', // Viewed by Admin - Light Blue
            '#818cf8', // Applied to University - Indigo
            '#facc15', // Need to give the test - Yellow
            '#22c55e', // Accepted by University - Green
            '#ef4444', // Rejected by University - Red
            '#8b5cf6', // Applied to another university - Purple
            '#f97316', // Forwarded to embassy - Orange
            '#0ea5e9', // On waiting list at embassy - Sky Blue
            '#16a34a', // Visa Approved - Dark Green
            '#b91c1c', // Visa Rejected - Dark Red
            '#6b7280'  // Lost - Gray
        ];


        // ---------- MONTHLY AGGREGATES ----------
        $monthlyApplications = Application::where('agent_id', $agentId)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month');

        $monthlyArr = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyArr[] = $monthlyApplications->get($m, 0);
        }

        // ---------- COUNTRY-WISE APPLICATIONS ----------
        $countryLabels = University::select('country')->distinct()->pluck('country')->toArray();
        $countryCounts = [];
        foreach ($countryLabels as $c) {
            $countryCounts[] = Application::where('agent_id', $agentId)
                ->whereHas('university', function ($q) use ($c) {
                    $q->where('country', $c);
                })->count();
        }

        // ---------- COURSE TYPE CHART DATA ----------
        $courseTypeCounts = Application::where('agent_id', $agentId)
            ->with('course')
            ->get()
            ->groupBy(function ($app) {
                return $app->course->course_type ?? 'Unknown';
            })
            ->map(fn($group) => $group->count());

        $courseTypeLabels = $courseTypeCounts->keys()->toArray();
        $courseTypeValues = $courseTypeCounts->values()->toArray();


        // ---------- RECENT ACTIVITIES ----------
        $studentActivities = Activity::where('user_id', $agentId)
            ->whereIn('type', ['student_added', 'student_deleted'])
            ->latest()->take(5)->get();

        $documentActivities = Activity::where('user_id', $agentId)
            ->whereIn('type', ['document_uploaded', 'document_deleted'])
            ->latest()->take(5)->get();

        $applicationActivities = Activity::where('user_id', $agentId)
            ->whereIn('type', ['application_submitted', 'application_withdrawn'])
            ->latest()->take(5)->get();

        $today = Carbon::today();

        $todayStudentCount = Activity::where('user_id', $agentId)
            ->whereIn('type', ['student_added', 'student_deleted'])
            ->whereDate('created_at', $today)
            ->count();

        $todayDocumentCount = Activity::where('user_id', $agentId)
            ->whereIn('type', ['document_uploaded', 'document_deleted'])
            ->whereDate('created_at', $today)
            ->count();

        $todayApplicationCount = Activity::where('user_id', $agentId)
            ->whereIn('type', ['application_submitted', 'application_withdrawn'])
            ->whereDate('created_at', $today)
            ->count();

        $todayActivitiesCount = $todayStudentCount + $todayDocumentCount + $todayApplicationCount;
        // ---------- FILTER DATA ----------
        $countries = University::select('country')->distinct()->pluck('country');
        $query = University::with('courses');
        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhereHas('courses', fn($qc) => $qc->where('title', 'like', "%{$keyword}%"));
            });
        }
        if ($request->filled('country')) $query->where('country', $request->country);
        if ($request->filled('city')) $query->where('city', $request->city);
        if ($request->filled('university_id')) $query->where('id', $request->university_id);
        if ($request->filled('course_id')) $query->whereHas('courses', fn($q) => $q->where('id', $request->course_id));
        $universities = $query->paginate(10)->withQueryString();

        return view('agent.dashboard', compact(
            'totalStudents',
            'totalApplications',
            'totalUniversities',
            'recentApplications',
            'visaApproved',
            'visaConversionPercent',
            'totalApproved',
            'totalRejected',
            'statuses',
            'applicationStatusCounts',
            'statusCounts',
            'statusColors',
            'monthlyArr',
            'countryLabels',
            'countryCounts',
            'studentActivities',
            'documentActivities',
            'applicationActivities',
            'todayActivitiesCount',
            'countries',
            'universities',
            'courseTypeLabels',
            'courseTypeValues'
        ));
    }

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
