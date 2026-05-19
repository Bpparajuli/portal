{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700&display=swap" rel="stylesheet">
@section('admin-content')
    <style>
        /* ============================================================
        DASHBOARD-SPECIFIC STYLES ONLY
        ============================================================ */

        /* === 1. Hero Banner === */
        .dash-hero {
            background: var(--active);
            padding: 4rem 2rem;
            position: relative;
            overflow: hidden;
            border-radius: 0 0 var(--radius-sm) var(--radius-sm);
        }

        .dash-hero::before {
            content: '';
            position: absolute;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
            top: -80px;
            right: -50px;
            pointer-events: none;
        }

        .dash-hero::after {
            content: '';
            position: absolute;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
            bottom: -60px;
            left: 20%;
            pointer-events: none;
        }

        .dash-hero-inner {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: flex-center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .dash-hero-title {
            font-size: 1.65rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: .2rem;
        }

        .dash-hero-sub {
            color: rgba(255, 255, 255, .65);
            font-size: .88rem;
        }

        .digital-clock-card {
            display: inline-block;
            padding: 18px 28px;
            border-radius: 16px;

            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);

            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            color: #fff;
            text-shadow: 0 0 10px rgba(0, 255, 150, 0.6);

        }

        /* Main time */
        .time-wrapper {
            display: flex;
            align-items: flex-end;
            gap: 8px;
        }

        /* Big clock */
        #main-time {
            letter-spacing: 3px;
            font-size: 32px;
            font-weight: 700;
            line-height: 1;
            font-family: 'Orbitron', sans-serif;

        }

        /* Small seconds */
        #seconds {
            font-size: 14px;
            opacity: 0.8;
            padding-bottom: 10px;

        }

        /* Date */
        .date-wrapper {
            margin-top: 6px;
            font-size: 12px;
            opacity: 0.85;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .date-wrapper i {
            font-size: 12px;
        }

        .btn-glass {
            background: rgba(52, 0, 207, 0.12) !important;
            border: 1px solid rgba(255, 255, 255, .25) !important;
            color: #fff !important;
            backdrop-filter: blur(8px);
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, .22) !important;
            color: #fff !important;
        }

        .btn-glass-accent {
            background: rgba(244, 63, 94, .3) !important;
            border: 1px solid rgba(244, 63, 94, .5) !important;
            color: #fff !important;
        }

        .btn-glass-accent:hover {
            background: rgba(244, 63, 94, .5) !important;
        }

        /* === 2. KPI Grid === */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 1rem;
            margin-bottom: 1.75rem;
            margin-top: -3rem;

        }

        @media (max-width: 1200px) {
            .kpi-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 640px) {
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
                margin-top: -1rem;

            }
        }

        .kpi-card {
            background: var(--white);
            border-radius: 8px;
            padding: 1.25rem;
            border: 1px solid var(--dash-border, var(--border));
            text-decoration: none;
            color: inherit;
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--kpi-color, var(--primary));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.1);
            color: inherit;
        }

        .kpi-card:hover::before {
            transform: scaleX(1);
        }

        .kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-bottom: 0.85rem;
            background: var(--kpi-bg, rgba(130, 11, 92, 0.1));
            color: var(--kpi-color, var(--primary));
        }

        .kpi-value {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.9rem;
            font-weight: 700;
            color: var(--dash-text, var(--text-color));
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .kpi-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--dash-muted, var(--text-muted));
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .kpi-footer {
            margin-top: 0.75rem;
            font-size: 0.78rem;
            color: var(--dash-muted, var(--text-muted));
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        /* === 3. Today Strip === */
        .today-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .today-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 1.25rem;
            text-align: center;
            box-shadow: var(--shadow-xs);
            position: relative;
            overflow: hidden;
        }

        .today-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--tc-color, var(--primary));
        }

        .today-num {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--tc-color, var(--primary));
            line-height: 1;
        }

        .today-lbl {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--text-muted);
            margin-top: .3rem;
        }

        .today-icon {
            font-size: 1.35rem;
            margin-bottom: .35rem;
        }

        /* === 4. Section Headers & Cards === */
        .sc-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .85rem 1.25rem;
            border-bottom: 1px solid var(--border);
        }

        .sc-title {
            font-size: .9rem;
            font-weight: 700;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: .5rem;
            margin: 0;
        }

        .sc-icon {
            width: 28px;
            height: 28px;
            border-radius: var(--radius-xs);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .78rem;
            background: var(--active);
            color: #fff;
            flex-shrink: 0;
        }

        #statusChart {
            width: 300px !important;
            height: 300px !important;
            margin: 0 auto;
            display: block;
        }

        @media (max-width: 768px) {
            #statusChart {
                width: 200px !important;
                height: 200px !important;
            }
        }

        /* center wrapper */
        .sc-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 1rem;
        }

        .view-all {
            font-size: .75rem;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: .3rem;
            transition: gap var(--transition-fast);
        }

        .view-all:hover {
            gap: .55rem;
            color: var(--secondary);
        }

        /* === 5. Charts Layout === */
        .charts-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 768px) {
            .charts-row {
                grid-template-columns: 1fr;
            }
        }

        /* === 6. Status Pills === */
        .status-pills {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: .4rem;
            margin-top: .85rem;
        }


        .s-pill {
            padding: .38rem .55rem;
            border-radius: var(--radius-xs);
            font-size: .68rem;
            font-weight: 700;
            text-align: center;
            transition: transform var(--transition-fast);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: default;
        }

        .s-pill:hover {
            transform: scale(1.03);
        }

        /* === 7. Stat Rows (2-column layouts) === */
        .stat-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-row.reverse {
            grid-template-columns: 1fr 2fr;
        }

        @media (max-width: 768px) {

            .stat-row,
            .stat-row.reverse {
                grid-template-columns: 1fr;
            }
        }

        /* === 8. Growth Badges === */
        .growth-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            font-size: .7rem;
            font-weight: 700;
            padding: .18rem .55rem;
            border-radius: var(--radius-full);
        }

        .growth-up {
            background: rgba(0, 128, 43, .1);
            color: var(--success);
        }

        .growth-down {
            background: rgba(239, 68, 68, .1);
            color: var(--danger);
        }

        /* === 9. Top Lists (Agents / Courses / Universities) === */
        .top-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 992px) {
            .top-row {
                grid-template-columns: 1fr;
            }
        }

        .rank-row {
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: .6rem 0;
            border-bottom: 1px solid var(--border);
            transition: padding-left var(--transition-fast);
        }

        .rank-row:last-child {
            border-bottom: none;
        }

        .rank-row:hover {
            padding-left: .35rem;
        }

        .rank-no {
            width: 24px;
            height: 24px;
            border-radius: var(--radius-xs);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .7rem;
            font-weight: 800;
            flex-shrink: 0;
        }

        .rk-gold {
            background: #fef3c7;
            color: #d97706;
        }

        .rk-silver {
            background: #f1f5f9;
            color: #64748b;
        }

        .rk-bronze {
            background: #fef2f2;
            color: #dc2626;
        }

        .rk-other {
            background: var(--bg-hover);
            color: var(--text-muted);
        }

        .rank-name {
            flex: 1;
            font-size: .83rem;
            font-weight: 600;
            color: var(--text-color);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .rank-count {
            font-size: .7rem;
            font-weight: 700;
            background: var(--active);
            color: #fff;
            border-radius: var(--radius-full);
            padding: .12rem .5rem;
            flex-shrink: 0;
        }

        .agent-ava {
            width: 34px;
            height: 34px;
            border-radius: var(--radius-xs);
            object-fit: cover;
            border: 2px solid var(--border);
            flex-shrink: 0;
        }

        .agent-ava-ph {
            width: 34px;
            height: 34px;
            border-radius: var(--radius-xs);
            background: var(--active);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: .82rem;
            flex-shrink: 0;
        }

        .mini-bar {
            height: 3px;
            background: var(--border);
            border-radius: 3px;
            margin-top: 3px;
        }

        .mini-bar-fill {
            height: 100%;
            border-radius: 3px;
            background: var(--active-reverse);
            transition: width 1.2s ease;
        }

        /* === 10. Applications Table === */
        .apps-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .83rem;
        }

        .apps-table thead th {
            padding: .65rem 1rem;
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--text-muted);
            background: var(--bg-main);
            border-bottom: 2px solid var(--border);
            text-align: left;
            white-space: nowrap;
        }

        .apps-table tbody td {
            padding: .78rem 1rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .apps-table tbody tr:last-child td {
            border-bottom: none;
        }

        .apps-table tbody tr:hover {
            background: var(--bg-hover);
        }

        .app-name {
            font-weight: 600;
            color: var(--secondary);
            text-decoration: none;
        }

        .app-name:hover {
            color: var(--primary);
        }

        /* === 11. Activity Feed === */
        .act-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 992px) {
            .act-grid {
                grid-template-columns: 1fr;
            }
        }

        .act-feed {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .act-item {
            display: flex;
            gap: .7rem;
            align-items: flex-start;
            padding: .72rem 0;
            border-bottom: 1px solid var(--border);
            text-decoration: none;
            color: inherit;
            transition: padding-left var(--transition-fast);
        }

        .act-item:last-child {
            border-bottom: none;
        }

        .act-item:hover {
            padding-left: .3rem;
        }

        .act-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
            margin-top: 5px;
        }

        .dot-stu {
            background: var(--primary);
        }

        .dot-doc {
            background: var(--success);
        }

        .dot-app {
            background: var(--warning);
        }

        .act-desc {
            font-size: .8rem;
            font-weight: 500;
            line-height: 1.5;
            color: var(--text-color);
        }

        .act-meta {
            display: flex;
            justify-content: space-between;
            margin-top: .2rem;
        }

        .act-user {
            font-size: .68rem;
            color: var(--primary);
            font-weight: 600;
        }

        .act-time {
            font-size: .68rem;
            color: var(--text-muted);
        }

        /* === 12. Mobile Responsive Tweaks === */
        @media (max-width: 768px) {
            .dash-hero {
                padding: 1.25rem 1rem 2.5rem;
                margin-bottom: -1.5rem;
            }

            .dash-hero-title {
                font-size: 1.3rem;
            }

            .dash-hero-sub {
                font-size: 0.8rem;
            }


            .dash-hero-inner .d-flex {
                width: 100%;
                justify-content: flex-start;
            }

            .dash-hero-inner .btn-sm {
                font-size: 0.75rem;
                padding: 0.35rem 0.7rem;
            }

            div[style*="padding:0 1.5rem 2.5rem"] {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }

            .today-strip {
                gap: 0.75rem;
            }

            .today-card {
                padding: 0.9rem;
            }

            .today-num {
                font-size: 1.8rem;
            }

            .today-icon {
                font-size: 1.2rem;
            }

            .today-lbl {
                font-size: 0.65rem;
            }

            .sc-head {
                padding: 0.75rem 1rem;
            }

            .sc-title {
                font-size: 0.85rem;
                gap: 0.4rem;
            }

            .sc-icon {
                width: 26px;
                height: 26px;
                font-size: 0.7rem;
            }

            .sc-body {
                padding: 1rem;
            }

            .kpi-value {
                font-size: 1.6rem;
            }

            .kpi-icon {
                width: 38px;
                height: 38px;
                font-size: 1rem;
                margin-bottom: 0.5rem;
            }

            .kpi-label {
                font-size: 0.65rem;
            }

            .apps-table tbody td {
                padding: 0.6rem 0.75rem;
                font-size: 0.75rem;
            }

            .apps-table thead th {
                padding: 0.5rem 0.75rem;
                font-size: 0.65rem;
            }

            .app-name {
                font-size: 0.8rem;
            }

            .status-pills {
                gap: 0.3rem;
            }

            .s-pill {
                font-size: 0.6rem;
                padding: 0.3rem 0.4rem;
            }

            .act-item {
                gap: 0.5rem;
                padding: 0.6rem 0;
            }

            .act-desc {
                font-size: 0.75rem;
            }

            .act-user,
            .act-time {
                font-size: 0.65rem;
            }

            .rank-row {
                gap: 0.5rem;
                padding: 0.5rem 0;
            }

            .rank-no {
                width: 22px;
                height: 22px;
                font-size: 0.65rem;
            }

            .rank-name {
                font-size: 0.75rem;
            }

            .rank-count {
                font-size: 0.65rem;
                padding: 0.1rem 0.45rem;
            }

            .agent-ava,
            .agent-ava-ph {
                width: 30px;
                height: 30px;
                font-size: 0.7rem;
            }
        }

        @media (max-width: 480px) {
            .dash-hero-inner {
                flex-direction: column;
                align-items: stretch;
            }

            .dash-hero-inner .d-flex {
                flex-wrap: wrap;
                justify-content: center;
            }

            .today-card {
                padding: 0.75rem;
            }

            .today-num {
                font-size: 1.6rem;
            }

            .kpi-value {
                font-size: 1.4rem;
            }

            .kpi-icon {
                width: 34px;
                height: 34px;
                font-size: 0.9rem;
            }

            .sc-head {
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .view-all {
                font-size: 0.7rem;
            }

            .apps-table thead th,
            .apps-table tbody td {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }

            .rank-count {
                min-width: 32px;
                text-align: center;
            }

            .status-pills {
                grid-template-columns: 1fr;
                gap: 0.4rem;
            }

            .s-pill {
                white-space: normal;
                word-break: keep-all;
            }
        }
    </style>

    {{-- ═══════ HERO ═══════ --}}
    <div class="dash-hero">
        <div class="dash-hero-inner">
            <div>
                <div class="dash-hero-title">👋 Welcome back, {{ Auth::user()->name }}</div>
                <div class="dash-hero-sub">Here's everything happening across your portal right now.</div>
            </div>
            <div class="digital-clock-card">
                <div class="time-wrapper">
                    <span id="main-time">00:00</span>
                    <span id="seconds">00</span>
                </div>

                <div class="date-wrapper">
                    <i class="fas fa-calendar-alt"></i>
                    <span id="live-date"></span>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap align-items-end">
                <a href="{{ route('admin.universities.create') }}" class="btn btn-sm btn-glass"><i
                        class="fas fa-university"></i> University</a>
                <a href="{{ route('admin.courses.create') }}" class="btn btn-sm btn-glass"><i class="fas fa-book-open"></i>
                    Course</a>
                <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-glass"><i class="fas fa-user-plus"></i>
                    User</a>
                <a href="{{ route('admin.applications.create') }}" class="btn btn-sm btn-glass-accent"><i
                        class="fas fa-plus"></i> Application</a>
            </div>
        </div>
    </div>

    <div class="p-4">

        {{-- ═══════ 2. KPI CARDS ═══════ --}}
        <div class="kpi-grid">
            @php
                $kpis = [
                    [
                        'label' => 'Active Agents',
                        'value' => $activeAgents,
                        'icon' => 'fas fa-users',
                        'color' => '#820b5c',
                        'bg' => 'rgba(130,11,92,0.1)',
                        'route' => route('admin.users.index'),
                        'sub' => $totalAgents . ' total agents',
                    ],
                    [
                        'label' => 'Students',
                        'value' => $totalStudents,
                        'icon' => 'fas fa-graduation-cap',
                        'color' => '#1a0262',
                        'bg' => 'rgba(26,2,98,0.1)',
                        'route' => route('admin.students.index'),
                        'sub' => 'registered students',
                    ],
                    [
                        'label' => 'Universities',
                        'value' => $totalUniversities,
                        'icon' => 'fas fa-building',
                        'color' => '#0d9488',
                        'bg' => 'rgba(13,148,136,0.1)',
                        'route' => route('admin.universities.index'),
                        'sub' => 'partner universities',
                    ],
                    [
                        'label' => 'Courses',
                        'value' => $totalCourses,
                        'icon' => 'fas fa-book-open',
                        'color' => '#7c3aed',
                        'bg' => 'rgba(124,58,237,0.1)',
                        'route' => route('admin.courses.index'),
                        'sub' => 'available courses',
                    ],
                    [
                        'label' => 'Applications',
                        'value' => $totalApplications,
                        'icon' => 'fas fa-file-alt',
                        'color' => '#f59e0b',
                        'bg' => 'rgba(245,158,11,0.1)',
                        'route' => route('admin.applications.index'),
                        'sub' => 'total applications',
                    ],
                    [
                        'label' => 'Waiting Users',
                        'value' => $totalWaitingUsers,
                        'icon' => 'fas fa-clock',
                        'color' => '#f43f5e',
                        'bg' => 'rgba(244,63,94,0.1)',
                        'route' => route('admin.users.waiting'),
                        'sub' => 'pending approval',
                    ],
                ];
            @endphp
            @foreach ($kpis as $k)
                <a href="{{ $k['route'] }}" class="kpi-card"
                    style="--kpi-color:{{ $k['color'] }};--kpi-bg:{{ $k['bg'] }};">
                    <div class="kpi-label">{{ $k['label'] }}</div>
                    <div class="d-flex justify-content-between">
                        <div class="kpi-icon"><i class="{{ $k['icon'] }}"></i></div>
                        <div class="kpi-value">{{ number_format($k['value']) }}</div>
                    </div>
                    <div class="kpi-footer"><i class="fas fa-arrow-right" style="font-size:0.65rem;"></i>
                        {{ $k['sub'] }}</div>
                </a>
            @endforeach
        </div>
        {{-- ═══════ 3.TODAY STRIP ═══════ --}}
        <div class="today-strip">
            <div class="today-card" style="--tc-color:var(--primary);">
                <div class="today-icon">🎓</div>
                <div class="today-num">{{ $todayStudentCount }}</div>
                <div class="today-lbl">Student Events Today</div>
            </div>
            <div class="today-card" style="--tc-color:var(--success);">
                <div class="today-icon">📄</div>
                <div class="today-num">{{ $todayDocumentCount }}</div>
                <div class="today-lbl">Document Events Today</div>
            </div>
            <div class="today-card" style="--tc-color:var(--warning);">
                <div class="today-icon">📋</div>
                <div class="today-num">{{ $todayApplicationCount }}</div>
                <div class="today-lbl">Application Events Today</div>
            </div>
        </div>

        {{-- ═══════ 4. Weekly TREND --}}
        <div class="stat-row">
            <div class="card" style="padding:0;">
                <div class="sc-head">
                    <h5 class="sc-title"><span class="sc-icon"><i class="fas fa-chart-area"></i></span> 7-Day Activity Trend
                    </h5>
                    <span class="growth-badge {{ $appGrowth >= 0 ? 'growth-up' : 'growth-down' }}">
                        <i class="fas fa-arrow-{{ $appGrowth >= 0 ? 'up' : 'down' }}"></i> {{ abs($appGrowth) }}% vs last
                        month
                    </span>
                </div>
                <div class="sc-body">
                    <canvas id="weeklyChart" height="130"></canvas>
                </div>
            </div>
            <div class="card uni-stat" style="padding:0;">
                <div class="sc-head">
                    <h5 class="sc-title"><span class="sc-icon"><i class="fas fa-university"></i></span> Applications by
                        University</h5>
                </div>
                <div class="sc-body">
                    <canvas id="universityChart" height="300"></canvas>
                </div>
            </div>
        </div>

        {{-- ═══════4. CHARTS ═══════ --}}
        <div class="stat-row reverse">
            <div class="card" style="padding:0;">
                <div class="sc-head">
                    <h5 class="sc-title"><span class="sc-icon"><i class="fas fa-chart-pie"></i></span> Applications By
                        Status</h5>
                </div>
                <div class="sc-body">
                    <canvas id="statusChart"></canvas>
                    <div class="status-pills">
                        @foreach ($statusChartData['statuses'] as $status)
                            <div class="s-pill"
                                style="background: {{ $status->bg_color }}cc; color: {{ $status->text_color }};">
                                {{ $status->name }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="card" style="padding:0;">
                <div class="sc-head">
                    <h5 class="sc-title"><span class="sc-icon"><i class="fas fa-chart-line"></i></span> Monthly Applications
                    </h5>
                    <span style="font-size:.75rem;color:var(--text-muted);">{{ date('Y') }}</span>
                </div>
                <div class="sc-body"><canvas id="applicationsChart" height="150"></canvas></div>
            </div>
        </div>

        {{-- ═══════ TOP AGENTS / COURSES / UNIVERSITIES ═══════ --}}
        <div class="top-row">
            {{-- Agents --}}
            <div class="card" style="padding:0;">
                <div class="sc-head">
                    <h5 class="sc-title"><span class="sc-icon"><i class="fas fa-trophy"></i></span> Top Agents</h5>
                    <a href="{{ route('admin.users.index') }}" class="view-all">View all <i
                            class="fas fa-arrow-right"></i></a>
                </div>
                <div class="m-3">
                    @php $maxApps = $topAgents->first()?->applications_count ?? 1; @endphp
                    @forelse($topAgents as $i => $agent)
                        <div class="rank-row">
                            <div class="rank-no {{ ['rk-gold', 'rk-silver', 'rk-bronze'][$i] ?? 'rk-other' }}">
                                {{ $i + 1 }}</div>
                            @if ($agent->business_logo)
                                <img src="{{ Storage::url($agent->business_logo) }}" class="agent-ava" alt="">
                            @else
                                <div class="agent-ava-ph">{{ strtoupper(substr($agent->name, 0, 1)) }}</div>
                            @endif
                            <div style="flex:1;min-width:0;">
                                <div class="rank-name">{{ $agent->name }}</div>
                                <div class="mini-bar">
                                    <div class="mini-bar-fill"
                                        style="width:{{ $maxApps > 0 ? round(($agent->applications_count / $maxApps) * 100) : 0 }}%">
                                    </div>
                                </div>
                            </div>
                            <span class="rank-count">{{ $agent->applications_count }}</span>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3" style="font-size:.83rem;">No agents yet</p>
                    @endforelse
                </div>
            </div>

            {{-- Courses --}}
            <div class="card" style="padding:0;">
                <div class="sc-head">
                    <h5 class="sc-title"><span class="sc-icon"><i class="fas fa-book-open"></i></span> Top Courses</h5>
                    <a href="{{ route('admin.courses.index') }}" class="view-all">View all <i
                            class="fas fa-arrow-right"></i></a>
                </div>
                <div class="m-3">
                    @forelse($topCourses as $i => $course)
                        <div class="rank-row">
                            <div class="rank-no {{ ['rk-gold', 'rk-silver', 'rk-bronze'][$i] ?? 'rk-other' }}">
                                {{ $i + 1 }}</div>
                            <div class="rank-name">{{ $course->title }}</div>
                            <span class="rank-count">{{ $course->applications_count }}</span>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3" style="font-size:.83rem;">No data yet</p>
                    @endforelse
                </div>
            </div>

            {{-- Universities --}}
            <div class="card" style="padding:0;">
                <div class="sc-head">
                    <h5 class="sc-title"><span class="sc-icon"><i class="fas fa-building"></i></span> Top Universities
                    </h5>
                    <a href="{{ route('admin.universities.index') }}" class="view-all">View all <i
                            class="fas fa-arrow-right"></i></a>
                </div>
                <div class="m-3">
                    @forelse($topUniversities as $i => $uni)
                        <div class="rank-row">
                            <div class="rank-no {{ ['rk-gold', 'rk-silver', 'rk-bronze'][$i] ?? 'rk-other' }}">
                                {{ $i + 1 }}</div>
                            <div class="rank-name">{{ $uni->name }}</div>
                            <span class="rank-count">{{ $uni->applications_count }}</span>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3" style="font-size:.83rem;">No data yet</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ═══════ PENDING AGENTS ═══════ --}}
        @if ($pendingAgents->count())
            <div class="card" style="padding:0;margin-bottom:1.25rem;">
                <div class="sc-head">
                    <h5 class="sc-title"><span class="sc-icon" style="background:var(--warning-gradient);"><i
                                class="fas fa-exclamation-triangle"></i></span> Agents Needing Attention <span
                            class="badge bg-danger ms-1">{{ $pendingAgents->count() }}</span></h5>
                    <a href="{{ route('admin.users.waiting') }}" class="view-all">View all <i
                            class="fas fa-arrow-right"></i></a>
                </div>
                <div style="overflow-x:auto;">
                    <table class="apps-table">
                        <thead>
                            <tr>
                                <th>Agent</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingAgents as $pa)
                                <tr>
                                    <td><a href="{{ route('admin.users.show', $pa->slug ?? $pa->id) }}"
                                            class="app-name">{{ $pa->business_name }}</a></td>
                                    <td style="font-size:.78rem;color:var(--text-muted);">{{ $pa->email }}</td>
                                    <td><span
                                            class="badge {{ $pa->active ? 'bg-warning' : 'bg-danger' }}">{{ $pa->active ? $pa->agreement_status ?? 'Pending' : 'Inactive' }}</span>
                                    </td>
                                    <td style="font-size:.78rem;color:var(--text-muted);">
                                        {{ $pa->created_at->format('d M Y') }}</td>
                                    <td><a href="{{ route('admin.users.show', $pa->slug ?? $pa->id) }}"
                                            class="btn btn-sm btn-outline-primary">Review</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ═══════ RECENT APPLICATIONS ═══════ --}}
        <div class="card" style="padding:0;margin-bottom:1.25rem;">
            <div class="sc-head">
                <h5 class="sc-title"><span class="sc-icon"><i class="fas fa-file-alt"></i></span> Recent Applications
                </h5>
                <a href="{{ route('admin.applications.index') }}" class="view-all">View all <i
                        class="fas fa-arrow-right"></i></a>
            </div>
            <div style="overflow-x:auto;">
                <table class="apps-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Applicant</th>
                            <th>Agent</th>
                            <th>Date</th>
                            <th>University & Course</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestApplications as $app)
                            <tr>
                                <td style="font-weight:700;color:var(--text-muted);">#{{ $app->id }}</td>
                                <td><a href="{{ route('admin.students.show', $app->student->id) }}"
                                        class="app-name">{{ $app->student->first_name ?? '' }}
                                        {{ $app->student->last_name ?? 'N/A' }}</a></td>
                                <td style="font-size:.78rem;"><a
                                        href="{{ route('admin.users.show', $app->agent->slug ?? $app->agent->id) }}"
                                        class="text-secondary"
                                        style="font-weight:500;text-decoration:none;">{{ $app->agent->business_name ?? ($app->agent->name ?? 'N/A') }}</a>
                                </td>
                                <td style="font-size:.78rem;color:var(--text-muted);white-space:nowrap;">
                                    {{ $app->created_at->timezone('Asia/Kathmandu')->format('d M Y') }}</td>
                                <td>
                                    <div style="font-weight:600;font-size:.8rem;">{{ $app->university->name ?? 'N/A' }}
                                    </div>
                                    <div style="font-size:.72rem;color:var(--text-muted);">
                                        {{ $app->course->title ?? 'N/A' }}</div>
                                </td>
                                <td><a href="{{ route('admin.applications.show', $app->id) }}"
                                        style="text-decoration:none;"><span class="status-badge"
                                            style="background:{{ $app->status?->bg_color ?? '#6c757d' }};color:{{ $app->status?->text_color ?? '#fff' }};font-size:.7rem;">{{ $app->status?->name ?? 'N/A' }}</span></a>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown"><i
                                                class="fas fa-ellipsis-h"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item"
                                                    href="{{ route('admin.applications.show', $app->id) }}"><i
                                                        class="fas fa-eye text-info me-2"></i> View</a></li>
                                            <li><a class="dropdown-item"
                                                    href="{{ route('admin.applications.edit', $app->id) }}"><i
                                                        class="fas fa-edit text-warning me-2"></i> Edit</a></li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li><a class="dropdown-item"
                                                    href="{{ route('admin.students.show', $app->student->id) }}"><i
                                                        class="fas fa-user-graduate me-2"></i> Student Profile</a></li>
                                            <li><a class="dropdown-item"
                                                    href="mailto:{{ $app->student->email ?? '' }}"><i
                                                        class="fas fa-envelope me-2"></i> Email Student</a></li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li><button class="dropdown-item text-danger btn-delete"
                                                    data-url="{{ route('admin.applications.destroy', $app->id) }}"
                                                    data-name="Application #{{ $app->id }}"><i
                                                        class="fas fa-trash me-2"></i> Delete</button></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No recent applications</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ═══════ RECENT ACTIVITIES ═══════ --}}
        <div class="act-grid">
            @php
                $actSections = [
                    [
                        'title' => 'Student Activities',
                        'icon' => 'fas fa-graduation-cap',
                        'dot' => 'dot-stu',
                        'items' => $studentActivities,
                    ],
                    [
                        'title' => 'Application Activities',
                        'icon' => 'fas fa-file-alt',
                        'dot' => 'dot-app',
                        'items' => $applicationActivities,
                    ],
                    [
                        'title' => 'Document Activities',
                        'icon' => 'fas fa-folder-open',
                        'dot' => 'dot-doc',
                        'items' => $documentActivities,
                    ],
                ];
            @endphp
            @foreach ($actSections as $sec)
                <div class="card">
                    <div class="sc-head">
                        <h5 class="sc-title"><span class="sc-icon"><i class="{{ $sec['icon'] }}"></i></span>
                            {{ $sec['title'] }}</h5>
                    </div>
                    <div class="p2">
                        <ul class="act-feed">
                            @forelse($sec['items'] as $act)
                                <a href="{{ $act->link ?? '#' }}" class="act-item">
                                    <div class="act-dot {{ $sec['dot'] }}"></div>
                                    <div>
                                        <div class="act-desc">{!! $act->description !!}</div>
                                        <div class="act-meta">
                                            <span
                                                class="act-user">{{ $act->user->business_name ?? ($act->user->name ?? 'System') }}</span>
                                            <span class="act-time">{{ $act->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <li style="list-style:none;font-size:.82rem;" class="text-muted text-center py-3">No
                                    activities yet</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>

        @if (auth()->check() && auth()->user()->id == 2)
            <div class="mt-2">
                <a href="{{ route('admin.backup.files') }}" class="btn btn-outline-secondary btn-sm"><i
                        class="fas fa-database me-1"></i> Backup Files</a>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const gridColor = 'rgba(0,0,0,0.04)';
            Chart.defaults.font.size = 11;
            Chart.defaults.color = '#6b7280';

            new Chart(document.getElementById('weeklyChart'), {
                data: {
                    labels: @json($weeklyLabels),
                    datasets: [{
                            type: 'bar',
                            label: 'Applications',
                            data: @json($weeklyAppsData),
                            backgroundColor: 'rgba(130,11,92,.22)',
                            borderColor: '#820b5c',
                            borderWidth: 2,
                            borderRadius: 5
                        },
                        {
                            type: 'line',
                            label: 'Students',
                            data: @json($weeklyStudentsData),
                            borderColor: '#1a0262',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.35,
                            pointRadius: 3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 11
                                },
                                padding: 10
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: gridColor
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            new Chart(document.getElementById('applicationsChart'), {
                type: 'line',
                data: @json($applicationsChartData),
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: gridColor
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: @json($statusChartData),
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // 🔥 IMPORTANT (override auto stretch)
                    cutout: '0%', // 🔥 makes it smaller + donut style

                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            const uniChart = new Chart(document.getElementById('universityChart'), {
                type: 'bar',
                data: @json($universityChartData),
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    indexAxis: 'y',
                    onClick: (e, els) => {
                        if (els.length) {
                            const l = uniChart.data.labels[els[0].index];
                            window.location.href =
                                `/admin/applications?university=${encodeURIComponent(l)}`;
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => `${ctx.parsed.x} Applications`
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: gridColor
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });
        });

        function updateClock() {
            const now = new Date();

            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();

            // 12-hour format (remove this if you want 24h)
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12;

            // Format
            hours = String(hours).padStart(2, '0');
            minutes = String(minutes).padStart(2, '0');
            seconds = String(seconds).padStart(2, '0');

            document.getElementById('main-time').innerText = `${hours}:${minutes}`;
            document.getElementById('seconds').innerText = `${seconds}`;

            const options = {
                weekday: 'short',
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            };
            document.getElementById('live-date').innerText = now.toLocaleDateString('en-US', options);
        }

        setInterval(updateClock, 1000);
        updateClock();
    </script>
@endsection
