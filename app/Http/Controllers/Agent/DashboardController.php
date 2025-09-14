<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\University;
use App\Models\Course;
use App\Models\Student;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $agentId = Auth::id();

        // ---------- METRICS ----------
        $totalStudents = Student::where('agent_id', $agentId)->count();
        $totalApplications = Application::where('agent_id', $agentId)->count();

        $totalApproved = Application::where('agent_id', $agentId)
            ->whereIn('application_status', ['Accepted by the University', 'Visa Approved'])
            ->count();

        $visaApproved = Application::where('agent_id', $agentId)
            ->where('application_status', 'Visa Approved')
            ->count();

        $totalRejected = Application::where('agent_id', $agentId)
            ->whereIn('application_status', ['Rejected by the University', 'Visa Rejected', 'Lost'])
            ->count();
        $totalUniversities = University::count();

        // ---------- FILTER: universities (for filter UI) ----------
        $countries = University::select('country')->distinct()->pluck('country');
        $query = University::with('courses');

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhereHas('courses', fn($qc) => $qc->where('title', 'like', "%{$keyword}%"));
            });
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('university_id')) {
            $query->where('id', $request->university_id);
        }

        if ($request->filled('course_id')) {
            $query->whereHas('courses', fn($q) => $q->where('id', $request->course_id));
        }

        $universities = $query->paginate(10)->withQueryString();

        // ---------- ACTIVITIES (use 'type' column) ----------
        // you said activities table has `user_id` and `type`
        $studentActivities = Activity::where('user_id', $agentId)
            ->where('type', 'newstudentadded')
            ->latest()
            ->take(6)
            ->get();

        $documentActivities = Activity::where('user_id', $agentId)
            ->where('type', 'documentuploaded')
            ->latest()
            ->take(6)
            ->get();

        $applicationActivities = Activity::where('user_id', $agentId)
            ->where('type', 'newapplicationsubmitted')
            ->latest()
            ->take(6)
            ->get();

        // ---------- APPLICATION STATUS COUNTS (for pie) ----------
        $applicationStatusCounts = Application::where('agent_id', $agentId)
            ->selectRaw('application_status, COUNT(*) as count')
            ->groupBy('application_status')
            ->pluck('count', 'application_status');

        // ---------- MONTHLY AGGREGATES ----------
        // total applications per month
        $monthlyApplications = Application::where('agent_id', $agentId)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month');

        // per-month accepted / rejected / other (stacked chart)
        $acceptedStatuses = ['Accepted by the University', 'Visa Approved'];
        $rejectedStatuses = ['Rejected by the University', 'Visa Rejected', 'Lost'];

        $acceptedMonthly = Application::where('agent_id', $agentId)
            ->whereIn('application_status', $acceptedStatuses)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month');

        $rejectedMonthly = Application::where('agent_id', $agentId)
            ->whereIn('application_status', $rejectedStatuses)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month');

        $otherMonthly = Application::where('agent_id', $agentId)
            ->whereNotIn('application_status', array_merge($acceptedStatuses, $rejectedStatuses))
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month');

        return view('agent.dashboard', compact(
            'countries',
            'universities',
            'totalStudents',
            'totalApplications',
            'totalApproved',
            'visaApproved',
            'totalRejected',
            'studentActivities',
            'documentActivities',
            'applicationActivities',
            'applicationStatusCounts',
            'monthlyApplications',
            'acceptedMonthly',
            'rejectedMonthly',
            'otherMonthly'
        ));
    }

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
