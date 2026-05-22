@extends('layouts.crm')

@section('title', 'CRM Pipeline')

@push('styles')
    <style>
        :root {
            --crm-bg: #f4f6fb;
            --crm-card: #ffffff;
            --crm-border: #e5e9f2;
            --crm-text: #1a1f36;
            --crm-primary: #4f46e5;
            --crm-danger: #ef4444;
            --crm-success: #10b981;
        }

        body {
            background: var(--crm-bg);
        }

        .crm-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .crm-stat {
            background: var(--crm-card);
            border: 1px solid var(--crm-border);
            border-radius: 10px;
            padding: .75rem 1.25rem;
            display: flex;
            align-items: center;
            gap: .75rem;
            flex: 1;
            min-width: 140px;
            text-decoration: none;
        }

        .crm-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .crm-stat .stat-icon {
            font-size: 1.4rem;
        }

        .crm-stat .stat-num {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--crm-text);
        }

        .crm-stat .stat-lbl {
            font-size: .75rem;
            color: #6b7280;
        }

        .crm-toolbar {
            background: var(--crm-card);
            border: 1px solid var(--crm-border);
            border-radius: 10px;
            padding: .6rem 1rem;
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
        }

        .crm-toolbar .search-wrap {
            position: relative;
            flex: 1;
            min-width: 200px;
        }

        .crm-toolbar .search-wrap input {
            width: 100%;
            padding: .45rem .9rem .45rem 2.2rem;
            border: 1px solid var(--crm-border);
            border-radius: 8px;
        }

        .crm-toolbar .search-wrap .si {
            position: absolute;
            left: .7rem;
            top: 50%;
            transform: translateY(-50%);
        }

        .crm-toolbar select,
        .crm-toolbar .view-btn {
            padding: .4rem .75rem;
            border: 1px solid var(--crm-border);
            border-radius: 8px;
            background: var(--crm-bg);
            cursor: pointer;
        }

        .view-btn.active {
            background: var(--crm-primary);
            color: #fff;
        }

        .top-scroll {
            overflow-x: auto;
            overflow-y: hidden;
            height: 18px;
        }

        .top-scroll-inner {
            height: 1px;
        }

        .kanban-board-wrapper {
            overflow-x: auto;
        }

        .kanban-board {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            min-width: max-content;
        }

        .kanban-col {
            flex: 0 0 250px;
            background: var(--crm-card);
            border: 1px solid var(--crm-border);
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 200px);
        }

        .kanban-col-header {
            padding: .75rem 1rem;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid var(--crm-border);
        }

        .kanban-col-body {
            padding: .5rem;
            display: flex;
            flex-direction: column;
            gap: .5rem;
            overflow-y: auto;
            flex: 1;
            min-height: 200px;
            max-height: calc(100vh - 300px);
        }

        .kanban-col-body.drag-over {
            background-color: rgba(79, 70, 229, 0.08);
        }

        .student-card {
            background: #fff;
            border: 1px solid var(--crm-border);
            border-radius: 10px;
            padding: .75rem;
            cursor: grab;
            transition: all .15s;
        }

        .student-card:active {
            cursor: grabbing;
        }

        .student-card.dragging {
            opacity: 0.5;
        }

        .student-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, .1);
            transform: translateY(-1px);
        }

        .student-card .sc-name {
            font-weight: 600;
            font-size: .875rem;
        }

        .student-card .sc-phone {
            font-size: .75rem;
            color: #6b7280;
            margin-top: .2rem;
        }

        .student-card .sc-followup {
            margin-top: .5rem;
            font-size: .72rem;
            padding: .3rem .5rem;
            border-radius: 6px;
        }

        .sc-followup.overdue {
            background: #fef2f2;
            color: var(--crm-danger);
        }

        .sc-followup.today {
            background: #fffbeb;
            color: #f59e0b;
        }

        .sc-followup.upcoming {
            background: #f0fdf4;
            color: var(--crm-success);
        }

        .sc-followup.none {
            background: #f9fafb;
            color: #6b7280;
        }

        .sc-tags-section {
            margin-top: 0.5rem;
        }

        .sc-tags-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.3rem;
            margin-bottom: 0.3rem;
        }

        .sc-tag {
            font-size: 0.7rem;
            background: #f0f2ff;
            color: var(--crm-primary);
            border-radius: 12px;
            padding: 0.2rem 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .remove-tag-btn {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            padding: 0;
            margin-left: 0.2rem;
        }

        .remove-tag-btn:hover {
            color: var(--crm-danger);
        }

        .add-tag-btn {
            font-size: 0.65rem;
            background: transparent;
            border: 1px dashed var(--crm-border);
            border-radius: 12px;
            padding: 0.2rem 0.5rem;
            cursor: pointer;
            color: #6b7280;
            width: 100%;
            text-align: center;
        }

        .add-tag-btn:hover {
            background: var(--crm-primary);
            color: white;
        }

        .tag-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10001;
            align-items: center;
            justify-content: center;
        }

        .tag-modal.active {
            display: flex;
        }

        .tag-modal-content {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            width: 90%;
            max-width: 400px;
        }

        .tag-modal-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .tag-modal-close {
            cursor: pointer;
            font-size: 1.5rem;
        }

        .tag-input-group input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--crm-border);
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .suggested-tags-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .suggested-tag {
            font-size: 0.75rem;
            background: #f0f2ff;
            color: var(--crm-primary);
            border-radius: 12px;
            padding: 0.2rem 0.6rem;
            cursor: pointer;
        }

        .suggested-tag:hover {
            background: var(--crm-primary);
            color: white;
        }

        .modal-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .modal-buttons button {
            flex: 1;
            padding: 0.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 5px;
            margin-top: 8px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 20px;
            color: #ccc;
            cursor: pointer;
        }

        .star-rating input:checked~label,
        .star-rating label:hover,
        .star-rating label:hover~label {
            color: #ffc107;
        }

        .drag-loading {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .drag-loading::after {
            content: "Processing...";
            background: white;
            padding: 1rem 2rem;
            border-radius: 8px;
        }

        .toast-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            z-index: 10000;
            animation: slideIn 0.3s ease-out;
        }

        .toast-notification.error {
            background: #ef4444;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Floating Action Button */
        .fab-add-student {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #4f46e5;
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            transition: all 0.3s;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .fab-add-student:hover {
            background: #4338ca;
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }

        .search-wrap {
            display: flex;
            align-items: center;
            gap: 8px;

            width: 100%;

            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 10px;

            padding: 6px 10px;
        }

        .search-type {
            border: none;
            outline: none;

            background: #f3f4f6;

            padding: 7px 10px;

            border-radius: 8px;

            font-size: 12px;
            font-weight: 600;

            color: #374151;

            cursor: pointer;
        }

        .search-wrap input {
            flex: 1;

            border: none;
            outline: none;

            background: transparent;

            font-size: 13px;
        }

        .search-btn {
            border: none;
            background: #14b8a6;

            color: white;

            width: 34px;
            height: 34px;

            border-radius: 8px;

            cursor: pointer;

            font-size: 14px;

            display: flex;
            align-items: center;
            justify-content: center;

            transition: .2s;
        }

        .search-btn:hover {
            transform: scale(1.05);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4 class="fw-bold">
                    <a href="{{ route('crm.dashboard') }}">
                        CRM Pipeline
                </h4></a>
                <p class="text-muted small mb-0">Students assigned to you or your team</p>
            </div>
            <div>
                @if (auth()->user()->is_admin)
                    <a href="{{ route('crm.configure.index') }}" class="btn btn-sm btn-outline-secondary">⚙️ Configure</a>
                @endif
                <a href="{{ route('crm.export') }}?{{ http_build_query(request()->query()) }}"
                    class="btn btn-sm btn-outline-secondary">⬇️ Export</a>
            </div>
        </div>

        <div class="crm-stats">
            <a href="{{ route('crm.dashboard', array_merge(request()->query(), ['stat_filter' => 'total'])) }}"
                class="crm-stat">
                <span class="stat-icon">👥</span>
                <div>
                    <div class="stat-num">{{ $stats['total'] }}</div>
                    <div class="stat-lbl">My Students</div>
                </div>
            </a>
            <a href="{{ route('crm.dashboard', array_merge(request()->query(), ['activity_filter' => 'today'])) }}"
                class="crm-stat">
                <span class="stat-icon">📅</span>
                <div>
                    <div class="stat-num">{{ $stats['today'] }}</div>
                    <div class="stat-lbl">Today's Tasks</div>
                </div>
            </a>
            <a href="{{ route('crm.dashboard', array_merge(request()->query(), ['activity_filter' => 'overdue'])) }}"
                class="crm-stat">
                <span class="stat-icon">⚠️</span>
                <div>
                    <div class="stat-num text-danger">{{ $stats['overdue'] }}</div>
                    <div class="stat-lbl">Overdue</div>
                </div>
            </a>
            <a href="{{ route('crm.dashboard', array_merge(request()->query(), ['activity_filter' => 'upcoming'])) }}"
                class="crm-stat">
                <span class="stat-icon">🔜</span>
                <div>
                    <div class="stat-num text-success">{{ $stats['upcoming'] }}</div>
                    <div class="stat-lbl">Upcoming</div>
                </div>
            </a>
        </div>

        <form method="GET" id="filterForm">
            <input type="hidden" name="view" value="{{ $view }}">
            <div class="crm-toolbar">
                <form method="GET" action="{{ route('crm.dashboard') }}" id="searchForm">

                    <div class="search-wrap">

                        {{-- Search Type --}}
                        <select name="search_type" class="search-type" id="searchType">

                            <option value="all" {{ request('search_type') == 'all' ? 'selected' : '' }}>
                                All
                            </option>

                            <option value="name" {{ request('search_type') == 'name' ? 'selected' : '' }}>
                                Name
                            </option>

                            <option value="phone_number" {{ request('search_type') == 'phone_number' ? 'selected' : '' }}>
                                Phone
                            </option>

                            <option value="email" {{ request('search_type') == 'email' ? 'selected' : '' }}>
                                Email
                            </option>

                            <option value="tag" {{ request('search_type') == 'tag' ? 'selected' : '' }}>
                                Tag
                            </option>

                            <option value="preferred_country"
                                {{ request('search_type') == 'preferred_country' ? 'selected' : '' }}>
                                Country
                            </option>

                            <option value="university" {{ request('search_type') == 'university' ? 'selected' : '' }}>
                                University
                            </option>

                            <option value="degree" {{ request('search_type') == 'degree' ? 'selected' : '' }}>
                                Degree
                            </option>

                        </select>

                        {{-- Search Input --}}
                        <input type="text" name="search" id="searchInput" placeholder="Search students..."
                            value="{{ request('search') }}">

                        {{-- Search Button --}}
                        <button type="submit" class="search-btn">
                            🔍
                        </button>

                    </div>

                </form>
                <script>
                    // Auto submit on dropdown change
                    document.getElementById('searchType')
                        .addEventListener('change', function() {

                            document.getElementById('searchForm').submit();

                        });
                </script>
                <select name="stage_id" onchange="this.form.submit()">
                    <option value="">All Stages</option>
                    @foreach ($stages as $stage)
                        <option value="{{ $stage->id }}" @selected(request('stage_id') == $stage->id)>{{ $stage->name }}</option>
                    @endforeach
                </select>
                @if ($assignees->count())
                    <select name="assignee_id" onchange="this.form.submit()">
                        <option value="">Team Members</option>
                        @foreach ($assignees as $a)
                            <option value="{{ $a->id }}" @selected(request('assignee_id') == $a->id)>{{ $a->name }}</option>
                        @endforeach
                    </select>
                @endif
                <select name="activity_filter" onchange="this.form.submit()">
                    <option value="">All Activity</option>
                    <option value="overdue" @selected(request('activity_filter') == 'overdue')>Has Overdue</option>
                    <option value="today" @selected(request('activity_filter') == 'today')>Active Today</option>
                    <option value="upcoming" @selected(request('activity_filter') == 'upcoming')>Has Upcoming</option>
                </select>
                <div class="d-flex gap-1">
                    <button type="button" onclick="setView('kanban')"
                        class="view-btn {{ $view == 'kanban' ? 'active' : '' }}">Kanban</button>
                    <button type="button" onclick="setView('list')"
                        class="view-btn {{ $view == 'list' ? 'active' : '' }}">List</button>
                    <button type="button" onclick="setView('table')"
                        class="view-btn {{ $view == 'table' ? 'active' : '' }}">Table</button>
                </div>
                @if (request()->hasAny(['search', 'stage_id', 'assignee_id', 'activity_filter']))
                    <a href="{{ route('crm.dashboard') }}" class="btn btn-sm btn-outline-danger">Clear</a>
                @endif
            </div>
        </form>

        @if ($view === 'kanban')
            <div class="top-scroll">
                <div class="top-scroll-inner"></div>
            </div>
            <div class="kanban-board-wrapper">
                <div class="kanban-board" id="kanbanBoard">
                    @foreach ($stages as $stage)
                        @php $colStudents = isset($students[$stage->id]) ? $students[$stage->id] : collect(); @endphp
                        <div class="kanban-col" data-stage-id="{{ $stage->id }}">
                            <div class="kanban-col-header" style="background:{{ $stage->color }}20;">
                                <div><span
                                        style="background:{{ $stage->color }};width:10px;height:10px;border-radius:50%;display:inline-block;margin-right:.4rem"></span>{{ $stage->name }}
                                </div>
                                <span class="stage-count">{{ $colStudents->count() }}</span>
                            </div>
                            <div class="kanban-col-body" data-stage-id="{{ $stage->id }}">
                                @forelse($colStudents as $student)
                                    @php
                                        $overdue = $student->overdueActivities->count();
                                        $upcoming = $student->upcomingActivities->count();
                                        $task = $student->pendingActivities->first();
                                        if ($overdue) {
                                            $fClass = 'overdue';
                                            $fLabel = "⚠️ {$overdue} overdue";
                                        } elseif ($task && $task->scheduled_at?->isToday()) {
                                            $fClass = 'today';
                                            $fLabel = '📅 Today';
                                        } elseif ($upcoming) {
                                            $fClass = 'upcoming';
                                            $fLabel = '📅 ' . ($task?->scheduled_at?->format('d M') ?? 'Upcoming');
                                        } else {
                                            $fClass = 'none';
                                            $fLabel = '— no tasks';
                                        }
                                    @endphp
                                    <div class="student-card" data-student-id="{{ $student->id }}"
                                        data-student-name="{{ $student->full_name }}" draggable="true">
                                        <a href="{{ route('crm.student.show', $student) }}"
                                            style="text-decoration:none;color:inherit;">
                                            <div class="sc-name">{{ $student->full_name }}</div>
                                            <div class="sc-phone">📞 {{ $student->phone_number ?? '—' }}</div>
                                            <div class="sc-followup {{ $fClass }}">{{ $fLabel }}</div>
                                            @if (auth()->user()->is_admin || auth()->user()->is_admin_staff || auth()->user()->is_agent)
                                                <div class="staff-info"
                                                    style="font-size:.7rem;color:#6b7280;margin-top:.2rem;">
                                                    <img src="{{ $student->agent?->business_logo ?? asset('images/default-avatar.png') }}"
                                                        alt="Agent Avatar" class="img-fluid rounded-circle"
                                                        style="width: 20px; height: 20px; object-fit: cover;">
                                                    👤 {{ $student->agent?->name ?? 'Unassigned' }}
                                                </div>
                                            @endif
                                        </a>
                                        <p> {{ $student->preferred_country ?? '—' }}</p>
                                        <div class="sc-tags-section">
                                            <div class="sc-tags-list">
                                                @if ($student->tags && is_array($student->tags))
                                                    @foreach ($student->tags as $tag)
                                                        <span class="sc-tag">🏷️ {{ $tag }}<button
                                                                type="button" class="remove-tag-btn"
                                                                data-tag="{{ $tag }}">×</button></span>
                                                    @endforeach
                                                @endif
                                            </div>
                                            <button type="button" class="add-tag-btn"
                                                data-student-id="{{ $student->id }}">+
                                                Add tag</button>
                                        </div>
                                        <form class="rating-form"
                                            action="{{ route('crm.dashboard.updateRating', $student->id) }}"
                                            method="POST">
                                            @csrf @method('PUT')
                                            <div class="star-rating">
                                                <input type="radio" name="rating" id="star3_{{ $student->id }}"
                                                    value="3"
                                                    {{ ($student->rating ?? '') == 3 ? 'checked' : '' }}><label
                                                    for="star3_{{ $student->id }}">★</label>
                                                <input type="radio" name="rating" id="star2_{{ $student->id }}"
                                                    value="2"
                                                    {{ ($student->rating ?? '') == 2 ? 'checked' : '' }}><label
                                                    for="star2_{{ $student->id }}">★</label>
                                                <input type="radio" name="rating" id="star1_{{ $student->id }}"
                                                    value="1"
                                                    {{ ($student->rating ?? '') == 1 ? 'checked' : '' }}><label
                                                    for="star1_{{ $student->id }}">★</label>
                                            </div>
                                        </form>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3">No students</div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif($view === 'list')
            @foreach ($students as $student)
                <div class="bg-white border rounded p-3 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <a href="{{ route('crm.student.show', $student) }}"
                                class="fw-bold text-decoration-none">{{ $student->full_name }}</a>
                            <div class="small text-muted">📞 {{ $student->phone_number ?? '—' }}</div>
                        </div>
                        <a href="{{ route('crm.student.show', $student) }}"
                            class="btn btn-sm btn-outline-primary">View</a>
                    </div>
                </div>
            @endforeach
            {{ $students->withQueryString()->links() }}
        @else
            <div class="bg-white border rounded">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Stage</th>
                            <th>Phone</th>
                            <th>Staff</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            <tr>
                                <td>{{ $student->full_name }}</td>
                                <td>{{ $student->currentStage?->name ?? '—' }}</td>
                                <td>{{ $student->phone_number ?? '—' }}</td>
                                <td>{{ $student->agent?->name ?? '—' }}</td>
                                <td><a href="{{ route('crm.student.show', $student) }}"
                                        class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $students->withQueryString()->links() }}
        @endif
    </div>

    <div class="drag-loading" id="dragLoading"></div>

    <!-- Floating Action Button -->
    <button class="fab-add-student" data-bs-toggle="modal" data-bs-target="#addStudentModal">
        <i class="fas fa-plus"></i>
    </button>
@endsection

@push('scripts')
    <script>
        //scrollbar for kanban header
        document.addEventListener('DOMContentLoaded', function() {
            const topScroll = document.querySelector('.top-scroll');
            const topInner = document.querySelector('.top-scroll-inner');
            const boardWrapper = document.querySelector('.kanban-board-wrapper');
            const board = document.querySelector('.kanban-board');

            function syncWidth() {
                if (topInner && board) {
                    topInner.style.width = board.scrollWidth + 'px';
                }
            }

            syncWidth();

            if (topScroll && boardWrapper) {
                topScroll.addEventListener('scroll', () => {
                    boardWrapper.scrollLeft = topScroll.scrollLeft;
                });

                boardWrapper.addEventListener('scroll', () => {
                    topScroll.scrollLeft = boardWrapper.scrollLeft;
                });
            }

            window.addEventListener('resize', syncWidth);
        });

        let searchTimer;

        function debounceSubmit() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => document.getElementById('filterForm').submit(), 450);
        }
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', debounceSubmit);
        }

        function setView(v) {
            const viewInput = document.querySelector('[name="view"]');
            if (viewInput) {
                viewInput.value = v;
                document.getElementById('filterForm').submit();
            }
        }

        function showToast(message, type = 'success') {
            let toast = document.querySelector('.toast-notification');
            if (toast) toast.remove();
            toast = document.createElement('div');
            toast.className = `toast-notification ${type === 'error' ? 'error' : ''}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Star rating
        document.querySelectorAll('.rating-form input[name="rating"]').forEach(star => {
            star.addEventListener('change', function() {
                this.closest('.rating-form').submit();
            });
        });

        // ========== TAG MANAGEMENT ==========
        (function() {
            let currentStudentId = null;

            function createModal() {
                if (document.getElementById('tagModal')) return;
                const html = `
    <div id="tagModal" class="tag-modal">
        <div class="tag-modal-content">
            <div class="tag-modal-header"><span>Add Tag</span><span class="tag-modal-close">&times;</span></div>
            <input type="text" id="tagInput" placeholder="Enter tag name..." maxlength="50">
            <div class="suggested-tags-list" id="suggestedTagsList"></div>
            <div class="modal-buttons">
                <button class="btn-secondary" id="cancelTagBtn">Cancel</button>
                <button class="btn-primary" id="saveTagBtn">Add Tag</button>
            </div>
        </div>
    </div>`;
                document.body.insertAdjacentHTML('beforeend', html);
                const modal = document.getElementById('tagModal');
                if (modal) {
                    modal.querySelector('.tag-modal-close').onclick = closeModal;
                }
                const cancelBtn = document.getElementById('cancelTagBtn');
                if (cancelBtn) cancelBtn.onclick = closeModal;
                const saveBtn = document.getElementById('saveTagBtn');
                if (saveBtn) saveBtn.onclick = saveTag;
                const tagInput = document.getElementById('tagInput');
                if (tagInput) {
                    tagInput.addEventListener('keypress', e => {
                        if (e.key === 'Enter') saveTag();
                    });
                }
            }

            function openModal(studentId) {
                createModal();
                currentStudentId = studentId;
                const modal = document.getElementById('tagModal');
                if (modal) modal.classList.add('active');
                const tagInput = document.getElementById('tagInput');
                if (tagInput) {
                    tagInput.value = '';
                    tagInput.focus();
                }
                loadPopularTags();
            }

            function closeModal() {
                const modal = document.getElementById('tagModal');
                if (modal) modal.classList.remove('active');
                currentStudentId = null;
            }

            async function loadPopularTags() {
                try {
                    const res = await fetch('/crm/popular-tags');
                    const data = await res.json();
                    const container = document.getElementById('suggestedTagsList');
                    if (container) {
                        if (data.tags && data.tags.length) {
                            container.innerHTML = data.tags.map(t =>
                                `<span class="suggested-tag" data-tag="${t}">🏷️ ${t}</span>`).join('');
                            document.querySelectorAll('.suggested-tag').forEach(t => {
                                t.onclick = () => {
                                    const tagInput = document.getElementById('tagInput');
                                    if (tagInput) tagInput.value = t.dataset.tag;
                                };
                            });
                        } else {
                            container.innerHTML = '<div class="text-muted small">No popular tags</div>';
                        }
                    }
                } catch (e) {
                    console.error(e);
                }
            }

            async function saveTag() {
                const tagInput = document.getElementById('tagInput');
                const tag = tagInput ? tagInput.value.trim() : '';
                if (!tag) {
                    showToast('Please enter a tag', 'error');
                    return;
                }
                const loadingDiv = document.getElementById('dragLoading');
                if (loadingDiv) loadingDiv.style.display = 'flex';
                try {
                    const res = await fetch(`/crm/students/${currentStudentId}/add-tag`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({
                            tag
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        updateTagsInCard(currentStudentId, data.tags);
                        showToast(`Tag "${tag}" added`);
                        closeModal();
                    } else throw new Error(data.error);
                } catch (e) {
                    showToast(e.message, 'error');
                } finally {
                    if (loadingDiv) loadingDiv.style.display = 'none';
                }
            }

            async function removeTag(studentId, tag) {
                if (!confirm(`Remove "${tag}"?`)) return;
                const loadingDiv = document.getElementById('dragLoading');
                if (loadingDiv) loadingDiv.style.display = 'flex';
                try {
                    const res = await fetch(`/crm/students/${studentId}/remove-tag`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({
                            tag
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        updateTagsInCard(studentId, data.tags);
                        showToast(`Tag "${tag}" removed`);
                    } else throw new Error(data.error);
                } catch (e) {
                    showToast(e.message, 'error');
                } finally {
                    if (loadingDiv) loadingDiv.style.display = 'none';
                }
            }

            function updateTagsInCard(studentId, tags) {
                const card = document.querySelector(`.student-card[data-student-id="${studentId}"]`);
                if (!card) return;
                const container = card.querySelector('.sc-tags-list');
                if (!container) return;
                if (tags && tags.length) {
                    container.innerHTML = tags.map(t =>
                        `<span class="sc-tag">🏷️ ${t}<button type="button" class="remove-tag-btn" data-tag="${t}">×</button></span>`
                    ).join('');
                    container.querySelectorAll('.remove-tag-btn').forEach(btn => {
                        btn.onclick = (e) => {
                            e.stopPropagation();
                            removeTag(studentId, btn.dataset.tag);
                        };
                    });
                } else {
                    container.innerHTML = '';
                }
            }

            document.querySelectorAll('.add-tag-btn').forEach(btn => {
                btn.onclick = (e) => {
                    e.stopPropagation();
                    openModal(btn.dataset.studentId);
                };
            });
            document.querySelectorAll('.remove-tag-btn').forEach(btn => {
                btn.onclick = (e) => {
                    e.stopPropagation();
                    removeTag(btn.closest('.student-card').dataset.studentId, btn.dataset.tag);
                };
            });
        })();

        // ========== DRAG AND DROP ==========
        (function() {
            const board = document.getElementById('kanbanBoard');
            if (!board) return;

            let draggedItem = null;
            let scrollInterval = null;

            function handleDragStart(e) {
                draggedItem = this;
                this.classList.add('dragging');
                e.dataTransfer.setData('text/plain', JSON.stringify({
                    studentId: this.dataset.studentId,
                    studentName: this.dataset.studentName,
                    sourceStageId: this.closest('.kanban-col').dataset.stageId
                }));
                e.dataTransfer.effectAllowed = 'move';
            }

            function handleDragEnd(e) {
                this.classList.remove('dragging');
                draggedItem = null;
                if (scrollInterval) clearInterval(scrollInterval);
                document.querySelectorAll('.kanban-col-body').forEach(z => z.classList.remove('drag-over'));
            }

            function handleDragOver(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                this.classList.add('drag-over');
            }

            function handleDragLeave(e) {
                this.classList.remove('drag-over');
            }

            function handleGlobalDragOver(e) {
                if (!draggedItem) return;
                const rect = board.getBoundingClientRect();
                const x = e.clientX;
                if (x - rect.left < 100) startScroll('left');
                else if (rect.right - x < 100) startScroll('right');
                else if (scrollInterval) {
                    clearInterval(scrollInterval);
                    scrollInterval = null;
                }
            }

            function startScroll(dir) {
                if (scrollInterval) return;
                scrollInterval = setInterval(() => {
                    if (dir === 'left') board.scrollLeft -= 15;
                    else board.scrollLeft += 15;
                }, 16);
            }

            async function handleDrop(e) {
                e.preventDefault();
                this.classList.remove('drag-over');
                if (scrollInterval) {
                    clearInterval(scrollInterval);
                    scrollInterval = null;
                }

                let data;
                try {
                    data = JSON.parse(e.dataTransfer.getData('text/plain'));
                } catch (err) {
                    return;
                }

                const {
                    studentId,
                    studentName,
                    sourceStageId
                } = data;
                const targetStageId = this.dataset.stageId;

                if (sourceStageId === targetStageId) {
                    showToast('Student already in this stage', 'error');
                    return;
                }

                const loadingDiv = document.getElementById('dragLoading');
                if (loadingDiv) loadingDiv.style.display = 'flex';

                try {
                    const res = await fetch(`/crm/students/${studentId}/stage`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ||
                                '',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            stage_id: parseInt(targetStageId)
                        })
                    });

                    const result = await res.json();
                    if (result.success) {
                        const el = document.querySelector(`.student-card[data-student-id="${studentId}"]`);
                        if (el) {
                            el.remove();
                            this.appendChild(el);
                            updateCounts();
                            const headerDiv = this.closest('.kanban-col').querySelector('.kanban-col-header div');
                            showToast(
                                `${studentName} moved to ${headerDiv ? headerDiv.textContent.trim() : 'new stage'}`
                            );
                        } else {
                            location.reload();
                        }
                    } else {
                        throw new Error(result.error);
                    }
                } catch (err) {
                    showToast(err.message, 'error');
                } finally {
                    if (loadingDiv) loadingDiv.style.display = 'none';
                }
            }

            function updateCounts() {
                document.querySelectorAll('.kanban-col').forEach(col => {
                    const count = col.querySelectorAll('.student-card').length;
                    const countSpan = col.querySelector('.stage-count');
                    if (countSpan) countSpan.textContent = count;
                });
            }

            document.querySelectorAll('.student-card[draggable="true"]').forEach(card => {
                card.addEventListener('dragstart', handleDragStart);
                card.addEventListener('dragend', handleDragEnd);
            });
            document.querySelectorAll('.kanban-col-body').forEach(zone => {
                zone.addEventListener('dragover', handleDragOver);
                zone.addEventListener('dragleave', handleDragLeave);
                zone.addEventListener('drop', handleDrop);
            });
            document.addEventListener('dragover', handleGlobalDragOver);
        })();
    </script>
@endpush

@push('scripts')
    {{-- Include modal partial at the end --}}
    @include('crm.partials._add_student_modal')
@endpush
