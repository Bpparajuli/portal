<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentStage;
use App\Models\CrmTasks;
use App\Models\StudentNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CrmStudentController extends Controller
{
    // -------------------------------------------------------------------------
    // Show — main CRM student detail page (the "show.blade.php")
    // -------------------------------------------------------------------------

    /**
     * Full CRM record for a student.
     * Tabs: Tasks | Documents | History
     *
     * Access: Admin (full) | Agent (read-only) | Staff (full, own students only)
     */
    public function show(Student $student)
    {
        $this->authorizeStudent($student);

        $student->load([
            'currentStage',
            'agent',
            'documents',
            'latestApplication',
        ]);

        // Tasks split: today's tasks vs. planned future tasks
        $todayTasks = CrmTasks::where('student_id', $student->id)
            ->whereDate('scheduled_at', today())
            ->whereNull('completed_at')
            ->orderBy('priority_time_slot')
            ->get();

        $plannedTasks = CrmTasks::where('student_id', $student->id)
            ->where(function ($q) {
                $q->whereDate('scheduled_at', '>', today())
                    ->orWhereNull('scheduled_at');
            })
            ->whereNull('completed_at')
            ->orderBy('scheduled_at')
            ->get();

        // Activity history (completed activities) — newest first
        $activityHistory = CrmTasks::where('student_id', $student->id)
            ->whereNotNull('completed_at')
            ->with('assignee')
            ->latest('completed_at')
            ->paginate(10);

        // Internal notes (pinned first, then latest)
        $notes = StudentNote::where('student_id', $student->id)
            ->with('creator')
            ->orderByDesc('is_pinned')
            ->latest()
            ->get();

        // Stage bar
        $stages       = StudentStage::active()->ordered()->get();
        $currentStage = $student->currentStage;

        // Staff list for task assignment
        $user = Auth::user();
        $assignableUsers = collect();

        if ($user->is_admin) {
            $assignableUsers = User::whereIn('role', ['admin', 'staff'])->select('id', 'name', 'role')->get();
        } elseif ($user->is_agent) {
            $assignableUsers = User::where('parent_id', $user->id)->where('role', 'staff')->select('id', 'name')->get();
        } elseif ($user->is_staff) {
            $assignableUsers = User::where('id', $user->id)->select('id', 'name')->get();
        }

        $canEdit = $user->is_admin || $user->is_staff;

        return view('crm.show', compact(
            'student',
            'todayTasks',
            'plannedTasks',
            'activityHistory',
            'notes',
            'stages',
            'currentStage',
            'assignableUsers',
            'canEdit'
        ));
    }

    // -------------------------------------------------------------------------
    // Inline note save (from the show page textarea)
    // -------------------------------------------------------------------------

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

    // -------------------------------------------------------------------------
    // Stage change (from the stage bar on show page)
    // -------------------------------------------------------------------------

    public function changeStage(Request $request, Student $student)
    {
        $this->authorizeStudent($student);
        $this->denyAgents();

        $validated = $request->validate([
            'new_stage_id' => ['required', 'exists:student_stages,id'],
            'reason'       => ['nullable', 'string', 'max:500'],
        ]);

        if ($student->currentStage && !$student->currentStage->canMoveToStage($validated['new_stage_id'])) {
            return back()->withErrors(['new_stage_id' => 'This transition is not allowed from the current stage.']);
        }

        $student->moveToStage($validated['new_stage_id'], $validated['reason'] ?? null);

        return back()->with('success', 'Stage updated.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function authorizeStudent(Student $student): void
    {
        $user = Auth::user();

        if ($user->is_admin) return;

        if ($user->is_agent) {
            // Agent can view students they own or that their staff owns
            $staffIds = User::where('parent_id', $user->id)->where('role', 'staff')->pluck('id');
            abort_unless(
                $student->agent_id === $user->id || $staffIds->contains($student->agent_id),
                403
            );
            return;
        }

        if ($user->is_staff) {
            abort_unless($student->agent_id === $user->id, 403);
            return;
        }

        abort(403);
    }

    private function denyAgents(): void
    {
        abort_if(Auth::user()->is_agent, 403, 'Agents have read-only access to the CRM.');
    }
}
