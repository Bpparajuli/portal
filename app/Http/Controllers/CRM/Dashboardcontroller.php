<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentStage;
use App\Models\CrmTasks;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Activity;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $agents = User::where('role', 'agent')->orderBy('name')->get();
        $query = Student::with([
            'currentStage',
            'agent',
            'upcomingActivities',
            'overdueActivities',
            'pendingActivities',
        ])->accessible();

        $isAdmin = $user->is_admin;

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $type = $request->search_type ?? 'all';

            $query->where(function ($q) use ($search, $type) {
                if ($type === 'all') {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('preferred_country', 'like', "%{$search}%")
                        ->orWhere('tags', 'like', "%{$search}%");
                }
                if ($type === 'name') {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                }
                if ($type === 'phone_number' || $type === 'phone') {
                    $q->where('phone_number', 'like', "%{$search}%");
                }
                if ($type === 'email') {
                    $q->where('email', 'like', "%{$search}%");
                }
                if ($type === 'tag') {
                    $q->where('tags', 'like', "%{$search}%");
                }
                if ($type === 'country' || $type === 'preferred_country') {
                    $q->where('preferred_country', 'like', "%{$search}%");
                }
                if ($type === 'degree') {
                    $q->where('degree_level', 'like', "%{$search}%");
                }
                if ($type === 'university') {
                    $q->whereHas('applications.university', function ($uni) use ($search) {
                        $uni->where('name', 'like', "%{$search}%");
                    });
                }
            });
        }

        // Stage filter
        if ($request->filled('stage_id')) {
            $query->byStage($request->stage_id);
        }

        // Assignee filter
        if ($request->filled('assignee_id')) {
            $query->whereHas('activities', function ($q) use ($request) {
                $q->where('assigned_to', $request->assignee_id);
            });
        }

        // Activity filter - UPDATED to support both regular and "my_" filters
        if ($request->filled('activity_filter')) {
            $filter = $request->activity_filter;

            // Check if it's a "my_" filter (for current user only)
            $isMyFilter = str_starts_with($filter, 'my_');
            $baseFilter = $isMyFilter ? substr($filter, 3) : $filter; // Remove 'my_' prefix

            // Build the task query for filtering students
            $taskQuery = CrmTasks::where('status', 'pending');

            // Apply user filter for "my_" prefixed filters
            if ($isMyFilter) {
                $taskQuery->where('assigned_to', $user->id);
            }

            // Apply date filter
            if ($baseFilter === 'overdue') {
                $taskQuery->whereDate('scheduled_for', '<', today());
            } elseif ($baseFilter === 'today') {
                $taskQuery->whereDate('scheduled_for', today());
            } elseif ($baseFilter === 'upcoming') {
                $taskQuery->whereDate('scheduled_for', '>', today());
            }

            // Get student IDs that have matching tasks
            $studentIdsWithTasks = $taskQuery->whereNotNull('student_id')->pluck('student_id')->unique();

            // Filter students by those IDs
            $query->whereIn('id', $studentIdsWithTasks);
        }

        // View type
        $view = $request->get('view', 'kanban');
        $view = $request->get('view');

        $hasFilters =
            $request->filled('search') ||
            $request->filled('stage_id') ||
            $request->filled('assignee_id') ||
            $request->filled('activity_filter');

        if (!$view) {
            $view = $hasFilters ? 'list' : 'kanban';
        }
        if ($view === 'kanban') {
            $students = $query->get()->groupBy('current_stage_id');
        } else {
            $students = $query->latest()->paginate(25)->withQueryString();
        }

        // Stages
        $stages = StudentStage::active()->ordered()->get();

        // Assignee dropdown - ONLY STAFF members
        $assignees = collect();

        if ($user->is_admin || $user->is_admin_staff) {
            $assignees = User::where('role', 'staff')->select('id', 'name')->orderBy('name')->get();
        } elseif ($user->is_agent) {
            $assignees = User::where('parent_id', $user->id)->where('role', 'staff')->select('id', 'name')->orderBy('name')->get();
        }

        // Dashboard stats
        $accessibleStudentIds = Student::accessible()->pluck('id');

        if ($user->is_staff) {
            $taskBase = CrmTasks::where(function ($q) use ($accessibleStudentIds, $user) {
                $q->whereIn('student_id', $accessibleStudentIds)->orWhere('assigned_to', $user->id);
            });
        } else {
            $taskBase = CrmTasks::whereIn('student_id', $accessibleStudentIds);
        }


        // Calculate completed this week
        $mycompletedThisWeek = CrmTasks::where('assigned_to', $user->id)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $stats = [
            'total_students' => $accessibleStudentIds->count(),
            'my_students' => CrmTasks::where('assigned_to', $user->id)->whereNotNull('student_id')->distinct()->count('student_id'),

            // My tasks (assigned to current user)
            'my_today' => CrmTasks::where('assigned_to', $user->id)
                ->whereDate('scheduled_for', today())
                ->where('status', 'pending')
                ->count(),

            'my_overdue' => CrmTasks::where('assigned_to', $user->id)
                ->whereDate('scheduled_for', '<', today())
                ->where('status', 'pending')
                ->count(),

            'my_upcoming' => CrmTasks::where('assigned_to', $user->id)
                ->whereDate('scheduled_for', '>', today())
                ->where('status', 'pending')
                ->count(),

            // All accessible tasks (existing)
            'today' => (clone $taskBase)->whereDate('scheduled_for', today())->where('status', 'pending')->count(),
            'overdue' => (clone $taskBase)->whereDate('scheduled_for', '<', today())->where('status', 'pending')->count(),
            'upcoming' => (clone $taskBase)->whereDate('scheduled_for', '>', today())->where('status', 'pending')->count(),

            'completed_today' => CrmTasks::whereIn('student_id', $accessibleStudentIds)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count(),

            'my_completed_this_week' => $mycompletedThisWeek,
            'completed_this_week' => CrmTasks::whereIn('student_id', $accessibleStudentIds)
                ->where('status', 'completed')
                ->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
        ];

        return view('crm.dashboard', compact('isAdmin', 'students', 'stages', 'assignees', 'stats', 'view', 'user', 'agents'));
    }
    public function taskSummary(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->is_admin ?? $user->role === 'admin';

        if ($isAdmin) {
            // Admin: all students
            $todayCount = Activity::whereDate('scheduled_for', today())->count();
            $upcomingCount = Activity::whereDate('scheduled_for', '>', today())->count();
            $overdueCount = Activity::whereDate('scheduled_for', '<', today())->where('status', '!=', 'completed')->count();
            $unscheduledCount = Student::whereDoesntHave('activities')->count();
        } else {
            // Staff: only their assigned students
            $studentIds = Student::where('agent_id', $user->id)->pluck('id');
            $todayCount = Activity::whereIn('student_id', $studentIds)->whereDate('scheduled_for', today())->count();
            $upcomingCount = Activity::whereIn('student_id', $studentIds)->whereDate('scheduled_for', '>', today())->count();
            $overdueCount = Activity::whereIn('student_id', $studentIds)->whereDate('scheduled_for', '<', today())->where('status', '!=', 'completed')->count();
            $unscheduledCount = Student::whereIn('id', $studentIds)->whereDoesntHave('activities')->count();
        }

        return response()->json([
            'today' => $todayCount,
            'upcoming' => $upcomingCount,
            'overdue' => $overdueCount,
            'unscheduled' => $unscheduledCount,
        ]);
    }
    public function updateStage(Request $request, Student $student)
    {
        try {
            $request->validate([
                'stage_id' => 'required|exists:student_stages,id',
            ]);

            $user = Auth::user();

            // Check access
            $hasAccess = false;
            if ($user->is_admin || $user->is_admin_staff) {
                $hasAccess = true;
            } elseif ($user->is_agent) {
                $staffIds = User::where('parent_id', $user->id)->where('role', 'staff')->pluck('id')->toArray();
                $allowedAgentIds = array_merge([$user->id], $staffIds);
                $hasAccess = in_array($student->agent_id, $allowedAgentIds);
            } elseif ($user->is_agent_staff) {
                $hasAccess = in_array($student->agent_id, [$user->id, $user->parent_id]);
            } elseif ($user->is_staff) {
                $hasAccess = $student->agent_id === $user->id;
            }

            if (!$hasAccess) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            $student->update(['current_stage_id' => $request->stage_id]);

            return response()->json([
                'success' => true,
                'message' => 'Stage updated successfully',
                'student' => $student->fresh('currentStage')
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating stage: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to update stage'], 500);
        }
    }

    public function addTag(Request $request, Student $student)
    {
        try {
            $request->validate(['tag' => 'required|string|max:50']);

            $user = Auth::user();
            if (!$this->canAccess($user, $student)) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            $currentTags = $student->tags ?? [];
            $newTag = trim($request->tag);

            if (!in_array($newTag, $currentTags)) {
                $currentTags[] = $newTag;
                $student->tags = $currentTags;
                $student->save();
            }

            return response()->json(['success' => true, 'tags' => $currentTags]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function removeTag(Request $request, Student $student)
    {
        try {
            $request->validate(['tag' => 'required|string']);

            $user = Auth::user();
            if (!$this->canAccess($user, $student)) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            $currentTags = $student->tags ?? [];
            $tagToRemove = $request->tag;
            $currentTags = array_values(array_filter($currentTags, function ($tag) use ($tagToRemove) {
                return $tag !== $tagToRemove;
            }));

            $student->tags = $currentTags;
            $student->save();

            return response()->json(['success' => true, 'tags' => $currentTags]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getPopularTags()
    {
        try {
            $students = Student::accessible()->get();
            $allTags = [];
            foreach ($students as $student) {
                if ($student->tags && is_array($student->tags)) {
                    $allTags = array_merge($allTags, $student->tags);
                }
            }
            $tagCounts = array_count_values($allTags);
            arsort($tagCounts);
            return response()->json(['tags' => array_slice(array_keys($tagCounts), 0, 10)]);
        } catch (\Exception $e) {
            return response()->json(['tags' => []], 200);
        }
    }

    // FIXED: Added proper AJAX handling for rating updates
    public function updateRating(Request $request, $id)
    {
        try {
            $request->validate(['rating' => 'nullable|integer|min:1|max:3']);
            $student = Student::findOrFail($id);

            $user = Auth::user();
            if (!$this->canAccess($user, $student)) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
                }
                return back()->with('error', 'Unauthorized');
            }

            $student->rating = $request->rating;
            $student->save();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Rating updated successfully']);
            }

            return back()->with('success', 'Rating updated successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to update rating');
        }
    }

    public function updateRatingSimple(Request $request, $id)
    {
        try {
            $request->validate([
                'rating' => 'nullable|integer|min:1|max:3',
            ]);

            $student = Student::findOrFail($id);
            $user = Auth::user();

            if (!$this->canAccess($user, $student)) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            $student->rating = $request->rating;
            $student->save();

            return response()->json([
                'success' => true,
                'message' => 'Rating updated successfully',
                'rating' => $student->rating
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Toggle pin status for a student
     */
    public function togglePin(Request $request, $id)
    {
        try {
            $request->validate([
                'pinned' => 'required|boolean'
            ]);

            $student = Student::findOrFail($id);

            // Check authorization
            $user = Auth::user();
            if (!$this->canAccess($user, $student)) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            $student->pinned = $request->pinned;
            $student->save();

            return response()->json([
                'success' => true,
                'message' => $request->pinned ? 'Student pinned successfully' : 'Student unpinned successfully',
                'pinned' => $student->pinned
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
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
            fputcsv($handle, ['ID', 'Name', 'Email', 'Phone', 'Stage', 'Staff', 'Tags', 'Created At']);
            foreach ($students as $s) {
                fputcsv($handle, [
                    $s->id,
                    $s->full_name,
                    $s->email ?? 'â€”',
                    $s->phone_number ?? 'â€”',
                    $s->currentStage?->name ?? 'â€”',
                    $s->agent?->name ?? 'â€”',
                    implode(', ', $s->tags ?? []),
                    $s->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show(Student $student)
    {
        $user = Auth::user();
        abort_unless($this->canAccess($user, $student), 403);
        $student->load([
            'agent',
            'currentStage',
            'activities' => fn($q) => $q->latest()->limit(20),
            'notes' => fn($q) => $q->latest()
        ]);
        $stages = StudentStage::active()->ordered()->get();

        $assignableUsers = User::whereIn('role', ['admin', 'staff', 'admin_staff'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('crm.student-show', compact('student', 'assignableUsers', 'stages', 'user'));
    }

    private function canAccess(User $user, Student $student): bool
    {
        if ($user->is_admin || $user->is_admin_staff) return true;
        if ($user->is_agent) {
            $staffIds = User::where('parent_id', $user->id)->where('role', 'staff')->pluck('id')->toArray();
            $allowedAgentIds = array_merge([$user->id], $staffIds);
            return in_array($student->agent_id, $allowedAgentIds);
        }
        if ($user->is_agent_staff) return in_array($student->agent_id, [$user->id, $user->parent_id]);
        if ($user->is_staff) return $student->agent_id === $user->id;
        return false;
    }

    /**
     * Get task statistics for navbar (AJAX endpoint)
     */
    public function getTaskStats(Request $request)
    {
        try {
            $user = Auth::user();
            $today = Carbon::today()->format('Y-m-d');

            $stats = [
                'late' => 0,
                'today' => 0,
                'future' => 0
            ];

            // Get tasks based on user role
            if ($user->is_admin) {
                // Admin sees all pending tasks
                $stats['late'] = CrmTasks::where('status', 'pending')
                    ->whereDate('scheduled_for', '<', $today)
                    ->count();

                $stats['today'] = CrmTasks::where('status', 'pending')
                    ->whereDate('scheduled_for', $today)
                    ->count();

                $stats['future'] = CrmTasks::where('status', 'pending')
                    ->whereDate('scheduled_for', '>', $today)
                    ->count();
            } elseif ($user->is_agent) {
                // Agent sees their own tasks and their staff's tasks
                $staffIds = User::where('parent_id', $user->id)
                    ->where('role', 'staff')
                    ->pluck('id')
                    ->toArray();
                $assigneeIds = array_merge([$user->id], $staffIds);

                $stats['late'] = CrmTasks::whereIn('assigned_to', $assigneeIds)  // Changed from assignee_id to assigned_to
                    ->where('status', 'pending')
                    ->whereDate('scheduled_for', '<', $today)
                    ->count();

                $stats['today'] = CrmTasks::whereIn('assigned_to', $assigneeIds)  // Changed from assignee_id to assigned_to
                    ->where('status', 'pending')
                    ->whereDate('scheduled_for', $today)
                    ->count();

                $stats['future'] = CrmTasks::whereIn('assigned_to', $assigneeIds)  // Changed from assignee_id to assigned_to
                    ->where('status', 'pending')
                    ->whereDate('scheduled_for', '>', $today)
                    ->count();
            } else {
                // Regular staff or other users see only their own tasks
                $stats['late'] = CrmTasks::where('assigned_to', $user->id)  // Changed from assignee_id to assigned_to
                    ->where('status', 'pending')
                    ->whereDate('scheduled_for', '<', $today)
                    ->count();

                $stats['today'] = CrmTasks::where('assigned_to', $user->id)  // Changed from assignee_id to assigned_to
                    ->where('status', 'pending')
                    ->whereDate('scheduled_for', $today)
                    ->count();

                $stats['future'] = CrmTasks::where('assigned_to', $user->id)  // Changed from assignee_id to assigned_to
                    ->where('status', 'pending')
                    ->whereDate('scheduled_for', '>', $today)
                    ->count();
            }

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Task stats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'stats' => ['late' => 0, 'today' => 0, 'future' => 0]
            ]);
        }
    }

    /**
     * Get detailed tasks for a specific category
     */
    public function getTaskDetails($type, Request $request)
    {
        try {
            $user = Auth::user();
            $today = Carbon::today()->format('Y-m-d');

            $query = CrmTasks::where('status', 'pending');

            // Apply role-based filtering
            if (!$user->is_admin) {
                if ($user->is_agent) {
                    $staffIds = User::where('parent_id', $user->id)
                        ->where('role', 'staff')
                        ->pluck('id')
                        ->toArray();
                    $assigneeIds = array_merge([$user->id], $staffIds);
                    $query->whereIn('assigned_to', $assigneeIds);  // Changed from assignee_id to assigned_to
                } else {
                    $query->where('assigned_to', $user->id);  // Changed from assignee_id to assigned_to
                }
            }

            // Apply type filtering
            switch ($type) {
                case 'late':
                    $query->whereDate('scheduled_for', '<', $today);
                    $title = 'Overdue Tasks';
                    $icon = 'fa-exclamation-triangle';
                    $color = 'danger';
                    break;
                case 'today':
                    $query->whereDate('scheduled_for', $today);
                    $title = 'Tasks Due Today';
                    $icon = 'fa-calendar-day';
                    $color = 'warning';
                    break;
                case 'future':
                    $query->whereDate('scheduled_for', '>', $today);
                    $title = 'Upcoming Tasks';
                    $icon = 'fa-calendar-alt';
                    $color = 'success';
                    break;
                default:
                    return response()->json(['success' => false, 'error' => 'Invalid type'], 400);
            }

            $tasks = $query->with('student')->orderBy('scheduled_for', 'asc')->paginate(20);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'title' => $title,
                    'icon' => $icon,
                    'color' => $color,
                    'type' => $type,
                    'tasks' => $tasks
                ]);
            }

            return view('crm.task-stats-details', compact('tasks', 'title', 'icon', 'color', 'type'));
        } catch (\Exception $e) {
            Log::error('Task details error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    /**
     * Get tasks query based on user role
     */
    private function getUserTasksQuery($user)
    {
        if ($user->is_admin) {
            // Admin sees all tasks - use CrmTasks model directly
            return CrmTasks::query();
        } elseif ($user->is_agent) {
            // Agent sees their own tasks and their staff's tasks
            $staffIds = User::where('parent_id', $user->id)
                ->where('role', 'staff')
                ->pluck('id')
                ->toArray();
            $assigneeIds = array_merge([$user->id], $staffIds);
            return CrmTasks::whereIn('assignee_id', $assigneeIds);
        } elseif ($user->is_staff) {
            // Staff sees their own tasks
            return CrmTasks::where('assignee_id', $user->id);
        }

        return CrmTasks::where('assignee_id', $user->id);
    }

    /**
     * Get weekly tasks data for the chart
     */
    public function weeklyTasks(Request $request)
    {
        try {
            $user = Auth::user();
            $isAdmin = ($user->role === 'admin');
            $startOfWeek = now()->startOfWeek(Carbon::SUNDAY);
            $today = now()->startOfDay();

            // Get all days of the week
            $days = [];
            for ($i = 0; $i < 7; $i++) {
                $date = $startOfWeek->copy()->addDays($i);
                $days[] = [
                    'name' => $date->format('l'),
                    'short' => $date->format('D'),
                    'date' => $date->toDateString(),
                    'is_today' => $date->isToday()
                ];
            }

            if ($isAdmin) {
                // ADMIN VIEW: Get ALL staff members (role = 'staff')
                $staffMembers = User::where('role', 'staff')
                    ->orderBy('name')
                    ->get();

                $staffData = [];

                foreach ($staffMembers as $staff) {
                    $staffWeekData = [
                        'staff_name' => $staff->name,
                        'staff_id' => $staff->id,
                        'total_overdue' => 0,
                        'total_completed' => 0,
                        'total_upcoming' => 0,
                        'total_all' => 0,
                        'days' => []
                    ];

                    foreach ($days as $day) {
                        $date = Carbon::parse($day['date']);

                        // Get tasks for this staff on this date
                        $tasksQuery = CrmTasks::where('assigned_to', $staff->id)
                            ->whereDate('scheduled_for', $date);

                        $completed = (clone $tasksQuery)->where('status', 'completed')->count();
                        $pending = (clone $tasksQuery)->where('status', 'pending')->count();

                        // Logic for overdue, completed, upcoming
                        if ($date->lt($today)) {
                            // Past dates: all pending are overdue
                            $overdue = $pending;
                            $completedCount = $completed;
                            $upcoming = 0;
                        } elseif ($date->eq($today)) {
                            // Today: no overdue, no upcoming
                            $overdue = 0;
                            $completedCount = $completed;
                            $upcoming = 0;
                        } else {
                            // Future dates: all pending are upcoming
                            $overdue = 0;
                            $completedCount = $completed;
                            $upcoming = $pending;
                        }

                        $dayData = [
                            'day' => $day['short'],
                            'overdue' => $overdue,
                            'completed' => $completedCount,
                            'upcoming' => $upcoming,
                            'total' => $overdue + $completedCount + $upcoming
                        ];

                        $staffWeekData['days'][] = $dayData;
                        $staffWeekData['total_overdue'] += $overdue;
                        $staffWeekData['total_completed'] += $completedCount;
                        $staffWeekData['total_upcoming'] += $upcoming;
                        $staffWeekData['total_all'] += $dayData['total'];
                    }

                    // Show ALL staff (including those with 0 tasks)
                    $staffData[] = $staffWeekData;
                }

                // Sort by total tasks descending (staff with most tasks first)
                usort($staffData, function ($a, $b) {
                    return $b['total_all'] <=> $a['total_all'];
                });

                return response()->json([
                    'is_admin' => true,
                    'days' => array_column($days, 'short'),
                    'staff_data' => $staffData
                ]);
            } else {
                // STAFF VIEW: Get ONLY their own weekly task breakdown
                $staffId = $user->id;
                $staffName = $user->name;

                $weekData = [
                    'staff_name' => $staffName,
                    'total_overdue' => 0,
                    'total_completed' => 0,
                    'total_upcoming' => 0,
                    'total_all' => 0,
                    'days' => []
                ];

                foreach ($days as $day) {
                    $date = Carbon::parse($day['date']);

                    $tasksQuery = CrmTasks::where('assigned_to', $staffId)
                        ->whereDate('scheduled_for', $date);

                    $completed = (clone $tasksQuery)->where('status', 'completed')->count();
                    $pending = (clone $tasksQuery)->where('status', 'pending')->count();

                    // Logic for overdue, completed, upcoming
                    if ($date->lt($today)) {
                        // Past dates: all pending are overdue
                        $overdue = $pending;
                        $completedCount = $completed;
                        $upcoming = 0;
                    } elseif ($date->eq($today)) {
                        // Today: no overdue, no upcoming
                        $overdue = 0;
                        $completedCount = $completed;
                        $upcoming = 0;
                    } else {
                        // Future dates: all pending are upcoming
                        $overdue = 0;
                        $completedCount = $completed;
                        $upcoming = $pending;
                    }

                    $dayData = [
                        'day' => $day['name'],
                        'short' => $day['short'],
                        'overdue' => $overdue,
                        'completed' => $completedCount,
                        'upcoming' => $upcoming,
                        'total' => $overdue + $completedCount + $upcoming,
                        'is_today' => $day['is_today']
                    ];

                    $weekData['days'][] = $dayData;
                    $weekData['total_overdue'] += $overdue;
                    $weekData['total_completed'] += $completedCount;
                    $weekData['total_upcoming'] += $upcoming;
                    $weekData['total_all'] += $dayData['total'];
                }

                return response()->json([
                    'is_admin' => false,
                    'staff_name' => $staffName,
                    'week_data' => $weekData
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Weekly tasks error: ' . $e->getMessage());

            if (isset($isAdmin) && $isAdmin) {
                return response()->json([
                    'is_admin' => true,
                    'staff_data' => [],
                    'days' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
                ]);
            }

            return response()->json([
                'is_admin' => false,
                'staff_name' => Auth::user()->name,
                'week_data' => [
                    'days' => [],
                    'total_overdue' => 0,
                    'total_completed' => 0,
                    'total_upcoming' => 0,
                    'total_all' => 0
                ]
            ]);
        }
    }
    /**
     * Get staff tasks for a specific date
     */
    public function staffTasksForDate(Request $request)
    {
        try {
            $user = Auth::user();
            $today = now()->startOfDay();
            $localTimezone = config('app.timezone', 'Asia/Kathmandu');

            // Parse the requested date
            $requestedDate = $request->date;

            // Get the assignee_id filter (for admin clicking on specific staff)
            $filterAssigneeId = $request->assignee_id;

            Log::info('=== STAFF TASKS POPUP DEBUG ===', [
                'requested_date' => $requestedDate,
                'filter_assignee_id' => $filterAssigneeId,
                'local_timezone' => $localTimezone,
                'user_id' => $user->id,
                'is_staff' => $user->is_staff,
                'is_admin' => $user->is_admin
            ]);

            // Build query
            $query = CrmTasks::with(['student', 'assignee']);

            // IMPORTANT: If a specific staff member is selected (admin clicking on staff name)
            if ($filterAssigneeId && $filterAssigneeId !== 'null' && $filterAssigneeId !== 'undefined') {
                // Filter by the selected staff member only
                $query->where('assigned_to', $filterAssigneeId);
                Log::info('Filtering by specific staff member: ' . $filterAssigneeId);
            } else {
                // Apply default user filter based on role
                if ($user->is_staff) {
                    $query->where('assigned_to', $user->id);
                } elseif ($user->is_agent) {
                    $staffIds = User::where('parent_id', $user->id)->where('role', 'staff')->pluck('id')->toArray();
                    $allowedUserIds = array_merge([$user->id], $staffIds);
                    $query->whereIn('assigned_to', $allowedUserIds);
                }
                Log::info('No specific staff filter - using role-based filtering');
            }

            // Apply additional filters if present
            if ($request->filled('stage_id')) {
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('current_stage_id', $request->stage_id);
                });
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $searchType = $request->search_type ?? 'all';
                $query->whereHas('student', function ($q) use ($search, $searchType) {
                    if ($searchType === 'all' || $searchType === 'name') {
                        $q->where(function ($sub) use ($search) {
                            $sub->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    }
                    if ($searchType === 'phone') {
                        $q->where('phone_number', 'like', "%{$search}%");
                    }
                    if ($searchType === 'email') {
                        $q->where('email', 'like', "%{$search}%");
                    }
                });
            }

            // Get all tasks (without date filter first)
            $allTasks = $query->get();

            Log::info('Total tasks before date filter', ['count' => $allTasks->count()]);

            // Filter tasks by local date - CONVERT UTC TO LOCAL FOR COMPARISON
            $tasks = $allTasks->filter(function ($task) use ($requestedDate, $localTimezone) {
                if (!$task->scheduled_for) {
                    return false;
                }
                // Convert UTC stored date to local timezone
                $localDate = Carbon::parse($task->scheduled_for)
                    ->setTimezone($localTimezone)
                    ->toDateString();

                $matches = $localDate === $requestedDate;

                return $matches;
            });

            Log::info('Tasks found for date after filter', [
                'requested_date' => $requestedDate,
                'filter_assignee_id' => $filterAssigneeId,
                'count' => $tasks->count()
            ]);

            $formattedTasks = [];

            foreach ($tasks as $task) {
                $metaData = $task->meta_data ? (is_string($task->meta_data) ? json_decode($task->meta_data, true) : $task->meta_data) : [];
                $eventDateLocal = Carbon::parse($task->scheduled_for)->setTimezone($localTimezone);
                $isOverdue = $eventDateLocal->startOfDay()->lt($today) && $task->status === 'pending';

                $formattedTasks[] = [
                    'id' => $task->id,
                    'title' => $task->subject,
                    'student_name' => $task->student?->full_name ?? 'Unknown',
                    'student_country' => $task->student?->preferred_country ?? 'N/A',
                    'student_id' => $task->student_id,
                    'scheduled_for' => $task->scheduled_for,
                    'priority_time_slot' => $task->priority_time_slot ?? 'evening',
                    'priority' => $metaData['priority'] ?? 'medium',
                    'assigned_to_name' => $task->assignee?->name ?? 'Unassigned',
                    'assigned_to' => $task->assigned_to,
                    'status' => $task->status,
                    'is_overdue' => $isOverdue,
                    'description' => $task->description,
                ];
            }

            return response()->json(['tasks' => $formattedTasks]);
        } catch (\Exception $e) {
            Log::error('Staff tasks error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['tasks' => []]);
        }
    }

    /**
     * Get calendar events for FullCalendar
     */
    public function calendarEvents(Request $request)
    {
        try {
            $start = Carbon::parse($request->start);
            $end = Carbon::parse($request->end);
            $user = Auth::user();
            $today = now()->startOfDay();
            $localTimezone = config('app.timezone', 'Asia/Kathmandu');

            $query = CrmTasks::with(['student', 'assignee'])
                ->whereNotNull('scheduled_for');

            // Apply user filter
            if ($user->is_staff) {
                $query->where('assigned_to', $user->id);
            } elseif ($user->is_agent) {
                $staffIds = User::where('parent_id', $user->id)->where('role', 'staff')->pluck('id')->toArray();
                $allowedUserIds = array_merge([$user->id], $staffIds);
                $query->whereIn('assigned_to', $allowedUserIds);
            }

            // Apply other filters
            if ($request->filled('assignee_id')) {
                $query->where('assigned_to', $request->assignee_id);
            }

            if ($request->filled('stage_id')) {
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('current_stage_id', $request->stage_id);
                });
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $searchType = $request->search_type ?? 'all';
                $query->whereHas('student', function ($q) use ($search, $searchType) {
                    if ($searchType === 'all' || $searchType === 'name') {
                        $q->where(function ($sub) use ($search) {
                            $sub->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    }
                    if ($searchType === 'phone') {
                        $q->where('phone_number', 'like', "%{$search}%");
                    }
                    if ($searchType === 'email') {
                        $q->where('email', 'like', "%{$search}%");
                    }
                });
            }

            // Get all tasks without date filter, then filter in PHP
            $allTasks = $query->get();

            // Filter tasks by local date range
            $startLocal = Carbon::parse($request->start, $localTimezone);
            $endLocal = Carbon::parse($request->end, $localTimezone);

            $tasks = $allTasks->filter(function ($task) use ($startLocal, $endLocal, $localTimezone) {
                if (!$task->scheduled_for) {
                    return false;
                }
                $localDate = Carbon::parse($task->scheduled_for)
                    ->setTimezone($localTimezone)
                    ->startOfDay();

                return $localDate->between($startLocal->startOfDay(), $endLocal->endOfDay());
            });

            $events = [];

            // STAFF USERS → show individual student events
            if ($user->is_staff) {
                foreach ($tasks as $task) {
                    $eventDateLocal = Carbon::parse($task->scheduled_for)->setTimezone($localTimezone);
                    $metaData = $task->meta_data ? (is_string($task->meta_data) ? json_decode($task->meta_data, true) : $task->meta_data) : [];
                    $priority = $metaData['priority'] ?? 'medium';
                    $timeSlot = $task->priority_time_slot ?? 'evening';
                    $isOverdue = $eventDateLocal->copy()->startOfDay()->lt($today) && $task->status === 'pending';
                    $studentName = $task->student?->full_name ?? 'Unknown';
                    $country = $task->student?->preferred_country ?? 'Unknown';

                    // Custom title for calendar display (Student Name - Country)
                    $displayTitle = $studentName . ' - ' . $country;

                    // Real task title from database
                    $realTaskTitle = $task->subject ?? 'No Title';

                    $events[] = [
                        'id' => $task->id,
                        'title' => $displayTitle,  // This shows on the calendar
                        'start' => $eventDateLocal->toIso8601String(),
                        'extendedProps' => [
                            'task_id' => $task->id,
                            'task_title' => $realTaskTitle,  // Real task title for popup
                            'student_id' => $task->student_id,
                            'student_name' => $studentName,
                            'student_country' => $country,
                            'assigned_to_name' => $task->assignee?->name ?? 'Unassigned',
                            'priority' => $priority,
                            'description' => $task->description ?? 'No description',
                            'time_slot' => $timeSlot,
                            'status' => $task->status,
                            'is_overdue' => $isOverdue,
                        ]
                    ];
                }
            } else {
                // ADMIN / AGENT USERS → group by staff + date
                $groupedTasks = [];

                foreach ($tasks as $task) {
                    $eventDate = Carbon::parse($task->scheduled_for)
                        ->setTimezone($localTimezone)
                        ->toDateString();

                    $staffId = $task->assigned_to ?? 0;
                    $staffName = $task->assignee?->name ?? 'Unassigned';

                    $groupKey = $eventDate . '_' . $staffId;

                    if (!isset($groupedTasks[$groupKey])) {
                        $groupedTasks[$groupKey] = [
                            'date' => $eventDate,
                            'staff_id' => $staffId,
                            'staff_name' => $staffName,
                            'tasks' => [],
                        ];
                    }

                    $groupedTasks[$groupKey]['tasks'][] = $task;
                }

                foreach ($groupedTasks as $group) {
                    $taskCount = count($group['tasks']);

                    // Format title to show staff name and task count
                    $title = $group['staff_name'] . ' (' . $taskCount . ')';

                    $events[] = [
                        'id' => $group['staff_id'] . '_' . $group['date'],
                        'title' => $title,
                        'start' => $group['date'],
                        'allDay' => true,
                        'extendedProps' => [
                            'staff_id' => $group['staff_id'],
                            'staff_name' => $group['staff_name'],
                            'task_count' => $taskCount,
                            'tasks' => collect($group['tasks'])->map(function ($task) {
                                return [
                                    'id' => $task->id,
                                    'student_id' => $task->student_id,
                                    'student_name' => $task->student?->full_name ?? 'Unknown',
                                    'student_country' => $task->student?->preferred_country ?? 'N/A',
                                    'title' => $task->subject,  // Real task title
                                    'priority_time_slot' => $task->priority_time_slot ?? 'evening',
                                    'priority' => json_decode($task->meta_data ?? '{}', true)['priority'] ?? 'medium',
                                    'description' => $task->description ?? 'No description',
                                    'status' => $task->status,
                                ];
                            })->values(),
                        ]
                    ];
                }
            }

            return response()->json(['events' => $events]);
        } catch (\Exception $e) {
            Log::error('Calendar events error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['events' => []]);
        }
    }
}
