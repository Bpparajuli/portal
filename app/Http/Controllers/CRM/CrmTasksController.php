<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CrmTasks;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CrmTasksController extends Controller
{
    // -------------------------------------------------------
    // STORE TASK
    // -------------------------------------------------------
    public function store(Request $request)
    {
        $this->denyAgents();

        $validated = $request->validate([
            'student_id'   => ['required', 'exists:students,id'],
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'task_type'    => ['required', 'string'],
            'priority'     => ['required', 'in:low,medium,high'],
            'due_date'     => ['nullable', 'date'],
            'time_slot'    => ['nullable', 'in:morning,afternoon,evening'],
            'assigned_to'  => ['nullable', 'exists:users,id'],
        ]);

        $this->authorizeStudentAccess($validated['student_id']);

        CrmTasks::create([
            'student_id'           => $validated['student_id'],
            'subject'              => $validated['title'], // ✅ FIX (DB expects subject)
            'description'          => $validated['description'] ?? '',
            'task_type'            => $validated['task_type'],
            'priority'             => $validated['priority'],
            'scheduled_at'         => $validated['due_date'] ?? null,
            'priority_time_slot'   => $validated['time_slot'] ?? null,
            'assigned_to'          => $validated['assigned_to'] ?? Auth::id(),
            'created_by'           => Auth::id(),
        ]);

        return back()->with('success', 'Task created successfully.');
    }

    // -------------------------------------------------------
    // UPDATE TASK
    // -------------------------------------------------------
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
            'task_type'          => $validated['task_type'],
            'priority'           => $validated['priority'],
            'scheduled_at'       => $validated['due_date'] ?? null,
            'priority_time_slot' => $validated['time_slot'] ?? null,
            'assigned_to'        => $validated['assigned_to'] ?? $task->assigned_to,
        ]);

        return back()->with('success', 'Task updated.');
    }

    // -------------------------------------------------------
    // COMPLETE TASK
    // -------------------------------------------------------
    public function complete(CrmTasks $task)
    {
        $this->denyAgents();
        $this->authorizeTask($task);

        $task->update([
            'completed_at' => now(),
            'completed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Task marked as completed.');
    }

    // -------------------------------------------------------
    // CANCEL TASK
    // -------------------------------------------------------
    public function cancel(CrmTasks $task)
    {
        $this->denyAgents();
        $this->authorizeTask($task);

        $task->update(['status' => 'cancelled']);

        return back()->with('success', 'Task cancelled.');
    }

    // -------------------------------------------------------
    // DELETE TASK
    // -------------------------------------------------------
    public function destroy(CrmTasks $task)
    {
        $this->denyAgents();
        $this->authorizeTask($task);

        $task->delete();

        return back()->with('success', 'Task deleted.');
    }

    // -------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------
    private function denyAgents(): void
    {
        abort_if(Auth::user()->is_agent, 403, 'Agents have read-only access.');
    }

    private function authorizeTask(CrmTasks $task): void
    {
        $this->authorizeStudentAccess($task->student_id);
    }

    private function authorizeStudentAccess(int $studentId): void
    {
        $user = Auth::user();
        $student = Student::findOrFail($studentId);

        if ($user->is_admin) return;

        if ($user->is_staff) {
            abort_unless($student->agent_id === $user->id, 403);
            return;
        }

        abort(403);
    }
}
