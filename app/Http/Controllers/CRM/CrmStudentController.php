<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CrmTasks;
use App\Models\Student;
use App\Models\StudentNote;
use App\Models\StudentStage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CrmStudentController extends Controller
{
    // =========================================================================
    // SHOW — full CRM student detail page
    // =========================================================================

    public function show(Student $student)
    {
        $this->authorizeStudent($student);

        $student->load([
            'currentStage',
            'agent',
            'documents',
            'latestApplication',
        ]);
        // GetCrmTaskss that are due (past due date but not completed)
        $dueTasks = CrmTasks::where('student_id', $student->id)
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<', now()->startOfDay())
            ->orderBy('scheduled_at', 'asc')
            ->get();

        // Today's pendingCrmTaskss for this student
        $todayTasks = CrmTasks::where('student_id', $student->id)
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->whereDate('scheduled_at', now()->toDateString())
            ->where('scheduled_at', '>=', now()->startOfDay())
            ->where('scheduled_at', '<', now()->endOfDay())
            ->orderBy('priority_time_slot', 'asc')
            ->get();

        // Planned futureCrmTaskss (andCrmTaskss with no date)
        $plannedTasks = CrmTasks::where('student_id', $student->id)
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->whereDate('scheduled_at', '>', now()->toDateString())
            ->orderBy('scheduled_at', 'asc')
            ->get();

        // Completed activity history — newest first
        $activityHistory = CrmTasks::where('student_id', $student->id)
            ->where('status', 'completed')
            ->with('assignee', 'creator')
            ->latest('completed_at')
            ->paginate(10);

        // Get COMPLETED and CANCELLEDCrmTaskss (done history)
        $completedTasks = CrmTasks::where('student_id', $student->id)
            ->whereIn('status', ['completed', 'cancelled'])
            ->orderBy('completed_at', 'desc')
            ->paginate(10);

        // Internal notes — pinned first, then latest
        $notes = StudentNote::where('student_id', $student->id)
            ->with('creator')
            ->orderByDesc('is_pinned')
            ->latest()
            ->get();

        $stages       = StudentStage::active()->ordered()->get();
        $currentStage = $student->currentStage;

        // Users available forCrmTasks assignment
        $user            = Auth::user();
        $assignableUsers = collect();

        if ($user->is_admin || $user->is_admin_staff) {
            $assignableUsers = User::whereIn('role', ['admin', 'agent', 'staff'])
                ->select('id', 'name', 'role')
                ->orderBy('name')
                ->get();
        } elseif ($user->is_agent) {
            // Agent sees themselves + their staff
            $assignableUsers = User::where(function ($q) use ($user) {
                $q->where('id', $user->id)
                    ->orWhere(fn($sq) => $sq->where('parent_id', $user->id)->where('role', 'staff'));
            })->select('id', 'name', 'role')->get();
        } elseif ($user->is_agent_staff) {
            // Agent's staff sees themselves + their parent agent
            $assignableUsers = User::whereIn('id', [$user->id, $user->parent_id])
                ->select('id', 'name', 'role')
                ->get();
        } else {
            // Fallback staff → only themselves
            $assignableUsers = User::where('id', $user->id)->select('id', 'name', 'role')->get();
        }

        // canEdit: anyone except agents (agents are read-only in CRM)
        $canEdit = ! $user->is_agent;

        return view('crm.show', compact(
            'student',
            'dueTasks',
            'todayTasks',
            'plannedTasks',
            'activityHistory',
            'completedTasks',
            'notes',
            'stages',
            'currentStage',
            'assignableUsers',
            'canEdit'
        ));
    }

    // =========================================================================
    // SAVE NOTE
    // =========================================================================

    public function saveNote(Request $request, Student $student)
    {
        $this->authorizeStudent($student);
        $this->denyAgents();

        $validated = $request->validate([
            'content'   => ['required', 'string'],
            'is_pinned' => ['boolean'],
        ]);

        StudentNote::create([
            'student_id' => $student->id,
            'created_by' => Auth::id(),
            'content'    => $validated['content'],
            'type'       => 'internal',
            'is_pinned'  => $request->boolean('is_pinned'),
        ]);

        return back()->with('success', 'Note saved.');
    }

    // =========================================================================
    // CHANGE STAGE
    // =========================================================================

    public function changeStage(Request $request, Student $student)
    {
        $this->authorizeStudent($student);
        $this->denyAgents();

        $validated = $request->validate([
            'new_stage_id' => ['required', 'exists:student_stages,id'],
            'reason'       => ['nullable', 'string', 'max:500'],
        ]);

        if ($student->currentStage && ! $student->currentStage->canMoveToStage($validated['new_stage_id'])) {
            return back()->withErrors(['new_stage_id' => 'This transition is not allowed from the current stage.']);
        }

        $student->moveToStage($validated['new_stage_id'], $validated['reason'] ?? null);

        return back()->with('success', 'Stage updated.');
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function authorizeStudent(Student $student): void
    {
        $user = Auth::user();

        if ($user->is_admin)       return;
        if ($user->is_admin_staff) return;

        if ($user->is_agent) {
            $staffIds = User::where('parent_id', $user->id)->where('role', 'staff')->pluck('id');
            abort_unless(
                $student->agent_id === $user->id || $staffIds->contains($student->agent_id),
                403
            );
            return;
        }

        if ($user->is_agent_staff) {
            abort_unless(in_array($student->agent_id, [$user->id, $user->parent_id]), 403);
            return;
        }

        // Fallback staff
        if ($user->is_staff) {
            abort_unless($student->agent_id === $user->id, 403);
            return;
        }

        abort(403);
    }

    private function denyAgents(): void
    {
        abort_if(Auth::user()->is_agent, 403, 'Agents have read-only CRM access.');
    }

    public function completeTask(Request $request, CrmTasks $task)
    {
        $request->validate([
            'completion_notes' => 'required|string|min:3'
        ]);

        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completion_notes' => $request->completion_notes
        ]);

        // Schedule nextCrmTasks if requested
        if ($request->schedule_next && $request->next_task_title) {
            CrmTasks::create([
                'student_id' => $task->student_id,
                'subject' => $request->next_task_title,
                'scheduled_at' => $request->next_due_date,
                'assigned_to' => $task->assigned_to,
                'status' => 'pending',
                'activity_type' => $task->activity_type,
                'created_by' => Auth::id()
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function cancelTask(Request $request, CrmTasks $task)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|min:3'
        ]);

        $task->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason,
            'cancelled_at' => now(),
            'cancelled_by' => Auth::id()
        ]);

        return response()->json(['success' => true]);
    }

    public function undoComplete(CrmTasks $task)
    {
        $task->update([
            'status' => 'pending',
            'completed_at' => null,
            'completion_notes' => null
        ]);

        return response()->json(['success' => true]);
    }

    public function updateRating(Request $request, $id)
    {
        $request->validate([
            'rating' => 'nullable|integer|min:1|max:3',
        ]);

        $student = Student::findOrFail($id);

        $student->rating = $request->rating;
        $student->save();

        return response()->json([
            'success' => true,
            'message' => 'Student rating updated successfully.',
            'rating' => $student->rating
        ]);
    }
}
