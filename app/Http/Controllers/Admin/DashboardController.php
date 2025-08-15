<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Student;
use App\Models\University;
use App\Models\Course;
use App\Models\Application;
use App\Models\PendingUser;

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
        // $totalStudents    = Student::count();
        $totalUniversities = University::count();
        $totalCourses     = Course::count();
        // $totalApplications = Application::count();
        $totalPendingUsers = PendingUser::count();
        // Example latest items
        // $latestStudents   = Student::latest()->take(5)->get();
        $latestAgents     = User::where('is_agent', 1)->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalAgents',
            'totalAdmins',
            // 'totalStudents',
            'totalUniversities',
            'totalCourses',
            'totalPendingUsers',
            // 'totalApplications',
            // 'latestStudents',
            'latestAgents'
        ));
    }
}
