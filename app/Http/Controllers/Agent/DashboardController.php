<?php

namespace App\Http\Controllers\Agent;


use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;
use App\Models\University;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\Student;
use App\Models\Activity;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Total approved applications (from applications table)
        $totalApproved = Application::where('agent_id', Auth::id())
            ->whereIn('application_status', [
                'Accepted by the University',
                'Visa Approved'
            ])
            ->count();

        // Total rejected applications (from applications table)
        $totalRejected = Application::where('agent_id', Auth::id())
            ->whereIn('application_status', [
                'Rejected by the University',
                'Visa Rejected',
                'Lost'
            ])
            ->count();

        // Total students (from students table)
        $totalStudents = Student::where('agent_id', Auth::id())->count();
        $totalApplications = Application::where('agent_id', Auth::id())->count();
        $countries = University::select('country')->distinct()->pluck('country');
        $query = University::with('courses');
        $activities = Activity::with('user')
            ->where('user_id', Auth::id())
            ->latest()
            ->take(10)
            ->get();

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%")
                    ->orWhereHas('courses', fn($qc) => $qc->where('title', 'like', "%$keyword%"));
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
        return view('agent.dashboard', compact('countries', 'universities', 'totalStudents', 'totalApplications', 'activities'));
    }
    public function getCities($country)
    {
        $cities = University::where('country', $country)
            ->select('city')->distinct()->pluck('city');
        return response()->json($cities);
    }

    public function getUniversities($city)
    {
        $unis = University::where('city', $city)
            ->select('id', 'name')->get();
        return response()->json($unis);
    }

    public function getCourses($universityId)
    {
        $courses = Course::where('university_id', $universityId)
            ->select('id', 'title')->get();
        return response()->json($courses);
    }
}
