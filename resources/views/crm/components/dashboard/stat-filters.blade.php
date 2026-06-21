@push('styles')
    <style>
        .metric-card {
            position: relative; border-radius: var(--radius); padding: 14px 16px;
            height: 100%; cursor: pointer; overflow: hidden; isolation: isolate;
            background: #fff; box-shadow: var(--card-shadow);
            transition: transform .15s, box-shadow .15s;
        }
        .metric-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.08); }
        .metric-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: var(--mc); z-index: 1;
        }
        .metric-card .bg-glow {
            position: absolute; top: -40%; right: -20%; width: 100px; height: 100px;
            border-radius: 50%; background: var(--mc); opacity: .04; z-index: 0;
        }
        .metric-card > * { position: relative; z-index: 1; }
        .metric-top { display: flex; align-items: flex-start; justify-content: space-between; }
        .metric-number { font-size: 1.6rem; font-weight: 800; color: #1e293b; line-height: 1.1; letter-spacing: -.02em; }
        .metric-icon { font-size: 1.4rem; color: var(--mc); opacity: .6; }
        .metric-label { font-size: .6rem; text-transform: uppercase; letter-spacing: .4px; color: #64748b; font-weight: 600; margin-top: 2px; }

        .crm-toolbar {
            background: var(--glass-bg); backdrop-filter: blur(12px);
            border-radius: var(--radius); padding: 8px 12px; margin-bottom: 10px;
            box-shadow: var(--card-shadow); border: 1px solid var(--card-border);
            position: sticky; top: 56px; z-index: 99;
        }
        .search-wrapper { display: flex; gap: 4px; align-items: center; flex-wrap: nowrap; }
        .search-wrapper .form-select { width: auto; min-width: 70px; }
        .filter-group { display: flex; gap: 4px; flex-wrap: nowrap; align-items: center; }
        .view-btn { border-radius: 6px !important; font-size: .65rem !important; padding: 3px 12px !important; border-color: #d1d5db !important; color: #475569 !important; }
        .view-btn:hover { border-color: var(--primary) !important; color: var(--primary) !important; background: var(--primary-light) !important; }
        .view-btn.active { background: var(--accent-gradient) !important; color: #fff !important; border-color: transparent !important; }
        .view-btn.active:hover { background: var(--accent-gradient) !important; color: #fff !important; }
        @media (max-width: 992px) { .search-wrapper, .filter-group { flex-wrap: wrap; } }
    </style>
@endpush

<div class="row g-2 mb-3">
    <div class="col-6 col-md-4 col-lg">
        <div class="metric-card" style="--mc:#1a0262" onclick="window.location.href='{{ route('crm.dashboard') }}'">
            <span class="bg-glow"></span>
            <div class="metric-top">
                <div>
                    <div class="metric-number">{{ $stats['total_students'] ?? 0 }}</div>
                    <div class="metric-label">All Students</div>
                </div>
                <div class="metric-icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>

    @if (auth()->user()->role !== 'admin')
        <div class="col-6 col-md-4 col-lg">
            <div class="metric-card" style="--mc:#2563eb"
                onclick="window.location.href='{{ route('crm.dashboard', ['view' => 'list', 'assignee_id' => auth()->id()]) }}'">
                <span class="bg-glow"></span>
                <div class="metric-top">
                    <div>
                        <div class="metric-number">{{ $stats['my_students'] ?? 0 }}</div>
                        <div class="metric-label">My Students</div>
                    </div>
                    <div class="metric-icon"><i class="fas fa-user-check"></i></div>
                </div>
            </div>
        </div>
    @endif

    <div class="col-6 col-md-4 col-lg">
        <div class="metric-card" style="--mc:#ef4444"
            onclick="window.location.href='{{ route('crm.dashboard', ['view' => 'list', 'activity_filter' => auth()->user()->role !== 'admin' ? 'my_overdue' : 'overdue']) }}'">
            <span class="bg-glow"></span>
            <div class="metric-top">
                <div>
                    <div class="metric-number">{{ auth()->user()->role !== 'admin' ? $stats['my_overdue'] ?? 0 : $stats['overdue'] ?? 0 }}</div>
                    <div class="metric-label">Overdue</div>
                </div>
                <div class="metric-icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg">
        <div class="metric-card" style="--mc:#f59e0b"
            onclick="window.location.href='{{ route('crm.dashboard', ['view' => 'list', 'activity_filter' => auth()->user()->role !== 'admin' ? 'my_today' : 'today']) }}'">
            <span class="bg-glow"></span>
            <div class="metric-top">
                <div>
                    <div class="metric-number">{{ auth()->user()->role !== 'admin' ? $stats['my_today'] ?? 0 : $stats['today'] ?? 0 }}</div>
                    <div class="metric-label">Today</div>
                </div>
                <div class="metric-icon"><i class="fas fa-calendar-day"></i></div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg">
        <div class="metric-card" style="--mc:#820b5c"
            onclick="window.location.href='{{ route('crm.dashboard', ['view' => 'list', 'activity_filter' => auth()->user()->role !== 'admin' ? 'my_upcoming' : 'upcoming']) }}'">
            <span class="bg-glow"></span>
            <div class="metric-top">
                <div>
                    <div class="metric-number">{{ auth()->user()->role !== 'admin' ? $stats['my_upcoming'] ?? 0 : $stats['upcoming'] ?? 0 }}</div>
                    <div class="metric-label">Upcoming</div>
                </div>
                <div class="metric-icon"><i class="fas fa-calendar-week"></i></div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg">
        <div class="metric-card" style="--mc:#7c3aed"
            onclick="window.location.href='{{ route('crm.dashboard', ['view' => 'list', 'activity_filter' => auth()->user()->role !== 'admin' ? 'my_completed_today' : 'completed_today']) }}'">
            <span class="bg-glow"></span>
            <div class="metric-top">
                <div>
                    <div class="metric-number">{{ auth()->user()->role !== 'admin' ? $stats['my_completed_today'] ?? 0 : $stats['completed_today'] ?? 0 }}</div>
                    <div class="metric-label">Done Today</div>
                </div>
                <div class="metric-icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>

    @if (auth()->user()->role == 'admin')
        <div class="col-6 col-md-4 col-lg">
            <div class="metric-card" style="--mc:#5c2d8a">
                <span class="bg-glow"></span>
                <div class="metric-top">
                    <div>
                        <div class="metric-number">{{ $stats['completed_this_week'] ?? 0 }}</div>
                        <div class="metric-label">Week Done</div>
                    </div>
                    <div class="metric-icon"><i class="fas fa-chart-line"></i></div>
                </div>
            </div>
        </div>
    @endif
</div>

<form method="GET" id="crmFilterForm" class="crm-toolbar">
    <input type="hidden" name="view" value="{{ $view ?? 'kanban' }}">
    <div class="row g-1 align-items-center">
        <div class="col-md-5 col-lg-6">
            <div class="search-wrapper">
                <select name="search_type" class="form-select form-select-sm">
                    <option value="all" {{ request('search_type') == 'all' ? 'selected' : '' }}>All</option>
                    <option value="name" {{ request('search_type') == 'name' ? 'selected' : '' }}>Name</option>
                    <option value="phone" {{ request('search_type') == 'phone' ? 'selected' : '' }}>Phone</option>
                    <option value="email" {{ request('search_type') == 'email' ? 'selected' : '' }}>Email</option>
                    <option value="tag" {{ request('search_type') == 'tag' ? 'selected' : '' }}>Tag</option>
                </select>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
            </div>
        </div>
        <div class="col-md-7 col-lg-6">
            <div class="filter-group">
                <select name="stage_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Stages</option>
                    @foreach ($stages as $stage)
                        <option value="{{ $stage->id }}" @selected(request('stage_id') == $stage->id)>{{ $stage->name }}</option>
                    @endforeach
                </select>

                @if ($isAdmin && $assignees->count())
                    <select name="assignee_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Filter Staff</option>
                        @foreach ($assignees as $a)
                            <option value="{{ $a->id }}" @selected(request('assignee_id') == $a->id)>{{ \Illuminate\Support\Str::words($a->name, 2, '') }}</option>
                        @endforeach
                    </select>
                @endif

                <select name="activity_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Activity</option>
                    @if (auth()->user()->role === 'admin')
                        <option value="overdue" @selected(request('activity_filter') == 'overdue')>Overdue</option>
                        <option value="today" @selected(request('activity_filter') == 'today')>Today</option>
                        <option value="upcoming" @selected(request('activity_filter') == 'upcoming')>Upcoming</option>
                        <option value="completed_today" @selected(request('activity_filter') == 'completed_today')>Done Today</option>
                    @else
                        <option value="overdue" @selected(request('activity_filter') == 'overdue')>Has Overdue</option>
                        <option value="today" @selected(request('activity_filter') == 'today')>Active Today</option>
                        <option value="upcoming" @selected(request('activity_filter') == 'upcoming')>Has Upcoming</option>
                        <option value="my_overdue" @selected(request('activity_filter') == 'my_overdue')>My Overdue</option>
                        <option value="my_today" @selected(request('activity_filter') == 'my_today')>My Today</option>
                        <option value="my_upcoming" @selected(request('activity_filter') == 'my_upcoming')>My Upcoming</option>
                        <option value="my_completed_today" @selected(request('activity_filter') == 'my_completed_today')>My Done Today</option>
                    @endif
                </select>

                <div class="btn-group btn-group-sm">
                    <button type="button" onclick="setView('kanban')" class="btn btn-outline-secondary view-btn {{ ($view ?? 'kanban') == 'kanban' ? 'active' : '' }}">Kanban</button>
                    <button type="button" onclick="setView('list')" class="btn btn-outline-secondary view-btn {{ ($view ?? 'kanban') == 'list' ? 'active' : '' }}">List</button>
                    <button type="button" onclick="setView('calendar')" class="btn btn-outline-secondary view-btn {{ ($view ?? 'kanban') == 'calendar' ? 'active' : '' }}">Cal</button>
                </div>

                @if (request()->hasAny(['search', 'stage_id', 'assignee_id', 'activity_filter']))
                    <a href="{{ route('crm.dashboard') }}" class="btn btn-outline-danger btn-sm">Clear</a>
                @endif
            </div>
        </div>
    </div>
</form>
