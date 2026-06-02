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
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyTaskFilter($query, $user)
    {
        // CRITICAL: Staff members (including admin_staff) should ONLY see tasks assigned to them
        // Only true admins (is_admin = true) should see all tasks

        // Check if user is staff (including admin_staff) but NOT a true admin
        if (($user->is_staff || $user->is_admin_staff) && !$user->is_admin) {
            Log::info('Task Filter: STAFF MODE - User ' . $user->id . ' sees only tasks assigned to them');
            return $query->where('assigned_to', $user->id);
        }

        // Agent staff - see tasks assigned to them or their staff
        if ($user->is_agent_staff && !$user->is_admin) {
            $staffIds = User::where('parent_id', $user->parent_id ?? $user->id)
                ->where('role', 'staff')
                ->pluck('id')
                ->toArray();
            $allowedIds = array_merge([$user->id], $staffIds);
            Log::info('Task Filter: AGENT STAFF MODE - User ' . $user->id . ' sees tasks for: ' . json_encode($allowedIds));
            return $query->whereIn('assigned_to', $allowedIds);
        }

        // Agent - see tasks assigned to them or their staff
        if ($user->is_agent && !$user->is_admin) {
            $staffIds = User::where('parent_id', $user->id)
                ->where('role', 'staff')
                ->pluck('id')
                ->toArray();
            $allowedIds = array_merge([$user->id], $staffIds);
            Log::info('Task Filter: AGENT MODE - User ' . $user->id . ' sees tasks for: ' . json_encode($allowedIds));
            return $query->whereIn('assigned_to', $allowedIds);
        }

        // True Admin (is_admin = true) - sees ALL tasks
        Log::info('Task Filter: ADMIN MODE - User ' . $user->id . ' sees ALL tasks');
        return $query;
    }

    // =========================================================================
    // STORE
    // =========================================================================

    /**
     * Store a newly created task with duplicate prevention
     */
    public function store(Request $request)
    {
        $this->denyAgents();

        // Map next_due_date to due_date if present
        if ($request->has('next_due_date') && !$request->has('due_date')) {
            $request->merge(['due_date' => $request->next_due_date]);
        }

        $validated = $request->validate([
            'student_id'  => ['required', 'exists:students,id'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'task_type'   => ['required', 'string', 'in:call,email,meeting,whatsapp,todo,follow_up,counseling,document_review'],
            'priority'    => ['required', 'in:low,medium,high'],
            'due_date'    => ['nullable', 'date'],
            'time_slot'   => ['nullable', 'in:morning,afternoon,evening'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $this->authorizeStudentAccess((int) $validated['student_id']);
        $assignedTo = $validated['assigned_to'] ?? Auth::id();

        // ==============================================
        // CHECK FOR DUPLICATE BEFORE TRANSACTION
        // ==============================================

        // Check for duplicate in last 5 minutes
        $existingTask = CrmTasks::where('student_id', $validated['student_id'])
            ->where('subject', $validated['title'])
            ->where('status', 'pending')
            ->where('created_at', '>=', Carbon::now()->subMinutes(5))
            ->first();

        if ($existingTask) {
            return redirect()->back()
                ->with('warning', '⚠️ A similar task was created recently. Please wait before creating duplicates.')
                ->withInput();
        }

        // Calculate the specific datetime if due_date and time_slot are provided
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
            $dueDateValue = $validated['due_date'];
            if (strpos($dueDateValue, 'T') !== false) {
                $dueDateValue = str_replace('T', ' ', $dueDateValue);
            }
            $scheduledDateTime = $dueDateValue;
        }

        // ==============================================
        // START DATABASE TRANSACTION
        // ==============================================
        DB::beginTransaction();

        try {
            // Final duplicate check with lock (prevents race conditions)
            $finalCheck = CrmTasks::where('student_id', $validated['student_id'])
                ->where('subject', $validated['title'])
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if ($finalCheck) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', '❌ Task already exists. Please check the task list.')
                    ->withInput();
            }

            // Create the task
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

            // Send notification if assigned to someone else
            if ($assignedTo && $assignedTo != Auth::id()) {
                $assignee = User::find($assignedTo);
                if ($assignee) {
                    $assignee->notify(new CrmTaskNotification($task, 'assigned'));
                }
            }

            // Log the activity
            $this->logActivity($task->student_id, 'task_created', "Task created: {$task->subject}");

            // Commit transaction
            DB::commit();

            return redirect()->back()->with('success', '✅ Task created successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            // Handle duplicate entry from database constraint (error code 1062 for MySQL)
            if ($e->errorInfo[1] == 1062) {
                Log::warning('Duplicate task prevented by database constraint', [
                    'student_id' => $validated['student_id'],
                    'subject' => $validated['title'],
                ]);
                return redirect()->back()
                    ->with('error', '❌ This task already exists. Duplicate prevented.')
                    ->withInput();
            }

            Log::error('Failed to create task: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', '❌ Failed to create task. Please try again.')
                ->withInput();
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

        // Calculate the specific datetime if due_date and time_slot are provided and changed
        $scheduledDateTime = $task->scheduled_for;
        if ($validated['due_date'] && $validated['time_slot']) {
            // Check if date or time_slot changed
            $dateChanged = $validated['due_date'] != ($task->scheduled_for ? $task->scheduled_for->format('Y-m-d') : null);
            $slotChanged = $validated['time_slot'] != $task->priority_time_slot;

            if ($dateChanged || $slotChanged) {
                $scheduledDateTime = $this->calculateTaskTime(
                    $validated['due_date'],
                    $validated['time_slot'],
                    $task->student_id,
                    $assignedTo,
                    $task->id // Exclude current task from count
                );
            }
        } elseif ($validated['due_date'] && !$validated['time_slot']) {
            // Only date provided, keep just the date
            $scheduledDateTime = $validated['due_date'];
        }

        $metaData = $task->meta_data ? json_decode($task->meta_data, true) : [];
        $metaData['priority'] = $validated['priority'];
        $oldAssignee = $task->assigned_to;

        $task->update([
            'subject'            => $validated['title'],
            'description'        => $validated['description'] ?? '',
            'activity_type'      => $validated['task_type'],
            'scheduled_for'      => $scheduledDateTime ?? $validated['due_date'] ?? $task->scheduled_for,
            'priority_time_slot' => $validated['time_slot'] ?? null,
            'assigned_to'        => $assignedTo,
            'meta_data'          => json_encode($metaData),
        ]);

        // Log assignment change
        if ($oldAssignee != $assignedTo) {
            $this->logActivity(
                $task->student_id,
                'task_reassigned',
                "Task '{$task->subject}' reassigned from User:{$oldAssignee} to User:{$assignedTo}"
            );
        }

        $this->logActivity($task->student_id, 'task_updated', "Task updated: {$task->subject}");

        return redirect()->back()->with('success', 'Task updated successfully.');
    }

    // =========================================================================
    // COMPLETE
    // =========================================================================

    public function complete(Request $request, CrmTasks $task)
    {
        $this->denyAgents();
        $this->authorizeTask($task);

        $request->validate([
            'completion_note' => ['nullable', 'string'],
            'completion_action' => ['required', 'in:just_complete,create_next'],
            'reassign_to' => ['nullable', 'exists:users,id'],
            'next_task_title' => ['required_if:completion_action,create_next', 'nullable', 'string', 'max:255'],
            'due_date' => ['required_if:completion_action,create_next', 'nullable', 'date'],
            'next_task_type' => ['required_if:completion_action,create_next', 'nullable', 'string', 'in:call,email,meeting,whatsapp,todo,follow_up,counseling,document_review'],
            'next_task_description' => ['nullable', 'string'],
            'next_time_slot' => ['nullable', 'in:morning,afternoon,evening'],
            'next_priority' => ['nullable', 'in:low,medium,high'],
            'next_assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        DB::beginTransaction();

        try {
            // Prepare update data
            $updateData = [
                'status' => 'completed',
                'completed_at' => now(),
                'completed_by' => Auth::id(),
                'completion_note' => $request->input('completion_note') ?? 'Task completed',
            ];

            // Handle reassignment if specified and different
            if ($request->filled('reassign_to') && $request->input('reassign_to') != $task->assigned_to) {
                $newAssignee = User::find($request->input('reassign_to'));
                if ($newAssignee && $this->isAssignableRole($newAssignee)) {
                    $updateData['assigned_to'] = $newAssignee->id;
                }
            }

            $task->update($updateData);

            // Create next task if create_next is selected
            // Inside the complete method, where it creates the next task
            if ($request->input('completion_action') === 'create_next') {
                if ($request->filled('next_task_title') && $request->filled('due_date')) {
                    $nextAssignee = $request->input('next_assigned_to') ?? $request->input('reassign_to') ?? $task->assigned_to;

                    // ==============================================
                    // CHECK FOR DUPLICATE NEXT TASK
                    // ==============================================
                    $existingNextTask = CrmTasks::where('student_id', $task->student_id)
                        ->where('subject', $request->input('next_task_title'))
                        ->where('status', 'pending')
                        ->where('created_at', '>=', Carbon::now()->subMinutes(5))
                        ->first();

                    if (!$existingNextTask) {
                        // Calculate time for next task if time slot is provided
                        $nextScheduledDateTime = null;
                        if ($request->input('due_date') && $request->input('next_time_slot')) {
                            $nextScheduledDateTime = $this->calculateTaskTime(
                                $request->input('due_date'),
                                $request->input('next_time_slot'),
                                $task->student_id,
                                $nextAssignee
                            );
                        }

                        $metaData = [
                            'priority' => $request->input('next_priority', 'medium'),
                            'parent_task_id' => $task->id,
                            'created_from_completion' => true,
                        ];

                        $nextTask = CrmTasks::create([
                            'student_id' => $task->student_id,
                            'subject' => $request->input('next_task_title'),
                            'description' => $request->input('next_task_description') ?? 'Follow-up from completed task: ' . $task->subject,
                            'activity_type' => $request->input('next_task_type', 'follow_up'),
                            'scheduled_for' => $nextScheduledDateTime ?? $request->input('due_date'),
                            'priority_time_slot' => $request->input('next_time_slot'),
                            'assigned_to' => $nextAssignee,
                            'created_by' => Auth::id(),
                            'status' => 'pending',
                            'meta_data' => json_encode($metaData),
                        ]);
                    }
                }
            }

            DB::commit();

            $message = $request->input('completion_action') === 'create_next'
                ? 'Task completed and follow-up task created successfully.'
                : 'Task marked as completed successfully.';

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Task completion failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to complete task: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // CANCEL
    // =========================================================================

    public function cancel(Request $request, CrmTasks $task)
    {
        $this->denyAgents();
        $this->authorizeTask($task);

        $request->validate([
            'cancellation_reason' => ['required', 'string', 'min:5'],
        ]);

        DB::beginTransaction();

        try {
            $oldStatus = $task->status;
            $oldAssignee = $task->assigned_to;

            $task->update([
                'status'             => 'cancelled',
                'cancelled_at'       => now(),
                'cancelled_by'       => Auth::id(),
                'cancellation_note'  => $request->input('cancellation_reason'),
            ]);

            $this->logActivity(
                $task->student_id,
                'task_cancelled',
                "Task cancelled: {$task->subject}. Reason: " . substr($request->input('cancellation_reason'), 0, 200)
            );

            DB::commit();

            return redirect()->back()->with('success', 'Task cancelled successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Task cancellation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to cancel task.');
        }
    }

    // =========================================================================
    // DELETE COMPLETED TASKS (Admin only)
    // =========================================================================

    public function deleteCompletedTasks(Request $request)
    {
        // Only admin can delete completed tasks
        if (!Auth::user()->is_admin) {
            abort(403, 'Only administrators can delete completed tasks.');
        }

        $request->validate([
            'days_old' => ['nullable', 'integer', 'min:1'],
            'task_ids' => ['nullable', 'array'],
            'task_ids.*' => ['exists:crm_tasks,id'],
        ]);

        $query = CrmTasks::where('status', 'completed');

        // If specific task IDs provided, delete those
        if ($request->has('task_ids') && !empty($request->task_ids)) {
            $query->whereIn('id', $request->task_ids);
        }
        // Otherwise delete based on age
        else {
            $daysOld = $request->input('days_old', 30); // Default 30 days
            $cutoffDate = Carbon::now()->subDays($daysOld);
            $query->where('completed_at', '<=', $cutoffDate);
        }

        $count = $query->delete();

        $this->logActivity(0, 'completed_tasks_deleted', "Admin deleted {$count} completed tasks");

        return redirect()->back()->with('success', "{$count} completed task(s) deleted successfully.");
    }

    // =========================================================================
    // DELETE CANCELLED TASKS (Admin only)
    // =========================================================================

    public function deleteCancelledTasks(Request $request)
    {
        // Only admin can delete cancelled tasks
        if (!Auth::user()->is_admin) {
            abort(403, 'Only administrators can delete cancelled tasks.');
        }

        $request->validate([
            'days_old' => ['nullable', 'integer', 'min:1'],
            'task_ids' => ['nullable', 'array'],
            'task_ids.*' => ['exists:crm_tasks,id'],
        ]);

        $query = CrmTasks::where('status', 'cancelled');

        // If specific task IDs provided, delete those
        if ($request->has('task_ids') && !empty($request->task_ids)) {
            $query->whereIn('id', $request->task_ids);
        }
        // Otherwise delete based on age
        else {
            $daysOld = $request->input('days_old', 30); // Default 30 days
            $cutoffDate = Carbon::now()->subDays($daysOld);
            $query->where('cancelled_at', '<=', $cutoffDate);
        }

        $count = $query->delete();

        $this->logActivity(0, 'cancelled_tasks_deleted', "Admin deleted {$count} cancelled tasks");

        return redirect()->back()->with('success', "{$count} cancelled task(s) deleted successfully.");
    }

    // =========================================================================
    // DELETE COMPLETED AND CANCELLED TASKS (Combined - Admin only)
    // =========================================================================

    public function deleteCompletedAndCancelledTasks(Request $request)
    {
        // Only admin can delete completed and cancelled tasks
        if (!Auth::user()->is_admin) {
            abort(403, 'Only administrators can delete completed and cancelled tasks.');
        }

        $request->validate([
            'days_old' => ['nullable', 'integer', 'min:1'],
            'include_completed' => ['boolean'],
            'include_cancelled' => ['boolean'],
            'task_ids' => ['nullable', 'array'],
        ]);

        $totalDeleted = 0;

        // If specific task IDs provided
        if ($request->has('task_ids') && !empty($request->task_ids)) {
            $tasks = CrmTasks::whereIn('id', $request->task_ids)
                ->whereIn('status', ['completed', 'cancelled'])
                ->get();

            $totalDeleted = $tasks->count();
            foreach ($tasks as $task) {
                $task->delete();
            }
        }
        // Otherwise delete based on age and status flags
        else {
            $daysOld = $request->input('days_old', 30);
            $cutoffDate = Carbon::now()->subDays($daysOld);

            $includeCompleted = $request->input('include_completed', true);
            $includeCancelled = $request->input('include_cancelled', true);

            $statuses = [];
            if ($includeCompleted) $statuses[] = 'completed';
            if ($includeCancelled) $statuses[] = 'cancelled';

            if (!empty($statuses)) {
                $totalDeleted = CrmTasks::whereIn('status', $statuses)
                    ->where(function ($query) use ($cutoffDate) {
                        $query->where('completed_at', '<=', $cutoffDate)
                            ->orWhere('cancelled_at', '<=', $cutoffDate);
                    })
                    ->delete();
            }
        }

        $this->logActivity(0, 'bulk_tasks_deleted', "Admin deleted {$totalDeleted} tasks (completed/cancelled)");

        return redirect()->back()->with('success', "{$totalDeleted} task(s) deleted successfully.");
    }

    // =========================================================================
    // RESCHEDULE
    // =========================================================================

    public function reschedule(Request $request, CrmTasks $task)
    {
        $this->denyAgents();
        $this->authorizeTask($task);

        $validated = $request->validate([
            'due_date' => ['nullable', 'date'],
            'time_slot' => ['nullable', 'in:morning,afternoon,evening'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'reschedule_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $oldDate = $task->scheduled_for?->format('Y-m-d');
        $newDate = $validated['due_date'] ?? null;
        $oldAssignee = $task->assigned_to;
        $newAssignee = $validated['assigned_to'] ?? $task->assigned_to;

        // Calculate new scheduled datetime if date and time slot are provided
        $scheduledDateTime = $task->scheduled_for;
        if ($newDate && $validated['time_slot']) {
            $scheduledDateTime = $this->calculateTaskTime(
                $newDate,
                $validated['time_slot'],
                $task->student_id,
                $newAssignee,
                $task->id // Exclude current task from count
            );
        } elseif ($newDate && !$validated['time_slot']) {
            // Only date changed, keep just the date
            $scheduledDateTime = $newDate;
        }

        // Track changes for logging
        $changes = [];
        if ($oldDate != $newDate) {
            $changes[] = "date from {$oldDate} to {$newDate}";
        }
        if ($oldAssignee != $newAssignee) {
            $newUser = User::find($newAssignee);
            $changes[] = "assignee to {$newUser?->name}";
        }
        if ($validated['time_slot'] && $validated['time_slot'] != $task->priority_time_slot) {
            $changes[] = "time slot to {$validated['time_slot']}";
        }

        $task->update([
            'scheduled_for' => $scheduledDateTime ?? $task->scheduled_for,
            'priority_time_slot' => $validated['time_slot'] ?? $task->priority_time_slot,
            'assigned_to' => $newAssignee,
        ]);

        // Handle meta_data properly - it might be array or string
        $metaData = $task->meta_data;

        // If it's a string, decode it; if it's already an array, use it directly
        if (is_string($metaData)) {
            $metaData = json_decode($metaData, true) ?? [];
        } elseif (!is_array($metaData)) {
            $metaData = [];
        }

        // Initialize reschedule_history if not exists
        if (!isset($metaData['reschedule_history'])) {
            $metaData['reschedule_history'] = [];
        }

        // Add to history
        $metaData['reschedule_history'][] = [
            'date' => now()->toDateTimeString(),
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'changes' => implode(', ', $changes),
            'reason' => $validated['reschedule_reason'] ?? null,
        ];

        // Store back as JSON
        $task->meta_data = json_encode($metaData);
        $task->save();

        $this->logActivity(
            $task->student_id,
            'task_rescheduled',
            "Task rescheduled: {$task->subject}. Changes: " . implode(', ', $changes)
        );

        $message = 'Task rescheduled successfully.';
        if ($validated['reschedule_reason']) {
            $message .= ' Reason noted.';
        }

        return redirect()->back()->with('success', $message);
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

        $this->logActivity($task->student_id, 'task_reopened', "Task reopened: {$task->subject}");

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

        $this->logActivity($task->student_id, 'task_restored', "Task restored from cancelled: {$task->subject}");

        return response()->json(['success' => true, 'message' => 'Task restored successfully.']);
    }

    // =========================================================================
    // GET EDIT DATA (AJAX)
    // =========================================================================

    public function getEditData(CrmTasks $task)
    {
        $this->denyAgents();
        $this->authorizeTask($task);

        $metaData = $task->meta_data;

        return response()->json([
            'id' => $task->id,
            'subject' => $task->subject,
            'description' => $task->description,
            'activity_type' => $task->activity_type,
            'scheduled_for' => $task->scheduled_for ? $task->scheduled_for->format('Y-m-d') : null,
            'scheduled_time' => $task->scheduled_for ? $task->scheduled_for->format('H:i') : null,
            'priority_time_slot' => $task->priority_time_slot,
            'assigned_to' => $task->assigned_to,
            'assignee_name' => $task->assignee?->name,
            'priority' => $metaData['priority'] ?? 'medium',
            'status' => $task->status,
        ]);
    }

    // =========================================================================
    // DELETE SINGLE TASK
    // =========================================================================

    public function destroy(CrmTasks $task)
    {
        try {
            $this->denyAgents();
            $this->authorizeTask($task);

            // Log what's being deleted for debugging
            Log::info('Deleting task', [
                'task_id' => $task->id,
                'task_subject' => $task->subject,
                'task_status' => $task->status,
                'task_type' => $task->activity_type,
                'student_id' => $task->student_id,
                'user_id' => Auth::id()
            ]);

            // Only allow deletion of pending tasks or by admin
            if ($task->status !== 'pending' && !Auth::user()->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only administrators can delete completed or cancelled tasks.'
                ], 403);
            }

            $studentId = $task->student_id;
            $taskSubject = $task->subject;

            // Delete the task
            $task->delete();

            // Log the deletion (this won't delete the student)
            $this->logActivity($studentId, 'task_deleted', "Task deleted: {$taskSubject}");

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully.',
                'deleted_task_id' => $task->id
            ]);
        } catch (\Exception $e) {
            Log::error('Task deletion failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

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
        $this->denyAgents();

        $request->validate([
            'task_ids' => ['required', 'array'],
            'task_ids.*' => ['exists:crm_tasks,id'],
            'completion_note' => ['nullable', 'string'],
        ]);

        $tasks = CrmTasks::whereIn('id', $request->input('task_ids'))
            ->where('status', 'pending')
            ->get();

        $count = 0;
        foreach ($tasks as $task) {
            $this->authorizeTask($task);
            $task->update([
                'status' => 'completed',
                'completed_at' => now(),
                'completed_by' => Auth::id(),
                'completion_note' => $request->input('completion_note') ?? 'Bulk completed',
            ]);
            $count++;
        }

        return redirect()->back()->with('success', "{$count} tasks completed successfully.");
    }

    /**
     * Check for due tasks and send notifications automatically
     */
    public function checkDueTasks(Request $request)
    {
        try {
            $user = Auth::user();
            $today = Carbon::today();
            $notificationsSent = 0;

            // Get user's pending tasks that are due, overdue, or upcoming
            $tasks = CrmTasks::where('assigned_to', $user->id)
                ->where('status', 'pending')
                ->where(function ($query) use ($today) {
                    $query->whereDate('scheduled_for', $today)      // Due today
                        ->orWhereDate('scheduled_for', $today->copy()->addDay()) // Due tomorrow
                        ->orWhereDate('scheduled_for', '<', $today); // Overdue
                })
                ->get();

            foreach ($tasks as $task) {
                // Get the last notification for this task
                $lastNotification = $user->notifications()
                    ->where('type', 'App\\Notifications\\CrmTaskNotification')
                    ->where('data->task_id', $task->id)
                    ->latest()
                    ->first();

                $dueDate = Carbon::parse($task->scheduled_for);
                $notificationType = null;

                if ($dueDate->lt($today)) {
                    $notificationType = 'overdue';
                } elseif ($dueDate->isToday()) {
                    $notificationType = 'due_today';
                } elseif ($dueDate->isTomorrow()) {
                    $notificationType = 'upcoming';
                }

                if (!$notificationType) continue;

                // Check if we should send a new notification
                $shouldSend = false;

                if (!$lastNotification) {
                    // No previous notification, send it
                    $shouldSend = true;
                } else {
                    $lastSentAt = Carbon::parse($lastNotification->created_at);
                    $hoursSinceLastSent = $lastSentAt->diffInHours(now());

                    // For overdue tasks: send every 24 hours
                    if ($notificationType === 'overdue' && $hoursSinceLastSent >= 24) {
                        $shouldSend = true;
                    }
                    // For due today and upcoming: only send once
                    elseif (in_array($notificationType, ['due_today', 'upcoming']) && $hoursSinceLastSent >= 24) {
                        $shouldSend = true;
                    }
                }

                if ($shouldSend) {
                    $user->notify(new CrmTaskNotification($task, $notificationType));
                    $notificationsSent++;

                    Log::info("Notification sent for task {$task->id}: {$notificationType}");
                }
            }

            return response()->json(['success' => true, 'sent' => $notificationsSent]);
        } catch (\Exception $e) {
            Log::error('Check due tasks error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function bulkCancel(Request $request)
    {
        $this->denyAgents();

        $request->validate([
            'task_ids' => ['required', 'array'],
            'task_ids.*' => ['exists:crm_tasks,id'],
            'cancellation_reason' => ['required', 'string', 'min:5'],
        ]);

        $tasks = CrmTasks::whereIn('id', $request->input('task_ids'))
            ->where('status', 'pending')
            ->get();

        $count = 0;
        foreach ($tasks as $task) {
            $this->authorizeTask($task);
            $task->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => Auth::id(),
                'cancellation_note' => $request->input('cancellation_reason'),
            ]);
            $count++;
        }

        return redirect()->back()->with('success', "{$count} tasks cancelled successfully.");
    }

    // =========================================================================
    // TASK TIME CALCULATION HELPER (5-MINUTE INTERVALS)
    // =========================================================================

    /**
     * Calculate the specific datetime for a task based on date, time slot, and existing tasks
     * Uses 5-minute intervals for auto-scheduling
     *
     * @param string $date The due date (Y-m-d format or Y-m-d\TH:i format)
     * @param string $timeSlot The time slot (morning, afternoon, evening)
     * @param int|null $studentId The student ID (for grouping by student)
     * @param int|null $assigneeId The assignee ID (for grouping by assignee)
     * @param int|null $excludeTaskId Task ID to exclude from count (for updates)
     * @return Carbon|null The calculated datetime or null if invalid
     */
    private function calculateTaskTime($date, $timeSlot, $studentId = null, $assigneeId = null, $excludeTaskId = null)
    {
        if (!$date || !$timeSlot) {
            Log::warning('Missing date or timeSlot', ['date' => $date, 'timeSlot' => $timeSlot]);
            return null;
        }

        // Extract just the date part if datetime is provided
        $dateOnly = $date;
        if (strpos($date, 'T') !== false) {
            $dateOnly = explode('T', $date)[0];
        }

        // Define time ranges for each slot
        $slotTimes = [
            'morning'   => ['start' => '09:00:00', 'end' => '12:00:00', 'interval' => 5],
            'afternoon' => ['start' => '12:00:00', 'end' => '15:00:00', 'interval' => 5],
            'evening'   => ['start' => '15:00:00', 'end' => '18:00:00', 'interval' => 5],
        ];

        $slot = $slotTimes[$timeSlot];

        // Create base Carbon instance for the date at the slot start time
        $baseDateTime = Carbon::parse($dateOnly . ' ' . $slot['start']);

        // Determine grouping strategy (by assignee if available, otherwise by student)
        $query = CrmTasks::whereDate('scheduled_for', $dateOnly)
            ->where('priority_time_slot', $timeSlot)
            ->whereIn('status', ['pending', 'in_progress']);

        // Group by assignee if provided, otherwise by student
        if ($assigneeId) {
            $query->where('assigned_to', $assigneeId);
        } elseif ($studentId) {
            $query->where('student_id', $studentId);
        }

        // Exclude current task if updating
        if ($excludeTaskId) {
            $query->where('id', '!=', $excludeTaskId);
        }

        // Get all existing scheduled times to find gaps
        $existingTasks = $query->orderBy('scheduled_for')->get();

        // Get the total minutes from base for each existing task
        $occupiedMinutes = [];
        foreach ($existingTasks as $existingTask) {
            if ($existingTask->scheduled_for) {
                $taskDateTime = Carbon::parse($existingTask->scheduled_for);
                $minutesFromBase = $baseDateTime->diffInMinutes($taskDateTime);
                // Round to nearest 5-minute interval
                $slotNumber = round($minutesFromBase / $slot['interval']);
                $occupiedMinutes[] = $slotNumber;
            }
        }

        // Find the first available slot (starting from 0)
        $availableSlot = 0;
        while (in_array($availableSlot, $occupiedMinutes)) {
            $availableSlot++;
        }

        // Calculate the time for the available slot
        $minutesToAdd = $availableSlot * $slot['interval'];
        $scheduledDateTime = clone $baseDateTime;
        $scheduledDateTime->addMinutes($minutesToAdd);

        // Ensure we don't exceed the slot's end time
        $endTime = Carbon::parse($dateOnly . ' ' . $slot['end']);

        if ($scheduledDateTime->gt($endTime)) {
            // If slot is full, return the end time
            Log::warning("Time slot {$timeSlot} is full for date {$dateOnly}", [
                'available_slot' => $availableSlot,
                'occupied_slots' => $occupiedMinutes,
                'existing_tasks_count' => count($existingTasks),
                'assignee_id' => $assigneeId,
                'student_id' => $studentId
            ]);
            return $endTime;
        }

        Log::info("Task time calculated (5-min intervals with gap finding)", [
            'date' => $dateOnly,
            'time_slot' => $timeSlot,
            'base_time' => $baseDateTime->format('Y-m-d H:i:s'),
            'existing_tasks_count' => count($existingTasks),
            'occupied_slots' => $occupiedMinutes,
            'available_slot' => $availableSlot,
            'calculated_time' => $scheduledDateTime->format('Y-m-d H:i:s')
        ]);

        return $scheduledDateTime;
    }

    /**
     * Get available time slots for a specific date and assignee/student
     *
     * @param string $date The date to check (Y-m-d format)
     * @param int|null $assigneeId The assignee ID
     * @param int|null $studentId The student ID
     * @return array Array of available time slots with their capacities
     */
    private function getAvailableTimeSlots($date, $assigneeId = null, $studentId = null)
    {
        $slots = ['morning', 'afternoon', 'evening'];
        $available = [];
        $slotLimits = [
            'morning' => 36,   // 3 hours * 12 (5 min intervals)
            'afternoon' => 36, // 3 hours * 12 (5 min intervals)
            'evening' => 36,   // 3 hours * 12 (5 min intervals)
        ];

        foreach ($slots as $slot) {
            $query = CrmTasks::whereDate('scheduled_for', $date)
                ->where('priority_time_slot', $slot)
                ->whereIn('status', ['pending', 'in_progress']);

            if ($assigneeId) {
                $query->where('assigned_to', $assigneeId);
            } elseif ($studentId) {
                $query->where('student_id', $studentId);
            }

            $taskCount = $query->count();
            $remainingCapacity = $slotLimits[$slot] - $taskCount;

            if ($remainingCapacity > 0) {
                $available[] = [
                    'slot' => $slot,
                    'available_slots' => $remainingCapacity,
                    'next_available_time' => $this->getNextAvailableTime($date, $slot, $taskCount)
                ];
            }
        }

        return $available;
    }

    /**
     * Get the next available time within a slot
     *
     * @param string $date
     * @param string $timeSlot
     * @param int $taskCount
     * @return string
     */
    private function getNextAvailableTime($date, $timeSlot, $taskCount)
    {
        $slotTimes = [
            'morning' => '09:00:00',
            'afternoon' => '12:00:00',
            'evening' => '15:00:00',
        ];

        $minutesToAdd = $taskCount * 5; // 5-minute intervals
        $timestamp = strtotime($date . ' ' . $slotTimes[$timeSlot]) + ($minutesToAdd * 60);

        return date('H:i', $timestamp);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Log activity for a student
     */
    private function logActivity(int $studentId, string $type, string $description): void
    {
        try {
            Log::info("CRM Activity: {$type}", [
                'student_id' => $studentId,
                'user_id' => Auth::id(),
                'description' => $description
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to log CRM activity: ' . $e->getMessage());
        }
    }

    private function denyAgents(): void
    {
        if (Auth::user() && Auth::user()->is_agent) {
            abort(403, 'Agents have read-only CRM access.');
        }
    }

    private function authorizeTask(CrmTasks $task): void
    {
        $this->authorizeStudentAccess($task->student_id);
    }

    private function isAssignableRole(User $user): bool
    {
        // Check if user has any assignable role
        return $user->is_admin === true
            || $user->is_staff === true
            || $user->is_admin_staff === true
            || (isset($user->role) && in_array($user->role, ['admin', 'staff', 'admin_staff']));
    }

    private function authorizeStudentAccess(int $studentId): void
    {
        $user = Auth::user();

        // Admin and Admin Staff have full access
        if ($user->is_admin) {
            return;
        }

        // CRITICAL FIX: Staff with admin_staff flag should NOT have admin-level access
        // They should follow the same rules as regular staff
        if ($user->is_admin_staff) {
            $student = Student::findOrFail($studentId);
            if ($student->agent_id !== $user->id) {
                abort(403, 'You do not have access to this student.');
            }
            return;
        }

        $student = Student::findOrFail($studentId);

        // Agents can only access their own students and staff under them
        if ($user->is_agent) {
            $staffIds = User::where('parent_id', $user->id)
                ->where(function ($q) {
                    $q->where('role', 'staff')->orWhere('is_staff', true);
                })
                ->pluck('id')
                ->toArray();
            $allowedAgentIds = array_merge([$user->id], $staffIds);

            if (!in_array($student->agent_id, $allowedAgentIds)) {
                abort(403, 'You do not have access to this student.');
            }
            return;
        }

        // Agent staff can access their own students
        if ($user->is_agent_staff) {
            if (!in_array($student->agent_id, [$user->id, $user->parent_id])) {
                abort(403, 'You do not have access to this student.');
            }
            return;
        }

        // Staff can access students assigned to them
        if ($user->is_staff) {
            if ($student->agent_id !== $user->id) {
                abort(403, 'You do not have access to this student.');
            }
            return;
        }

        abort(403, 'Unauthorized access.');
    }
    /**
     * Check for duplicate tasks (AJAX endpoint)
     */
    /**
     * Check for duplicate tasks (AJAX endpoint for frontend)
     */
    /**
     * Check for duplicate tasks (AJAX endpoint for frontend)
     */
    public function checkDuplicate(Request $request, $studentId)
    {
        try {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'has_duplicate' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Duplicate check failed: ' . $e->getMessage());
            return response()->json([
                'has_duplicate' => false,
                'error' => 'Server error occurred'
            ], 500);
        }
    }
}
