@extends('layouts.crm')

@section('title', 'CRM Pipeline')

@push('styles')
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <style>
        /* ============================================ */
        /* ROOT VARIABLES */
        /* ============================================ */
        :root {
            --crm-primary: #4f46e5;
            --crm-primary-dark: #4338ca;
            --crm-danger: #ef4444;
            --crm-success: #10b981;
            --crm-warning: #f59e0b;
            --crm-info: #3b82f6;
            --crm-gray: #6b7280;
            --crm-border: #e5e7eb;
            --crm-bg: #f3f4f6;
        }

        /* ============================================ */
        /* TOAST NOTIFICATIONS */
        /* ============================================ */
        .toast-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 280px;
            background: white;
            border-radius: 12px;
            padding: 12px 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
            border-left: 4px solid;
        }

        .toast-notification.success {
            border-left-color: #10b981;
        }

        .toast-notification.error {
            border-left-color: #ef4444;
        }

        .toast-notification.info {
            border-left-color: #3b82f6;
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

        .toast-notification.fade-out {
            animation: slideOut 0.3s ease forwards;
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

        /* ============================================ */
        /* LOADER */
        /* ============================================ */
        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }

        .loader-overlay.show {
            display: flex;
        }

        .loader-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f4f6;
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            background: white;
            padding: 10px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ============================================ */
        /* STATS CARDS */
        /* ============================================ */
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1rem;
            transition: all 0.2s;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            height: 100%;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--card-color);
        }

        .stat-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-left {
            flex: 1;
        }

        .stat-icon {
            font-size: 2rem;
            opacity: 0.8;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1f2937;
            line-height: 1.2;
        }

        .stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            font-weight: 600;
            margin-top: 4px;
        }

        /* ============================================ */
        /* TOOLBAR / FILTERS */
        /* ============================================ */
        .crm-toolbar {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .search-wrapper {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: nowrap;
        }

        .search-wrapper .form-select {
            width: auto;
            min-width: 100px;
        }

        .filter-group {
            display: flex;
            gap: 10px;
            flex-wrap: nowrap;
            align-items: center;
        }

        .view-btn.active {
            background: var(--crm-primary);
            color: white;
            border-color: var(--crm-primary);
        }

        @media (max-width: 992px) {
            .search-wrapper {
                flex-wrap: wrap;
            }

            .filter-group {
                flex-wrap: wrap;
            }
        }

        /* ============================================ */
        /* KANBAN BOARD */
        /* ============================================ */
        .kanban-wrapper {
            position: relative;
            margin-top: 1rem;
        }

        .kanban-scroll-top {
            overflow-x: auto;
            overflow-y: hidden;
            height: 12px;
            margin-bottom: 10px;
        }

        .kanban-scroll-top::-webkit-scrollbar {
            height: 8px;
        }

        .kanban-board {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
            min-height: calc(100vh - 100px);
        }

        .kanban-col {
            min-width: 320px;
            width: 320px;
            background: white;
            border-radius: 12px;
            border: 1px solid var(--crm-border);
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 80px);
        }

        .kanban-col-header {
            padding: 0.75rem 1rem;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid var(--crm-border);
            background: #fafbfc;
            border-radius: 12px 12px 0 0;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .kanban-col-header-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .kanban-col-body {
            padding: 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            overflow-y: auto;
            flex: 1;
            min-height: 200px;
        }

        .add-student-to-col-btn {
            background: none;
            border: none;
            color: #4f46e5;
            cursor: pointer;
            font-size: 0.75rem;
            padding: 2px 6px;
            border-radius: 20px;
            transition: all 0.2s;
        }

        .add-student-to-col-btn:hover {
            background: #e0e7ff;
            transform: scale(1.05);
        }

        /* ============================================ */
        /* STUDENT CARD */
        /* ============================================ */
        .student-card {
            background: white;
            border: 1px solid var(--crm-border);
            border-radius: 10px;
            padding: 0.75rem;
            cursor: grab;
            transition: all 0.15s;
            position: relative;
        }

        .student-card:active {
            cursor: grabbing;
        }

        .student-card.dragging {
            opacity: 0.5;
        }

        .student-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        .student-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: #1f2937;
            padding-right: 70px;
        }

        .followup-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.65rem;
            font-weight: 600;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
        }

        .followup-badge.overdue {
            background: #fef2f2;
            color: var(--crm-danger);
        }

        .followup-badge.today {
            background: #fffbeb;
            color: var(--crm-warning);
        }

        .followup-badge.upcoming {
            background: #f0fdf4;
            color: var(--crm-success);
        }

        .followup-badge.none {
            background: #f3f4f6;
            color: var(--crm-gray);
        }

        /* ============================================ */
        /* RATING STARS */
        /* ============================================ */
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 2px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 14px;
            color: #d1d5db;
            cursor: pointer;
        }

        .star-rating input:checked~label,
        .star-rating label:hover,
        .star-rating label:hover~label {
            color: #fbbf24;
        }

        /* ============================================ */
        /* TAGS */
        /* ============================================ */
        .tags-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.3rem;
            margin: 0.5rem 0 0.3rem;
        }

        .tag {
            font-size: 0.65rem;
            background: #eef2ff;
            color: var(--crm-primary);
            border-radius: 12px;
            padding: 0.15rem 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .tag-remove {
            cursor: pointer;
            font-weight: bold;
            margin-left: 0.2rem;
            opacity: 0.7;
        }

        .tag-remove:hover {
            opacity: 1;
        }

        .add-tag-btn {
            font-size: 0.65rem;
            background: transparent;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 0.15rem 0.5rem;
            cursor: pointer;
            color: #6b7280;
            width: 100%;
            text-align: center;
            margin-top: 5px;
        }

        .add-tag-btn:hover {
            background: var(--crm-primary);
            color: white;
        }

        /* ============================================ */
        /* BOTTOM ROW */
        /* ============================================ */
        .bottom-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .insight-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }

        .insight-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .insight-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #eef2ff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stage-progress-item {
            margin-bottom: 1rem;
        }

        .stage-progress-header {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            margin-bottom: 0.25rem;
        }

        .progress-bar-custom {
            height: 6px;
            background: #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px;
            background: #f8fafc;
            border-radius: 12px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .quick-action-btn:hover {
            background: white;
            transform: translateX(4px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .quick-action-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        /* ============================================ */
        /* WEEKLY TASK REVIEW */
        /* ============================================ */
        .week-day-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px;
            border-radius: 12px;
            transition: all 0.2s;
            cursor: pointer;
        }

        .week-day-item.today {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
        }

        .week-day-item.today .day-name,
        .week-day-item.today .task-count,
        .week-day-item.today .task-status {
            color: white;
        }

        .week-day-item:hover:not(.today) {
            background: #f8fafc;
        }

        .day-name {
            width: 80px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .task-progress {
            flex: 1;
        }

        .task-bar-bg {
            height: 8px;
            background: #e5e7eb;
            border-radius: 20px;
            overflow: hidden;
        }

        .task-bar-fill {
            height: 100%;
            border-radius: 20px;
            transition: width 0.3s ease;
        }

        .task-stats {
            display: flex;
            gap: 12px;
            font-size: 0.7rem;
        }

        .task-count {
            font-weight: 700;
            font-size: 0.9rem;
            min-width: 40px;
            text-align: right;
        }

        .task-status {
            font-size: 0.7rem;
            min-width: 60px;
        }

        /* ============================================ */
        /* CALENDAR */
        /* ============================================ */
        .calendar-container {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            border: 1px solid var(--crm-border);
        }

        .fc-event {
            cursor: pointer;
        }

        .calendar-legend {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .legend-dot {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            display: inline-block;
            margin-right: 6px;
        }

        .fc-daygrid-day-frame {
            cursor: pointer;
        }

        .task-count-badge {
            position: absolute;
            bottom: 4px;
            right: 4px;
            background: #4f46e5;
            color: white;
            font-size: 10px;
            border-radius: 10px;
            padding: 2px 6px;
            min-width: 20px;
            text-align: center;
            cursor: pointer;
        }

        .fc-daygrid-day {
            position: relative;
        }

        .staff-tasks-popup {
            position: fixed;
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            z-index: 10001;
            min-width: 350px;
            max-width: 450px;
            display: none;
            border: 1px solid #e5e7eb;
            max-height: 80vh;
            overflow-y: auto;
        }

        .staff-group {
            margin-bottom: 1rem;
        }

        .staff-group-title {
            font-weight: 600;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e5e7eb;
        }

        .staff-task-item {
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 4px;
            transition: all 0.2s;
        }

        .staff-task-item:hover {
            background: #f8fafc;
            transform: translateX(4px);
        }

        /* ============================================ */
        /* LIST VIEW */
        /* ============================================ */
        .list-view-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        .list-view-table .table {
            margin-bottom: 0;
        }

        .list-view-table .table th {
            background: #f8fafc;
            border-bottom: 2px solid #e5e7eb;
        }

        /* ============================================ */
        /* FAB BUTTON */
        /* ============================================ */
        .fab-add-student {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--crm-primary);
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            transition: all 0.2s;
        }

        .fab-add-student:hover {
            background: var(--crm-primary-dark);
            transform: scale(1.05);
        }

        @media (max-width: 992px) {
            .bottom-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }

        @media (max-width: 768px) {
            .kanban-col {
                min-width: 280px;
                width: 280px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-3 px-md-4 py-3 py-md-4">

        <!-- ============================================ -->
        <!-- SECTION 1: STATS CARDS ROW -->
        <!-- ============================================ -->
        <div class="row g-3 mb-4">
            <!-- Card 1: All Students - SAME for both -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-card" style="--card-color: #4f46e5"
                    onclick="window.location.href='{{ route('crm.dashboard') }}'">
                    <div class="stat-content">
                        <div class="stat-left">
                            <div class="stat-number">{{ $stats['total_students'] ?? 0 }}</div>
                            <div class="stat-label">All Students</div>
                        </div>
                        <div class="stat-icon"><i class="fas fa-users" style="color:#4f46e5"></i></div>
                    </div>
                </div>
            </div>
            @if (auth()->user()->role !== 'admin')
                <!-- Card 2: My Students - DIFFERENT numbers for Admin vs Staff -->
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-card" style="--card-color: #3b82f6"
                        onclick="window.location.href='{{ route('crm.dashboard', ['assignee_id' => auth()->id()]) }}'">
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
            @if (auth()->user()->role !== 'admin')
                <!-- Card 3: Overdue Tasks -->
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-card" style="--card-color: #ef4444"
                        onclick="window.location.href='{{ route('crm.dashboard', ['activity_filter' => 'my_overdue']) }}'">
                        <div class="stat-content">
                            <div class="stat-left">
                                <div class="stat-number">{{ $stats['my_overdue'] ?? 0 }}</div>
                                <div class="stat-label">Overdue Tasks</div>
                            </div>
                            <div class="stat-icon"><i class="fas fa-clock" style="color:#ef4444"></i></div>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-card" style="--card-color: #ef4444"
                        onclick="window.location.href='{{ route('crm.dashboard', ['activity_filter' => 'overdue']) }}'">
                        <div class="stat-content">
                            <div class="stat-left">
                                <div class="stat-number">{{ $stats['overdue'] ?? 0 }}</div>
                                <div class="stat-label">Overdue Tasks</div>
                            </div>
                            <div class="stat-icon"><i class="fas fa-clock" style="color:#ef4444"></i></div>
                        </div>
                    </div>
                </div>
            @endif

            @if (auth()->user()->role !== 'admin')
                <!-- Card 4: Today's Tasks -->
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-card" style="--card-color: #f59e0b"
                        onclick="window.location.href='{{ route('crm.dashboard', ['activity_filter' => 'my_today']) }}'">
                        <div class="stat-content">
                            <div class="stat-left">
                                <div class="stat-number">{{ $stats['my_today'] ?? 0 }}</div>
                                <div class="stat-label">Today's Tasks</div>
                            </div>
                            <div class="stat-icon"><i class="fas fa-calendar-day" style="color:#f59e0b"></i></div>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-card" style="--card-color: #f59e0b"
                        onclick="window.location.href='{{ route('crm.dashboard', ['activity_filter' => 'today']) }}'">
                        <div class="stat-content">
                            <div class="stat-left">
                                <div class="stat-number">{{ $stats['today'] ?? 0 }}</div>
                                <div class="stat-label">Today's Tasks</div>
                            </div>
                            <div class="stat-icon"><i class="fas fa-calendar-day" style="color:#f59e0b"></i></div>
                        </div>
                    </div>
                </div>
            @endif


            @if (auth()->user()->role !== 'admin')
                <!-- Card 5: Upcoming Tasks -->
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-card" style="--card-color: #10b981"
                        onclick="window.location.href='{{ route('crm.dashboard', ['activity_filter' => 'my_upcoming']) }}'">
                        <div class="stat-content">
                            <div class="stat-left">
                                <div class="stat-number">{{ $stats['my_upcoming'] ?? 0 }}</div>
                                <div class="stat-label">Upcoming Tasks</div>
                            </div>
                            <div class="stat-icon"><i class="fas fa-calendar-week" style="color:#10b981"></i></div>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-card" style="--card-color: #10b981"
                        onclick="window.location.href='{{ route('crm.dashboard', ['activity_filter' => 'upcoming']) }}'">
                        <div class="stat-content">
                            <div class="stat-left">
                                <div class="stat-number">{{ $stats['upcoming'] ?? 0 }}</div>
                                <div class="stat-label">Upcoming Tasks</div>
                            </div>
                            <div class="stat-icon"><i class="fas fa-calendar-week" style="color:#10b981"></i></div>
                        </div>
                    </div>
                </div>
            @endif
            @if (auth()->user()->role == 'admin')
                <!-- Card 6: Completed This Week -->
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-card" style="--card-color: #8b5cf6">
                        <div class="stat-content">
                            <div class="stat-left">
                                <div class="stat-number">{{ $stats['completed_today'] ?? 0 }}</div>
                                <div class="stat-label">Completed Today</div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-check-circle" style="color:#14622b"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if (auth()->user()->role !== 'admin')
                <!-- Card 6: Completed This Week -->
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-card" style="--card-color: #8b5cf6">
                        <div class="stat-content">
                            <div class="stat-left">
                                <div class="stat-number">{{ $stats['completed_this_week'] ?? 0 }}</div>
                                <div class="stat-label">Completed This Week</div>
                            </div>
                            <div class="stat-icon"><i class="fas fa-chart-line" style="color:#8b5cf6"></i></div>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-card" style="--card-color: #8b5cf6">
                        <div class="stat-content">
                            <div class="stat-left">
                                <div class="stat-number">{{ $stats['completed_this_week'] ?? 0 }}</div>
                                <div class="stat-label">Completed This Week</div>
                            </div>
                            <div class="stat-icon"><i class="fas fa-chart-line" style="color:#8b5cf6"></i></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- ============================================ -->
        <!-- SECTION 2: TOOLBAR / FILTERS -->
        <!-- ============================================ -->
        <form method="GET" id="filterForm" class="crm-toolbar">
            <input type="hidden" name="view" value="{{ $view ?? 'kanban' }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-5 col-lg-6">
                    <div class="search-wrapper">
                        <select name="search_type" class="form-select form-select-sm">
                            <option value="all" {{ request('search_type') == 'all' ? 'selected' : '' }}>All</option>
                            <option value="name" {{ request('search_type') == 'name' ? 'selected' : '' }}>Name</option>
                            <option value="phone" {{ request('search_type') == 'phone' ? 'selected' : '' }}>Phone
                            </option>
                            <option value="email" {{ request('search_type') == 'email' ? 'selected' : '' }}>Email
                            </option>
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
                                <option value="{{ $stage->id }}" @selected(request('stage_id') == $stage->id)>{{ $stage->name }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Assignee Filter - Only visible to Admin -->
                        @if ($isAdmin && $assignees->count())
                            <select name="assignee_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Filter by Staff</option>
                                @foreach ($assignees as $a)
                                    <option value="{{ $a->id }}" @selected(request('assignee_id') == $a->id)>{{ $a->name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif

                        <select name="activity_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Activity</option>
                            <option value="overdue" @selected(request('activity_filter') == 'overdue')>Has Overdue</option>
                            <option value="today" @selected(request('activity_filter') == 'today')>Active Today</option>
                            <option value="upcoming" @selected(request('activity_filter') == 'upcoming')>Has Upcoming</option>
                        </select>

                        <div class="btn-group btn-group-sm">
                            <button type="button" onclick="setView('kanban')"
                                class="btn btn-outline-secondary view-btn {{ ($view ?? 'kanban') == 'kanban' ? 'active' : '' }}">Kanban</button>
                            <button type="button" onclick="setView('list')"
                                class="btn btn-outline-secondary view-btn {{ ($view ?? 'kanban') == 'list' ? 'active' : '' }}">List</button>
                            <button type="button" onclick="setView('calendar')"
                                class="btn btn-outline-secondary view-btn {{ ($view ?? 'kanban') == 'calendar' ? 'active' : '' }}">Calendar</button>
                        </div>

                        @if (request()->hasAny(['search', 'stage_id', 'assignee_id', 'activity_filter']))
                            <a href="{{ route('crm.dashboard') }}" class="btn btn-outline-danger btn-sm">Clear</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        <!-- ============================================ -->
        <!-- SECTION 3: DYNAMIC VIEW -->
        <!-- ============================================ -->

        <!-- ========== KANBAN VIEW ========== -->
        @if (($view ?? 'kanban') === 'kanban')
            <div class="kanban-wrapper">
                <div class="kanban-scroll-top" id="kanbanScrollTop">
                    <div style="height:1px" id="scrollInner"></div>
                </div>
                <div class="kanban-board" id="kanbanBoard">
                    @foreach ($stages as $stage)
                        @php $colStudents = isset($students[$stage->id]) ? $students[$stage->id] : collect(); @endphp
                        <div class="kanban-col" data-stage-id="{{ $stage->id }}">
                            <div class="kanban-col-header" style="background: {{ $stage->color }}10;">
                                <span>
                                    <span
                                        style="background:{{ $stage->color }}; width:10px; height:10px; border-radius:50%; display:inline-block; margin-right:8px;"></span>
                                    {{ $stage->name }}
                                </span>
                                <div class="kanban-col-header-actions">
                                    <span class="badge bg-secondary rounded-pill">{{ $colStudents->count() }}</span>
                                    @if ($isAdmin)
                                        <button type="button" class="add-student-to-col-btn"
                                            onclick="openAddStudentModal({{ $stage->id }}, '{{ addslashes($stage->name) }}')"
                                            title="Add student to {{ $stage->name }}">
                                            <i class="fas fa-plus-circle"></i>
                                        </button>
                                    @endif
                                </div>
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
                                        } elseif ($task && $task->scheduled_for?->isToday()) {
                                            $fClass = 'today';
                                            $fLabel = '📅 Today';
                                        } elseif ($upcoming) {
                                            $fClass = 'upcoming';
                                            $fLabel = '📅 ' . ($task?->scheduled_for?->format('d M') ?? 'Upcoming');
                                        } else {
                                            $fClass = 'none';
                                            $fLabel = 'No tasks';
                                        }
                                    @endphp
                                    <div class="student-card" data-student-id="{{ $student->id }}" draggable="true">
                                        <a href="{{ route('crm.student.show', $student) }}"
                                            class="text-decoration-none text-dark">
                                            <div class="student-name">{{ $student->full_name }}</div>
                                            <div class="small text-muted"><i class="fas fa-globe-americas"></i>
                                                {{ $student->preferred_country ?? 'N/A' }}</div>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <div class="small"><i class="fas fa-phone-alt"></i>
                                                    {{ $student->phone_number ?? '—' }}</div>
                                                <form class="rating-form"
                                                    action="{{ route('crm.dashboard.updateRating', $student->id) }}"
                                                    method="POST">
                                                    @csrf @method('PUT')
                                                    <div class="star-rating">
                                                        <input type="radio" name="rating"
                                                            id="star3_{{ $student->id }}" value="3"
                                                            {{ ($student->rating ?? '') == 3 ? 'checked' : '' }}>
                                                        <label for="star3_{{ $student->id }}">★</label>
                                                        <input type="radio" name="rating"
                                                            id="star2_{{ $student->id }}" value="2"
                                                            {{ ($student->rating ?? '') == 2 ? 'checked' : '' }}>
                                                        <label for="star2_{{ $student->id }}">★</label>
                                                        <input type="radio" name="rating"
                                                            id="star1_{{ $student->id }}" value="1"
                                                            {{ ($student->rating ?? '') == 1 ? 'checked' : '' }}>
                                                        <label for="star1_{{ $student->id }}">★</label>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="followup-badge {{ $fClass }}">{{ $fLabel }}</div>
                                            @if ($isAdmin && $student->agent)
                                                <div class="small text-muted mt-1"><i class="fas fa-user-tie"></i>
                                                    {{ $student->agent->name }}</div>
                                            @endif
                                        </a>
                                        <div class="tags-list">
                                            @if ($student->tags && is_array($student->tags))
                                                @foreach ($student->tags as $tag)
                                                    <span class="tag"><i class="fas fa-tag"></i>
                                                        {{ $tag }}<span class="tag-remove"
                                                            data-tag="{{ $tag }}"
                                                            onclick="event.stopPropagation(); window.removeTag({{ $student->id }}, '{{ addslashes($tag) }}')">×</span></span>
                                                @endforeach
                                            @endif
                                        </div>
                                        <button type="button" class="add-tag-btn"
                                            data-student-id="{{ $student->id }}"><i class="fas fa-plus"></i> Add
                                            tag</button>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-4 small">No students</div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- ========== BOTTOM ROW ========== -->
            <div class="bottom-row">
                <!-- Pipeline Strategy -->
                <div class="insight-card">
                    <div class="insight-title"><i class="fas fa-chart-line" style="color:#4f46e5;"></i> Pipeline Strategy
                    </div>
                    @php
                        $totalStudents = $stats['total_students'] ?? 1;
                        $maxStageCount = 0;
                        $maxStageName = '';
                        foreach ($stages as $stage) {
                            $count = isset($students[$stage->id]) ? $students[$stage->id]->count() : 0;
                            if ($count > $maxStageCount) {
                                $maxStageCount = $count;
                                $maxStageName = $stage->name;
                            }
                        }
                    @endphp
                    @foreach ($stages->take(5) as $stage)
                        @php
                            $colCount = isset($students[$stage->id]) ? $students[$stage->id]->count() : 0;
                            $percent = $totalStudents > 0 ? ($colCount / $totalStudents) * 100 : 0;
                        @endphp
                        <div class="stage-progress-item">
                            <div class="stage-progress-header"><span>{{ $stage->name }}</span><span>{{ $colCount }}
                                    / {{ $totalStudents }}</span></div>
                            <div class="progress-bar-custom">
                                <div class="progress-fill"
                                    style="width: {{ $percent }}%; background: {{ $stage->color }};"></div>
                            </div>
                        </div>
                    @endforeach
                    <div class="mt-3 pt-2 border-top">
                        <div class="d-flex justify-content-between mb-2">
                            <div><small>Total Pipeline</small><br><strong>{{ $totalStudents }}</strong></div>
                            <div><small>Highest Stage</small><br><strong>{{ $maxStageName }}
                                    ({{ $maxStageCount }})</strong></div>
                            <div><small>Conversion
                                    Rate</small><br><strong>{{ $stages->count() > 0 ? round((($students[$stages->last()->id] ?? collect())->count() / max($totalStudents, 1)) * 100, 1) : 0 }}%</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="insight-card">
                    <div class="insight-title"><i class="fas fa-bolt" style="color:#f59e0b;"></i> Quick Actions</div>
                    <div class="quick-action-btn" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                        <div class="quick-action-icon" style="background:#e0e7ff;color:#4f46e5;"><i
                                class="fas fa-user-plus"></i></div>
                        <div>
                            <div style="font-weight:600;font-size:0.85rem;">Add New Student</div>
                            <div style="font-size:0.7rem;color:#6b7280;">Create a new student profile</div>
                        </div>
                    </div>
                    <div class="quick-action-btn" onclick="setView('calendar')">
                        <div class="quick-action-icon" style="background:#fed7aa;color:#d97706;"><i
                                class="fas fa-calendar-alt"></i></div>
                        <div>
                            <div style="font-weight:600;font-size:0.85rem;">View Calendar</div>
                            <div style="font-size:0.7rem;color:#6b7280;">See all upcoming tasks</div>
                        </div>
                    </div>
                    <div class="quick-action-btn"
                        onclick="window.location.href='{{ route('crm.dashboard', ['activity_filter' => 'overdue']) }}'">
                        <div class="quick-action-icon" style="background:#fee2e2;color:#dc2626;"><i
                                class="fas fa-clock"></i></div>
                        <div>
                            <div style="font-weight:600;font-size:0.85rem;">Review Overdue Tasks</div>
                            <div style="font-size:0.7rem;color:#6b7280;">{{ $stats['overdue'] ?? 0 }} tasks need attention
                            </div>
                        </div>
                    </div>
                    <div class="quick-action-btn" onclick="window.location.href='{{ route('crm.export') }}'">
                        <div class="quick-action-icon" style="background:#fef3c7;color:#d97706;"><i
                                class="fas fa-download"></i></div>
                        <div>
                            <div style="font-weight:600;font-size:0.85rem;">Export Data</div>
                            <div style="font-size:0.7rem;color:#6b7280;">Download students list</div>
                        </div>
                    </div>
                </div>

                <!-- Weekly Task Review -->
                <div class="insight-card">
                    <div class="insight-title"><i class="fas fa-calendar-week" style="color:#8b5cf6;"></i> Weekly Task
                        Review</div>
                    <div class="weekly-task-container" id="weeklyTaskContainer">
                        <div class="text-center py-3">
                            <div class="loader-spinner" style="width:30px;height:30px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- ========== LIST VIEW ========== -->
        @if (($view ?? 'kanban') === 'list')
            <div class="list-view-table">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student Name</th>
                                <th>Contact</th>
                                <th>Country</th>
                                <th>Stage</th>
                                @if ($isAdmin)
                                    <th>Assigned To</th>
                                @endif
                                <th>
                                    Tasks</th>
                                <th>Rating</th>
                                <th>Tags</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students ?? [] as $student)
                                <tr>
                                    <td class="fw-semibold">{{ $student->full_name }}</td>
                                    <td>
                                        <div class="small">{{ $student->phone_number ?? '—' }}</div>
                                        <div class="small text-muted">{{ $student->email ?? '—' }}</div>
                                    </td>
                                    <td>{{ $student->preferred_country ?? '—' }}</td>
                                    <td><span class="badge"
                                            style="background: {{ $student->currentStage?->color ?? '#6b7280' }}">{{ $student->currentStage?->name ?? 'Unknown' }}</span>
                                    </td>
                                    @if ($isAdmin)
                                        <td>{{ $student->agent?->name ?? 'Unassigned' }}</td>
                                    @endif
                                    <td><span class="badge bg-danger">{{ $student->overdueActivities->count() }}
                                            overdue</span> <span
                                            class="badge bg-warning">{{ $student->pendingActivities->count() }}
                                            pending</span></td>
                                    <td>
                                        <div class="star-rating">
                                            @for ($i = 3; $i >= 1; $i--)
                                                <input type="radio" name="rating_list_{{ $student->id }}"
                                                    value="{{ $i }}"
                                                    {{ ($student->rating ?? '') == $i ? 'checked' : '' }}
                                                    onchange="updateRating({{ $student->id }}, {{ $i }})"><label>★</label>
                                            @endfor
                                        </div>
                                    </td>
                                    <td>
                                        @if ($student->tags && is_array($student->tags))
                                            @foreach (array_slice($student->tags, 0, 2) as $tag)
                                                <span class="badge bg-secondary me-1">{{ $tag }}</span>
                                            @endforeach
                                            @if (count($student->tags) > 2)
                                                <span class="badge bg-light">+{{ count($student->tags) - 2 }}</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td><a href="{{ route('crm.student.show', $student) }}"
                                            class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isAdmin ? 9 : 8 }}" class="text-center py-5 text-muted">No students
                                        found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if (isset($students) && method_exists($students, 'links'))
                    <div class="p-3">{{ $students->withQueryString()->links() }}</div>
                @endif
            </div>
        @endif

        <!-- ========== CALENDAR VIEW ========== -->
        @if (($view ?? 'kanban') === 'calendar')
            <div class="calendar-container">
                <div class="calendar-legend">
                    <div><span class="legend-dot" style="background:#ef4444;"></span> Overdue</div>
                    <div><span class="legend-dot" style="background:#f59e0b;"></span> Today</div>
                    <div><span class="legend-dot" style="background:#10b981;"></span> Upcoming</div>
                </div>
                <div id="workCalendar"></div>
            </div>
        @endif

    </div>

    <!-- ============================================ -->
    <!-- SECTION 4: MODALS -->
    <!-- ============================================ -->

    <div class="loader-overlay" id="globalLoader">
        <div class="loader-spinner"></div>
    </div>
    <button class="fab-add-student" data-bs-toggle="modal" data-bs-target="#addStudentModal"><i
            class="fas fa-plus"></i></button>

    <!-- Task Detail Modal -->
    <div class="modal fade" id="taskDetailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-tasks me-2"></i>Task Details</h5><button type="button"
                        class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="taskDetailBody">
                    <div class="text-center py-4">
                        <div class="loader-spinner" style="width:30px;height:30px;"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Close</button><a href="#" id="taskDetailLink"
                        class="btn btn-primary">View Student Profile</a></div>
            </div>
        </div>
    </div>

    <!-- Tag Modal -->
    <div class="modal fade" id="tagModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-tag me-2"></i>Add Tag</h5><button type="button"
                        class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="tagInput" class="form-control" placeholder="Enter tag name..."
                        maxlength="50">
                    <div class="mt-3"><label class="small text-muted">Popular tags</label>
                        <div id="suggestedTagsList" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary"
                        id="saveTagBtn">Add Tag</button></div>
            </div>
        </div>
    </div>

    <!-- Staff Tasks Popup (For Calendar) -->
    <div class="staff-tasks-popup" id="staffTasksPopup">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0" id="popupTitle">Tasks</h6><button class="btn-close" onclick="closeStaffPopup()"></button>
        </div>
        <div id="staffTasksList"></div>
    </div>

    <!-- Add Student Modals -->
    @if ($isAdmin)
        <div class="modal fade" id="addStudentToColModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add Student to <span
                                id="colStageName"></span></h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('crm.student.store') }}">@csrf
                        <input type="hidden" name="current_stage_id" id="colStageId">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">First Name *</label><input type="text"
                                        name="first_name" class="form-control" required></div>
                                <div class="col-md-6"><label class="form-label">Last Name *</label><input type="text"
                                        name="last_name" class="form-control" required></div>
                                <div class="col-md-6"><label class="form-label">Email</label><input type="email"
                                        name="email" class="form-control"></div>
                                <div class="col-md-6"><label class="form-label">Phone Number</label><input type="text"
                                        name="phone_number" class="form-control"></div>
                                <div class="col-md-6"><label class="form-label">Preferred Country</label><input
                                        type="text" name="preferred_country" class="form-control"></div>
                                <div class="col-md-6"><label class="form-label">Assign to Staff</label><select
                                        name="agent_id" class="form-select">
                                        <option value="">Select Staff</option>
                                        @foreach ($assignees as $assignee)
                                            <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add
                                Student</button></div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade" id="addStudentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New Student</h5><button
                        type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('crm.student.store') }}" method="POST">@csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">First Name *</label><input type="text"
                                    name="first_name" class="form-control" required></div>
                            <div class="col-md-6"><label class="form-label">Last Name *</label><input type="text"
                                    name="last_name" class="form-control" required></div>
                            <div class="col-md-6"><label class="form-label">Email</label><input type="email"
                                    name="email" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Phone Number</label><input type="text"
                                    name="phone_number" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Preferred Country</label><input
                                    type="text" name="preferred_country" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Stage *</label><select
                                    name="current_stage_id" class="form-select" required>
                                    @foreach ($stages as $stage)
                                        <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($isAdmin)
                                <div class="col-md-6"><label class="form-label">Assign to Staff</label><select
                                        name="agent_id" class="form-select">
                                        <option value="">Select Staff</option>
                                        @foreach ($assignees as $assignee)
                                            <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                                        @endforeach
                                    </select></div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add
                            Student</button></div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script>
        // ============================================ //
        // GLOBAL VARIABLES
        // ============================================ //
        let currentStudentId = null;
        let calendar = null;
        const isAdmin = {{ $isAdmin ? 'true' : 'false' }};

        // ============================================ //
        // UTILITY FUNCTIONS
        // ============================================ //
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast-notification ${type}`;
            toast.innerHTML =
                `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i><span>${message}</span><i class="fas fa-times" onclick="this.parentElement.remove()"></i>`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        function showLoader() {
            document.getElementById('globalLoader').classList.add('show');
        }

        function hideLoader() {
            document.getElementById('globalLoader').classList.remove('show');
        }

        function setView(view) {
            document.querySelector('[name="view"]').value = view;
            document.getElementById('filterForm').submit();
        }

        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        }

        function closeStaffPopup() {
            document.getElementById('staffTasksPopup').style.display = 'none';
        }

        function escapeHtml(str) {
            if (!str) return '';
            const d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        }

        @if ($isAdmin)
            function openAddStudentModal(stageId, stageName) {
                document.getElementById('colStageId').value = stageId;
                document.getElementById('colStageName').innerText = stageName;
                new bootstrap.Modal(document.getElementById('addStudentToColModal')).show();
            }
        @endif

        // ============================================ //
        // KANBAN FUNCTIONS
        // ============================================ //
        function initKanbanScroll() {
            const board = document.querySelector('.kanban-board');
            const topScroll = document.getElementById('kanbanScrollTop');
            const inner = document.getElementById('scrollInner');
            if (!board) return;
            const updateInner = () => {
                if (inner) inner.style.width = board.scrollWidth + 'px';
            };
            updateInner();
            if (topScroll) topScroll.onscroll = () => board.scrollLeft = topScroll.scrollLeft;
            board.onscroll = () => {
                if (topScroll) topScroll.scrollLeft = board.scrollLeft;
            };
            new ResizeObserver(updateInner).observe(board);
        }

        function handleDragStart(e) {
            this.classList.add('dragging');
            e.dataTransfer.setData('text/plain', JSON.stringify({
                studentId: this.dataset.studentId,
                sourceStageId: this.closest('.kanban-col')?.dataset.stageId
            }));
        }

        function handleDragEnd(e) {
            this.classList.remove('dragging');
            document.querySelectorAll('.kanban-col-body').forEach(z => z.classList.remove('drag-over'));
        }

        function handleDragOver(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        }

        function handleDragLeave(e) {
            this.classList.remove('drag-over');
        }
        async function handleDrop(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            let data;
            try {
                data = JSON.parse(e.dataTransfer.getData('text/plain'));
            } catch (err) {
                return;
            }
            const {
                studentId,
                sourceStageId
            } = data;
            const targetStageId = this.dataset.stageId;
            if (!targetStageId || sourceStageId === targetStageId) return;
            showLoader();
            try {
                const res = await fetch(`/crm/students/${studentId}/stage`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        stage_id: parseInt(targetStageId)
                    })
                });
                const result = await res.json();
                if (result.success) {
                    showToast('Student moved successfully', 'success');
                    location.reload();
                } else throw new Error(result.error);
            } catch (err) {
                showToast(err.message, 'error');
            } finally {
                hideLoader();
            }
        }

        function initDragAndDrop() {
            document.querySelectorAll('.student-card[draggable="true"]').forEach(c => {
                c.ondragstart = handleDragStart;
                c.ondragend = handleDragEnd;
            });
            document.querySelectorAll('.kanban-col-body').forEach(z => {
                z.ondragover = handleDragOver;
                z.ondragleave = handleDragLeave;
                z.ondrop = handleDrop;
            });
        }

        // ============================================ //
        // WEEKLY TASK REVIEW
        // ============================================ //

        async function loadWeeklyTaskReview() {
            const container = document.getElementById('weeklyTaskContainer');
            if (!container) return;

            showLoader();

            try {
                const res = await fetch('{{ route('crm.weekly.tasks') }}', {
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken()
                    }
                });

                const data = await res.json();
                hideLoader();

                // Sunday-first UI
                const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

                const jsToday = new Date().getDay();
                // JS: 0=Sunday ... 6=Saturday

                let html = '';

                for (let i = 0; i < days.length; i++) {

                    /**
                     * IMPORTANT FIX:
                     * Backend usually returns Monday-first arrays:
                     * index 0 = Monday ... index 6 = Sunday
                     *
                     * So we remap:
                     * Sunday (0) -> 6
                     * Monday (1) -> 0
                     * Tuesday (2) -> 1
                     * ...
                     */
                    const apiIndex = i === 0 ? 6 : i - 1;

                    const completed = data.completed?.[apiIndex] || 0;
                    const pending = data.pending?.[apiIndex] || 0;

                    const total = completed + pending;
                    const percent = total > 0 ? (completed / total) * 100 : 0;

                    const isToday = i === jsToday;

                    let statusText =
                        isToday ?
                        'Today' :
                        completed > 0 && pending === 0 ?
                        'Completed' :
                        completed > 0 ?
                        'In Progress' :
                        pending > 0 ?
                        'Pending' :
                        'No Tasks';

                    html += `
                <div class="week-day-item ${isToday ? 'today' : ''}" onclick="setView('calendar')">
                    <div class="day-name">${days[i]}</div>

                    <div class="task-progress">
                        <div class="task-bar-bg">
                            <div class="task-bar-fill"
                                 style="width: ${percent}%; background: ${isToday ? '#fff' : '#4f46e5'}">
                            </div>
                        </div>

                        <div class="task-stats">
                            <span>✅ ${completed} done</span>
                            <span>📋 ${pending} todo</span>
                        </div>
                    </div>

                    <div class="task-count">${total}</div>
                    <div class="task-status">${statusText}</div>
                </div>
            `;
                }

                container.innerHTML = html;

            } catch (err) {
                hideLoader();
                console.error(err);
                container.innerHTML = '<div class="text-center text-muted">Failed to load</div>';
            }
        }

        // ============================================ //
        // CALENDAR FUNCTIONS
        // ============================================ //
        async function fetchStaffTasksForDate(date) {
            showLoader();
            try {
                const res = await fetch(`/crm/calendar/staff-tasks?date=${date}`, {
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken()
                    }
                });
                const data = await res.json();
                hideLoader();
                const popup = document.getElementById('staffTasksPopup');
                const list = document.getElementById('staffTasksList');
                document.getElementById('popupTitle').innerText = `Tasks for ${new Date(date).toLocaleDateString()}`;

                if (data.tasks && data.tasks.length > 0) {
                    if (isAdmin) {
                        // Group by staff for admin
                        const groups = {};
                        data.tasks.forEach(t => {
                            const name = t.assigned_to_name || 'Unassigned';
                            if (!groups[name]) groups[name] = [];
                            groups[name].push(t);
                        });
                        list.innerHTML = Object.entries(groups).map(([name, tasks]) => `
                            <div class="staff-group">
                                <div class="staff-group-title">👤 ${name} (${tasks.length} tasks)</div>
                                ${tasks.map(t => `<div class="staff-task-item" onclick="window.location.href='/crm/student/${t.student_id}'">📌 ${escapeHtml(t.title)} - ${escapeHtml(t.student_name)}</div>`).join('')}
                            </div>
                        `).join('');
                    } else {
                        // For staff, show individual tasks
                        list.innerHTML = data.tasks.map(t => `
                            <div class="staff-task-item p-2 mb-2" onclick="window.location.href='/crm/student/${t.student_id}'">
                                <strong>${escapeHtml(t.student_name)}</strong><br>
                                <small class="text-muted">${escapeHtml(t.title)}</small>
                                <span class="badge ${t.priority === 'high' ? 'bg-danger' : (t.priority === 'medium' ? 'bg-warning' : 'bg-success')} ms-2">${t.priority || 'Medium'}</span>
                            </div>
                        `).join('');
                    }
                } else {
                    list.innerHTML = '<div class="text-center py-4 text-muted">No tasks for this day</div>';
                }

                popup.style.display = 'block';
                popup.style.top = '50%';
                popup.style.left = '50%';
                popup.style.transform = 'translate(-50%, -50%)';
            } catch (err) {
                hideLoader();
                showToast(err.message, 'error');
            }
        }

        function initCalendar() {
            const el = document.getElementById('workCalendar');
            if (!el) return;

            calendar = new FullCalendar.Calendar(el, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: function(info, success, failure) {
                    showLoader();
                    fetch('{{ route('crm.calendar.events') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken()
                            },
                            body: JSON.stringify({
                                start: info.startStr,
                                end: info.endStr
                            })
                        })
                        .then(r => r.json())
                        .then(data => {
                            hideLoader();
                            success(data.events || []);
                            updateDayNumbers(data.events || []);
                        })
                        .catch(err => {
                            hideLoader();
                            failure(err);
                        });
                },
                dateClick: function(info) {
                    if (isAdmin) fetchStaffTasksForDate(info.dateStr);
                },
                eventClick: function(info) {
                    const props = info.event.extendedProps;
                    if (isAdmin) {
                        fetchStaffTasksForDate(info.event.startStr);
                    } else {
                        const modalBody = document.getElementById('taskDetailBody');
                        modalBody.innerHTML = `
                            <div class="mb-3"><label class="text-muted">Task</label><p>${escapeHtml(info.event.title)}</p></div>
                            <div class="mb-3"><label class="text-muted">Student</label><p><i class="fas fa-user"></i> ${escapeHtml(props.student_name || 'Unknown')}<br><i class="fas fa-globe"></i> ${escapeHtml(props.student_country || 'N/A')}</p></div>
                            <div class="mb-3"><label class="text-muted">Due Date</label><p>${info.event.start ? info.event.start.toLocaleString() : 'No date'}</p></div>
                            <div class="mb-3"><label class="text-muted">Priority</label><p><span class="badge ${props.priority === 'high' ? 'bg-danger' : (props.priority === 'medium' ? 'bg-warning' : 'bg-success')}">${props.priority || 'Medium'}</span></p></div>
                            <div class="mb-3"><label class="text-muted">Description</label><p class="small">${escapeHtml(props.description || 'No description')}</p></div>
                        `;
                        document.getElementById('taskDetailLink').href = `/crm/student/${props.student_id}`;
                        new bootstrap.Modal(document.getElementById('taskDetailModal')).show();
                    }
                },
                eventDidMount: function(info) {
                    if (!isAdmin) {
                        info.el.setAttribute('title',
                            `${info.event.extendedProps?.student_name || 'Unknown'}\n${info.event.title}`);
                    }
                },
                dayHeaderDidRender: function(arg) {
                    if (isAdmin) {
                        arg.el.style.cursor = 'pointer';
                        arg.el.title = 'Click to see staff tasks';
                    }
                },
                height: 650
            });
            calendar.render();
        }

        function updateDayNumbers(events) {
            const counts = {};
            events.forEach(e => {
                const d = new Date(e.start).toDateString();
                counts[d] = (counts[d] || 0) + 1;
            });

            document.querySelectorAll('.fc-daygrid-day').forEach(day => {
                const date = day.getAttribute('data-date');
                if (date) {
                    const count = counts[new Date(date).toDateString()] || 0;
                    const old = day.querySelector('.task-count-badge');
                    if (old) old.remove();
                    if (count > 0) {
                        const badge = document.createElement('span');
                        badge.className = 'task-count-badge';
                        badge.innerText = count;
                        if (isAdmin) badge.style.cursor = 'pointer';
                        day.appendChild(badge);
                    }
                }
            });
        }

        // ============================================ //
        // TAG FUNCTIONS
        // ============================================ //
        window.openTagModal = function(id) {
            currentStudentId = id;
            new bootstrap.Modal(document.getElementById('tagModal')).show();
            document.getElementById('tagInput').value = '';
            loadPopularTags();
        };

        async function loadPopularTags() {
            try {
                const res = await fetch('{{ route('crm.student.popularTags') }}');
                const data = await res.json();
                const container = document.getElementById('suggestedTagsList');
                if (container && data.tags) {
                    container.innerHTML = data.tags.map(t =>
                        `<span class="badge bg-light text-dark p-2" style="cursor:pointer" onclick="document.getElementById('tagInput').value='${escapeHtml(t)}'">🏷️ ${escapeHtml(t)}</span>`
                    ).join('');
                }
            } catch (e) {
                console.error(e);
            }
        }

        window.saveTag = async function() {
            const tag = document.getElementById('tagInput').value.trim();
            if (!tag) {
                showToast('Enter a tag', 'error');
                return;
            }
            showLoader();
            try {
                const res = await fetch(`/crm/students/${currentStudentId}/add-tag`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        tag
                    })
                });
                const data = await res.json();
                if (data.success) {
                    updateTagsInCard(currentStudentId, data.tags);
                    bootstrap.Modal.getInstance(document.getElementById('tagModal')).hide();
                    showToast('Tag added', 'success');
                }
            } catch (err) {
                showToast(err.message, 'error');
            } finally {
                hideLoader();
            }
        };

        window.removeTag = async function(id, tag) {
            if (!confirm(`Remove "${tag}"?`)) return;
            showLoader();
            try {
                const res = await fetch(`/crm/students/${id}/remove-tag`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        tag
                    })
                });
                const data = await res.json();
                if (data.success) {
                    updateTagsInCard(id, data.tags);
                    showToast('Tag removed', 'success');
                }
            } catch (err) {
                showToast(err.message, 'error');
            } finally {
                hideLoader();
            }
        };

        function updateTagsInCard(id, tags) {
            const card = document.querySelector(`.student-card[data-student-id="${id}"]`);
            if (!card) return;
            const container = card.querySelector('.tags-list');
            if (!container) return;
            if (tags?.length) {
                container.innerHTML = tags.map(t =>
                    `<span class="tag"><i class="fas fa-tag"></i> ${escapeHtml(t)}<span class="tag-remove" onclick="event.stopPropagation(); window.removeTag(${id}, '${escapeHtml(t)}')">×</span></span>`
                ).join('');
            } else {
                container.innerHTML = '';
            }
        }

        // ============================================ //
        // RATING FUNCTIONS
        // ============================================ //
        async function handleRating(e) {
            e.stopPropagation();
            const radio = this,
                form = radio.closest('.rating-form');
            if (!form) return;
            showLoader();
            try {
                const res = await fetch(form.action, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        rating: radio.value
                    })
                });
                const data = await res.json();
                if (data.success) showToast('Rating updated', 'success');
            } catch (err) {
                showToast(err.message, 'error');
            } finally {
                hideLoader();
            }
        }

        window.updateRating = async function(id, rating) {
            showLoader();
            try {
                const res = await fetch(`/crm/students/${id}/rating`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        rating
                    })
                });
                const data = await res.json();
                if (data.success) showToast('Rating updated', 'success');
            } catch (err) {
                showToast(err.message, 'error');
            } finally {
                hideLoader();
            }
        };

        // ============================================ //
        // INITIALIZATION
        // ============================================ //
        let searchTimer;
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => document.getElementById('filterForm').submit(), 500);
                });
            }

            @if (($view ?? 'kanban') === 'kanban')
                setTimeout(() => {
                    initKanbanScroll();
                    initDragAndDrop();
                    loadWeeklyTaskReview();
                }, 100);
            @endif

            @if (($view ?? 'kanban') === 'calendar')
                initCalendar();
            @endif

            document.querySelectorAll('.star-rating input').forEach(i => {
                i.onchange = handleRating;
            });
            document.getElementById('saveTagBtn')?.addEventListener('click', window.saveTag);
            document.querySelectorAll('.add-tag-btn').forEach(btn => {
                btn.onclick = (e) => {
                    e.stopPropagation();
                    window.openTagModal(btn.dataset.studentId);
                };
            });
        });
    </script>
@endpush
