{{-- resources/views/crm/show.blade.php --}}
@extends('layouts.app')

@section('title', $student->full_name . ' — CRM')

@push('styles')
    <style>
        /* CRM-specific components - Not in main styles.css */

        .crm-back-bar {
            background: var(--light);
            border-bottom: 1px solid var(--glass-gradient);
            padding: .5rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .crm-back-bar a {
            text-decoration: none;
            color: var(--muted);
        }

        .crm-back-bar a:hover {
            color: var(--primary);
        }

        .crm-student-header {
            background: var(--light-blue);
            border-bottom: 1px solid var(--glass-gradient);
            padding: 1.25rem 1.5rem;
        }

        .crm-student-header img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid var(--glass-gradient);
        }

        .stu-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text);
        }

        .stu-meta {
            font-size: .8rem;
            color: var(--muted);
            margin-top: .25rem;
        }

        .stu-tag {
            display: inline-block;
            font-size: .7rem;
            background: #f0f2ff;
            color: var(--primary);
            border-radius: 20px;
            padding: .15rem .55rem;
            margin: .1rem;
        }

        .stage-bar {
            background: var(--light-gradient);
            border-bottom: 1px solid var(--glass-gradient);
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
            color: var(--muted);
            border: none;
            background: none;
            transition: all .15s;
        }

        .stage-bar-item::after {
            content: '›';
            font-size: 1.1rem;
            color: var(--glass-gradient);
            margin-left: .75rem;
        }

        .stage-bar-item:last-child::after {
            display: none;
        }

        .stage-bar-item.passed {
            color: var(--success);
            font-weight: 600;
        }

        .stage-bar-item.current {
            color: #fff;
            border-radius: 20px;
            padding: .35rem 1.1rem;
            font-weight: 700;
        }

        .crm-body {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 1.25rem;
            padding: 1.25rem 1.5rem;
        }

        @media (max-width:992px) {
            .crm-body {
                grid-template-columns: 1fr;
            }
        }

        .crm-section {
            background: var(--light);
            border: 1px solid var(--dark-gradient);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1.25rem;
        }

        .crm-section-header {
            padding: .75rem 1.1rem;
            border-bottom: 1px solid var(--glass-gradient);
            font-size: .8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--muted);
            background: #fafbff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .crm-section-body {
            padding: 1rem 1.1rem;
        }

        .note-display {
            white-space: pre-wrap;
            font-size: .875rem;
            color: var(--text);
            line-height: 1.6;
        }

        .note-item {
            padding: .65rem .85rem;
            border: 1px solid var(--glass-gradient);
            border-radius: 8px;
            margin-bottom: .6rem;
            font-size: .85rem;
        }

        .note-item.pinned {
            border-left: 3px solid var(--warning);
            background: #fffbeb;
        }

        .note-pin-btn {
            background: none;
            border: none;
            font-size: .85rem;
            cursor: pointer;
            color: var(--muted);
            padding: 0;
        }

        .note-pin-btn:hover {
            color: var(--warning);
        }

        .crm-tabs {
            display: flex;
            border-bottom: 2px solid var(--glass-gradient);
            padding: 0 1.1rem;
            background: var(--light-gradient);
        }

        .crm-tab {
            padding: .65rem 1.1rem;
            font-size: .85rem;
            font-weight: 500;
            border: none;
            background: none;
            cursor: pointer;
            color: var(--muted);
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all .15s;
        }

        .crm-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tasks-container {
            display: flex;
            gap: 1.25rem;
        }

        .task-box {
            border: 1px solid var(--light-gradient);
            border-radius: 10px;
            padding: .85rem 1rem;
            background: var(--crm-bg);
        }

        .task-box-header {
            font-size: .8rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: .75rem;
            padding-bottom: .5rem;
            border-bottom: 2px solid var(--glass-gradient);
        }

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
            border-left: 4px solid var(--danger);
            background: #fef2f2;
        }

        .task-item.due {
            border-left: 4px solid var(--warning);
            background: #fffbeb;
        }

        .task-item.today {
            border-left: 4px solid var(--success);
            background: #ecfdf5;
        }

        .task-item.upcoming {
            border-left: 4px solid var(--primary);
            background: #f0f2ff;
        }

        .task-item.completed {
            border-left: 4px solid #9ca3af;
            background: #f9fafb;
            opacity: 0.8;
        }

        .task-check {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            border: 2px solid var(--glass-gradient);
            cursor: pointer;
            flex-shrink: 0;
            margin-top: 2px;
            background: none;
            transition: all 0.2s;
        }

        .task-check:hover {
            border-color: var(--success);
            background: var(--success);
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
            background: var(--crm-bg);
            border-radius: 6px;
        }

        .task-actions {
            margin-left: auto;
            display: flex;
            gap: .3rem;
            flex-shrink: 0;
        }

        .priority-high {
            color: var(--danger);
            font-weight: 600;
        }

        .priority-medium {
            color: var(--warning);
            font-weight: 600;
        }

        .priority-low {
            color: var(--success);
            font-weight: 600;
        }

        /* Modal Styles - Page specific */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--glass-gradient);
            font-weight: 700;
            font-size: 1.1rem;
            background: #fafbff;
            border-radius: 12px 12px 0 0;
        }

        .modal-body {
            padding: 1.25rem;
        }

        .modal-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--glass-gradient);
            display: flex;
            gap: .75rem;
            justify-content: flex-end;
        }

        .activity-item {
            padding: .85rem 1rem;
            border-bottom: 1px solid var(--glass-gradient);
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
            color: var(--muted);
            margin-top: .2rem;
        }

        .activity-item .act-desc {
            font-size: .82rem;
            color: var(--text);
            margin-top: .35rem;
            background: var(--crm-bg);
            border-radius: 6px;
            padding: .5rem .75rem;
            border-left: 3px solid var(--glass-gradient);
        }

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
            color: var(--muted);
            display: block;
            margin-bottom: .2rem;
        }

        .sidebar-field .val {
            font-size: .875rem;
            color: var(--text);
            font-weight: 500;
        }

        .add-task-form {
            background: var(--crm-bg);
            border-radius: 10px;
            padding: 1rem;
            border: 1px dashed var(--glass-gradient);
        }

        .add-task-form input,
        .add-task-form select,
        .add-task-form textarea {
            font-size: .83rem;
            border: 1px solid var(--glass-gradient);
            border-radius: 6px;
            padding: .4rem .65rem;
            width: 100%;
            background: #fff;
            margin-bottom: .5rem;
        }

        .read-only-badge {
            background: #fef3c7;
            color: #92400e;
            font-size: .72rem;
            border-radius: 20px;
            padding: .2rem .65rem;
            font-weight: 600;
        }

        .undo-complete {
            cursor: pointer;
            color: var(--primary);
            font-size: .7rem;
        }

        .undo-complete:hover {
            text-decoration: underline;
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 5px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 32px;
            color: #ccc;
            cursor: pointer;
            transition: 0.2s;
        }

        .star-rating input:checked~label,
        .star-rating label:hover,
        .star-rating label:hover~label {
            color: #ffc107;
        }
    </style>
