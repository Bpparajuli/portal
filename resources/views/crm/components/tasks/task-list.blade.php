{{-- resources/views/crm/components/tasks/task-list.blade.php --}}
<div class="crm-section-body">
    <div class="tasks-container">
        @if ($canEdit)
            <div class="d-flex justify-content-end">
                <button class="btn btn-sm btn-outline-primary w-auto" onclick="openNewTaskModal()">
                    + Create New Task
                </button>
            </div>
        @endif

        @if ($dueTasks->count() > 0)
            <div class="task-box">
                <div class="task-box-header" style="color: #ef4444;">⚠️ Overdue Tasks
                    ({{ $dueTasks->count() }})</div>
                @foreach ($dueTasks as $task)
                    @include('crm.components.tasks.task-item', [
                        'task' => $task,
                        'type' => 'overdue',
                        'canEdit' => $canEdit,
                    ])
                @endforeach
            </div>
        @endif

        @if ($todayTasks->count() > 0)
            <div class="task-box">
                <div class="task-box-header" style="color: #10b981;">📅 Today's Tasks
                    ({{ $todayTasks->count() }})</div>
                @foreach ($todayTasks as $task)
                    @include('crm.components.tasks.task-item', [
                        'task' => $task,
                        'type' => 'today',
                        'canEdit' => $canEdit,
                    ])
                @endforeach
            </div>
        @endif

        @if ($plannedTasks->count() > 0)
            <div class="task-box">
                <div class="task-box-header" style="color: #3b82f6;">🗓 Upcoming Tasks
                    ({{ $plannedTasks->count() }})</div>
                @foreach ($plannedTasks as $task)
                    @include('crm.components.tasks.task-item', [
                        'task' => $task,
                        'type' => 'upcoming',
                        'canEdit' => $canEdit,
                    ])
                @endforeach
            </div>
        @endif

        @if ($dueTasks->count() == 0 && $todayTasks->count() == 0 && $plannedTasks->count() == 0)
            <div class="task-box">
                <div class="text-muted text-center py-4">No pending tasks 🎉</div>
            </div>
        @endif
    </div>

    <div class="mt-4">
        <div class="task-box-header">✅ Completed & Cancelled Tasks ({{ $completedTasks->total() }})
        </div>
        <div id="completedTasksContainer">
            @forelse($completedTasks as $task)
                <div class="activity-item d-flex gap-3">
                    <div class="col-md-10 flex-grow-1">
                        <div class="fw-bold" style="font-size:.85rem">
                            {{ $task->subject }}
                            @if ($task->status === 'cancelled')
                                <span class="badge bg-danger">CANCELLED</span>
                            @else
                                <span class="badge bg-success">COMPLETED</span>
                            @endif
                        </div>
                        @if ($task->completion_note)
                            <div class="act-desc"><strong>Completion notes:</strong>
                                {{ $task->completion_note }}</div>
                        @endif
                        @if ($task->cancellation_note)
                            <div class="act-desc"><strong>Cancellation reason:</strong>
                                {{ $task->cancellation_note }}</div>
                        @endif

                        @if ($task->activity_type === 'stage_change')
                            <div>
                                <span>{{ $task->description ?? 'Unknown' }}</span><br>
                                <small>On: {{ $task->completed_at?->format('d M Y, g:i A') }} by:
                                    {{ $task->creator?->name ?? 'Unknown' }}</small>
                            </div>
                        @elseif ($task->activity_type !== 'stage_change')
                            <div class="text-muted mt-1" style="font-size:.72rem">
                                @if ($task->status === 'completed')
                                    Completed on: {{ $task->completed_at?->format('d M Y, g:i A') }} by
                                    {{ $task->completedBy?->name ?? 'Unknown' }}
                                @elseif($task->status === 'cancelled')
                                    Cancelled on: {{ $task->cancelled_at?->format('d M Y, g:i A') }} by
                                    {{ $task->cancelledBy?->name ?? 'Unknown' }}
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="d-flex flex-column gap-2">
                        @if ($canEdit)
                            @if ($task->status === 'completed')
                                <button onclick="undoComplete({{ $task->id }})"
                                    class="btn btn-sm btn-outline-secondary">↩️ Undo</button>
                            @elseif($task->status === 'cancelled')
                                <button onclick="undoCancel({{ $task->id }})"
                                    class="btn btn-sm btn-outline-secondary">↩️ Restore</button>
                            @endif
                        @endif
                        <button onclick="deleteTask({{ $task->id }}, '{{ $task->activity_type }}')"
                            class="btn btn-sm btn-outline-danger">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-muted text-center py-3">No completed or cancelled tasks yet.</div>
            @endforelse

            @if ($completedTasks->hasPages())
                <div class="mt-3">{{ $completedTasks->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Activity Log --}}
    @include('crm.components.show.activity-log')
</div>
