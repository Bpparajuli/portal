{{-- resources/views/crm/show.blade.php --}}
@extends('layouts.crm')

@section('title', $student->full_name . ' — CRM')

@push('styles')
    <style>
        /* CRM-specific components */
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

        .staff-avatar {
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

        /* Stage Pipeline - Arrow/Card Style */
        .stage-pipeline {
            width: 100%;
            overflow: hidden;
            background: #f9fafb;
            padding: 0.5rem 0;
        }

        .stage-track {
            display: flex;
            align-items: stretch;
            gap: 2px;
            width: 100%;
        }

        .stage-wrapper {
            flex: 1;
            min-width: 0;
        }

        .stage-wrapper form {
            margin: 0;
            width: 100%;
            height: 100%;
        }

        .stage-card {
            position: relative;
            width: 100%;
            min-height: 48px;
            border: none;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 6px 14px;
            font-size: 10px;
            font-weight: 600;
            line-height: 1.2;
            text-align: center;
            color: #374151;
            white-space: normal;
            word-break: break-word;
            cursor: pointer;
            clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 50%, calc(100% - 8px) 100%, 0 100%, 8px 50%);
            transition: 0.2s ease;
        }

        .stage-wrapper:first-child .stage-card {
            clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 50%, calc(100% - 8px) 100%, 0 100%);
        }

        .stage-card.current {
            background: var(--warning-gradient);
            border: 1px solid #000000;
            color: #ffffff;
        }

        .stage-card.passed {
            background: #a8ebc4;
            color: #0f6e00;
            font-weight: 800;
        }

        .stage-card.passed::before {
            content: "✓✓";
            position: absolute;
            right: 8px;
            top: 80%;
            transform: translateY(-50%);

            font-size: 11px;
            font-weight: bold;
            color: #059669;
        }

        .stage-card.pending {
            background: #e5e7eb;
            color: #4b5563;
        }

        .stage-title {
            display: block;
            width: 100%;
        }

        .stage-days {
            font-size: 9px;
            opacity: 0.7;
            display: block;
            margin-top: 2px;
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
            flex-direction: column;
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

        /* Avatar/Profile Image */
        .staff-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 4px;
        }

        .staff-avatar-sm {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Modal Styles */
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
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-content.large {
            max-width: 700px;
        }

        .modal-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e5e9f2;
            font-weight: 700;
            font-size: 1.1rem;
            background: #fafbff;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 1.25rem;
        }

        .modal-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid #e5e9f2;
            display: flex;
            gap: .75rem;
            justify-content: flex-end;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #e5e9f2;
            border-radius: 6px;
            font-size: 0.875rem;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .activity-item {
            padding: .85rem 1rem;
            border-bottom: 1px solid #e5e9f2;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item .act-desc {
            font-size: .82rem;
            color: var(--text);
            margin-top: .35rem;
            background: #f8fafc;
            border-radius: 6px;
            padding: .5rem .75rem;
            border-left: 3px solid #e5e9f2;
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
            background: #f8fafc;
            border-radius: 10px;
            padding: 1rem;
            border: 1px dashed #cbd5e1;
        }

        .add-task-form input,
        .add-task-form select,
        .add-task-form textarea {
            font-size: .83rem;
            border: 1px solid #e5e9f2;
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

        .admin-badge {
            background: #e0e7ff;
            color: #3730a3;
            font-size: .65rem;
            border-radius: 20px;
            padding: .15rem .5rem;
            font-weight: 600;
            margin-left: .5rem;
        }

        .btn {
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-size: 0.875rem;
            cursor: pointer;
            border: 1px solid transparent;
            display: inline-block;
            text-align: center;
            text-decoration: none;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .btn-primary {
            background-color: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
            border-color: #ef4444;
        }

        .btn-success {
            background-color: #10b981;
            color: white;
            border-color: #10b981;
        }

        .btn-warning {
            background-color: #f59e0b;
            color: white;
            border-color: #f59e0b;
        }

        .btn-outline-secondary {
            background: transparent;
            border-color: #94a3b8;
            color: #64748b;
        }

        .btn-outline-primary {
            background: transparent;
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .btn-outline-danger {
            background: transparent;
            border-color: #ef4444;
            color: #ef4444;
        }

        .w-100 {
            width: 100%;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        .mt-4 {
            margin-top: 1.5rem;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .gap-3 {
            gap: 1rem;
        }

        .text-muted {
            color: #64748b;
        }

        .text-center {
            text-align: center;
        }

        .py-3 {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }

        .py-4 {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin: -0.5rem;
        }

        .col-md-6 {
            flex: 0 0 50%;
            padding: 0.5rem;
        }

        .col-sm-6 {
            flex: 0 0 50%;
            padding: 0.5rem;
        }

        .col-12 {
            flex: 0 0 100%;
            padding: 0.5rem;
        }

        .d-flex {
            display: flex;
        }

        .flex-grow-1 {
            flex-grow: 1;
        }

        .flex-column {
            flex-direction: column;
        }

        .align-items-center {
            align-items: center;
        }

        .align-items-start {
            align-items: flex-start;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .justify-content-end {
            justify-content: flex-end;
        }

        .ms-auto {
            margin-left: auto;
        }

        .ms-2 {
            margin-left: 0.5rem;
        }

        .reschedule-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
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
            @if (auth()->user()->is_admin)
                <span class="admin-badge">👑 Admin</span>
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
                    @if ($student->agent && $student->agent->business_logo)
                        <img src="{{ Storage::url($student->agent->business_logo) }}" alt="Logo" width="30"
                            height="30" class="rounded object-fit-cover shadow-sm border">
                    @endif
                    Student of: <strong>{{ $student->agent?->name ?? '—' }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Stage Pipeline - Arrow/Card Style --}}
    <div class="stage-pipeline">
        <div class="stage-track">
            @foreach ($stages as $index => $stg)
                @php
                    $isCurrent = $currentStage?->id === $stg->id;
                    $isPassed = $currentStage && $stg->stage_order < $currentStage->stage_order;
                    $statusClass = $isCurrent ? 'current' : ($isPassed ? 'passed' : 'pending');
                @endphp
                <div class="stage-wrapper">
                    @if ($canEdit)
                        <form action="{{ route('crm.student.stage', $student) }}" method="POST">
                            @csrf
                            <input type="hidden" name="new_stage_id" value="{{ $stg->id }}">
                            <button type="submit" class="stage-card {{ $statusClass }}"
                                onclick="return confirm('Move to \'{{ $stg->name }}\'?')">
                                <span class="stage-title">{{ $stg->name }}</span>
                            </button>
                        </form>
                    @else
                        <div class="stage-card {{ $statusClass }}">
                            <span class="stage-title">{{ $stg->name }}</span>
                            @if ($isCurrent)
                                <span class="stage-days">{{ $student->days_in_current_stage ?? 0 }}d</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Main body --}}
    <div class="crm-body">

        {{-- LEFT --}}
        <div>

            {{-- Notes Section --}}
            <div class="crm-section">
                <div class="crm-section-header">📝 Internal Note</div>
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
                        <div class="text-muted text-center py-3">No notes yet.</div>
                    @endif

                    <div id="noteForm">
                        <form action="{{ route('crm.notes.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $student->id }}">
                            <input type="hidden" name="type" value="internal">
                            <textarea name="content" rows="3" class="form-control mb-2" placeholder="Write an internal note…" required></textarea>
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div>
                                    <input type="checkbox" name="is_pinned" value="1" id="pin_note">
                                    <label for="pin_note">Pin this note</label>
                                </div>
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
                            {{-- OVERDUE TASKS --}}
                            @if ($dueTasks->count() > 0)
                                <div class="task-box">
                                    <div class="task-box-header" style="color: #ef4444;">⚠️ Overdue Tasks
                                        ({{ $dueTasks->count() }})</div>
                                    @foreach ($dueTasks as $task)
                                        @include('crm.partials.task-item', [
                                            'task' => $task,
                                            'type' => 'overdue',
                                        ])
                                    @endforeach
                                </div>
                            @endif

                            {{-- TODAY'S TASKS --}}
                            @if ($todayTasks->count() > 0)
                                <div class="task-box">
                                    <div class="task-box-header" style="color: #10b981;">📅 Today's Tasks
                                        ({{ $todayTasks->count() }})</div>
                                    @foreach ($todayTasks as $task)
                                        @include('crm.partials.task-item', [
                                            'task' => $task,
                                            'type' => 'today',
                                        ])
                                    @endforeach
                                </div>
                            @endif

                            {{-- UPCOMING TASKS --}}
                            @if ($plannedTasks->count() > 0)
                                <div class="task-box">
                                    <div class="task-box-header" style="color: #3b82f6;">🗓 Upcoming Tasks
                                        ({{ $plannedTasks->count() }})</div>
                                    @foreach ($plannedTasks as $task)
                                        @include('crm.partials.task-item', [
                                            'task' => $task,
                                            'type' => 'upcoming',
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

                        {{-- Add new task button --}}
                        @if ($canEdit)
                            <div class="mt-3">
                                <button class="btn btn-sm btn-outline-primary w-100" onclick="openNewTaskModal()">
                                    + Create New Task
                                </button>
                            </div>
                        @endif

                        {{-- COMPLETED & CANCELLED TASKS HISTORY --}}
                        <div class="mt-4">
                            <div class="task-box-header">✅ Completed & Cancelled Tasks ({{ $completedTasks->total() }})
                            </div>
                            <div>
                                @forelse($completedTasks as $task)
                                    <div class="activity-item d-flex gap-3">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold" style="font-size:.85rem">
                                                {{ $task->subject }}
                                                @if ($task->status === 'cancelled')
                                                    <span class="badge"
                                                        style="background:#ef4444; color:white; padding:2px 6px; border-radius:4px; font-size:10px;">CANCELLED</span>
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
                                            <div class="text-muted mt-1" style="font-size:.72rem">
                                                @if ($task->status === 'completed')
                                                    Completed on: {{ $task->completed_at?->format('d M Y, g:i A') }} by
                                                    {{ $task->completedBy?->name ?? 'Unknown' }}
                                                @elseif($task->status === 'cancelled')
                                                    Cancelled on: {{ $task->cancelled_at?->format('d M Y, g:i A') }} by
                                                    {{ $task->cancelledBy?->name ?? 'Unknown' }}
                                                @endif
                                            </div>
                                        </div>
                                        @if ($canEdit && $task->status === 'completed')
                                            <button onclick="undoComplete({{ $task->id }})"
                                                class="btn btn-sm btn-outline-secondary">↩️ Undo</button>
                                        @endif
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
                        <label>Remarks</label>
                        <div class="val small">{{ $student->remarks ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MODAL 1: CREATE NEW TASK --}}
    {{-- ============================================ --}}
    <div id="newTaskModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <span>➕ Create New Task</span>
                <button onclick="closeModal('newTaskModal')"
                    style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            <form action="{{ route('crm.tasks.store') }}" method="POST">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->id }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Task Title *</label>
                        <input type="text" name="title" class="form-control" required
                            placeholder="e.g., Call student for follow-up">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Task details..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Task Type *</label>
                                <select name="task_type" class="form-select" required>
                                    <option value="call">📞 Call</option>
                                    <option value="email">✉️ Email</option>
                                    <option value="whatsapp">💬 WhatsApp</option>
                                    <option value="meeting">👥 Meeting</option>
                                    <option value="follow_up">⏰ Follow Up</option>
                                    <option value="counseling">🎓 Counseling</option>
                                    <option value="todo">✅ To Do</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Due Date</label>
                                <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Time Slot</label>
                                <select name="time_slot" class="form-select">
                                    <option value="">Any time</option>
                                    <option value="morning">🌅 Morning</option>
                                    <option value="afternoon">☀️ Afternoon</option>
                                    <option value="evening">🌙 Evening</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Priority</label>
                                <select name="priority" class="form-select">
                                    <option value="low">🟢 Low</option>
                                    <option value="medium" selected>🟡 Medium</option>
                                    <option value="high">🔴 High</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Assign To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Myself ({{ auth()->user()->name }})</option>
                            @foreach ($assignableUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="closeModal('newTaskModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Task</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MODAL 2: COMPLETE TASK (WITH OPTION TO CREATE NEXT) --}}
    {{-- ============================================ --}}
    <div id="completeModal" class="modal-overlay">
        <div class="modal-content large">
            <div class="modal-header">
                <span>✅ Complete Task</span>
                <button onclick="closeModal('completeModal')"
                    style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            <form id="completeTaskForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Task: <strong><span id="completeTaskTitle"></span></strong></label>
                    </div>

                    <div class="form-group">
                        <label>Completion Notes / Remarks *</label>
                        <textarea name="completion_note" id="completion_note" rows="3" class="form-control" required
                            placeholder="What was accomplished?"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Reassign this task (Optional)</label>
                        <select name="reassign_to" id="reassign_to" class="form-select">
                            <option value="">Keep current assignee</option>
                            @foreach ($assignableUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>After completion:</label>
                        <div class="d-flex gap-3">
                            <label class="d-flex align-items-center gap-2">
                                <input type="radio" name="completion_action" value="just_complete" checked>
                                <span>✓ Just mark as Done</span>
                            </label>
                            <label class="d-flex align-items-center gap-2">
                                <input type="radio" name="completion_action" value="create_next"
                                    id="create_next_radio">
                                <span>➕ Create a New Follow-up Task</span>
                            </label>
                        </div>
                    </div>

                    {{-- New Task Section (shown only when create_next is selected) --}}
                    <div id="newTaskSection"
                        style="display:none; margin-top: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <div class="form-group">
                            <label style="font-weight: 600;">📋 New Task Details</label>
                        </div>
                        <div class="form-group">
                            <label>Task Title *</label>
                            <input type="text" name="next_task_title" id="next_task_title" class="form-control"
                                placeholder="e.g., Follow up on application">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="next_task_description" id="next_task_description" rows="2" class="form-control"
                                placeholder="Additional details..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Task Type *</label>
                                    <select name="next_task_type" id="next_task_type" class="form-select">
                                        <option value="follow_up">⏰ Follow Up</option>
                                        <option value="call">📞 Call</option>
                                        <option value="email">✉️ Email</option>
                                        <option value="whatsapp">💬 WhatsApp</option>
                                        <option value="meeting">👥 Meeting</option>
                                        <option value="counseling">🎓 Counseling</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Due Date *</label>
                                    <input type="date" name="next_due_date" id="next_due_date" class="form-control"
                                        value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Time Slot</label>
                                    <select name="next_time_slot" id="next_time_slot" class="form-select">
                                        <option value="">Any time</option>
                                        <option value="morning">🌅 Morning</option>
                                        <option value="afternoon">☀️ Afternoon</option>
                                        <option value="evening">🌙 Evening</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Priority</label>
                                    <select name="next_priority" id="next_priority" class="form-select">
                                        <option value="low">🟢 Low</option>
                                        <option value="medium" selected>🟡 Medium</option>
                                        <option value="high">🔴 High</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Assign To</label>
                            <select name="next_assigned_to" id="next_assigned_to" class="form-select">
                                <option value="">Same as above</option>
                                @foreach ($assignableUsers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->role) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="closeModal('completeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="completeSubmitBtn">✓ Complete Task</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MODAL 3: RESCHEDULE TASK --}}
    {{-- ============================================ --}}
    <div id="rescheduleModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <span>📅 Reschedule Task</span>
                <button onclick="closeModal('rescheduleModal')"
                    style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            <form id="rescheduleTaskForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Task: <strong><span id="rescheduleTaskTitle"></span></strong></label>
                    </div>

                    <div class="form-group">
                        <label>Quick Reschedule Options</label>
                        <div class="reschedule-buttons">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick="setRescheduleDate(1)">Tomorrow</button>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick="setRescheduleDate(3)">In 3 days</button>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick="setRescheduleDate(7)">Next week</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Or choose specific date</label>
                        <input type="date" name="due_date" id="reschedule_due_date" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Time Slot</label>
                        <select name="time_slot" id="reschedule_time_slot" class="form-select">
                            <option value="">Any time</option>
                            <option value="morning">🌅 Morning</option>
                            <option value="afternoon">☀️ Afternoon</option>
                            <option value="evening">🌙 Evening</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Reassign to (Optional)</label>
                        <select name="assigned_to" id="reschedule_assigned_to" class="form-select">
                            <option value="">Keep current assignee</option>
                            @foreach ($assignableUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Reschedule Reason (Optional)</label>
                        <textarea name="reschedule_reason" rows="2" class="form-control"
                            placeholder="Why is this task being rescheduled?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="closeModal('rescheduleModal')">Cancel</button>
                    <button type="submit" class="btn btn-warning">Reschedule Task</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MODAL 4: CANCEL TASK --}}
    {{-- ============================================ --}}
    <div id="cancelModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <span>❌ Cancel Task</span>
                <button onclick="closeModal('cancelModal')"
                    style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            <form id="cancelTaskForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Task: <strong><span id="cancelTaskTitle"></span></strong></label>
                    </div>
                    <div class="form-group">
                        <label>Cancellation Reason *</label>
                        <textarea name="cancellation_reason" id="cancellation_reason" rows="3" class="form-control" required
                            placeholder="Why is this task being cancelled?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="closeModal('cancelModal')">Go
                        Back</button>
                    <button type="submit" class="btn btn-danger">Cancel Task</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MODAL 5: EDIT TASK (Admin Only) --}}
    {{-- ============================================ --}}
    <div id="editTaskModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <span>✏️ Edit Task (Admin Only)</span>
                <button onclick="closeModal('editTaskModal')"
                    style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            <form id="editTaskForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="task_id" id="edit_task_id">
                    <div class="form-group">
                        <label>Task Title *</label>
                        <input type="text" name="title" id="edit_task_title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="edit_task_description" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Task Type</label>
                                <select name="task_type" id="edit_task_type" class="form-select">
                                    <option value="call">📞 Call</option>
                                    <option value="email">✉️ Email</option>
                                    <option value="whatsapp">💬 WhatsApp</option>
                                    <option value="meeting">👥 Meeting</option>
                                    <option value="follow_up">⏰ Follow Up</option>
                                    <option value="counseling">🎓 Counseling</option>
                                    <option value="todo">✅ To Do</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Due Date</label>
                                <input type="date" name="due_date" id="edit_due_date" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Time Slot</label>
                                <select name="time_slot" id="edit_time_slot" class="form-select">
                                    <option value="">Any time</option>
                                    <option value="morning">🌅 Morning</option>
                                    <option value="afternoon">☀️ Afternoon</option>
                                    <option value="evening">🌙 Evening</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Priority</label>
                                <select name="priority" id="edit_priority" class="form-select">
                                    <option value="low">🟢 Low</option>
                                    <option value="medium">🟡 Medium</option>
                                    <option value="high">🔴 High</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Assign To</label>
                        <select name="assigned_to" id="edit_assigned_to" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach ($assignableUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="closeModal('editTaskModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Task</button>
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
                            <div style="font-size:.85rem;font-weight:500">${h.from} → ${h.to}</div>
                            <div style="font-size:.72rem;color:#6b7280">By ${h.changed_by} &bull; ${h.date}</div>
                        </div>
                    </div>
                `).join('');
                });
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

        // ============================================
        // MODAL FUNCTIONS
        // ============================================

        function openNewTaskModal() {
            document.getElementById('newTaskModal').style.display = 'flex';
        }

        function openCompleteModal(taskId, taskTitle) {
            currentTaskId = taskId;
            document.getElementById('completeTaskTitle').innerHTML = taskTitle;
            document.getElementById('completeTaskForm').action = `/crm/tasks/${taskId}/complete`;

            // Reset form
            document.getElementById('completion_note').value = '';
            document.getElementById('reassign_to').value = '';
            document.querySelector('input[name="completion_action"][value="just_complete"]').checked = true;
            document.getElementById('newTaskSection').style.display = 'none';
            document.getElementById('completeSubmitBtn').innerHTML = '✓ Complete Task';

            // Reset next task fields
            document.getElementById('next_task_title').value = '';
            document.getElementById('next_task_description').value = '';
            document.getElementById('next_task_type').value = 'follow_up';
            document.getElementById('next_due_date').value = getDefaultDueDate();
            document.getElementById('next_time_slot').value = '';
            document.getElementById('next_priority').value = 'medium';
            document.getElementById('next_assigned_to').value = '';

            document.getElementById('completeModal').style.display = 'flex';
        }

        function openRescheduleModal(taskId, taskTitle) {
            currentTaskId = taskId;
            document.getElementById('rescheduleTaskTitle').innerHTML = taskTitle;
            document.getElementById('rescheduleTaskForm').action = `/crm/tasks/${taskId}/reschedule`;

            // Reset form
            document.getElementById('reschedule_due_date').value = '';
            document.getElementById('reschedule_time_slot').value = '';
            document.getElementById('reschedule_assigned_to').value = '';

            document.getElementById('rescheduleModal').style.display = 'flex';
        }

        function openCancelModal(taskId, taskTitle) {
            currentTaskId = taskId;
            document.getElementById('cancelTaskTitle').innerHTML = taskTitle;
            document.getElementById('cancelTaskForm').action = `/crm/tasks/${taskId}/cancel`;
            document.getElementById('cancellation_reason').value = '';
            document.getElementById('cancelModal').style.display = 'flex';
        }

        function openEditModal(taskId) {
            @if (!auth()->user()->is_admin)
                alert('Only administrators can edit tasks.');
                return;
            @endif

            fetch(`/crm/tasks/${taskId}/edit-data`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(task => {
                    document.getElementById('edit_task_id').value = task.id;
                    document.getElementById('edit_task_title').value = task.subject;
                    document.getElementById('edit_task_description').value = task.description || '';
                    document.getElementById('edit_task_type').value = task.activity_type;
                    document.getElementById('edit_due_date').value = task.scheduled_at || '';
                    document.getElementById('edit_time_slot').value = task.priority_time_slot || '';
                    document.getElementById('edit_priority').value = task.priority;
                    document.getElementById('edit_assigned_to').value = task.assigned_to || '';
                    document.getElementById('editTaskForm').action = `/crm/tasks/${taskId}`;
                    document.getElementById('editTaskModal').style.display = 'flex';
                })
                .catch(error => {
                    alert('Failed to load task details.');
                });
        }

        function setRescheduleDate(days) {
            const date = new Date();
            date.setDate(date.getDate() + days);
            document.getElementById('reschedule_due_date').value = date.toISOString().split('T')[0];
        }

        function getDefaultDueDate() {
            const date = new Date();
            date.setDate(date.getDate() + 7);
            return date.toISOString().split('T')[0];
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Toggle new task section in complete modal
        document.querySelectorAll('input[name="completion_action"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const newTaskSection = document.getElementById('newTaskSection');
                const submitBtn = document.getElementById('completeSubmitBtn');
                if (this.value === 'create_next') {
                    newTaskSection.style.display = 'block';
                    submitBtn.innerHTML = '✓ Complete & Create Next Task';
                    // Add required attributes
                    document.getElementById('next_task_title').setAttribute('required', 'required');
                    document.getElementById('next_due_date').setAttribute('required', 'required');
                } else {
                    newTaskSection.style.display = 'none';
                    submitBtn.innerHTML = '✓ Complete Task';
                    // Remove required attributes
                    document.getElementById('next_task_title').removeAttribute('required');
                    document.getElementById('next_due_date').removeAttribute('required');
                }
            });
        });

        function undoComplete(taskId) {
            if (confirm('Undo mark as completed? This will reopen the task.')) {
                fetch(`/crm/tasks/${taskId}/undo`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) window.location.reload();
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
