<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CrmTasks;
use App\Models\Student;
use App\Models\User;
use App\Notifications\CrmTaskNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CrmTasksController extends Controller
{
    // =========================================================================
    // HELPER METHODS FOR ROLE-BASED ACCESS
    // =========================================================================

    /**
     * Apply role-based task filtering to a query
     */
    private function applyTaskFilter($query, $user)
    {
        // TRUE ADMIN - sees ALL tasks
        if ($user->is_admin) {
            return $query;
        }

        // For all other roles (staff, agent staff, admin staff, etc.)
        // Show tasks that are:
        // 1. Assigned to them, OR
        // 2. Created by them, OR
        // 3. For students they have access to
        return $query->where(function ($q) use ($user) {
            $q->where('assigned_to', $user->id)
                ->orWhere('created_by', $user->id)
                ->orWhereHas('student', function ($studentQuery) use ($user) {
                    $studentQuery->whereIn('agent_id', $this->getAccessibleUserIds($user));
                });
        });
    }

    /**
     * Get all user IDs that this user can access students for
     */
    private function getAccessibleUserIds($user)
    {
        $userIds = [$user->id];

        // If user is an agent, include their staff
        if ($user->is_agent) {
            $staffIds = User::where('parent_id', $user->id)
                ->pluck('id')
                ->toArray();
            $userIds = array_merge($userIds, $staffIds);
        }

        // If user is agent staff, include their parent agent
        if ($user->is_agent_staff && $user->parent_id) {
            $userIds[] = $user->parent_id;
        }

        return $userIds;
    }

    // =========================================================================
    // STORE
    // =========================================================================

    public function store(Request $request)
    {
        // Skip agent check for now
        // $this->denyAgents();

        if ($request->has('next_due_date') && !$request->has('due_date')) {
            $request->merge(['due_date' => $request->next_due_date]);
        }

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

        // TEMPORARILY DISABLE STUDENT ACCESS CHECK FOR DEBUGGING
        // $this->authorizeStudentAccess((int) $validated['student_id']);

        $assignedTo = $validated['assigned_to'] ?? Auth::id();

        $existingTask = CrmTasks::where('student_id', $validated['student_id'])
            ->where('subject', $validated['title'])
            ->where('status', 'pending')
            ->where('created_at', '>=', Carbon::now()->subMinutes(5))
            ->first();

        if ($existingTask) {
            return redirect()->back()
                ->with('warning', '⚠️ A similar task was created recently.')
                ->withInput();
        }

        $scheduledDateTime = null;
        if (!empty($validated['due_date']) && !empty($validated['time_slot'])) {
            $calculatedTime = $this->calculateTaskTime(
                $validated['due_date'],
                $validated['time_slot'],
                $validated['student_id'],
                $assignedTo
            );
            $scheduledDateTime = $calculatedTime instanceof Carbon ? $calculatedTime->toDateTimeString() : $calculatedTime;
        } elseif (!empty($validated['due_date'])) {
            $scheduledDateTime = $validated['due_date'];
        }

        DB::beginTransaction();

        try {
            $task = CrmTasks::create([
                'student_id'         => $validated['student_id'],
                'subject'            => $validated['title'],
                'description'        => $validated['description'] ?? '',
                'activity_type'      => $validated['task_type'],
                'scheduled_for'      => $scheduledDateTime,
                'priority_time_slot' => $validated['time_slot'] ?? null,
                'assigned_to'        => $assignedTo,
                'created_by'         => Auth::id(),
                'status'             => 'pending',
                'meta_data'          => json_encode([
                    'priority' => $validated['priority'],
                    'created_at' => now()->toDateTimeString(),
                ]),
            ]);

            if ($assignedTo && $assignedTo != Auth::id()) {
                $assignee = User::find($assignedTo);
                if ($assignee) {
                    $assignee->notify(new CrmTaskNotification($task, 'assigned'));
                }
            }

            DB::commit();
            return redirect()->back()->with('success', '✅ Task created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create task: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', '❌ Failed to create task: ' . $e->getMessage())
                ->withInput();
        }
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(Request $request, CrmTasks $task)
    {
        // $this->denyAgents();
        // $this->authorizeTask($task);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'task_type'   => ['required', 'string'],
            'priority'    => ['required', 'in:low,medium,high'],
            'due_date'    => ['nullable', 'date'],
            'time_slot'   => ['nullable', 'in:morning,afternoon,evening'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $assignedTo = $validated['assigned_to'] ?? $task->assigned_to;

        $scheduledDateTime = $task->scheduled_for;
        if ($validated['due_date'] && $validated['time_slot']) {
            $scheduledDateTime = $this->calculateTaskTime(
                $validated['due_date'],
                $validated['time_slot'],
                $task->student_id,
                $assignedTo,
                $task->id
            );
        } elseif ($validated['due_date'] && !$validated['time_slot']) {
            $scheduledDateTime = $validated['due_date'];
        }

        $metaData = $task->meta_data ? json_decode($task->meta_data, true) : [];
        $metaData['priority'] = $validated['priority'];

        $task->update([
            'subject'            => $validated['title'],
            'description'        => $validated['description'] ?? '',
            'activity_type'      => $validated['task_type'],
            'scheduled_for'      => $scheduledDateTime ?? $validated['due_date'] ?? $task->scheduled_for,
            'priority_time_slot' => $validated['time_slot'] ?? null,
            'assigned_to'        => $assignedTo,
            'meta_data'          => json_encode($metaData),
        ]);

        return redirect()->back()->with('success', 'Task updated successfully.');
    }

    // =========================================================================
    // COMPLETE
    // =========================================================================

    public function complete(Request $request, CrmTasks $task)
    {
        $request->validate([
            'completion_note' => ['nullable', 'string'],
            'completion_action' => ['required', 'in:just_complete,create_next'],
            'reassign_to' => ['nullable', 'exists:users,id'],
            'next_task_title' => ['required_if:completion_action,create_next', 'nullable', 'string'],
            'due_date' => ['required_if:completion_action,create_next', 'nullable', 'date'],
            'next_task_type' => ['nullable', 'string'],
            'next_task_description' => ['nullable', 'string'],
            'next_time_slot' => ['nullable', 'in:morning,afternoon,evening'],
            'next_priority' => ['nullable', 'in:low,medium,high'],
            'next_assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        DB::beginTransaction();

        try {
            $updateData = [
                'status' => 'completed',
                'completed_at' => now(),
                'completed_by' => Auth::id(),
                'completion_note' => $request->input('completion_note') ?? 'Task completed',
            ];

            if ($request->filled('reassign_to') && $request->input('reassign_to') != $task->assigned_to) {
                $updateData['assigned_to'] = $request->input('reassign_to');
            }

            $task->update($updateData);

            if ($request->input('completion_action') === 'create_next' && $request->filled('next_task_title')) {
                $nextAssignee = $request->input('next_assigned_to') ?? $request->input('reassign_to') ?? $task->assigned_to;

                $nextScheduledDateTime = null;
                if ($request->input('due_date') && $request->input('next_time_slot')) {
                    $nextScheduledDateTime = $this->calculateTaskTime(
                        $request->input('due_date'),
                        $request->input('next_time_slot'),
                        $task->student_id,
                        $nextAssignee
                    );
                }

                CrmTasks::create([
                    'student_id' => $task->student_id,
                    'subject' => $request->input('next_task_title'),
                    'description' => $request->input('next_task_description') ?? 'Follow-up from completed task',
                    'activity_type' => $request->input('next_task_type', 'follow_up'),
                    'scheduled_for' => $nextScheduledDateTime ?? $request->input('due_date'),
                    'priority_time_slot' => $request->input('next_time_slot'),
                    'assigned_to' => $nextAssignee,
                    'created_by' => Auth::id(),
                    'status' => 'pending',
                    'meta_data' => json_encode(['priority' => $request->input('next_priority', 'medium')]),
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Task completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to complete task: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // CANCEL
    // =========================================================================

    public function cancel(Request $request, CrmTasks $task)
    {
        $request->validate([
            'cancellation_reason' => ['required', 'string', 'min:5'],
        ]);

        DB::beginTransaction();

        try {
            $task->update([
                'status'             => 'cancelled',
                'cancelled_at'       => now(),
                'cancelled_by'       => Auth::id(),
                'cancellation_note'  => $request->input('cancellation_reason'),
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Task cancelled successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to cancel task.');
        }
    }

    // =========================================================================
    // RESCHEDULE
    // =========================================================================

    public function reschedule(Request $request, CrmTasks $task)
    {
        $validated = $request->validate([
            'due_date' => ['nullable', 'date'],
            'time_slot' => ['nullable', 'in:morning,afternoon,evening'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'reschedule_reason' => ['nullable', 'string'],
        ]);

        $scheduledDateTime = $task->scheduled_for;
        if ($validated['due_date'] && $validated['time_slot']) {
            $scheduledDateTime = $this->calculateTaskTime(
                $validated['due_date'],
                $validated['time_slot'],
                $task->student_id,
                $validated['assigned_to'] ?? $task->assigned_to,
                $task->id
            );
        } elseif ($validated['due_date']) {
            $scheduledDateTime = $validated['due_date'];
        }

        $task->update([
            'scheduled_for' => $scheduledDateTime ?? $task->scheduled_for,
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
        $task->update([
            'status'           => 'pending',
            'cancelled_at'     => null,
            'cancelled_by'     => null,
            'cancellation_note' => null,
        ]);

        return response()->json(['success' => true, 'message' => 'Task restored successfully.']);
    }

    // =========================================================================
    // DELETE SINGLE TASK
    // =========================================================================

    public function destroy(CrmTasks $task)
    {
        try {
            if ($task->status !== 'pending' && !Auth::user()->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only administrators can delete completed or cancelled tasks.'
                ], 403);
            }

            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task: ' . $e->getMessage()
            ], 500);
        }
    }

    // =========================================================================
    // BULK ACTIONS
    // =========================================================================

    public function bulkComplete(Request $request)
    {
        $request->validate([
            'task_ids' => ['required', 'array'],
            'task_ids.*' => ['exists:crm_tasks,id'],
            'completion_note' => ['nullable', 'string'],
        ]);

        $count = CrmTasks::whereIn('id', $request->input('task_ids'))
            ->where('status', 'pending')
            ->update([
                'status' => 'completed',
                'completed_at' => now(),
                'completed_by' => Auth::id(),
                'completion_note' => $request->input('completion_note') ?? 'Bulk completed',
            ]);

        return redirect()->back()->with('success', "{$count} tasks completed successfully.");
    }

    public function bulkCancel(Request $request)
    {
        $request->validate([
            'task_ids' => ['required', 'array'],
            'task_ids.*' => ['exists:crm_tasks,id'],
            'cancellation_reason' => ['required', 'string'],
        ]);

        $count = CrmTasks::whereIn('id', $request->input('task_ids'))
            ->where('status', 'pending')
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => Auth::id(),
                'cancellation_note' => $request->input('cancellation_reason'),
            ]);

        return redirect()->back()->with('success', "{$count} tasks cancelled successfully.");
    }

    /**
     * Check for due tasks and send notifications
     */
    public function checkDueTasks(Request $request)
    {
        try {
            $user = Auth::user();
            $today = Carbon::today();
            $notificationsSent = 0;

            $tasks = CrmTasks::where('assigned_to', $user->id)
                ->where('status', 'pending')
                ->where(function ($query) use ($today) {
                    $query->whereDate('scheduled_for', $today)
                        ->orWhereDate('scheduled_for', $today->copy()->addDay())
                        ->orWhereDate('scheduled_for', '<', $today);
                })
                ->get();

            foreach ($tasks as $task) {
                $user->notify(new CrmTaskNotification($task, 'due_today'));
                $notificationsSent++;
            }

            return response()->json(['success' => true, 'sent' => $notificationsSent]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // TASK TIME CALCULATION HELPER
    // =========================================================================

    private function calculateTaskTime($date, $timeSlot, $studentId = null, $assigneeId = null, $excludeTaskId = null)
    {
        if (!$date || !$timeSlot) {
            return null;
        }

        $dateOnly = $date;
        if (strpos($date, 'T') !== false) {
            $dateOnly = explode('T', $date)[0];
        }

        $slotTimes = [
            'morning'   => ['start' => '09:00:00', 'end' => '12:00:00', 'interval' => 5],
            'afternoon' => ['start' => '12:00:00', 'end' => '15:00:00', 'interval' => 5],
            'evening'   => ['start' => '15:00:00', 'end' => '18:00:00', 'interval' => 5],
        ];

        $slot = $slotTimes[$timeSlot];
        $baseDateTime = Carbon::parse($dateOnly . ' ' . $slot['start']);

        $query = CrmTasks::whereDate('scheduled_for', $dateOnly)
            ->where('priority_time_slot', $timeSlot)
            ->whereIn('status', ['pending', 'in_progress']);

        if ($assigneeId) {
            $query->where('assigned_to', $assigneeId);
        } elseif ($studentId) {
            $query->where('student_id', $studentId);
        }

        if ($excludeTaskId) {
            $query->where('id', '!=', $excludeTaskId);
        }

        $taskCount = $query->count();
        $minutesToAdd = $taskCount * $slot['interval'];
        $scheduledDateTime = clone $baseDateTime;
        $scheduledDateTime->addMinutes($minutesToAdd);

        $endTime = Carbon::parse($dateOnly . ' ' . $slot['end']);

        if ($scheduledDateTime->gt($endTime)) {
            return $endTime;
        }

        return $scheduledDateTime;
    }

    // =========================================================================
    // DELETE METHODS (Admin only)
    // =========================================================================

    public function deleteCompletedTasks(Request $request)
    {
        if (!Auth::user()->is_admin) {
            abort(403, 'Only administrators can delete completed tasks.');
        }

        $daysOld = $request->input('days_old', 30);
        $cutoffDate = Carbon::now()->subDays($daysOld);

        $count = CrmTasks::where('status', 'completed')
            ->where('completed_at', '<=', $cutoffDate)
            ->delete();

        return redirect()->back()->with('success', "{$count} completed task(s) deleted successfully.");
    }

    public function deleteCancelledTasks(Request $request)
    {
        if (!Auth::user()->is_admin) {
            abort(403, 'Only administrators can delete cancelled tasks.');
        }

        $daysOld = $request->input('days_old', 30);
        $cutoffDate = Carbon::now()->subDays($daysOld);

        $count = CrmTasks::where('status', 'cancelled')
            ->where('cancelled_at', '<=', $cutoffDate)
            ->delete();

        return redirect()->back()->with('success', "{$count} cancelled task(s) deleted successfully.");
    }

    public function getEditData(CrmTasks $task)
    {
        $metaData = $task->meta_data;

        return response()->json([
            'id' => $task->id,
            'subject' => $task->subject,
            'description' => $task->description,
            'activity_type' => $task->activity_type,
            'scheduled_for' => $task->scheduled_for ? $task->scheduled_for->format('Y-m-d') : null,
            'priority_time_slot' => $task->priority_time_slot,
            'assigned_to' => $task->assigned_to,
            'assignee_name' => $task->assignee?->name,
            'priority' => $metaData['priority'] ?? 'medium',
            'status' => $task->status,
        ]);
    }

    public function checkDuplicate(Request $request, $studentId)
    {
        try {
            $validated = $request->validate([
                'title' => ['required', 'string'],
                'minutes' => ['nullable', 'integer', 'min:1', 'max:60'],
            ]);

            $minutes = $validated['minutes'] ?? 5;

            $existingTask = CrmTasks::where('student_id', $studentId)
                ->where('subject', $validated['title'])
                ->where('status', 'pending')
                ->where('created_at', '>=', Carbon::now()->subMinutes($minutes))
                ->first();

            return response()->json([
                'has_duplicate' => !is_null($existingTask),
                'existing_task' => $existingTask ? [
                    'id' => $existingTask->id,
                    'subject' => $existingTask->subject,
                    'created_at' => $existingTask->created_at->diffForHumans(),
                ] : null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['has_duplicate' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
