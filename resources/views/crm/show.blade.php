{{-- resources/views/crm/show.blade.php --}}
@extends('layouts.app')

@section('title', $student->full_name . ' — CRM')

@push('styles')
    <style>
        :root {
            --crm-bg: #f4f6fb;
            --crm-card: #ffffff;
            --crm-border: #e5e9f2;
            --crm-text: #1a1f36;
            --crm-muted: #6b7280;
            --crm-primary: #4f46e5;
            --crm-danger: #ef4444;
            --crm-warning: #f59e0b;
            --crm-success: #10b981;
        }

        body {
            background: var(--crm-bg);
        }

        /* ── Back bar ── */
        .crm-back-bar {
            background: var(--crm-card);
            border-bottom: 1px solid var(--crm-border);
            padding: .5rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: .875rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .crm-back-bar a {
            text-decoration: none;
            color: var(--crm-muted);
        }

        .crm-back-bar a:hover {
            color: var(--crm-primary);
        }

        /* ── Student header ── */
        .crm-student-header {
            background: var(--crm-card);
            border-bottom: 1px solid var(--crm-border);
            padding: 1.25rem 1.5rem;
        }

        .crm-student-header img {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid var(--crm-border);
        }

        .stu-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--crm-text);
        }

        .stu-meta {
            font-size: .8rem;
            color: var(--crm-muted);
            margin-top: .25rem;
        }

        .stu-tag {
            display: inline-block;
            font-size: .7rem;
            background: #f0f2ff;
            color: var(--crm-primary);
            border-radius: 20px;
            padding: .15rem .55rem;
            margin: .1rem;
        }

        /* ── Stage bar ── */
        .stage-bar {
            background: var(--crm-card);
            border-bottom: 1px solid var(--crm-border);
            padding: .75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .stage-bar::-webkit-scrollbar {
            display: none;
        }

        .stage-bar-item {
            display: flex;
            align-items: center;
            font-size: .78rem;
            font-weight: 500;
            padding: .4rem .9rem;
            cursor: pointer;
            white-space: nowrap;
            position: relative;
            color: var(--crm-muted);
            border: none;
            background: none;
            transition: all .15s;
        }

        .stage-bar-item::after {
            content: '›';
            font-size: 1.1rem;
            color: var(--crm-border);
            margin-left: .75rem;
        }

        .stage-bar-item:last-child::after {
            display: none;
        }

        .stage-bar-item.passed {
            color: var(--crm-success);
            font-weight: 600;
        }

        .stage-bar-item.current {
            color: #fff;
            border-radius: 20px;
            padding: .35rem 1.1rem;
            font-weight: 700;
        }

        .stage-bar-item.current::after {
            color: var(--crm-border);
        }

        @if (!$canEdit)
            .stage-bar-item {
                cursor: default;
                pointer-events: none;
            }
        @endif

        /* ── Main layout ── */
        .crm-body {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 1.25rem;
            padding: 1.25rem 1.5rem;
        }

        @media (max-width: 992px) {
            .crm-body {
                grid-template-columns: 1fr;
            }
        }

        /* ── Cards ── */
        .crm-section {
            background: var(--crm-card);
            border: 1px solid var(--crm-border);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1.25rem;
        }

        .crm-section-header {
            padding: .75rem 1.1rem;
            border-bottom: 1px solid var(--crm-border);
            font-size: .8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--crm-muted);
            background: #fafbff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .crm-section-body {
            padding: 1rem 1.1rem;
        }

        /* ── Internal notes panel ── */
        .note-display {
            white-space: pre-wrap;
            font-size: .875rem;
            color: var(--crm-text);
            line-height: 1.6;
        }

        .note-item {
            padding: .65rem .85rem;
            border: 1px solid var(--crm-border);
            border-radius: 8px;
            margin-bottom: .6rem;
            position: relative;
            font-size: .85rem;
        }

        .note-item.pinned {
            border-left: 3px solid var(--crm-warning);
            background: #fffbeb;
        }

        .note-pin-btn {
            background: none;
            border: none;
            font-size: .85rem;
            cursor: pointer;
            color: var(--crm-muted);
            padding: 0;
        }

        .note-pin-btn:hover {
            color: var(--crm-warning);
        }

        /* ── Tabs ── */
        .crm-tabs {
            display: flex;
            border-bottom: 2px solid var(--crm-border);
            padding: 0 1.1rem;
            background: var(--crm-card);
        }

        .crm-tab {
            padding: .65rem 1.1rem;
            font-size: .85rem;
            font-weight: 500;
            border: none;
            background: none;
            cursor: pointer;
            color: var(--crm-muted);
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all .15s;
        }

        .crm-tab.active {
            color: var(--crm-primary);
            border-bottom-color: var(--crm-primary);
        }

        /* ── Today's tasks ── */
        .task-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem;
        }

        @media (max-width: 600px) {
            .task-grid {
                grid-template-columns: 1fr;
            }
        }

        .task-box {
            border: 1px solid var(--crm-border);
            border-radius: 10px;
            padding: .85rem 1rem;
        }

        .task-box-header {
            font-size: .72rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--crm-muted);
            margin-bottom: .6rem;
        }

        .task-item {
            display: flex;
            align-items: flex-start;
            gap: .6rem;
            padding: .6rem .75rem;
            border: 1px solid var(--crm-border);
            border-radius: 8px;
            margin-bottom: .5rem;
            font-size: .83rem;
        }

        .task-item.overdue {
            border-left: 3px solid var(--crm-danger);
        }

        .task-item.today {
            border-left: 3px solid var(--crm-warning);
        }

        .task-item.upcoming {
            border-left: 3px solid var(--crm-success);
        }

        .task-check {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 2px solid var(--crm-border);
            cursor: pointer;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .task-title {
            font-weight: 500;
            color: var(--crm-text);
        }

        .task-meta {
            font-size: .72rem;
            color: var(--crm-muted);
            margin-top: .15rem;
        }

        .task-actions {
            margin-left: auto;
            display: flex;
            gap: .3rem;
            flex-shrink: 0;
        }

        .task-actions button,
        .task-actions a {
            font-size: .72rem;
            padding: .15rem .4rem;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .priority-high {
            color: var(--crm-danger);
            font-weight: 600;
        }

        .priority-medium {
            color: var(--crm-warning);
            font-weight: 600;
        }

        .priority-low {
            color: var(--crm-success);
            font-weight: 600;
        }

        /* ── Activity history ── */
        .activity-item {
            padding: .85rem 1rem;
            border-bottom: 1px solid var(--crm-border);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item .act-icon {
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .activity-item .act-meta {
            font-size: .75rem;
            color: var(--crm-muted);
            margin-top: .2rem;
        }

        .activity-item .act-desc {
            font-size: .82rem;
            color: var(--crm-text);
            margin-top: .35rem;
            background: var(--crm-bg);
            border-radius: 6px;
            padding: .5rem .75rem;
            border-left: 3px solid var(--crm-border);
        }

        /* ── Right sidebar ── */
        .crm-sidebar {
            position: sticky;
            top: 56px;
            align-self: start;
        }

        .sidebar-field {
            margin-bottom: .85rem;
        }

        .sidebar-field label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--crm-muted);
            display: block;
            margin-bottom: .2rem;
        }

        .sidebar-field .val {
            font-size: .875rem;
            color: var(--crm-text);
            font-weight: 500;
        }

        /* ── Add task form ── */
        .add-task-form {
            background: var(--crm-bg);
            border-radius: 10px;
            padding: 1rem;
            border: 1px dashed var(--crm-border);
        }

        .add-task-form input,
        .add-task-form select,
        .add-task-form textarea {
            font-size: .83rem;
            border: 1px solid var(--crm-border);
            border-radius: 6px;
            padding: .4rem .65rem;
            width: 100%;
            background: #fff;
            margin-bottom: .5rem;
        }

        /* read-only badge */
        .read-only-badge {
            background: #fef3c7;
            color: #92400e;
            font-size: .72rem;
            border-radius: 20px;
            padding: .2rem .65rem;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')

    {{-- ── Back bar ── --}}
    <div class="crm-back-bar">
        <a href="{{ route('crm.dashboard') }}">◀ Back to Pipeline</a>
        <div class="d-flex align-items-center gap-2">
            @if (!$canEdit)
                <span class="read-only-badge">👁 Read-only</span>
            @endif
            @if ($canEdit)
                <a href="#" class="btn btn-sm btn-outline-danger"
                    onclick="return confirm('Delete this student?')">Delete</a>
            @endif
        </div>
    </div>

    {{-- ── Student header ── --}}
    <div class="crm-student-header">
        <div class="d-flex gap-3 flex-wrap align-items-start">
            <img src="{{ $student->avatar_url }}" alt="{{ $student->full_name }}">
            <div class="flex-grow-1">
                <div class="stu-name">{{ $student->full_name }}</div>
                <div class="stu-meta">
                    📞 {{ $student->phone_number ?? '—' }}
                    &nbsp;&nbsp;✉️ {{ $student->email ?? '—' }}
                </div>
                <div class="mt-1">
                    @foreach ($student->tags ?? [] as $tag)
                        <span class="stu-tag">🏷️ {{ $tag }}</span>
                    @endforeach
                </div>
                <div class="stu-meta mt-1">
                    👤 Assigned to: <strong>{{ $student->agent?->name ?? '—' }}</strong>
                    &nbsp;&bull;&nbsp;
                    📋 Qualification: {{ $student->qualification ?? '—' }}
                    &nbsp;&bull;&nbsp;
                    🌍 Preferred Country: {{ $student->preferred_country ?? '—' }}
                </div>
            </div>
        </div>
    </div>

    {{-- ── Stage bar ── --}}
    <div class="stage-bar">
        @foreach ($stages as $stg)
            @php
                $isCurrent = $currentStage?->id === $stg->id;
                $isPassed = $currentStage && $stg->stage_order < $currentStage->stage_order;
                $cls = $isCurrent ? 'current' : ($isPassed ? 'passed' : '');
            @endphp
            @if ($canEdit)
                <form action="{{ route('crm.student.stage', $student) }}" method="POST" style="display:inline">
                    @csrf
                    <input type="hidden" name="new_stage_id" value="{{ $stg->id }}">
                    <button type="submit" class="stage-bar-item {{ $cls }}"
                        style="{{ $isCurrent ? 'background:' . $stg->color . ';' : '' }}"
                        @if ($isCurrent) disabled @endif
                        @if (!$isCurrent) onclick="return confirm('Move to \'{{ $stg->name }}\'?')" @endif>
                        {{ $stg->name }}
                    </button>
                </form>
            @else
                <span class="stage-bar-item {{ $cls }}"
                    style="{{ $isCurrent ? 'background:' . $stg->color . '; color:#fff;' : '' }}">
                    {{ $stg->name }}
                </span>
            @endif
        @endforeach
    </div>

    {{-- ── Main body ── --}}
    <div class="crm-body">

        {{-- ═══ LEFT — main content ═══ --}}
        <div>

            {{-- Internal notes / extra info panel --}}
            <div class="crm-section">
                <div class="crm-section-header">
                    📝 Internal Notes &amp; Extra Information
                    @if ($canEdit)
                        <button class="btn btn-sm btn-outline-primary" onclick="toggleNoteForm()">+ Add Note</button>
                    @endif
                </div>
                <div class="crm-section-body">

                    {{-- Pinned notes first --}}
                    @foreach ($notes->where('is_pinned', true) as $note)
                        <div class="note-item pinned">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="note-display">{{ $note->content }}</div>
                                @if ($canEdit)
                                    <div class="d-flex gap-1 ms-2">
                                        <button class="note-pin-btn" title="Unpin"
                                            onclick="togglePin({{ $note->id }}, this)">📌</button>
                                        <form action="{{ route('crm.notes.destroy', $note) }}" method="POST"
                                            onsubmit="return confirm('Delete note?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="note-pin-btn">🗑️</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                            <div class="text-muted mt-1" style="font-size:.72rem">
                                By {{ $note->creator?->name ?? 'Unknown' }} &bull;
                                {{ $note->created_at->format('d M Y') }}
                            </div>
                        </div>
                    @endforeach

                    {{-- Unpinned notes --}}
                    @foreach ($notes->where('is_pinned', false) as $note)
                        <div class="note-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="note-display">{{ $note->content }}</div>
                                @if ($canEdit)
                                    <div class="d-flex gap-1 ms-2">
                                        <button class="note-pin-btn" title="Pin"
                                            onclick="togglePin({{ $note->id }}, this)">📍</button>
                                        <form action="{{ route('crm.notes.destroy', $note) }}" method="POST"
                                            onsubmit="return confirm('Delete note?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="note-pin-btn">🗑️</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                            <div class="text-muted mt-1" style="font-size:.72rem">
                                By {{ $note->creator?->name ?? 'Unknown' }} &bull;
                                {{ $note->created_at->format('d M Y') }}
                            </div>
                        </div>
                    @endforeach

                    @if ($notes->isEmpty())
                        <div class="text-muted text-center py-3" style="font-size:.875rem">No notes yet.</div>
                    @endif

                    {{-- Add note form --}}
                    @if ($canEdit)
                        <div id="noteForm" style="display:none; margin-top:.75rem">
                            <form action="{{ route('crm.notes.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                <input type="hidden" name="type" value="internal">
                                <textarea name="content" rows="4" class="form-control form-control-sm mb-2" placeholder="Write an internal note…"
                                    required></textarea>
                                <div class="d-flex gap-2">
                                    <label class="d-flex align-items-center gap-1 small">
                                        <input type="checkbox" name="is_pinned" value="1"> Pin this note
                                    </label>
                                    <button type="submit" class="btn btn-sm btn-primary ms-auto">Save Note</button>
                                </div>
                            </form>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Tabs: Tasks | Documents | History --}}
            <div class="crm-section">
                <div class="crm-tabs">
                    <button class="crm-tab active" onclick="switchTab('tasks', this)">Tasks</button>
                    <button class="crm-tab" onclick="switchTab('documents', this)">Documents</button>
                    <button class="crm-tab" onclick="switchTab('history', this)">History</button>
                </div>

                {{-- ── TASKS TAB ── --}}
                <div id="tab-tasks">
                    <div class="crm-section-body">
                        <div class="task-grid">

                            {{-- TODAY'S TASKS --}}
                            <div class="task-box">
                                <div class="task-box-header">📅 Today's Tasks ({{ $todayTasks->count() }})</div>
                                @forelse($todayTasks as $task)
                                    <div class="task-item today" id="task-{{ $task->id }}">
                                        @if ($canEdit)
                                            <form action="{{ route('crm.tasks.complete', $task) }}" method="POST"
                                                style="display:inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="task-check" title="Mark done"></button>
                                            </form>
                                        @endif
                                        <div class="flex-grow-1">
                                            <div class="task-title">{{ $task->title }}</div>
                                            <div class="task-meta">
                                                🕐 {{ ucfirst($task->time_slot ?? 'anytime') }}
                                                &bull;
                                                <span class="priority-{{ $task->priority }}">
                                                    {{ $task->priority === 'high' ? '🔴' : ($task->priority === 'medium' ? '🟡' : '🟢') }}
                                                    {{ ucfirst($task->priority) }}
                                                </span>
                                            </div>
                                        </div>
                                        @if ($canEdit)
                                            <div class="task-actions">
                                                <button onclick="openEditTask({{ $task->id }})"
                                                    class="btn btn-xs btn-outline-secondary">Edit</button>
                                                <form action="{{ route('crm.tasks.cancel', $task) }}" method="POST"
                                                    style="display:inline">
                                                    @csrf @method('PATCH')
                                                    <button class="btn btn-xs btn-outline-danger">✕</button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-muted small text-center py-2">No tasks for today 🎉</div>
                                @endforelse
                            </div>

                            {{-- PLANNED TASKS --}}
                            <div class="task-box">
                                <div class="task-box-header">🗓 Planned Activities ({{ $plannedTasks->count() }})</div>
                                @forelse($plannedTasks as $task)
                                    <div class="task-item upcoming" id="task-{{ $task->id }}">
                                        <div class="flex-grow-1">
                                            <div class="task-title">{{ $task->title }}</div>
                                            <div class="task-meta">
                                                For: {{ $task->assignee?->name ?? 'Me' }}
                                                &bull;
                                                Due:
                                                {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->diffForHumans() : 'No date' }}
                                                <span
                                                    style="color: {{ \Carbon\Carbon::parse($task->due_date)->isPast() ? 'var(--crm-danger)' : 'var(--crm-success)' }}">
                                                    {{ \Carbon\Carbon::parse($task->due_date)->isPast() ? '🔴' : '🟢' }}
                                                </span>
                                            </div>
                                        </div>
                                        @if ($canEdit)
                                            <div class="task-actions">
                                                <form action="{{ route('crm.tasks.complete', $task) }}" method="POST"
                                                    style="display:inline">
                                                    @csrf @method('PATCH')
                                                    <button class="btn btn-xs btn-success">✓ Done</button>
                                                </form>
                                                <form action="{{ route('crm.tasks.cancel', $task) }}" method="POST"
                                                    style="display:inline">
                                                    @csrf @method('PATCH')
                                                    <button class="btn btn-xs btn-outline-danger">Cancel</button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-muted small text-center py-2">No planned activities</div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Add task form --}}
                        @if ($canEdit)
                            <div class="mt-3">
                                <button class="btn btn-sm btn-outline-primary w-100" onclick="toggleTaskForm()">
                                    + Log New Task / Activity
                                </button>

                                <div id="addTaskForm" style="display:none; margin-top:.75rem" class="add-task-form">

                                    <form action="{{ route('crm.tasks.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="student_id" value="{{ $student->id }}">

                                        <div class="row g-2">

                                            <div class="col-12">
                                                <input type="text" name="title"
                                                    placeholder="Task title / what to do" required>
                                            </div>

                                            <div class="col-sm-4">
                                                <select name="task_type" required>
                                                    <option value="">Type…</option>
                                                    <option value="call">📞 Call</option>
                                                    <option value="email">✉️ Email</option>
                                                    <option value="whatsapp">💬 WhatsApp</option>
                                                    <option value="meeting">👥 Meeting</option>
                                                    <option value="document">📄 Document</option>
                                                    <option value="follow_up">⏰ Follow Up</option>
                                                    <option value="other">📌 Other</option>
                                                </select>
                                            </div>

                                            <div class="col-sm-4">
                                                <select name="priority" required>
                                                    <option value="medium">🟡 Medium</option>
                                                    <option value="high">🔴 High</option>
                                                    <option value="low">🟢 Low</option>
                                                </select>
                                            </div>

                                            <div class="col-sm-4">
                                                <select name="time_slot">
                                                    <option value="">Any time</option>
                                                    <option value="morning">🌅 Morning</option>
                                                    <option value="afternoon">☀️ Afternoon</option>
                                                    <option value="evening">🌙 Evening</option>
                                                </select>
                                            </div>

                                            <div class="col-sm-6">
                                                <input type="date" name="due_date" value="{{ date('Y-m-d') }}">
                                            </div>

                                            <div class="col-sm-6">
                                                <select name="assigned_to">
                                                    <option value="">Assign to…</option>
                                                    @foreach ($assignableUsers as $u)
                                                        <option value="{{ $u->id }}" @selected($u->id === auth()->id())>
                                                            {{ $u->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-12">
                                                <textarea name="description" rows="2" placeholder="Optional description…"></textarea>
                                            </div>

                                            <div class="col-12 d-flex gap-2">
                                                <button type="submit" class="btn btn-sm btn-primary">Save Task</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="toggleTaskForm()">
                                                    Cancel
                                                </button>
                                            </div>

                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif
                        {{-- ── Activity History ── --}}
                        <div class="mt-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <strong style="font-size:.85rem">🕒 Activity History</strong>
                            </div>
                            @forelse($activityHistory as $act)
                                <div class="activity-item d-flex gap-3">
                                    <span class="act-icon">{{ $act->task_icon ?? '📌' }}</span>
                                    <div class="flex-grow-1">
                                        <div class="fw-medium" style="font-size:.85rem">{{ $act->title }}</div>
                                        <div class="act-meta">
                                            By {{ $act->assignee?->name ?? 'Unknown' }}
                                            &bull;
                                            {{ $act->completed_at?->format('d M Y, g:i A') ?? $act->updated_at->format('d M Y') }}
                                        </div>
                                        @if ($act->description)
                                            <div class="act-desc">{{ $act->description }}</div>
                                        @endif
                                    </div>
                                    <span class="badge bg-success-subtle text-success align-self-start"
                                        style="font-size:.7rem">✅ Done</span>
                                </div>
                            @empty
                                <div class="text-muted text-center py-3" style="font-size:.875rem">No completed activities
                                    yet.</div>
                            @endforelse

                            @if ($activityHistory->hasPages())
                                <div class="mt-2">{{ $activityHistory->links() }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ── DOCUMENTS TAB ── --}}
                <div id="tab-documents" style="display:none">
                    <div class="crm-section-body">
                        @forelse($student->documents as $doc)
                            <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                                <span class="fs-5">📄</span>
                                <div class="flex-grow-1">
                                    <div class="fw-medium small">{{ $doc->document_type }}</div>
                                    <div class="text-muted" style="font-size:.72rem">Uploaded
                                        {{ $doc->created_at->format('d M Y') }}</div>
                                </div>
                                <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                                    class="btn btn-xs btn-outline-primary">View</a>
                            </div>
                        @empty
                            <div class="text-muted text-center py-4">No documents uploaded.</div>
                        @endforelse
                    </div>
                </div>

                {{-- ── HISTORY TAB ── --}}
                <div id="tab-history" style="display:none">
                    <div class="crm-section-body">
                        <div id="stageHistoryContent">
                            <div class="text-center text-muted py-3 small">Loading history…</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ RIGHT sidebar ═══ --}}
        <div class="crm-sidebar">
            <div class="crm-section">
                <div class="crm-section-header">Student Details</div>
                <div class="crm-section-body">
                    <div class="sidebar-field">
                        <label>Stage</label>
                        <div class="val">
                            @if ($currentStage)
                                <span class="stage-pill d-inline-block"
                                    style="background:{{ $currentStage->color }}20; color:{{ $currentStage->color }}; border-radius:20px; padding:.2rem .65rem; font-size:.8rem; font-weight:600">
                                    {{ $currentStage->name }}
                                </span>
                                <div class="text-muted mt-1" style="font-size:.72rem">
                                    {{ $student->days_in_current_stage }} days in this stage</div>
                            @else
                                <span class="text-muted">Not assigned</span>
                            @endif
                        </div>
                    </div>
                    <div class="sidebar-field">
                        <label>Date of Birth</label>
                        <div class="val">{{ $student->dob?->format('d M Y') ?? '—' }} ({{ $student->age }} yrs)</div>
                    </div>
                    <div class="sidebar-field">
                        <label>Gender</label>
                        <div class="val">{{ $student->gender ?? '—' }}</div>
                    </div>
                    <div class="sidebar-field">
                        <label>Nationality</label>
                        <div class="val">{{ $student->nationality ?? '—' }}</div>
                    </div>
                    <div class="sidebar-field">
                        <label>Passport</label>
                        <div class="val">{{ $student->passport_number ?? '—' }}</div>
                        @if ($student->passport_expiry)
                            <div class="text-muted" style="font-size:.72rem">Expires
                                {{ $student->passport_expiry->format('d M Y') }}</div>
                        @endif
                    </div>
                    <div class="sidebar-field">
                        <label>Qualification</label>
                        <div class="val">{{ $student->qualification ?? '—' }} ({{ $student->passed_year ?? '—' }})
                        </div>
                    </div>
                    <div class="sidebar-field">
                        <label>Preferred Country</label>
                        <div class="val">{{ $student->preferred_country ?? '—' }}</div>
                    </div>
                    <div class="sidebar-field">
                        <label>Preferred Course</label>
                        <div class="val">{{ $student->preferred_course ?? '—' }}</div>
                    </div>
                    <div class="sidebar-field">
                        <label>Marital Status</label>
                        <div class="val">{{ $student->marital_status ?? '—' }}</div>
                    </div>
                    <div class="sidebar-field">
                        <label>Documents</label>
                        <div class="progress" style="height:6px; margin-top:.3rem">
                            <div class="progress-bar" style="width:{{ $student->completion_percentage }}%"></div>
                        </div>
                        <div class="text-muted mt-1" style="font-size:.72rem">{{ $student->completion_percentage }}% —
                            {{ $student->completion_status }}</div>
                    </div>
                    <div class="sidebar-field">
                        <label>Remarks</label>
                        <div class="val small">{{ $student->remarks ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        // ── Tab switching ──────────────────────────────────────────────
        function switchTab(name, btn) {
            document.querySelectorAll('[id^="tab-"]').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.crm-tab').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + name).style.display = 'block';
            btn.classList.add('active');

            if (name === 'history') loadHistory();
        }

        // ── Load stage history via fetch ──────────────────────────────
        let historyLoaded = false;

        function loadHistory() {
            if (historyLoaded) return;
            historyLoaded = true;

            fetch('{{ route('crm.student.history', $student) }}')
                .then(r => r.json())
                .then(data => {
                    const el = document.getElementById('stageHistoryContent');
                    if (!data.length) {
                        el.innerHTML =
                            '<div class="text-muted text-center py-4 small">No stage changes recorded.</div>';
                        return;
                    }
                    el.innerHTML = data.map(h => `
                <div style="display:flex; gap:.75rem; padding:.75rem 0; border-bottom:1px solid #e5e9f2">
                    <span style="font-size:1.1rem">🔄</span>
                    <div>
                        <div style="font-size:.85rem; font-weight:500">
                            <span style="color:#6b7280">${h.from}</span>
                            <span style="margin:0 .4rem">→</span>
                            <span style="color:#1a1f36">${h.to}</span>
                        </div>
                        <div style="font-size:.72rem; color:#6b7280; margin-top:.15rem">
                            By ${h.changed_by} &bull; ${h.date}
                            ${h.days_in_previous ? '&bull; ' + h.days_in_previous + ' days in previous stage' : ''}
                        </div>
                        ${h.reason ? '<div style="font-size:.78rem; margin-top:.25rem; color:#4b5563">Reason: ' + h.reason + '</div>' : ''}
                    </div>
                </div>
            `).join('');
                });
        }

        // ── Toggle forms ──────────────────────────────────────────────
        function toggleNoteForm() {
            const f = document.getElementById('noteForm');
            f.style.display = f.style.display === 'none' ? 'block' : 'none';
        }

        function toggleTaskForm() {
            const f = document.getElementById('addTaskForm');
            f.style.display = f.style.display === 'none' ? 'block' : 'none';
        }

        // ── Toggle pin note via fetch ─────────────────────────────────
        function togglePin(noteId, btn) {
            fetch(`/crm/notes/${noteId}/pin`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // Reload to reflect pinned/unpinned reordering
                        window.location.reload();
                    }
                });
        }
    </script>
@endpush
