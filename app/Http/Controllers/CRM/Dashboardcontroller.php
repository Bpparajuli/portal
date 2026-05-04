<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentStage;
use App\Models\CrmTasks;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // =========================================================================
    // INDEX — main CRM pipeline
    // =========================================================================

    public function index(Request $request)
    {
        $user = Auth::user();

        /*
    |--------------------------------------------------------------------------
    | Base Student Query
    |--------------------------------------------------------------------------
    */

        $query = Student::with([
            'currentStage',
            'agent',
            'upcomingActivities',
            'overdueActivities',
            'pendingActivities',
        ])->accessible();

        /*
    |--------------------------------------------------------------------------
    | Search Filter
    |--------------------------------------------------------------------------
    */

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        /*
    |--------------------------------------------------------------------------
    | Stage Filter
    |--------------------------------------------------------------------------
    */

        if ($request->filled('stage_id')) {
            $query->byStage($request->stage_id);
        }

        /*
    |--------------------------------------------------------------------------
    | Assignee Filter
    |--------------------------------------------------------------------------
    */

        if (
            $request->filled('assignee_id') &&
            ($user->is_admin || $user->is_admin_staff || $user->is_agent)
        ) {
            $query->where('agent_id', $request->assignee_id);
        }

        /*
    |--------------------------------------------------------------------------
    | Clickable Stats Filter
    |--------------------------------------------------------------------------
    */

        $statFilter = $request->get('stat_filter');

        if ($statFilter === 'my_students') {
            $studentIds = CrmTasks::where('assigned_to', $user->id)
                ->whereNotNull('student_id')
                ->distinct()
                ->pluck('student_id');

            $query->whereIn('id', $studentIds);
        }

        /*
    |--------------------------------------------------------------------------
    | Activity Filter
    |--------------------------------------------------------------------------
    */

        if ($request->filled('activity_filter')) {

            if ($request->activity_filter === 'overdue') {
                $query->whereHas('overdueActivities');
            }

            if ($request->activity_filter === 'today') {
                $query->whereHas('pendingActivities', function ($q) {
                    $q->whereDate('scheduled_at', today())
                        ->where('status', 'pending');
                });
            }

            if ($request->activity_filter === 'upcoming') {
                $query->whereHas('upcomingActivities');
            }
        }

        /*
    |--------------------------------------------------------------------------
    | View Type
    |--------------------------------------------------------------------------
    */

        $view = $request->get('view', 'kanban');

        if ($view === 'kanban') {
            $students = $query->get()->groupBy('current_stage_id');
        } else {
            $students = $query->latest()
                ->paginate(25)
                ->withQueryString();
        }

        /*
    |--------------------------------------------------------------------------
    | Stages
    |--------------------------------------------------------------------------
    */

        $stages = StudentStage::active()
            ->ordered()
            ->get();

        /*
    |--------------------------------------------------------------------------
    | Assignee Dropdown
    |--------------------------------------------------------------------------
    */

        $assignees = collect();

        if ($user->is_admin || $user->is_admin_staff) {
            $assignees = User::whereIn('role', ['agent', 'staff'])
                ->select('id', 'name', 'role')
                ->orderBy('role')
                ->orderBy('name')
                ->get();
        } elseif ($user->is_agent) {
            $assignees = User::where('parent_id', $user->id)
                ->where('role', 'staff')
                ->select('id', 'name', 'role')
                ->get();
        }

        /*
    |--------------------------------------------------------------------------
    | Dashboard Stats
    |--------------------------------------------------------------------------
    */

        $accessibleStudentIds = Student::accessible()->pluck('id');

        if ($user->is_staff) {
            $taskBase = CrmTasks::where(function ($q) use ($accessibleStudentIds, $user) {
                $q->whereIn('student_id', $accessibleStudentIds)
                    ->orWhere('assigned_to', $user->id);
            });
        } else {
            $taskBase = CrmTasks::whereIn('student_id', $accessibleStudentIds);
        }

        $stats = [
            'total' => $accessibleStudentIds->count(),

            'my_students' => CrmTasks::where('assigned_to', $user->id)
                ->whereNotNull('student_id')
                ->distinct()
                ->count('student_id'),

            'today' => (clone $taskBase)
                ->whereDate('scheduled_at', today())
                ->where('status', 'pending')
                ->count(),

            'overdue' => (clone $taskBase)
                ->whereDate('scheduled_at', '<', today())
                ->where('status', 'pending')
                ->count(),

            'upcoming' => (clone $taskBase)
                ->whereDate('scheduled_at', '>', today())
                ->where('status', 'pending')
                ->count(),
        ];

        /*
    |--------------------------------------------------------------------------
    | Return View
    |--------------------------------------------------------------------------
    */

        return view('crm.dashboard', compact(
            'students',
            'stages',
            'assignees',
            'stats',
            'view'
        ));
    }

    // =========================================================================
    // SHOW — individual student (quick view, delegated to CrmStudentController)
    // =========================================================================

    public function show(Student $student)
    {
        $user = Auth::user();

        abort_unless($this->canAccess($user, $student), 403, 'Access denied.');

        $student->load([
            'agent',
            'currentStage',
            'activities'   => fn($q) => $q->latest()->limit(20),
            'notes'        => fn($q) => $q->latest(),
            'stageHistory' => fn($q) => $q->with('fromStage', 'toStage', 'changer')->latest()->limit(10),
        ]);

        $stages = StudentStage::active()->ordered()->get();

        return view('crm.student-show', compact('student', 'stages'));
    }

    // =========================================================================
    // EXPORT
    // =========================================================================

    public function export(Request $request)
    {
        $query = Student::with('currentStage', 'agent')->accessible();

        if ($request->filled('stage_id')) {
            $query->byStage($request->stage_id);
        }

        $students = $query->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="crm-students-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($students) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Name', 'Email', 'Phone', 'Stage', 'Agent ID', 'Agent Name', 'Agent Role', 'Tags', 'Created At']);

            foreach ($students as $s) {
                fputcsv($handle, [
                    $s->id,
                    $s->full_name,
                    $s->email        ?? '—',
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

    // =========================================================================
    // Helpers
    // =========================================================================

    private function canAccess(User $user, Student $student): bool
    {
        if ($user->is_admin)       return true;
        if ($user->is_admin_staff) return true;

        if ($user->is_agent) {
            $staffIds        = User::where('parent_id', $user->id)->where('role', 'staff')->pluck('id')->toArray();
            $allowedAgentIds = array_merge([$user->id], $staffIds);
            return in_array($student->agent_id, $allowedAgentIds);
        }

        if ($user->is_agent_staff) {
            return in_array($student->agent_id, [$user->id, $user->parent_id]);
        }

        // Fallback staff
        if ($user->is_staff) {
            return $student->agent_id === $user->id;
        }

        return false;
    }
    public function updateRating(Request $request, $id)
    {
        $request->validate([
            'rating' => 'nullable|integer|min:1|max:3',
        ]);

        $student = Student::findOrFail($id);
        $student->rating = $request->rating;
        $student->save();

        return back()->with('success', 'Student rating updated successfully.');
    }
}
