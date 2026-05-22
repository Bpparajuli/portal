@php
    $priority = is_array($task->meta_data) ? $task->meta_data['priority'] ?? 'medium' : 'medium';
@endphp
<div class="task-item {{ $type }}">
    <div class="flex-grow-1">
        <div class="task-title">{{ $task->subject }}</div>
        <div class="task-meta">
            <span>{{ $task->activity_icon ?? '📌' }} {{ ucfirst($task->activity_type) }}</span>
            @if ($task->assignee)
                <span>
                    @if ($task->assignee->business_logo && Storage::disk('public')->exists($task->assignee->business_logo))
                        <img src="{{ Storage::url($task->assignee->business_logo) }}" class="staff-avatar">
                    @endif
                    {{ $task->assignee->name }}
                </span>
            @endif
            <span
                class="priority-{{ $priority }}">{{ $priority === 'high' ? '🔴' : ($priority === 'medium' ? '🟡' : '🟢') }}
                {{ ucfirst($priority) }}</span>
            @if ($task->scheduled_at)
                <span>📅 {{ $task->scheduled_at->format('d M Y') }}</span>
            @endif
        </div>
        @if ($task->description)
            <div class="task-description" style="font-size:0.75rem; margin-top:0.25rem;">{{ $task->description }}</div>
        @endif
    </div>
    <div class="task-actions">
        <button onclick="editTask({{ $task->id }})" class="btn btn-sm btn-outline-primary"
            title="Edit">✏️</button>
        <button onclick="rescheduleTask({{ $task->id }}, '{{ addslashes($task->subject) }}')"
            class="btn btn-sm btn-outline-warning" title="Reschedule">📅</button>
        <button onclick="cancelTask({{ $task->id }}, '{{ addslashes($task->subject) }}')"
            class="btn btn-sm btn-outline-danger" title="Cancel">✕</button>
        <button onclick="completeTask({{ $task->id }}, '{{ addslashes($task->subject) }}')"
            class="btn btn-sm btn-success" title="Complete">✓</button>
    </div>
</div>
