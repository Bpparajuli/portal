<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationStatus;
use App\Models\Student;
use App\Models\University;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $studentQuery = Student::query()->accessible();
        $applicationQuery = Application::query();

        if ($user->is_agent_staff) {
            $studentQuery->where('agent_id', $user->parent_id);
            $applicationQuery->where('agent_id', $user->parent_id);
        }

        $totalStudents = (clone $studentQuery)->count();
        $totalApplications = (clone $applicationQuery)->count();
        $totalUniversities = University::count();

        $studentsWithDocs = (clone $studentQuery)->whereHas('documents')->count();
        $docCompletionRate = $totalStudents > 0 ? round(($studentsWithDocs / $totalStudents) * 100) : 0;

        $statuses = ApplicationStatus::where('is_active', 1)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($status) use ($applicationQuery) {
                $q = clone $applicationQuery;
                $status->count = $q->where('application_status_id', $status->id)->count();
                return $status;
            });

        $recentStudents = (clone $studentQuery)->with('agent')->latest()->take(5)->get();

        $recentApplications = (clone $applicationQuery)
            ->with('student', 'university', 'status')
            ->latest()
            ->take(5)
            ->get();

        $monthlyLabels = collect(range(1, 12))->map(fn($m) => Carbon::create()->month($m)->format('M'));
        $monthlyCounts = collect(range(1, 12))->map(function ($m) use ($applicationQuery) {
            return (clone $applicationQuery)->whereMonth('created_at', $m)->whereYear('created_at', now()->year)->count();
        });
        $monthlyData = $monthlyLabels->zip($monthlyCounts)->map(fn($pair) => ['month' => $pair[0], 'total' => $pair[1]]);

        return view('staff.dashboard', compact(
            'totalStudents', 'totalApplications', 'totalUniversities',
            'docCompletionRate', 'studentsWithDocs',
            'statuses', 'recentStudents', 'recentApplications', 'monthlyData'
        ));
    }
}
