<?php
// app/Http/Controllers/CRM/DashboardController.php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentStage;
use App\Models\CrmTask;
use App\Models\CrmTasks;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Main CRM dashboard with proper staff access
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Debug: Log user info (remove in production)
        Log::info('CRM Dashboard Access', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'parent_id' => $user->parent_id
        ]);

        // Build query with accessible scope
        $query = Student::with([
            'currentStage',
            'agent',
            'upcomingActivities',
            'overdueActivities',
            'pendingActivities',
        ])->accessible();

        // Debug: Check if query has results (remove in production)
        $debugCount = $query->count();
        Log::info('Accessible students count', ['count' => $debugCount]);

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('stage_id')) {
            $query->byStage($request->stage_id);
        }

        // Assignee filter (admin/agent only)
        if ($request->filled('assignee_id') && ($user->is_admin || $user->is_agent)) {
            $query->where('agent_id', $request->assignee_id);
        }

        // Activity filter
        if ($request->filled('activity_filter')) {
            switch ($request->activity_filter) {
                case 'overdue':
                    $query->withOverdueActivities();
                    break;
                case 'today':
                    $query->whereHas('activities', function ($q) {
                        $q->whereDate('scheduled_at', today())
                            ->where('status', 'pending');
                    });
                    break;
                case 'upcoming':
                    $query->withUpcomingActivities();
                    break;
            }
        }

        $view = $request->get('view', 'kanban');

        if ($view === 'kanban') {
            $students = $query->get()->groupBy('current_stage_id');
        } else {
            $students = $query->latest()->paginate(25)->withQueryString();
        }

        $stages = StudentStage::active()->ordered()->get();

        // Build assignee list for filters
        $assignees = collect();
        if ($user->is_admin) {
            $assignees = User::whereIn('role', ['agent', 'staff'])
                ->select('id', 'name', 'role')
                ->get();
        } elseif ($user->is_agent) {
            // For agents: show their staff members
            $assignees = User::where('parent_id', $user->id)
                ->where('role', 'staff')
                ->select('id', 'name', 'role')
                ->get();
        }

        // Calculate stats based on accessible students
        $accessibleStudentIds = Student::accessible()->pluck('id');

        $stats = [
            'total' => $accessibleStudentIds->count(),
            'today' => CrmTasks::whereIn('student_id', $accessibleStudentIds)
                ->whereDate('scheduled_at', today())
                ->where('status', 'pending')
                ->count(),
            'overdue' => CrmTasks::whereIn('student_id', $accessibleStudentIds)
                ->whereDate('scheduled_at', '<', today())
                ->where('status', 'pending')
                ->count(),
            'upcoming' => CrmTasks::whereIn('student_id', $accessibleStudentIds)
                ->whereDate('scheduled_at', '>', today())
                ->where('status', 'pending')
                ->count(),
        ];

        // For staff, add additional info about their assigned students
        if ($user->is_staff) {
            $myStudentsCount = Student::where('agent_id', $user->id)->count();
            Log::info('Staff student check', [
                'staff_id' => $user->id,
                'students_count' => $myStudentsCount,
                'stats_total' => $stats['total']
            ]);
        }

        return view('crm.dashboard', compact('students', 'stages', 'assignees', 'stats', 'view'));
    }

    /**
     * Show individual student with proper authorization
     */
    public function show(Student $student)
    {
        $user = Auth::user();

        // Authorization logic
        $hasAccess = false;

        if ($user->is_admin) {
            $hasAccess = true;
        } elseif ($user->is_agent) {
            // Agent can see their own students and their staff's students
            $staffIds = User::where('parent_id', $user->id)->pluck('id')->toArray();
            $allowedAgentIds = array_merge([$user->id], $staffIds);
            $hasAccess = in_array($student->agent_id, $allowedAgentIds);
        } elseif ($user->is_staff) {
            // Staff can only see students they created
            $hasAccess = ($student->agent_id == $user->id);
        }

        if (!$hasAccess) {
            abort(403, 'You do not have permission to view this student.');
        }

        $student->load([
            'agent',
            'currentStage',
            'activities' => fn($q) => $q->latest()->limit(20),
            'notes' => fn($q) => $q->latest(),
            'stageHistory' => fn($q) => $q->with('fromStage', 'toStage', 'changer')->latest()->limit(10),
        ]);

        $stages = StudentStage::active()->ordered()->get();

        return view('crm.student-show', compact('student', 'stages'));
    }

    /**
     * Test endpoint to debug staff access (remove in production)
     */
    public function debug()
    {
        $user = Auth::user();

        if (!$user->is_admin) {
            abort(403);
        }

        $allStudents = Student::with('agent')->get();
        $staffUsers = User::where('role', 'staff')->get();

        $debug = [
            'staff_members' => $staffUsers->map(function ($staff) {
                return [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'parent_id' => $staff->parent_id,
                    'students_count' => Student::where('agent_id', $staff->id)->count(),
                    'students' => Student::where('agent_id', $staff->id)->get(['id', 'first_name', 'last_name', 'agent_id'])->toArray()
                ];
            }),
            'all_students' => $allStudents->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->full_name,
                    'agent_id' => $student->agent_id,
                    'agent_name' => $student->agent?->name,
                    'agent_role' => $student->agent?->role
                ];
            })
        ];

        return response()->json($debug);
    }

    public function export(Request $request)
    {
        $query = Student::with('currentStage', 'agent')->accessible();

        if ($request->filled('stage_id')) {
            $query->byStage($request->stage_id);
        }

        $students = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="crm-students-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($students) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Name', 'Email', 'Phone', 'Stage', 'Assigned To (Agent ID)', 'Assigned To Name', 'Created By Role', 'Tags', 'Created At']);

            foreach ($students as $s) {
                fputcsv($handle, [
                    $s->id,
                    $s->full_name,
                    $s->email ?? '—',
                    $s->phone_number ?? '—',
                    $s->currentStage?->name ?? '—',
                    $s->agent_id,
                    $s->agent?->name ?? '—',
                    $s->agent?->role ?? '—',
                    implode(', ', $s->tags ?? []),
                    $s->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
