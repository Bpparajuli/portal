{{-- resources/views/crm/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'CRM Pipeline')

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
            color: var(--crm-muted);
        }

        .crm-toolbar {
            background: var(--crm-card);
            border: 1px solid var(--crm-border);
            border-radius: 10px;
            padding: .6rem 1rem;
            display: flex;
            align-items: center;
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
            font-size: .875rem;
        }

        .crm-toolbar .search-wrap .search-icon {
            position: absolute;
            left: .7rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--crm-muted);
        }

        .crm-toolbar select,
        .crm-toolbar .view-btn {
            font-size: .8rem;
            border: 1px solid var(--crm-border);
            border-radius: 8px;
            padding: .4rem .75rem;
            background: var(--crm-bg);
            cursor: pointer;
        }

        .view-btn.active {
            background: var(--crm-primary);
            color: #fff;
            border-color: var(--crm-primary);
        }

        .kanban-board {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 1rem;
            align-items: flex-start;
        }

        .kanban-col {
            flex: 0 0 300px;
            background: var(--crm-card);
            border: 1px solid var(--crm-border);
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 280px);
        }

        .kanban-col-header {
            padding: .75rem 1rem;
            font-size: .8rem;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid var(--crm-border);
            background: var(--crm-card);
        }

        .kanban-col-body {
            padding: .5rem;
            display: flex;
            flex-direction: column;
            gap: .5rem;
            overflow-y: auto;
            flex: 1;
        }

        .student-card {
            background: #fff;
            border: 1px solid var(--crm-border);
            border-radius: 10px;
            padding: .75rem;
            cursor: pointer;
            transition: all .15s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .student-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, .1);
            transform: translateY(-1px);
        }

        .student-card .sc-name {
            font-weight: 600;
            font-size: .875rem;
        }

        .student-card .sc-phone {
            font-size: .75rem;
            color: var(--crm-muted);
            margin-top: .2rem;
        }

        .student-card .sc-tags {
            display: flex;
            flex-wrap: wrap;
            gap: .25rem;
            margin-top: .4rem;
        }

        .student-card .sc-tag {
            font-size: .65rem;
            background: #f0f2ff;
            color: var(--crm-primary);
            border-radius: 4px;
            padding: .1rem .35rem;
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
            color: var(--crm-warning);
        }

        .sc-followup.upcoming {
            background: #f0fdf4;
            color: var(--crm-success);
        }

        .crm-list-table {
            background: var(--crm-card);
            border: 1px solid var(--crm-border);
            border-radius: 12px;
            overflow: hidden;
        }

        .crm-list-table table {
            width: 100%;
            border-collapse: collapse;
            font-size: .875rem;
        }

        .crm-list-table th {
            background: var(--crm-bg);
            padding: .6rem 1rem;
            text-align: left;
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--crm-muted);
        }

        .crm-list-table td {
            padding: .75rem 1rem;
            border-bottom: 1px solid var(--crm-border);
        }

        .stage-pill {
            display: inline-block;
            font-size: .72rem;
            font-weight: 600;
            border-radius: 20px;
            padding: .2rem .65rem;
        }

        .staff-info {
            font-size: .7rem;
            color: var(--crm-muted);
            margin-top: .2rem;
        }

        @media (max-width: 768px) {
            .kanban-col {
                flex: 0 0 260px;
            }

            .crm-stat {
                flex: 0 0 calc(50% - .5rem);
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4 px-3 px-md-4">

        {{-- Page header --}}
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <div>
                <h4 class="mb-0 fw-bold" style="color:var(--crm-text)">CRM Pipeline</h4>
                <p class="text-muted small mb-0">
                    @if (auth()->user()->is_admin)
                        All students (Admin View)
                    @elseif(auth()->user()->is_agent)
                        Your team's students (Agent View)
                    @else
                        Your created students (Staff View)
                    @endif
                </p>
                @if (auth()->user()->is_staff)
                    <p class="text-muted small mt-1 mb-0">
                        <i class="fas fa-info-circle"></i> Showing students where you are the assigned agent (agent_id =
                        {{ auth()->id() }})
                    </p>
                @endif
            </div>
            <div class="d-flex gap-2">
                @if (auth()->user()->is_admin)
                    <a href="{{ route('crm.configure.index') }}" class="btn btn-sm btn-outline-secondary">⚙️ Configure</a>
                @endif
                <a href="{{ route('crm.export') . '?' . http_build_query(request()->query()) }}"
                    class="btn btn-sm btn-outline-secondary">⬇️ Export</a>
            </div>
        </div>

        {{-- Stats bar --}}
        <div class="crm-stats">
            <div class="crm-stat">
                <span class="stat-icon">👥</span>
                <div>
                    <div class="stat-num">{{ $stats['total'] }}</div>
                    <div class="stat-lbl">
                        @if (auth()->user()->is_staff)
                            Your Students
                        @else
                            Total Students
                        @endif
                    </div>
                </div>
            </div>
            <div class="crm-stat">
                <span class="stat-icon">📅</span>
                <div>
                    <div class="stat-num">{{ $stats['today'] }}</div>
                    <div class="stat-lbl">Today's Tasks</div>
                </div>
            </div>
            <div class="crm-stat">
                <span class="stat-icon">⚠️</span>
                <div>
                    <div class="stat-num text-danger">{{ $stats['overdue'] }}</div>
                    <div class="stat-lbl">Overdue</div>
                </div>
            </div>
            <div class="crm-stat">
                <span class="stat-icon">🔜</span>
                <div>
                    <div class="stat-num" style="color:var(--crm-success)">{{ $stats['upcoming'] }}</div>
                    <div class="stat-lbl">Upcoming</div>
                </div>
            </div>
        </div>

        {{-- Toolbar --}}
        <form method="GET" id="filterForm">
            <input type="hidden" name="view" value="{{ $view }}">
            <div class="crm-toolbar">
                <div class="search-wrap">
                    <span class="search-icon">🔍</span>
                    <input type="text" name="search" placeholder="Search students…" value="{{ request('search') }}"
                        oninput="debounceSubmit()">
                </div>

                <select name="stage_id" onchange="this.form.submit()">
                    <option value="">All Stages</option>
                    @foreach ($stages as $stage)
                        <option value="{{ $stage->id }}" @selected(request('stage_id') == $stage->id)>{{ $stage->name }}</option>
                    @endforeach
                </select>

                @if ($assignees->count())
                    <select name="assignee_id" onchange="this.form.submit()">
                        <option value="">All Staff</option>
                        @foreach ($assignees as $a)
                            <option value="{{ $a->id }}" @selected(request('assignee_id') == $a->id)>{{ $a->name }}
                                ({{ ucfirst($a->role) }})</option>
                        @endforeach
                    </select>
                @endif

                <select name="activity_filter" onchange="this.form.submit()">
                    <option value="">All Activity</option>
                    <option value="overdue" @selected(request('activity_filter') === 'overdue')>Has Overdue</option>
                    <option value="today" @selected(request('activity_filter') === 'today')>Active Today</option>
                    <option value="upcoming" @selected(request('activity_filter') === 'upcoming')>Has Upcoming</option>
                </select>

                <div class="d-flex gap-1">
                    <button type="button" onclick="setView('kanban')"
                        class="view-btn {{ $view === 'kanban' ? 'active' : '' }}">⬛ Kanban</button>
                    <button type="button" onclick="setView('list')"
                        class="view-btn {{ $view === 'list' ? 'active' : '' }}">☰ List</button>
                    <button type="button" onclick="setView('table')"
                        class="view-btn {{ $view === 'table' ? 'active' : '' }}">⊞ Table</button>
                </div>

                @if (request()->hasAny(['search', 'stage_id', 'assignee_id', 'activity_filter']))
                    <a href="{{ route('crm.dashboard') }}" class="btn btn-sm btn-outline-danger">✕ Clear</a>
                @endif
            </div>
        </form>

        {{-- KANBAN VIEW --}}
        @if ($view === 'kanban')
            <div class="kanban-board">
                @foreach ($stages as $stage)
                    @php $colStudents = $students[$stage->id] ?? collect(); @endphp
                    <div class="kanban-col">
                        <div class="kanban-col-header">
                            <div>
                                <span class="col-dot"
                                    style="background:{{ $stage->color }}; width:10px;height:10px;border-radius:50%;display:inline-block;margin-right:.4rem"></span>
                                {{ $stage->name }}
                            </div>
                            <span class="badge rounded-pill"
                                style="background:{{ $stage->color }}30; color:{{ $stage->color }}; font-size:.7rem">
                                {{ $colStudents->count() }}
                            </span>
                        </div>
                        <div class="kanban-col-body">
                            @forelse($colStudents as $student)
                                @php
                                    $overdue = $student->overdueActivities->count();
                                    $upcoming = $student->upcomingActivities->count();
                                    $task = $student->pendingActivities->first();
                                    $followupClass = $overdue ? 'overdue' : ($upcoming ? 'upcoming' : 'today');
                                    $followupLabel = $overdue
                                        ? "⚠️ {$overdue} overdue"
                                        : ($task
                                            ? '📅 ' .
                                                ($task->scheduled_at?->isToday()
                                                    ? 'Today'
                                                    : $task->scheduled_at?->format('d M'))
                                            : '—');
                                @endphp
                                <a href="{{ route('crm.student.show', $student) }}" class="student-card">
                                    <div class="sc-name">{{ $student->full_name }}</div>
                                    <div class="sc-phone">📞 {{ $student->phone_number ?? '—' }}</div>
                                    @if ($student->tags)
                                        <div class="sc-tags">
                                            @foreach (array_slice($student->tags, 0, 3) as $tag)
                                                <span class="sc-tag">🏷️ {{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="sc-followup {{ $followupClass }}">{{ $followupLabel }}</div>
                                    @if (auth()->user()->is_admin || auth()->user()->is_agent)
                                        <div class="staff-info">Agent: {{ $student->agent?->name ?? 'Unassigned' }}</div>
                                    @endif
                                </a>
                            @empty
                                <div class="text-center text-muted py-3" style="font-size:.75rem">No students</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- LIST VIEW --}}
        @elseif($view === 'list')
            <div class="crm-list-table">
                @forelse($students as $student)
                    <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                        <img src="{{ $student->avatar_url }}" class="rounded-circle" width="38" height="38"
                            alt="">
                        <div class="flex-grow-1 min-w-0">
                            <a href="{{ route('crm.student.show', $student) }}" class="fw-semibold text-decoration-none"
                                style="color:var(--crm-text)">
                                {{ $student->full_name }}
                            </a>
                            <div class="small text-muted">📞 {{ $student->phone_number }} &bull; {{ $student->email }}
                            </div>
                            @if (auth()->user()->is_admin || auth()->user()->is_agent)
                                <div class="small text-muted mt-1">👤 Agent: {{ $student->agent?->name ?? 'Unassigned' }}
                                </div>
                            @endif
                        </div>
                        <div class="d-none d-md-block">
                            @if ($student->currentStage)
                                <span class="stage-pill"
                                    style="background:{{ $student->currentStage->color }}20; color:{{ $student->currentStage->color }}">
                                    {{ $student->currentStage->name }}
                                </span>
                            @endif
                        </div>
                        <div>
                            @if ($student->overdueActivities->count())
                                <span class="badge bg-danger">⚠️ {{ $student->overdueActivities->count() }} overdue</span>
                            @elseif($student->upcomingActivities->count())
                                <span class="badge bg-success">✅ {{ $student->upcomingActivities->count() }}
                                    upcoming</span>
                            @else
                                <span class="badge bg-secondary">— no tasks</span>
                            @endif
                        </div>
                        <a href="{{ route('crm.student.show', $student) }}"
                            class="btn btn-sm btn-outline-primary">View</a>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        No students found.
                        @if (auth()->user()->is_staff)
                            <div class="mt-2 small">You don't have any students assigned to you.</div>
                        @endif
                    </div>
                @endforelse
            </div>
            @if ($students->hasPages())
                <div class="mt-3">{{ $students->withQueryString()->links() }}</div>
            @endif

            {{-- TABLE VIEW --}}
        @else
            <div class="crm-list-table">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Stage</th>
                            <th>Phone</th>
                            <th>Assigned To</th>
                            <th>Tags</th>
                            <th>Activity</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $student->avatar_url }}" class="rounded-circle" width="30"
                                            height="30" alt="">
                                        <div>
                                            <div class="fw-medium">{{ $student->full_name }}</div>
                                            <div class="text-muted" style="font-size:.75rem">{{ $student->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($student->currentStage)
                                        <span class="stage-pill"
                                            style="background:{{ $student->currentStage->color }}20; color:{{ $student->currentStage->color }}">
                                            {{ $student->currentStage->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $student->phone_number ?? '—' }}</td>
                                <td class="text-muted small">{{ $student->agent?->name ?? '—' }}</td>
                                <td>
                                    @foreach (array_slice($student->tags ?? [], 0, 2) as $tag)
                                        <span class="badge bg-secondary me-1">{{ $tag }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @if ($student->overdueActivities->count())
                                        <span class="badge bg-danger">{{ $student->overdueActivities->count() }}
                                            overdue</span>
                                    @elseif($student->upcomingActivities->count())
                                        <span class="badge bg-success">{{ $student->upcomingActivities->count() }}
                                            upcoming</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td><a href="{{ route('crm.student.show', $student) }}"
                                        class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No students found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($students->hasPages())
                <div class="mt-3">{{ $students->withQueryString()->links() }}</div>
            @endif
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        function setView(v) {
            document.querySelector('[name="view"]').value = v;
            document.getElementById('filterForm').submit();
        }

        let searchTimer;

        function debounceSubmit() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => document.getElementById('filterForm').submit(), 450);
        }
    </script>
@endpush
