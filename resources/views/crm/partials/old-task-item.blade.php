@props(['task', 'type' => 'upcoming', 'canEdit' => false])
<style>
    .task-item {
        display: flex;
        align-items: flex-start;
        gap: .6rem;
        padding: .6rem .75rem;
        border: 1px solid var(--glass-gradient);
        border-radius: 8px;
        margin-bottom: .5rem;
        font-size: .83rem;
        transition: all 0.2s;
    }

    .task-item:hover {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .task-item.overdue {
        border-left: 4px solid #ef4444;
        background: #fef2f2;
    }

    .task-item.today {
        border-left: 4px solid #10b981;
        background: #ecfdf5;
    }

    .task-item.upcoming {
        border-left: 4px solid #3b82f6;
        background: #f0f2ff;
    }

    .task-title {
        font-weight: 600;
        color: var(--text);
        font-size: .9rem;
    }

    .task-meta {
        font-size: .72rem;
        color: var(--muted);
        margin-top: .15rem;
    }

    .task-description {
        font-size: .78rem;
        color: var(--text);
        margin-top: .35rem;
        padding: .4rem .6rem;
        background: #f8fafc;
        border-radius: 6px;
    }

    .task-actions {
        margin-left: auto;
        display: flex;
        gap: .3rem;
        flex-shrink: 0;
    }

    .priority-high {
        color: #ef4444;
        font-weight: 600;
    }

    .priority-medium {
        color: #f59e0b;
        font-weight: 600;
    }

    .priority-low {
        color: #10b981;
        font-weight: 600;
    }

    .time-slot-badge {
        display: inline-block;
        font-size: 0.65rem;
        background: #e5e9f2;
        color: #475569;
        border-radius: 12px;
        padding: 0.1rem 0.5rem;
        margin-left: 0.5rem;
    }

    .btn-outline-success {
        border: 1px solid green;
    }
</style>

<div class="task-item {{ $type }}" data-task-id="{{ $task->id }}">
    <div class="flex-grow-1">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="task-title">
                    @php $priority = $task->meta_data['priority'] ?? 'medium'; @endphp
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
                    <span>📋
                        {{ ucfirst(str_replace('_', ' ', $task->activity_type)) }}
                    </span>
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
            <div class="task-description">{{ \Illuminate\Support\Str::limit($task->description, 150) }}</div>
        @endif

        <div class="task-meta mt-2">
            @if ($task->assignee)
                <div class="task-meta mt-1">

                    {{-- Creator Avatar --}}
                    @if ($task->creator && $task->creator->business_logo)
                        <img src="{{ Storage::url($task->creator->business_logo) }}" class="staff-avatar-sm"
                            alt="{{ $task->creator->name }}">
                    @endif

                    {{-- Self Assigned --}}
                    @if ($task->creator && $task->creator->id === $task->assignee->id)

                        {{ $task->creator->name }}
                        <span class="badge bg-info text-dark ms-1">
                            Self Assigned Task
                        </span>
                    @else
                        {{-- Assigned To Others --}}
                        {{ $task->creator?->name }}
                        Assigned this task to:

                        @if ($task->assignee->business_logo)
                            <img src="{{ Storage::url($task->assignee->business_logo) }}" class="staff-avatar-sm"
                                alt="{{ $task->assignee->name }}">
                        @endif

                        {{ $task->assignee->name }}

                    @endif
                </div>
            @endif

        </div>
    </div>

    @if ($canEdit && $task->status == 'pending')
        <div class="task-actions d-flex flex-column">
            @if (auth()->user()->is_admin)
                <button onclick="openEditModal({{ $task->id }})" class="btn btn-sm btn-outline-primary"
                    title="Edit">✏️ Edit</button>
            @endif
            <button onclick="openRescheduleModal({{ $task->id }}, '{{ addslashes($task->subject) }}')"
                class="btn btn-sm btn-outline-warning" title="Reschedule">📅 Reschedule</button>

            <button onclick="openCancelModal({{ $task->id }}, '{{ addslashes($task->subject) }}')"
                class="btn btn-sm btn-outline-danger" title="Cancel">✗ Cancel</button>
            <button onclick="openCompleteModal({{ $task->id }}, '{{ addslashes($task->subject) }}')"
                class="btn btn-sm btn-outline-success" title="Complete">✓ Complete</button>
        </div>
    @endif
</div>
