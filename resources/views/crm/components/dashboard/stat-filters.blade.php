{{-- resources/views/crm/components/stat-filters.blade.php --}}
@push('styles')
    <style>
        /* ============================================ */
        /* STATS CARDS STYLES */
        /* ============================================ */
        .stat-card {
            background: white;
            border-radius: var(--radius-sm);
            padding: 0.35rem 0.5rem;
            transition: all 0.2s;
            box-shadow: var(--shadow-sm);
            height: 100%;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .stat-card:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--card-color);
        }

        .stat-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 4px;
        }

        .stat-left {
            flex: 1;
            min-width: 0;
        }

        .stat-icon {
            font-size: 1.1rem;
            opacity: 0.7;
        }

        .stat-number {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-color);
            line-height: 1.1;
        }

        .stat-label {
            font-size: 0.62rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: var(--text-muted);
            font-weight: 600;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ============================================ */
        /* TOOLBAR / FILTERS STYLES */
        /* ============================================ */
        .crm-toolbar {
            background: white;
            border-radius: var(--radius-sm);
            padding: 0.35rem 0.5rem;
            margin-bottom: 0.6rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            position: sticky;
            top: 46px;
            z-index: 99;
        }

        .search-wrapper {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: nowrap;
        }

        .search-wrapper .form-select {
            width: auto;
            min-width: 80px;
        }

        .filter-group {
            display: flex;
            gap: 6px;
            flex-wrap: nowrap;
            align-items: center;
        }

        .view-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        @media (max-width: 992px) {
            .search-wrapper {
                flex-wrap: wrap;
            }

            .filter-group {
                flex-wrap: wrap;
            }
        }
    </style>
@endpush

<!-- Stats Cards Row -->
<div class="row g-3 mb-4">
    <!-- Card 1: All Students -->
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card" style="--card-color: #4f46e5" onclick="window.location.href='{{ route('crm.dashboard') }}'">
            <div class="stat-content">
                <div class="stat-left">
                    <div class="stat-number">{{ $stats['total_students'] ?? 0 }}</div>
                    <div class="stat-label">All Students</div>
                </div>
                <div class="stat-icon"><i class="fas fa-users" style="color:#4f46e5"></i></div>
            </div>
        </div>
    </div>

    <!-- My Students Card - Only show for staff -->
    @if (auth()->user()->role !== 'admin')
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card" style="--card-color: #3b82f6"
                onclick="window.location.href='{{ route('crm.dashboard', ['view' => 'list', 'assignee_id' => auth()->id()]) }}'">
                <div class="stat-content">
                    <div class="stat-left">
                        <div class="stat-number">{{ $stats['my_students'] ?? 0 }}</div>
                        <div class="stat-label">My Students</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-user-check" style="color:#3b82f6"></i></div>
                </div>
            </div>
        </div>
    @endif

    <!-- Overdue Tasks Card -->
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card" style="--card-color: #ef4444"
            onclick="window.location.href='{{ route('crm.dashboard', ['view' => 'list', 'activity_filter' => auth()->user()->role !== 'admin' ? 'my_overdue' : 'overdue']) }}'">
            <div class="stat-content">
                <div class="stat-left">
                    <div class="stat-number">
                        {{ auth()->user()->role !== 'admin' ? $stats['my_overdue'] ?? 0 : $stats['overdue'] ?? 0 }}
                    </div>
                    <div class="stat-label">Overdue Tasks</div>
                </div>
                <div class="stat-icon"><i class="fas fa-clock" style="color:#ef4444"></i></div>
            </div>
        </div>
    </div>

    <!-- Today's Tasks Card -->
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card" style="--card-color: #f59e0b"
            onclick="window.location.href='{{ route('crm.dashboard', ['view' => 'list', 'activity_filter' => auth()->user()->role !== 'admin' ? 'my_today' : 'today']) }}'">
            <div class="stat-content">
                <div class="stat-left">
                    <div class="stat-number">
                        {{ auth()->user()->role !== 'admin' ? $stats['my_today'] ?? 0 : $stats['today'] ?? 0 }}
                    </div>
                    <div class="stat-label">Today's Tasks</div>
                </div>
                <div class="stat-icon"><i class="fas fa-calendar-day" style="color:#f59e0b"></i></div>
            </div>
        </div>
    </div>

    <!-- Upcoming Tasks Card -->
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card" style="--card-color: #10b981"
            onclick="window.location.href='{{ route('crm.dashboard', ['view' => 'list', 'activity_filter' => auth()->user()->role !== 'admin' ? 'my_upcoming' : 'upcoming']) }}'">
            <div class="stat-content">
                <div class="stat-left">
                    <div class="stat-number">
                        {{ auth()->user()->role !== 'admin' ? $stats['my_upcoming'] ?? 0 : $stats['upcoming'] ?? 0 }}
                    </div>
                    <div class="stat-label">Upcoming Tasks</div>
                </div>
                <div class="stat-icon"><i class="fas fa-calendar-week" style="color:#10b981"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card" style="--card-color: #8b5cf6"
            onclick="window.location.href='{{ route('crm.dashboard', ['view' => 'list', 'activity_filter' => auth()->user()->role !== 'admin' ? 'my_completed_today' : 'completed_today']) }}'">
            <div class="stat-content">
                <div class="stat-left">
                    <div class="stat-number">
                        {{ auth()->user()->role !== 'admin' ? $stats['my_completed_today'] ?? 0 : $stats['completed_today'] ?? 0 }}
                    </div>
                    <div class="stat-label">Completed Today</div>
                </div>
                <div class="stat-icon"><i class="fas fa-chart-line" style="color:#8b5cf6"></i></div>
            </div>
        </div>
    </div>

    @if (auth()->user()->role == 'admin')
        <!-- Completed This Week Card -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card" style="--card-color: #8b5cf6">
                <div class="stat-content">
                    <div class="stat-left">
                        <div class="stat-number">
                            {{ auth()->user()->role !== 'admin' ? $stats['completed_this_week'] ?? 0 : $stats['completed_this_week'] ?? 0 }}
                        </div>
                        <div class="stat-label">Completed This Week</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-chart-line" style="color:#8b5cf6"></i></div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Toolbar / Filters -->
<form method="GET" id="crmFilterForm" class="crm-toolbar">
    <input type="hidden" name="view" value="{{ $view ?? 'kanban' }}">
    <div class="row g-2 align-items-end">
        <div class="col-md-5 col-lg-6">
            <div class="search-wrapper">
                <select name="search_type" class="form-select form-select-sm">
                    <option value="all" {{ request('search_type') == 'all' ? 'selected' : '' }}>All</option>
                    <option value="name" {{ request('search_type') == 'name' ? 'selected' : '' }}>Name</option>
                    <option value="phone" {{ request('search_type') == 'phone' ? 'selected' : '' }}>Phone</option>
                    <option value="email" {{ request('search_type') == 'email' ? 'selected' : '' }}>Email</option>
                    <option value="tag" {{ request('search_type') == 'tag' ? 'selected' : '' }}>Tag</option>
                </select>
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Search students..." value="{{ request('search') }}">
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
                        <option value="">Filter by Staff</option>
                        @foreach ($assignees as $a)
                            <option value="{{ $a->id }}" @selected(request('assignee_id') == $a->id)>{{ $a->name }}
                            </option>
                        @endforeach
                    </select>
                @endif

                <!-- Activity Filter Dropdown - Shows the selected filter properly -->
                <select name="activity_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Activity</option>

                    <!-- For Admin: Show only non-my options -->
                    @if (auth()->user()->role === 'admin')
                        <option value="overdue" @selected(request('activity_filter') == 'overdue')>Overdue Tasks</option>
                        <option value="today" @selected(request('activity_filter') == 'today')>Today's Tasks</option>
                        <option value="upcoming" @selected(request('activity_filter') == 'upcoming')>Upcoming Tasks</option>
                        <option value="completed_today" @selected(request('activity_filter') == 'completed_today')>Completed Today</option>
                    @else
                        <!-- For Staff: Show both regular and my options -->
                        <option value="overdue" @selected(request('activity_filter') == 'overdue')>Has Overdue</option>
                        <option value="today" @selected(request('activity_filter') == 'today')>Active Today</option>
                        <option value="upcoming" @selected(request('activity_filter') == 'upcoming')>Has Upcoming</option>
                        <option value="my_overdue" @selected(request('activity_filter') == 'my_overdue')>My Overdue Tasks</option>
                        <option value="my_today" @selected(request('activity_filter') == 'my_today')>My To-do Today's Tasks</option>
                        <option value="my_upcoming" @selected(request('activity_filter') == 'my_upcoming')>My Upcoming Tasks</option>
                        <option value="my_completed_today" @selected(request('activity_filter') == 'my_completed_today')>My Completed Today</option>
                    @endif
                </select>

                <div class="btn-group btn-group-sm">
                    <button type="button" onclick="window.setView('kanban')"
                        class="btn btn-outline-secondary view-btn {{ ($view ?? 'kanban') == 'kanban' ? 'active' : '' }}">Kanban</button>
                    <button type="button" onclick="window.setView('list')"
                        class="btn btn-outline-secondary view-btn {{ ($view ?? 'kanban') == 'list' ? 'active' : '' }}">List</button>
                    <button type="button" onclick="window.setView('calendar')"
                        class="btn btn-outline-secondary view-btn {{ ($view ?? 'kanban') == 'calendar' ? 'active' : '' }}">Calendar</button>
                </div>

                @if (request()->hasAny(['search', 'stage_id', 'assignee_id', 'activity_filter']))
                    <a href="{{ route('crm.dashboard') }}" class="btn btn-outline-danger btn-sm">Clear</a>
                @endif
            </div>
        </div>
    </div>
</form>
