<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CrmTasks;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CrmTasksController extends Controller
{
    // =========================================================================
    // STORE
    // =========================================================================

    public function store(Request $request)
    {
        $this->denyAgents();

        $validated = $request->validate([
            'student_id'  => ['required', 'exists:students,id'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'task_type'   => ['required', 'string'],
            'priority'    => ['required', 'in:low,medium,high'],
            'due_date'    => ['nullable', 'date'],
            'time_slot'   => ['nullable', 'in:morning,afternoon,evening'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $this->authorizeStudentAccess((int) $validated['student_id']);

        CrmTasks::create([
            'student_id'         => $validated['student_id'],
            'subject'            => $validated['title'],       // DB column = subject
            'description'        => $validated['description'] ?? '',
            'activity_type'      => $validated['task_type'],   // DB column = activity_type
            'scheduled_at'       => $validated['due_date'] ?? null,
            'priority_time_slot' => $validated['time_slot'] ?? null,
            'assigned_to'        => $validated['assigned_to'] ?? Auth::id(),
            'created_by'         => Auth::id(),
            'status'             => 'pending',
            'meta_data'          => ['priority' => $validated['priority']], // priority stored in meta_data
        ]);

        return back()->with('success', 'Task created successfully.');
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(Request $request, CrmTasks $task)
    {
        $this->denyAgents();
        $this->authorizeTask($task);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'task_type'   => ['required', 'string'],
            'priority'    => ['required', 'in:low,medium,high'],
            'due_date'    => ['nullable', 'date'],
            'time_slot'   => ['nullable', 'in:morning,afternoon,evening'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $task->update([
            'subject'            => $validated['title'],
            'description'        => $validated['description'] ?? '',
            'activity_type'      => $validated['task_type'],
            'scheduled_at'       => $validated['due_date'] ?? null,
            'priority_time_slot' => $validated['time_slot'] ?? null,
            'assigned_to'        => $validated['assigned_to'] ?? $task->assigned_to,
            'meta_data'          => array_merge($task->meta_data ?? [], ['priority' => $validated['priority']]),
        ]);

        return back()->with('success', 'Task updated.');
    }

    // =========================================================================
    // COMPLETE
    // =========================================================================

    public function complete(CrmTasks $task)
    {
        $this->denyAgents();
        $this->authorizeTask($task);

        $task->update([
            'status'       => 'completed',
            'completed_at' => now(),
            'completed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Task marked as completed.');
    }

    // =========================================================================
    // CANCEL
    // =========================================================================

    public function cancel(CrmTasks $task)
    {
        $this->denyAgents();
        $this->authorizeTask($task);

        $task->update(['status' => 'cancelled']);

        return back()->with('success', 'Task cancelled.');
    }

    // =========================================================================
    // DELETE
    // =========================================================================

    public function destroy(CrmTasks $task)
    {
        $this->denyAgents();
        $this->authorizeTask($task);

        $task->delete();

        return back()->with('success', 'Task deleted.');
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function denyAgents(): void
    {
        abort_if(Auth::user()->is_agent, 403, 'Agents have read-only CRM access.');
    }

    private function authorizeTask(CrmTasks $task): void
    {
        $this->authorizeStudentAccess($task->student_id);
    }

    /**
     * Access rules match Student::scopeAccessible exactly:
     *   Admin           → any student
     *   Admin's staff   → any student
     *   Agent           → own students + staff-under-them students
     *   Agent's staff   → own students + parent agent's students
     *   Fallback staff  → only own students
     */
    private function authorizeStudentAccess(int $studentId): void
    {
        $user    = Auth::user();
        $student = Student::findOrFail($studentId);

        if ($user->is_admin)       return;
        if ($user->is_admin_staff) return;

        if ($user->is_agent) {
            $staffIds        = User::where('parent_id', $user->id)->where('role', 'staff')->pluck('id')->toArray();
            $allowedAgentIds = array_merge([$user->id], $staffIds);
            abort_unless(in_array($student->agent_id, $allowedAgentIds), 403);
            return;
        }

        if ($user->is_agent_staff) {
            abort_unless(in_array($student->agent_id, [$user->id, $user->parent_id]), 403);
            return;
        }

        if ($user->is_staff) {
            abort_unless($student->agent_id === $user->id, 403);
            return;
        }

        abort(403);
    }
}
