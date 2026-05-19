<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\University;
use App\Models\Course;
use App\Models\Student;
use App\Models\Activity;
use App\Models\ApplicationStatus;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $agentId = Auth::id();

        // ---------- BASIC METRICS ----------
        $totalStudents = Student::where('agent_id', $agentId)->count();

        $totalApplications = Application::where('agent_id', $agentId)->count();

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $recentApplications = Application::where('agent_id', $agentId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        $totalUniversities = University::count();

        // ---------- VISA CONVERSION ----------
        $visaApprovedStatusId = ApplicationStatus::where('name', 'Visa Approved')->value('id');

        $visaApproved = Application::where('agent_id', $agentId)
            ->where('application_status_id', $visaApprovedStatusId)
            ->count();

        $visaConversionPercent = $totalApplications > 0
            ? round(($visaApproved / $totalApplications) * 100, 1)
            : 0;

        // ---------- STATUS (ONLY WITH APPLICATIONS) ----------
        $statuses = ApplicationStatus::where('is_active', 1)
            ->whereHas('applications', function ($q) use ($agentId) {
                $q->where('agent_id', $agentId);
            })
            ->withCount(['applications' => function ($q) use ($agentId) {
                $q->where('agent_id', $agentId);
            }])
            ->orderBy('sort_order')
            ->get();

        $statusLabels = $statuses->pluck('name')->values();
        $statusCounts = $statuses->pluck('applications_count')->values();
        $statusColors = $statuses->map(function ($status) {
            return $status->bg_color ?? '#6b7280';
        })->values();

        // Get ALL active statuses ordered by sort_order
        $pipelineStatuses = ApplicationStatus::where('is_active', 1)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'bg_color', 'text_color', 'sort_order']);

        // Get counts for each status based on agent
        $pipelineCounts = [];
        foreach ($pipelineStatuses as $status) {
            $pipelineCounts[$status->id] = Application::where('agent_id', $agentId)
                ->where('application_status_id', $status->id)
                ->count();
        }

        // Create a mapping of sort_order to status for easy lookup in blade
        $statusByOrder = [];
        foreach ($pipelineStatuses as $status) {
            $statusByOrder[$status->sort_order] = $status;
        }

        // ---------- MONTHLY APPLICATIONS ----------
        $monthlyApplications = Application::where('agent_id', $agentId)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month');

        $monthlyArr = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyArr[] = $monthlyApplications->get($m, 0);
        }

        // ---------- UNIVERSITY CHART ----------
        $universityChartData = $this->applicationsByUniversityChart($agentId);


        // ---------- COURSE TYPE CHART (OPTIMIZED) ----------
        $courseTypeData = Application::where('agent_id', $agentId)
            ->join('courses', 'applications.course_id', '=', 'courses.id')
            ->select('courses.course_type', DB::raw('COUNT(*) as total'))
            ->groupBy('courses.course_type')
            ->get();

        $courseTypeLabels = $courseTypeData->pluck('course_type')->toArray();
        $courseTypeValues = $courseTypeData->pluck('total')->toArray();

        // ---------- ACTIVITIES ----------
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

        $todayActivitiesCount = Activity::where('user_id', $agentId)
            ->whereIn('type', [
                'student_added',
                'student_deleted',
                'document_uploaded',
                'document_deleted',
                'application_submitted',
                'application_withdrawn'
            ])
            ->whereDate('created_at', $today)
            ->count();

        // ---------- TOP UNIVERSITIES ----------
        $topUniversities = University::select('universities.id', 'universities.name', 'universities.short_name')
            ->join('applications', 'applications.university_id', '=', 'universities.id')
            ->where('applications.agent_id', $agentId)
            ->selectRaw('COUNT(applications.id) as applications_count')
            ->groupBy('universities.id', 'universities.name', 'universities.short_name')
            ->orderByDesc('applications_count')
            ->limit(5)
            ->get();

        // ---------- FILTERS ----------
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
        if ($request->filled('course_id')) {
            $query->whereHas('courses', fn($q) => $q->where('id', $request->course_id));
        }

        $universities = $query->paginate(10)->withQueryString();

        return view('agent.dashboard', compact(
            'totalStudents',
            'totalApplications',
            'totalUniversities',
            'recentApplications',
            'visaApproved',
            'visaConversionPercent',
            'statuses',
            'statusLabels',
            'statusCounts',
            'statusColors',
            'pipelineStatuses',
            'pipelineCounts',
            'statusByOrder',
            'monthlyArr',
            'universityChartData',
            'studentActivities',
            'documentActivities',
            'applicationActivities',
            'todayActivitiesCount',
            'countries',
            'universities',
            'courseTypeLabels',
            'courseTypeValues',
            'topUniversities'
        ));
    }

    /**
     * Applications by University Chart Data
     */
    private function applicationsByUniversityChart($agentId)
    {
        $data = DB::table('applications')
            ->join('universities', 'applications.university_id', '=', 'universities.id')
            ->where('applications.agent_id', $agentId)
            ->select(
                'universities.short_name',
                DB::raw('COUNT(applications.id) as count')
            )
            ->groupBy('universities.short_name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $labels = $data->pluck('short_name')->toArray();
        $counts = $data->pluck('count')->toArray();

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
            '#16a34a'
        ];

        $colors = [];
        foreach ($labels as $i => $label) {
            $colors[] = $baseColors[$i % count($baseColors)];
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Applications',
                'data' => $counts,
                'backgroundColor' => $colors,
                'borderColor' => '#fff',
                'borderWidth' => 2
            ]]
        ];
    }

    /**
     * Get default color for status if not set in DB
     */


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
