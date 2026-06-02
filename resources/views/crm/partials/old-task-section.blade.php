{{-- resources/views/crm/components/tasks/task-section.blade.php --}}
@props([
    'dueTasks',
    'todayTasks',
    'plannedTasks',
    'completedTasks',
    'canEdit' => false,
    'assignableUsers' => [],
    'student',
])

<div class="crm-section">
    <div class="crm-tabs">
        <button class="crm-tab active" onclick="switchTab('tasks', this)">Tasks</button>
        <button class="crm-tab" onclick="switchTab('documents', this)">Documents</button>
        <button class="crm-tab" onclick="switchTab('history', this)">History</button>
    </div>

    {{-- TASKS TAB --}}
    <div id="tab-tasks">
        <div class="crm-section-body">
            {{-- Pending Tasks --}}
            @include('crm.components.tasks.task-list', [
                'dueTasks' => $dueTasks,
                'todayTasks' => $todayTasks,
                'plannedTasks' => $plannedTasks,
                'canEdit' => $canEdit,
                'assignableUsers' => $assignableUsers,
                'student' => $student,
            ])

            {{-- Completed & Cancelled Tasks --}}
            <div class="mt-4">
                <div class="task-box-header">✅ Completed & Cancelled Tasks ({{ $completedTasks->total() }})</div>
                <div id="completedTasksContainer">
                    @forelse($completedTasks as $task)
                        <div
                            class="completed-task-item d-flex justify-content-between align-items-start p-3 border-bottom">
                            <div class="flex-grow-1">
                                <div class="fw-bold">
                                    {{ $task->subject }}
                                    @if ($task->status === 'cancelled')
                                        <span class="badge bg-danger">CANCELLED</span>
                                    @else
                                        <span class="badge bg-success">COMPLETED</span>
                                    @endif
                                </div>
                                @if ($task->completion_note)
                                    <div class="text-muted small mt-1"><strong>Notes:</strong>
                                        {{ $task->completion_note }}</div>
                                @endif
                                @if ($task->cancellation_note)
                                    <div class="text-muted small mt-1"><strong>Reason:</strong>
                                        {{ $task->cancellation_note }}</div>
                                @endif
                                <div class="text-muted small mt-1">
                                    @if ($task->status === 'completed')
                                        Completed: {{ $task->completed_at?->format('d M Y, g:i A') }}
                                    @else
                                        Cancelled: {{ $task->cancelled_at?->format('d M Y, g:i A') }}
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                @if ($canEdit && $task->status === 'completed')
                                    <button onclick="undoComplete({{ $task->id }})"
                                        class="btn btn-sm btn-outline-secondary">↩️ Undo</button>
                                @endif
                                @if ($canEdit && $task->status === 'cancelled')
                                    <button onclick="undoCancel({{ $task->id }})"
                                        class="btn btn-sm btn-outline-secondary">↩️ Restore</button>
                                @endif
                                @if (auth()->user()->is_admin && in_array($task->status, ['completed', 'cancelled']))
                                    <form action="{{ route('crm.tasks.destroy', $task) }}" method="POST"
                                        onsubmit="return confirm('Delete this task permanently?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">🗑️ Delete</button>
                                    </form>
                                @endif
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
        </div>
    </div>

    {{-- DOCUMENTS TAB --}}
    <div id="tab-documents" style="display:none">
        <div class="crm-section-body">
            @forelse($student->documents as $doc)
                <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                    <span class="fs-5">📄</span>
                    <div class="flex-grow-1">
                        <div class="fw-medium small">{{ $doc->document_type }}</div>
                        <div class="text-muted small">Uploaded {{ $doc->created_at->format('d M Y') }}</div>
                    </div>
                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                        class="btn btn-sm btn-outline-primary">View</a>
                </div>
            @empty
                <div class="text-muted text-center py-4">No documents uploaded.</div>
            @endforelse
        </div>
    </div>

    {{-- HISTORY TAB --}}
    <div id="tab-history" style="display:none">
        <div class="crm-section-body">
            <div id="stageHistoryContent">
                <div class="text-center text-muted py-3 small">Loading history…</div>
            </div>
        </div>
    </div>
</div>

@push('crm-styles')
    <style>
        .crm-tabs {
            display: flex;
            border-bottom: 2px solid #e5e9f2;
            padding: 0 1.1rem;
            background: #fafbff;
        }

        .crm-tab {
            padding: .65rem 1.1rem;
            font-size: .85rem;
            font-weight: 500;
            border: none;
            background: none;
            cursor: pointer;
            color: #6b7280;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all .15s;
        }

        .crm-tab.active {
            color: #3b82f6;
            border-bottom-color: #3b82f6;
        }

        .completed-task-item {
            padding: .85rem 1rem;
            border-bottom: 1px solid #e5e9f2;
        }

        .completed-task-item:last-child {
            border-bottom: none;
        }
    </style>
@endpush
