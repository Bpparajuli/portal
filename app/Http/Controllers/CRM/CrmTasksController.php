<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CrmTasks;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            'task_type'   => ['required', 'string', 'in:call,email,meeting,whatsapp,todo,follow_up,counseling,document_review'],
            'priority'    => ['required', 'in:low,medium,high'],
            'due_date'    => ['nullable', 'date'],
            'time_slot'   => ['nullable', 'in:morning,afternoon,evening'],
            'assigned_to' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $user = User::find($value);
                        if ($user && !$this->isAssignableRole($user)) {
                            $fail('Tasks can only be assigned to Admin or Staff users.');
                        }
                    }
                }
            ],
        ]);

        $this->authorizeStudentAccess((int) $validated['student_id']);

        $assignedTo = $validated['assigned_to'] ?? Auth::id();

        if ($assignedTo && !$this->isAssignableRole(Auth::user())) {
            abort(403, 'You cannot assign tasks to yourself because you are not an Admin or Staff member.');
        }

        CrmTasks::create([
            'student_id'         => $validated['student_id'],
            'subject'            => $validated['title'],
            'description'        => $validated['description'] ?? '',
            'activity_type'      => $validated['task_type'],
            'scheduled_at'       => $validated['due_date'] ?? null,
            'priority_time_slot' => $validated['time_slot'] ?? null,
            'assigned_to'        => $assignedTo,
            'created_by'         => Auth::id(),
            'status'             => 'pending',
            'meta_data'          => json_encode(['priority' => $validated['priority']]),
        ]);

        return redirect()->back()->with('success', 'Task created successfully.');
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
            'task_type'   => ['required', 'string', 'in:call,email,meeting,whatsapp,todo,follow_up,counseling,document_review'],
            'priority'    => ['required', 'in:low,medium,high'],
            'due_date'    => ['nullable', 'date'],
            'time_slot'   => ['nullable', 'in:morning,afternoon,evening'],
            'assigned_to' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $user = User::find($value);
                        if ($user && !$this->isAssignableRole($user)) {
                            $fail('Tasks can only be assigned to Admin or Staff users.');
                        }
                    }
                }
            ],
        ]);

        $assignedTo = $validated['assigned_to'] ?? $task->assigned_to;

        if ($assignedTo) {
            $user = User::find($assignedTo);
            if ($user && !$this->isAssignableRole($user)) {
                abort(403, 'Tasks can only be assigned to Admin or Staff users.');
            }
        }

        $metaData = $task->meta_data ? json_decode($task->meta_data, true) : [];
        $metaData['priority'] = $validated['priority'];

        $task->update([
            'subject'            => $validated['title'],
            'description'        => $validated['description'] ?? '',
            'activity_type'      => $validated['task_type'],
            'scheduled_at'       => $validated['due_date'] ?? null,
            'priority_time_slot' => $validated['time_slot'] ?? null,
            'assigned_to'        => $assignedTo,
            'meta_data'          => json_encode($metaData),
        ]);

        return redirect()->back()->with('success', 'Task updated successfully.');
    }

    // =========================================================================
    // COMPLETE - FIXED VERSION
    // =========================================================================

    public function complete(Request $request, CrmTasks $task)
    {
        $this->denyAgents();
        $this->authorizeTask($task);

        $request->validate([
            'completion_note' => ['required', 'string', 'min:3'],
            'completion_action' => ['required', 'in:just_complete,schedule_next'],
        ]);

        // Update the current task as completed
        $updateData = [
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => Auth::id(),
            'completion_note' => $request->input('completion_note'),
        ];

        // Handle reassignment if specified
        if ($request->input('reassign_to') && $request->input('reassign_to') != $task->assigned_to) {
            $newAssignee = User::find($request->input('reassign_to'));
            if ($newAssignee && $this->isAssignableRole($newAssignee)) {
                $updateData['assigned_to'] = $newAssignee->id;
            }
        }

        $task->update($updateData);

        // Create next task if schedule_next is selected
        if ($request->input('completion_action') === 'schedule_next') {
            $request->validate([
                'next_task_title' => ['required', 'string', 'max:255'],
                'next_due_date' => ['required', 'date'],
                'next_task_type' => ['required', 'string', 'in:call,email,meeting,whatsapp,todo,follow_up,counseling,document_review'],
            ]);

            $nextAssignee = $request->input('next_assigned_to') ?? $request->input('reassign_to') ?? $task->assigned_to;

            $metaData = [
                'priority' => $request->input('next_priority', 'medium'),
                'parent_task_id' => $task->id,
                'created_from_completion' => true,
            ];

            CrmTasks::create([
                'student_id' => $task->student_id,
                'subject' => $request->input('next_task_title'),
                'description' => $request->input('next_task_description') ?? 'Follow-up from completed task: ' . $task->subject,
                'activity_type' => $request->input('next_task_type'),
                'scheduled_at' => $request->input('next_due_date'),
                'priority_time_slot' => $request->input('next_time_slot'),
                'assigned_to' => $nextAssignee,
                'created_by' => Auth::id(),
                'status' => 'pending',
                'meta_data' => json_encode($metaData),
            ]);
        }

        return redirect()->back()->with('success', 'Task marked as completed successfully.');
    }

    // =========================================================================
    // CANCEL - FIXED VERSION
    // =========================================================================

    public function cancel(Request $request, CrmTasks $task)
    {
        $this->denyAgents();
        $this->authorizeTask($task);

        $request->validate([
            'cancellation_reason' => ['required', 'string', 'min:5'],
        ]);

        $task->update([
            'status'             => 'cancelled',
            'cancelled_at'       => now(),
            'cancelled_by'       => Auth::id(),
            'cancellation_note'  => $request->input('cancellation_reason'), // Make sure this matches database column name
        ]);

        return redirect()->back()->with('success', 'Task cancelled successfully.');
    }


    public function reschedule(Request $request, CrmTasks $task)
    {
        $this->denyAgents();
        $this->authorizeTask($task);

        $validated = $request->validate([
            'due_date' => ['nullable', 'date'],
            'time_slot' => ['nullable', 'in:morning,afternoon,evening'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'reschedule_reason' => ['nullable', 'string'],
        ]);

        $task->update([
            'scheduled_at' => $validated['due_date'] ?? $task->scheduled_at,
            'priority_time_slot' => $validated['time_slot'] ?? $task->priority_time_slot,
            'assigned_to' => $validated['assigned_to'] ?? $task->assigned_to,
        ]);

        return redirect()->back()->with('success', 'Task rescheduled successfully.');
    }

    // =========================================================================
    // UNDO COMPLETE
    // =========================================================================

    public function undoComplete(CrmTasks $task)
    {
        $this->denyAgents();
        $this->authorizeTask($task);

        $task->update([
            'status'          => 'pending',
            'completed_at'    => null,
            'completed_by'    => null,
            'completion_note' => null,
        ]);

        return response()->json(['success' => true, 'message' => 'Task reopened successfully.']);
    }

    // =========================================================================
    // UNDO CANCEL
    // =========================================================================

    public function undoCancel(CrmTasks $task)
    {
        $this->denyAgents();
        $this->authorizeTask($task);

        $task->update([
            'status'           => 'pending',
            'cancelled_at'     => null,
            'cancelled_by'     => null,
            'cancellation_note' => null,
        ]);

        return response()->json(['success' => true, 'message' => 'Task restored successfully.']);
    }

    // =========================================================================
    // GET EDIT DATA (AJAX)
    // =========================================================================

    public function getEditData(CrmTasks $task)
    {
        $this->denyAgents();
        $this->authorizeTask($task);

        $metaData = $task->meta_data ? json_decode($task->meta_data, true) : [];

        return response()->json([
            'id' => $task->id,
            'subject' => $task->subject,
            'description' => $task->description,
            'activity_type' => $task->activity_type,
            'scheduled_at' => $task->scheduled_at ? $task->scheduled_at->format('Y-m-d') : null,
            'priority_time_slot' => $task->priority_time_slot,
            'assigned_to' => $task->assigned_to,
            'assignee_name' => $task->assignee?->name,
            'priority' => $metaData['priority'] ?? 'medium',
        ]);
    }

    // =========================================================================
    // DELETE
    // =========================================================================

    public function destroy(CrmTasks $task)
    {
        $this->denyAgents();
        $this->authorizeTask($task);

        $task->delete();

        return redirect()->back()->with('success', 'Task deleted successfully.');
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function scheduleNextTask($completedTask, $request)
    {
        if (!$request->input('next_task_title')) {
            return;
        }

        $nextAssignee = $request->input('reassign_to') ?? $completedTask->assigned_to;

        CrmTasks::create([
            'student_id'         => $completedTask->student_id,
            'subject'            => $request->input('next_task_title'),
            'description'        => $request->input('next_task_description') ?? 'Follow-up from: ' . $completedTask->subject,
            'activity_type'      => 'follow_up',
            'scheduled_at'       => $request->input('next_due_date') ?? now()->addDays(7),
            'priority_time_slot' => $completedTask->priority_time_slot,
            'assigned_to'        => $nextAssignee,
            'created_by'         => Auth::id(),
            'status'             => 'pending',
            'meta_data'          => json_encode(['priority' => 'medium', 'parent_task_id' => $completedTask->id]),
        ]);
    }

    private function denyAgents(): void
    {
        abort_if(Auth::user()->is_agent, 403, 'Agents have read-only CRM access.');
    }

    private function authorizeTask(CrmTasks $task): void
    {
        $this->authorizeStudentAccess($task->student_id);
    }

    private function isAssignableRole(User $user): bool
    {
        return $user->is_admin || $user->is_staff || $user->is_admin_staff;
    }

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
