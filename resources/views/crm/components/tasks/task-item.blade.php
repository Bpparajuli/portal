{{-- resources/views/crm/components/tasks/task-item.blade.php --}}
<div class="task-item {{ $type }}" data-task-id="{{ $task->id }}">
    <div class="flex-grow-1">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="task-title">
                    @php
                        // Get priority from meta_data - this is the fix
                        $metaData = is_string($task->meta_data)
                            ? json_decode($task->meta_data, true)
                            : $task->meta_data;
                        $priority = $metaData['priority'] ?? 'medium';
                    @endphp
                    <span class="priority-{{ $priority }}">
                        @if ($priority == 'high')
                            🔴
                        @elseif($priority == 'medium')
                            🟡
                        @else
                            🟢
                        @endif
                    </span> {{ $task->subject }}
                </div>
                <div class="small">
                    <span>📋 {{ ucfirst(str_replace('_', ' ', $task->activity_type)) }}</span>
                </div>
            </div>

            @if ($task->scheduled_for)
                <div class="task-meta">
                    📅 {{ \Carbon\Carbon::parse($task->scheduled_for)->format('d M Y') }}
                    <br>
                    @if (\Carbon\Carbon::parse($task->scheduled_for)->format('H:i') != '00:00')
                        🕐 {{ \Carbon\Carbon::parse($task->scheduled_for)->format('g:i A') }}
                    @endif
                    @if ($task->priority_time_slot)
                        <span class="time-slot-badge">{{ ucfirst($task->priority_time_slot) }}</span>
                    @endif
                </div>
            @endif
        </div>

        @if ($task->description)
            <div class="task-description">{{ $task->description }}</div>
        @endif

        <div class="task-meta mt-2">
            @if ($task->assignee)
                <div class="task-meta mt-1">
                    @if ($task->creator && $task->creator->business_logo && Storage::disk('public')->exists($task->creator->business_logo))
                        <img src="{{ Storage::url($task->creator->business_logo) }}" class="staff-avatar-sm"
                            alt="{{ $task->creator->name }}">
                    @endif

                    @if ($task->creator && $task->creator->id === $task->assignee->id)
                        {{ \Illuminate\Support\Str::words($task->creator->name, 2, '') }}
                        <span class="badge bg-info text-dark ms-1">Self Assigned Task</span>
                    @else
                        {{ \Illuminate\Support\Str::words($task->creator?->name ?? '', 2, '') }} Assigned this task to:
                        @if ($task->assignee->business_logo && Storage::disk('public')->exists($task->assignee->business_logo))
                            <img src="{{ Storage::url($task->assignee->business_logo) }}" class="staff-avatar-sm"
                                alt="{{ $task->assignee->name }}">
                        @endif
                        {{ \Illuminate\Support\Str::words($task->assignee->name, 2, '') }}
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if ($canEdit && $task->status == 'pending')
        <div class="task-actions d-flex flex-column">
            @if (auth()->user()->is_admin)
                <button data-id="{{ $task->id }}" onclick="openEditModal(this.dataset.id)" class="btn btn-sm btn-outline-primary"
                    title="Edit">✏️ Edit</button>
            @endif
            <button data-id="{{ $task->id }}" data-title="{{ $task->subject }}"
                onclick="openRescheduleModal(this.dataset.id, this.dataset.title)"
                class="btn btn-sm btn-outline-warning" title="Reschedule">📅 Reschedule</button>
            <button data-id="{{ $task->id }}" data-title="{{ $task->subject }}"
                onclick="openCancelModal(this.dataset.id, this.dataset.title)"
                class="btn btn-sm btn-outline-danger" title="Cancel">✗ Cancel</button>
            <button data-id="{{ $task->id }}" data-title="{{ $task->subject }}"
                onclick="openCompleteModal(this.dataset.id, this.dataset.title)"
                class="btn btn-sm btn-outline-success" title="Complete">✓ Complete</button>
        </div>
    @endif
</div>
