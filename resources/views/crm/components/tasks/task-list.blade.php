{{-- resources/views/crm/components/tasks/task-list.blade.php --}}
<div class="crm-section-body">
    <div class="tasks-container">
        @if ($canEdit)
            <div class="d-flex justify-content-end">
                <button class="btn btn-sm btn-solid-dark" onclick="openNewTaskModal()">
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
                <div class="task-box-header" style="color: #820b5c;">📅 Today's Tasks
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
        <div class="task-box-header"
            style="font-size:.75rem;font-weight:700;color:#374151;padding-bottom:8px;border-bottom:2px solid #ede5f8;margin-bottom:10px;">
            ✅ Completed & Cancelled ({{ $completedTasks->count() }})</div>
        <div id="completedTasksContainer">
            @php
                $groups = [];
                $today = \Carbon\Carbon::today();
                foreach ($completedTasks as $task) {
                    $date = $task->completed_at ?? ($task->cancelled_at ?? $task->updated_at);
                    if (!$date) {
                        $groups['Other'][] = $task;
                        continue;
                    }
                    $diff = $today->diffInDays($date, false);
                    if ($diff === 0) {
                        $key = 'Today';
                    } elseif ($diff === 1) {
                        $key = 'Yesterday';
                    } elseif ($diff <= 7) {
                        $key = 'This Week';
                    } else {
                        $key = 'Earlier';
                    }
                    $groups[$key][] = $task;
                }
                $order = ['Today', 'Yesterday', 'This Week', 'Earlier'];
            @endphp

            @forelse($order as $groupKey)
                @if (!isset($groups[$groupKey]) || empty($groups[$groupKey]))
                    @continue
                @endif
                <div style="margin-bottom:12px;">
                    <div
                        style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#94a3b8;margin-bottom:6px;padding-left:2px;">
                        {{ $groupKey }}</div>
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        @foreach ($groups[$groupKey] as $task)
                            @php
                                $isCompleted = $task->status === 'completed';
                                $isStageChange = $task->activity_type === 'stage_change';
                                $actionDate = $isCompleted
                                    ? $task->completed_at
                                    : $task->cancelled_at ?? $task->updated_at;
                            @endphp
                            <div style="display:flex;gap:10px;background:#fff;border:1px solid #e8e5ee;border-radius:8px;padding:10px 12px;transition:box-shadow .12s;"
                                onmouseover="this.style.boxShadow='0 2px 8px rgba(26,2,98,.06)'"
                                onmouseout="this.style.boxShadow='none'">
                                {{-- Timeline indicator --}}
                                <div
                                    style="display:flex;flex-direction:column;align-items:center;width:14px;flex-shrink:0;padding-top:3px;">
                                    <div
                                        style="width:10px;height:10px;border-radius:50%;background:{{ $isCompleted ? '#820b5c' : '#ef4444' }};border:2px solid {{ $isCompleted ? '#ede5f8' : '#fef2f2' }};">
                                    </div>
                                    <div style="flex:1;width:1px;background:#e8e5ee;min-height:100%;"></div>
                                </div>
                                {{-- Content --}}
                                <div style="flex:1;min-width:0;">
                                    <div
                                        style="font-size:.7rem;color:#545455;font-weight:400;line-height:1.3;margin-bottom:2px;">
                                        {{ $task->subject }}
                                    </div>
                                    {{-- Completion note (hero) --}}
                                    @if ($isCompleted && $task->completion_note)
                                        <div
                                            style="font-size:.7rem;font-weight:700;color:#545455;line-height:1.3;margin-bottom:2px;">
                                            {{ $task->completion_note }}</div>
                                    @elseif (!$isCompleted && $task->cancellation_note)
                                        <div
                                            style="font-size:.7rem;font-weight:700;color:#991b1b;line-height:1.3;margin-bottom:2px;">
                                            {{ $task->cancellation_note }}</div>
                                    @elseif ($isStageChange)
                                        <div style="font-size:.7rem;color:#c0fab3;margin-bottom:2px;">
                                            {{ $task->description ?? 'Stage changed' }}</div>
                                    @endif
                                    {{-- Task subject (full, not truncated) --}}

                                    <div
                                        style="font-size:.7rem;font-weight:800;color:#1a0262;display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                        <span> {{ ucfirst(str_replace('_', ' ', $task->activity_type)) }}</span>
                                        <span
                                            style="display:inline-block;background:{{ $isCompleted ? '#c9f9b9' : '#fee2e2' }};color:{{ $isCompleted ? 'green' : 'red' }};font-size:.55rem;font-weight:700;padding:0 6px;border-radius:3px;">{{ $isCompleted ? 'DONE' : 'CANCELLED' }}
                                        </span>
                                    </div>
                                    {{-- Meta: who assigned whom and who did it --}}
                                    <div style="font-size:.65rem;color:#64748b;margin-top:4px;line-height:1.4;">
                                        @if ($isStageChange)
                                            By {{ $task->creator?->name ?? 'Unknown' }} •
                                            {{ $actionDate?->format('d M Y, g:i A') }}
                                        @else
                                            @php
                                                $creator = $task->creator;
                                                $assignee = $task->assignee;
                                                $doer = $isCompleted ? $task->completedBy : $task->cancelledBy ?? null;
                                                $creatorName = $creator?->name ?? 'Unknown';
                                                $assigneeName = $assignee?->name ?? 'Unknown';
                                                $doerName = $doer?->name ?? '';
                                                $isSelfAssign = $creator && $assignee && $creator->id === $assignee->id;
                                                $isSelfDo = $doer && $assignee && $doer->id === $assignee->id;
                                            @endphp
                                            @if ($isSelfAssign)
                                                <span>{{ $creatorName }} (self-assigned)</span>
                                            @elseif ($creator && $assignee)
                                                <span>{{ $creatorName }} → {{ $assigneeName }}</span>
                                            @elseif ($creator)
                                                <span>By {{ $creatorName }}</span>
                                            @elseif ($assignee)
                                                <span>To {{ $assigneeName }}</span>
                                            @endif
                                            @if ($isCompleted)
                                                @if ($isSelfDo)
                                                    <span style="color:#059669;"> • Self completed</span>
                                                @elseif ($doer)
                                                    <span style="color:#059669;"> • Completed by
                                                        {{ $doerName }}</span>
                                                @endif
                                            @else
                                                @if ($doer)
                                                    <span style="color:#ef4444;"> • Cancelled by
                                                        {{ $doerName }}</span>
                                                @endif
                                            @endif
                                            <span style="color:#94a3b8;"> •
                                                {{ $actionDate?->format('d M Y, g:i A') }}</span>
                                        @endif
                                    </div>
                                </div>
                                {{-- Actions --}}
                                <div style="display:flex;flex-direction:column;gap:3px;flex-shrink:0;">
                                    @if ($canEdit)
                                        <button
                                            onclick="{{ $isCompleted ? 'undoComplete' : 'undoCancel' }}({{ $task->id }})"
                                            style="background:#fff;border:1px solid #e5e7eb;border-radius:5px;font-size:.62rem;padding:1px 7px;color:#6b7280;cursor:pointer;line-height:1.6;">↩️</button>
                                    @endif
                                    <button data-id="{{ $task->id }}" data-type="{{ $task->activity_type }}"
                                        onclick="deleteTask(this.dataset.id, this.dataset.type)"
                                        style="background:#fff;border:1px solid #e5e7eb;border-radius:5px;font-size:.62rem;padding:1px 7px;color:#ef4444;cursor:pointer;line-height:1.6;">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-muted text-center py-3" style="font-size:.75rem">No completed or cancelled tasks yet.
                </div>
            @endforelse

            {{-- System Logs --}}
            @if (isset($systemLogs) && $systemLogs->count() > 0)
                <div class="mt-3 pt-2" style="border-top:1px solid #e5e7eb;">
                    <div class="task-box-header" style="color:#6b7280;font-size:.75rem;">📋 System Updates
                        ({{ $systemLogs->count() }})</div>
                    @foreach ($systemLogs as $log)
                        <div
                            style="display:flex;gap:10px;background:#faf9fc;border:1px solid #e8e5ee;border-radius:8px;padding:8px 12px;margin-bottom:4px;">
                            <div
                                style="width:10px;height:10px;border-radius:50%;background:#3b82f6;margin-top:4px;flex-shrink:0;">
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:.78rem;font-weight:600;color:#374151;">
                                    {{ $log->title ?? 'Update' }}
                                    @if ($log->title === 'Revenue Added')
                                        <span style="color:#820b5c;">✓</span>
                                    @elseif($log->title === 'Revenue Deleted')
                                        <span style="color:#ef4444;">✗</span>
                                    @else
                                        <span style="color:#3b82f6;">↻</span>
                                    @endif
                                </div>
                                <div style="font-size:.72rem;color:#475569;white-space:pre-line;margin-top:2px;">
                                    {{ $log->content }}</div>
                                <div style="font-size:.65rem;color:#94a3b8;margin-top:2px;">
                                    {{ $log->creator?->name ?? 'Unknown' }} •
                                    {{ $log->created_at->format('d M Y, g:i A') }}
                                </div>
                            </div>
                            @if ($canEdit)
                                <x-confirm-delete url="{{ route('crm.notes.destroy', $log) }}" label=""
                                    title="Delete entry?" message="Delete this entry?" class="btn btn-sm"
                                    style="background:none;border:1px solid #e5e7eb;border-radius:5px;font-size:.62rem;padding:1px 7px;color:#ef4444;cursor:pointer;line-height:1.6;" />
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Activity Log --}}
    @include('crm.components.show.activity-log')
</div>