@endpush

@section('content')

    {{-- Back bar --}}
    <div class="crm-back-bar">
        <a href="{{ route('crm.dashboard') }}">◀ Back to Pipeline</a>
        <div class="d-flex align-items-center gap-2">
            @if (!$canEdit)
                <span class="read-only-badge">👁 Read-only</span>
            @endif
        </div>
    </div>

    {{-- Student header --}}
    <div class="crm-student-header">
        <div class="d-flex gap-3 flex-wrap align-items-start">
            @if ($student->students_photo && Storage::disk('public')->exists($student->students_photo))
                <img src="{{ Storage::url($student->students_photo) }}" class="rounded object-fit-cover" width="150"
                    height="150" alt="Photo">
            @else
                <div class="rounded bg-primary d-flex align-items-center justify-content-center"
                    style="width:150px;height:150px;">
                    <i class="fa-solid fa-user text-white"></i>
                </div>
            @endif
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
                    📋 Qualification: {{ $student->qualification ?? '—' }}
                    &nbsp;&bull;&nbsp;
                    🌍 Preferred Country: {{ $student->preferred_country ?? '—' }}
                </div>
                <div class="stu-meta mt-1">
                    👤 Student of: <strong>{{ $student->agent?->name ?? '—' }}</strong>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Rating</label>

                <div class="star-rating">
                    <input type="radio" name="rating" id="star3" value="3"
                        {{ old('rating', $student->rating ?? '') == 3 ? 'checked' : '' }}>
                    <label for="star3">&#9733;</label>

                    <input type="radio" name="rating" id="star2" value="2"
                        {{ old('rating', $student->rating ?? '') == 2 ? 'checked' : '' }}>
                    <label for="star2">&#9733;</label>

                    <input type="radio" name="rating" id="star1" value="1"
                        {{ old('rating', $student->rating ?? '') == 1 ? 'checked' : '' }}>
                    <label for="star1">&#9733;</label>
                </div>
            </div>

        </div>
    </div>

    {{-- Stage bar --}}
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

    {{-- Main body --}}
    <div class="crm-body">

        {{-- LEFT --}}
        <div>

            {{-- Notes --}}
            <div class="crm-section">
                <div class="crm-section-header">
                    📝 Internal Note
                </div>
                <div class="crm-section-body">

                    @foreach ($notes->where('is_pinned', true) as $note)
                        <div class="note-item pinned">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="note-display">{{ $note->content }}</div>
                                @if ($canEdit)
                                    <div class="d-flex gap-1 ms-2">
                                        <button class="note-pin-btn" onclick="togglePin({{ $note->id }})">📌</button>
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

                    @foreach ($notes->where('is_pinned', false) as $note)
                        <div class="note-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="note-display">{{ $note->content }}</div>
                                @if ($canEdit)
                                    <div class="d-flex gap-1 ms-2">
                                        <button class="note-pin-btn" onclick="togglePin({{ $note->id }})">📍</button>
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
                    <div id="noteForm">
                        <form action="{{ route('crm.notes.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $student->id }}">
                            <input type="hidden" name="type" value="internal">
                            <textarea name="content" rows="10" class="form-control form-control-sm mb-2" placeholder="Write an internal note…"
                                required></textarea>
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <input style="width:auto;" type="checkbox" name="is_pinned" value="1">
                                <label>Pin this note
                                </label>
                                <button type="submit" class="btn btn-sm btn-primary ms-auto">Save Note</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="crm-section">
                <div class="crm-tabs">
                    <button class="crm-tab active" onclick="switchTab('tasks', this)">Tasks</button>
                    <button class="crm-tab" onclick="switchTab('documents', this)">Documents</button>
                    <button class="crm-tab" onclick="switchTab('history', this)">History</button>
                </div>

                {{-- TASKS TAB --}}
                <div id="tab-tasks">
                    <div class="crm-section-body">
                        <div class="tasks-container">
                            <div class="task-combined">
                                {{-- OVERDUE TASKS BOX --}}
                                @if ($dueTasks->count() > 0)
                                    <div class="task-box">
                                        <div class="task-box-header" style="color: var(--danger);">
                                            ⚠️ Overdue Tasks ({{ $dueTasks->count() }})
                                        </div>
                                        @foreach ($dueTasks as $task)
                                            @php $priority = $task->meta_data['priority'] ?? 'medium'; @endphp
                                            <div class="task-item overdue" data-task-id="{{ $task->id }}">
                                                <button class="task-check"
                                                    onclick="openCompleteModal({{ $task->id }}, '{{ addslashes($task->subject) }}')"></button>
                                                <div class="flex-grow-1">
                                                    <div class="task-title">{{ $task->subject }}</div>
                                                    <div class="task-meta">
                                                        {{ $task->activity_icon }} {{ $task->activity_type_label }}
                                                        &bull; 🕐 {{ ucfirst($task->priority_time_slot ?? 'anytime') }}
                                                        &bull; <span class="priority-{{ $priority }}">
                                                            {{ $priority === 'high' ? '🔴' : ($priority === 'medium' ? '🟡' : '🟢') }}
                                                            {{ ucfirst($priority) }}
                                                        </span>
                                                        @if ($task->assignee)
                                                            &bull; 👤 {{ $task->assignee->name }}
                                                        @endif
                                                        &bull; Due:
                                                        {{ $task->scheduled_at?->format('d M Y') ?? 'No date' }}
                                                    </div>
                                                    @if ($task->description)
                                                        <div class="task-description">{{ $task->description }}</div>
                                                    @endif
                                                </div>
                                                @if ($canEdit)
                                                    <div class="task-actions">
                                                        <button
                                                            onclick="openCancelModal({{ $task->id }}, '{{ addslashes($task->subject) }}')"
                                                            class="btn btn-sm btn-outline-danger"
                                                            style="font-size:.72rem;padding:.15rem .4rem">✕ Cancel</button>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif


                                {{-- TODAY'S TASKS BOX --}}
                                @if ($todayTasks->count() > 0)
                                    <div class="task-box">
                                        <div class="task-box-header" style="color: var(--success);">
                                            📅 Today's Tasks ({{ $todayTasks->count() }})
                                        </div>
                                        @foreach ($todayTasks as $task)
                                            @php $priority = $task->meta_data['priority'] ?? 'medium'; @endphp
                                            <div class="task-item today" data-task-id="{{ $task->id }}">
                                                <button class="task-check"
                                                    onclick="openCompleteModal({{ $task->id }}, '{{ addslashes($task->subject) }}')"></button>
                                                <div class="flex-grow-1">
                                                    <div class="task-title">{{ $task->subject }}</div>
                                                    <div class="task-meta">
                                                        {{ $task->activity_icon }} {{ $task->activity_type_label }}
                                                        &bull; 🕐 {{ ucfirst($task->priority_time_slot ?? 'anytime') }}
                                                        &bull; <span class="priority-{{ $priority }}">
                                                            {{ $priority === 'high' ? '🔴' : ($priority === 'medium' ? '🟡' : '🟢') }}
                                                            {{ ucfirst($priority) }}
                                                        </span>
                                                        @if ($task->assignee)
                                                            &bull; 👤 {{ $task->assignee->name }}
                                                        @endif
                                                    </div>
                                                    @if ($task->description)
                                                        <div class="task-description">{{ $task->description }}</div>
                                                    @endif
                                                </div>
                                                @if ($canEdit)
                                                    <div class="task-actions">
                                                        <button
                                                            onclick="openCancelModal({{ $task->id }}, '{{ addslashes($task->subject) }}')"
                                                            class="btn btn-sm btn-outline-danger"
                                                            style="font-size:.72rem;padding:.15rem .4rem">✕ Cancel</button>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            {{-- UPCOMING TASKS BOX --}}
                            @if ($plannedTasks->count() > 0)
                                <div class="task-box">
                                    <div class="task-box-header" style="color: var(--primary);">
                                        🗓 Upcoming Tasks ({{ $plannedTasks->count() }})
                                    </div>
                                    @foreach ($plannedTasks as $task)
                                        @php $priority = $task->meta_data['priority'] ?? 'medium'; @endphp
                                        <div class="task-item upcoming" data-task-id="{{ $task->id }}">
                                            <button class="task-check"
                                                onclick="openCompleteModal({{ $task->id }}, '{{ addslashes($task->subject) }}')"></button>
                                            <div class="flex-grow-1">
                                                <div class="task-title">{{ $task->subject }}</div>
                                                <div class="task-meta">
                                                    {{ $task->activity_icon }} {{ $task->activity_type_label }}
                                                    &bull; 👤 {{ $task->assignee?->name ?? 'Me' }}
                                                    &bull; Due:
                                                    {{ $task->scheduled_at?->format('d M Y') ?? 'No date' }}
                                                    &bull; <span class="priority-{{ $priority }}">
                                                        {{ $priority === 'high' ? '🔴' : ($priority === 'medium' ? '🟡' : '🟢') }}
                                                        {{ ucfirst($priority) }}
                                                    </span>
                                                </div>
                                                @if ($task->description)
                                                    <div class="task-description">{{ $task->description }}</div>
                                                @endif
                                            </div>
                                            @if ($canEdit)
                                                <div class="task-actions">
                                                    <button
                                                        onclick="openCancelModal({{ $task->id }}, '{{ addslashes($task->subject) }}')"
                                                        class="btn btn-sm btn-outline-danger"
                                                        style="font-size:.72rem;padding:.15rem .4rem">✕ Cancel</button>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if ($dueTasks->count() == 0 && $todayTasks->count() == 0 && $plannedTasks->count() == 0)
                                <div class="task-box">
                                    <div class="text-muted text-center py-4">No pending tasks 🎉</div>
                                </div>
                            @endif
                        </div>

                        {{-- Add task --}}
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
                                            <div class="col-sm-6">
                                                <select name="task_type" required>
                                                    <option value="">Activity Type…</option>
                                                    <option value="call">📞 Call</option>
                                                    <option value="email">✉️ Email</option>
                                                    <option value="whatsapp">💬 WhatsApp</option>
                                                    <option value="meeting">👥 Meeting</option>
                                                    <option value="document_review">📄 Document Review</option>
                                                    <option value="follow_up">⏰ Follow Up</option>
                                                    <option value="counseling">🎓 Counseling</option>
                                                    <option value="todo">✅ To Do</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-6">
                                                <input type="text" name="title" placeholder="Summary" required>
                                            </div>
                                            <div class="col-12">
                                                <textarea name="description" rows="2" placeholder="Log a note..."></textarea>
                                            </div>
                                            <div class="col-sm-6">
                                                <input type="date" name="due_date" value="{{ date('Y-m-d') }}">
                                            </div>
                                            <div class="col-sm-6">
                                                <select name="assigned_to">
                                                    <option value="">Assign to…</option>
                                                    @foreach ($assignableUsers as $u)
                                                        <option value="{{ $u->id }}" @selected($u->id === auth()->id())>
                                                            {{ $u->name }} ({{ ucfirst($u->role) }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-6">
                                                <select name="priority">
                                                    <option value="medium">🟡 Medium Priority</option>
                                                    <option value="high">🔴 High Priority</option>
                                                    <option value="low">🟢 Low Priority</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-6">
                                                <select name="time_slot">
                                                    <option value="">Any time</option>
                                                    <option value="morning">🌅 Morning</option>
                                                    <option value="afternoon">☀️ Afternoon</option>
                                                    <option value="evening">🌙 Evening</option>
                                                </select>
                                            </div>
                                            <div class="col-12 d-flex gap-2">
                                                <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="toggleTaskForm()">Cancel</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif

                        {{-- COMPLETED TASKS HISTORY --}}
                        <div class="mt-4">
                            <div class="task-box-header" style="margin-bottom: 1rem;">
                                ✅ Completed Tasks History ({{ $completedTasks->total() }})
                            </div>
                            <div>
                                @forelse($completedTasks as $task)
                                    <div class="activity-item d-flex gap-3">
                                        <span class="act-icon">{{ $task->activity_icon }}</span>
                                        <div class="flex-grow-1">
                                            <div class="fw-medium" style="font-size:.85rem">{{ $task->subject }}
                                            </div>
                                            <div class="act-meta">
                                                By {{ $task->assignee?->name ?? ($task->creator?->name ?? 'Unknown') }}
                                                &bull; Completed:
                                                {{ $task->completed_at?->format('d M Y, g:i A') ?? $task->updated_at->format('d M Y') }}
                                                @if ($task->scheduled_at)
                                                    &bull; Original Due: {{ $task->scheduled_at->format('d M Y') }}
                                                @endif
                                            </div>
                                            @if ($task->completion_notes)
                                                <div class="act-desc">
                                                    <strong>Completion notes:</strong> {{ $task->completion_notes }}
                                                </div>
                                            @endif
                                            @if ($task->description)
                                                <div class="act-desc mt-1">
                                                    <strong>Task details:</strong> {{ $task->description }}
                                                </div>
                                            @endif
                                        </div>
                                        @if ($canEdit)
                                            <button onclick="undoComplete({{ $task->id }})"
                                                class="undo-complete btn btn-sm btn-outline-secondary"
                                                style="font-size:.7rem">↩️ Undo</button>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-muted text-center py-3" style="font-size:.875rem">No completed
                                        tasks
                                        yet.</div>
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
                                    <div class="text-muted" style="font-size:.72rem">Uploaded
                                        {{ $doc->created_at->format('d M Y') }}</div>
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
        </div>

        {{-- RIGHT sidebar --}}
        <div class="crm-sidebar">
            <div class="crm-section">
                <div class="crm-section-header">Student Details</div>
                <div class="crm-section-body">
                    <div class="sidebar-field">
                        <label>Stage</label>
                        <div class="val">
                            @if ($currentStage)
                                <span
                                    style="background:{{ $currentStage->color }}20;color:{{ $currentStage->color }};border-radius:20px;padding:.2rem .65rem;font-size:.8rem;font-weight:600;display:inline-block">
                                    {{ $currentStage->name }}
                                </span>
                                <div class="text-muted mt-1" style="font-size:.72rem">
                                    {{ $student->days_in_current_stage }} days in this stage
                                </div>
                            @else
                                <span class="text-muted">Not assigned</span>
                            @endif
                        </div>
                    </div>
                    <div class="sidebar-field">
                        <label>Date of Birth</label>
                        <div class="val">{{ $student->dob?->format('d M Y') ?? '—' }} ({{ $student->age }} yrs)
                        </div>
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
                        <div class="val">{{ $student->qualification ?? '—' }}
                            ({{ $student->passed_year ?? '—' }})
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
                            <div class="progress-bar bg-primary" style="width:{{ $student->completion_percentage }}%">
                            </div>
                        </div>
                        <div class="text-muted mt-1" style="font-size:.72rem">
                            {{ $student->completion_percentage }}% — {{ $student->completion_status }}
                        </div>
                    </div>
                    <div class="sidebar-field">
                        <label>Remarks</label>
                        <div class="val small">{{ $student->remarks ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- MODAL FOR MARK AS DONE --}}
    <div id="completeModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                Mark Task as Completed
            </div>
            <form id="completeTaskForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Task: <span id="modalTaskTitle"></span></label>
                    </div>
                    <div class="form-group">
                        <label for="completion_notes">Completion Notes / Remarks *</label>
                        <textarea name="completion_notes" id="completion_notes" rows="4"
                            placeholder="Please add notes about what was accomplished..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="schedule_next">
                            <input type="checkbox" name="schedule_next" id="schedule_next" value="1">
                            Schedule next follow-up
                        </label>
                    </div>
                    <div id="nextTaskFields" style="display:none; margin-top: 1rem;">
                        <div class="form-group">
                            <label>Next Task Summary</label>
                            <input type="text" name="next_task_title" placeholder="e.g., Follow up on application">
                        </div>
                        <div class="form-group">
                            <label>Next Due Date</label>
                            <input type="date" name="next_due_date">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('completeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Mark as Done</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL FOR CANCEL TASK --}}
    <div id="cancelModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                Cancel Task
            </div>
            <form id="cancelTaskForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Task: <span id="cancelTaskTitle"></span></label>
                    </div>
                    <div class="form-group">
                        <label for="cancellation_reason">Cancellation Reason *</label>
                        <textarea name="cancellation_reason" id="cancellation_reason" rows="4"
                            placeholder="Please explain why this task is being cancelled..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('cancelModal')">Go
                        Back</button>
                    <button type="submit" class="btn btn-danger">Cancel Task</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        let currentTaskId = null;

        function switchTab(name, btn) {
            document.querySelectorAll('[id^="tab-"]').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.crm-tab').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + name).style.display = 'block';
            btn.classList.add('active');
            if (name === 'history') loadHistory();
        }

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
                        <div style="display:flex;gap:.75rem;padding:.75rem 0;border-bottom:1px solid #e5e9f2">
                            <span style="font-size:1.1rem">🔄</span>
                            <div>
                                <div style="font-size:.85rem;font-weight:500">
                                    <span style="color:#6b7280">${h.from}</span>
                                    <span style="margin:0 .4rem">→</span>
                                    <span style="color:#1a1f36">${h.to}</span>
                                </div>
                                <div style="font-size:.72rem;color:#6b7280;margin-top:.15rem">
                                    By ${h.changed_by} &bull; ${h.date}
                                    ${h.days_in_previous ? '&bull; ' + h.days_in_previous + ' days in previous stage' : ''}
                                </div>
                                ${h.reason ? '<div style="font-size:.78rem;margin-top:.25rem;color:#4b5563">Reason: ' + h.reason + '</div>' : ''}
                            </div>
                        </div>
                    `).join('');
                });
        }

        function toggleTaskForm() {
            const f = document.getElementById('addTaskForm');
            f.style.display = f.style.display === 'none' ? 'block' : 'none';
        }

        function togglePin(noteId) {
            fetch(`/crm/notes/${noteId}/pin`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }).then(r => r.json()).then(d => {
                if (d.success) window.location.reload();
            });
        }

        function openCompleteModal(taskId, taskTitle) {
            currentTaskId = taskId;
            document.getElementById('modalTaskTitle').innerHTML = taskTitle;
            const form = document.getElementById('completeTaskForm');
            form.action = `/crm/tasks/${taskId}/complete`;
            document.getElementById('completeModal').style.display = 'flex';
        }

        function openCancelModal(taskId, taskTitle) {
            currentTaskId = taskId;
            document.getElementById('cancelTaskTitle').innerHTML = taskTitle;
            const form = document.getElementById('cancelTaskForm');
            form.action = `/crm/tasks/${taskId}/cancel`;
            document.getElementById('cancelModal').style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        document.getElementById('schedule_next')?.addEventListener('change', function() {
            const nextFields = document.getElementById('nextTaskFields');
            if (this.checked) {
                nextFields.style.display = 'block';
            } else {
                nextFields.style.display = 'none';
            }
        });

        function undoComplete(taskId) {
            if (confirm('Undo mark as completed? This will reopen the task.')) {
                fetch(`/crm/tasks/${taskId}/undo`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                }).then(r => r.json()).then(d => {
                    if (d.success) {
                        window.location.reload();
                    } else {
                        alert('Failed to undo. Please try again.');
                    }
                });
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.style.display = 'none';
            }
        }
    </script>
@endpush
