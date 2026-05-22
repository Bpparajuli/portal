<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentStage;
use App\Models\CrmTasks;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // Search filter
        if ($request->filled('search')) {

            $search = trim($request->search);
            $type = $request->search_type ?? 'all';

            $query->where(function ($q) use ($search, $type) {

                // ALL
                if ($type === 'all') {

                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('preferred_country', 'like', "%{$search}%")
                        ->orWhere('tags', 'like', "%{$search}%");
                }

                // NAME
                if ($type === 'name') {

                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                }

                // PHONE
                if ($type === 'phone_number') {

                    $q->where('phone_number', 'like', "%{$search}%");
                }

                // EMAIL
                if ($type === 'email') {

                    $q->where('email', 'like', "%{$search}%");
                }

                // TAG
                if ($type === 'tag') {

                    $q->where('tags', 'like', "%{$search}%");
                }

                // COUNTRY
                if ($type === 'preferred_country') {

                    $q->where('preferred_country', 'like', "%{$search}%");
                }

                // DEGREE
                if ($type === 'degree') {

                    $q->where('degree_level', 'like', "%{$search}%");
                }

                // UNIVERSITY
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

        // Assignee filter - ONLY STAFF users
        if ($request->filled('assignee_id')) {
            if ($user->is_admin || $user->is_admin_staff) {
                $query->where('agent_id', $request->assignee_id);
            } elseif ($user->is_agent) {
                $staffIds = User::where('parent_id', $user->id)->where('role', 'staff')->pluck('id')->toArray();
                if (in_array($request->assignee_id, $staffIds)) {
                    $query->where('agent_id', $request->assignee_id);
                }
            }
        }

        // Activity filter
        if ($request->filled('activity_filter')) {
            if ($request->activity_filter === 'overdue') {
                $query->whereHas('overdueActivities');
            }
            if ($request->activity_filter === 'today') {
                $query->whereHas('pendingActivities', function ($q) {
                    $q->whereDate('scheduled_at', today())->where('status', 'pending');
                });
            }
            if ($request->activity_filter === 'upcoming') {
                $query->whereHas('upcomingActivities');
            }
        }

        // View type
        $view = $request->get('view', 'kanban');

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

        $stats = [
            'total' => $accessibleStudentIds->count(),
            'my_students' => CrmTasks::where('assigned_to', $user->id)->whereNotNull('student_id')->distinct()->count('student_id'),
            'today' => (clone $taskBase)->whereDate('scheduled_at', today())->where('status', 'pending')->count(),
            'overdue' => (clone $taskBase)->whereDate('scheduled_at', '<', today())->where('status', 'pending')->count(),
            'upcoming' => (clone $taskBase)->whereDate('scheduled_at', '>', today())->where('status', 'pending')->count(),
        ];

        return view('crm.dashboard', compact('students', 'stages', 'assignees', 'stats', 'view', 'user', 'request', 'agents'));
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
    }

    public function updateRating(Request $request, $id)
    {
        $request->validate(['rating' => 'nullable|integer|min:1|max:3']);
        $student = Student::findOrFail($id);
        $student->rating = $request->rating;
        $student->save();
        return back()->with('success', 'Rating updated successfully.');
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
                    $s->email ?? '—',
                    $s->phone_number ?? '—',
                    $s->currentStage?->name ?? '—',
                    $s->agent?->name ?? '—',
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
        $student->load(['agent', 'currentStage', 'activities' => fn($q) => $q->latest()->limit(20), 'notes' => fn($q) => $q->latest()]);
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
}
