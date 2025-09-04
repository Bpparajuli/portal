<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Student;
use App\Models\University;
use App\Models\Course;
use App\Models\Application;
use App\models\Activity;

class DashboardController extends Controller
{
    public function index()
    {
        // Only allow admins here
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403, 'Unauthorized');
        }

        // Example counts
        $totalAgents      = User::where('is_agent', 1)->count();
        $totalAdmins      = User::where('is_admin', 1)->count();
        $totalStudents    = Student::count();
        $totalUniversities = University::count();
        $totalCourses     = Course::count();
        $totalApplications = Application::count();
        $totalWaitingUsers = User::where('active', 0)->count();
        $latestAgents     = User::where('is_agent', 1)->latest()->take(5)->get();
        $activities = Activity::with('user')->latest()->take(10)->get();

        return view('admin.dashboard', compact(
            'totalAgents',
            'totalAdmins',
            'totalStudents',
            'totalUniversities',
            'totalCourses',
            'totalWaitingUsers',
            'totalApplications',
            'latestAgents',
            'activities'
        ));
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
