{{-- Agent Dashboard Blade --}}
@extends('layouts.agent')
@section('title', 'Agent Dashboard')

@section('agent-content')
    <style>
        /* ============================================================
                                                                                       AGENT DASHBOARD – ONLY UNIQUE/SPECIFIC STYLES
                                                                                    ============================================================ */

        /* === 1. Custom Agent Font Override === */
        .agent-dash {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* === 2. Banner (hero section) – completely custom === */
        .ag-banner {
            background: linear-gradient(135deg, #1a0262 0%, #2d1270 35%, #820b5c 100%);
            padding: 1.75rem 2rem 4rem;
            position: relative;
            overflow: hidden;
            margin-bottom: -2.5rem;
        }

        .ag-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -100px;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
        }

        .ag-banner::after {
            content: '';
            position: absolute;
            bottom: -100px;
            left: 15%;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: rgba(6, 182, 212, 0.08);
        }

        .ag-banner-inner {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .ag-welcome h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.3rem;
        }

        .ag-welcome p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        .ag-banner-logo {
            max-height: 100px;
            object-fit: contain;
            opacity: 0.9;
        }

        .ag-banner-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        /* Custom banner buttons (not in global) */
        .ag-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.2rem;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            color: #fff;
        }

        .ag-action-btn.outline {
            border: 1.5px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .ag-action-btn.solid {
            background: var(--secondary);
            border: none;
        }

        .ag-action-btn:hover {
            transform: translateY(-2px);
            color: #fff;
        }

        .ag-action-btn.outline:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .ag-action-btn.solid:hover {
            background: #0891b2;
        }

        /* === 3. Main Body Wrapper === */
        .ag-body {
            padding: 0 1.5rem 2rem;
        }

        /* === 4. Custom Stats Row Layout === */
        .ag-stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 768px) {
            .ag-stats-row {
                grid-template-columns: 1fr;
            }
        }

        /* Stats cards – custom layout only, colors from global */
        .ag-stat-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.4rem;
            position: relative;
            overflow: hidden;
        }

        .ag-stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--sc-color, rgba(130, 11, 92, 0.06));
            transform: translate(30px, -30px);
        }

        .ag-stat-icon {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .ag-stat-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .ag-stat-value {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1;
            margin: 0.2rem 0 0;
        }

        /* === 5. Custom Card Header (unique to agent) === */
        .ag-card-header {
            padding: 1.1rem 1.4rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .ag-card-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .ag-card-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            color: #fff;
        }

        .ag-card-body {
            padding: 1.4rem;
        }

        /* === 6. Main Grid Layout === */
        .ag-main-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 1100px) {
            .ag-main-grid {
                grid-template-columns: 1fr;
            }
        }

        .ag-left {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .ag-right {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        /* === 7. Filter Section === */
        .ag-filter-section {
            padding: 1.25rem 1.4rem;
            margin-bottom: 1.25rem;
        }

        .ag-filter-section h6 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* === 8. Status Grid (2 columns) === */
        .status-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
            margin-top: 1rem;
        }

        /* Custom status pill (not in global) */
        .ag-status-pill {
            padding: 0.45rem 0.6rem;
            border-radius: 8px;
            text-align: center;
            font-size: 0.7rem;
            font-weight: 700;
            transition: transform 0.15s;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ag-status-pill:hover {
            transform: scale(1.04);
        }

        /* === 9. Widget Row Layout === */
        .ag-widget-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 768px) {
            .ag-widget-row {
                grid-template-columns: 1fr;
            }
        }

        .ag-widget {
            padding: 1.2rem;
        }

        .ag-widget-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.5rem;
        }

        .ag-widget-value {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.9rem;
            font-weight: 700;
        }

        /* === 10. Activity Grid Layout === */
        .ag-activity-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 992px) {
            .ag-activity-grid {
                grid-template-columns: 1fr;
            }
        }

        .ag-activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .ag-activity-item {
            padding: 0.8rem 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.82rem;
        }

        .ag-activity-item:last-child {
            border-bottom: none;
        }

        .ag-activity-link {
            text-decoration: none;
            font-weight: 500;
        }

        .ag-activity-time {
            font-size: 0.72rem;
            margin-top: 0.25rem;
        }

        /* === 11. Event Lists (custom) === */
        .ag-event-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .ag-event-item {
            padding: 0.65rem 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.82rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .ag-event-item:last-child {
            border-bottom: none;
        }

        .ag-event-dot-primary {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--primary);
            flex-shrink: 0;
        }

        .ag-event-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--secondary);
            flex-shrink: 0;
        }

        /* === 12. Top Universities List (custom) === */
        .ag-uni-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0;
            border-bottom: 1px solid var(--border);
        }

        .ag-uni-item:last-child {
            border-bottom: none;
        }

        .ag-uni-rank {
            width: 26px;
            height: 26px;
            border-radius: 7px;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            color: #fff;
            font-size: 0.72rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .ag-uni-name {
            flex: 1;
            font-size: 0.83rem;
            font-weight: 600;
        }

        .ag-uni-short {
            font-size: 0.72rem;
        }

        .ag-uni-count {
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            color: #fff;
            border-radius: 20px;
            padding: 0.15rem 0.6rem;
            font-size: 0.72rem;
            font-weight: 700;
        }

        /* === 13. Pipeline Image === */
        .pipeline-img {
            width: 100%;
            border-radius: 10px;
        }

        /* === 14. Visa Conversion Widget === */
        .visa-conversion-ring {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 1rem 0;
        }

        .ring-value {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--success);
        }

        .ring-label {
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* === 15. Mobile Responsive === */
        @media (max-width: 640px) {
            .ag-banner {
                padding: 1.25rem 1rem 3.5rem;
            }

            .ag-welcome h2 {
                font-size: 1.3rem;
            }

            .ag-body {
                padding: 0 1rem 2rem;
            }
        }
    </style>

    <div class="agent-dash">
        {{-- BANNER --}}
        <div class="ag-banner">
            <div class="ag-banner-inner">
                <div class="ag-welcome">
                    <h2>Hi {{ auth()->user()->name }}! 👋</h2>
                    <p>Here's your dashboard overview — students, applications & more.</p>
                </div>
                <img src="{{ asset('images/pfh-notice.png') }}" class="ag-banner-logo bg-white rounded" alt="Portal">
                <div class="ag-banner-actions">
                    <a href="{{ route('agent.students.create') }}" class="ag-action-btn outline"><i
                            class="fa fa-user-plus"></i> Add Student</a>
                    <a href="{{ route('agent.applications.create') }}" class="ag-action-btn solid"><i
                            class="fa fa-plus"></i> New Application</a>
                </div>
            </div>
        </div>

        <div class="ag-body">
            {{-- STATS ROW --}}
            <div class="ag-stats-row">
                <a href="{{ route('agent.students.index') }}" class="ag-stat-card card"
                    style="--sc-color:rgba(130,11,92,0.08);">
                    <div class="ag-stat-icon" style="background:rgba(130,11,92,0.1);color:#820b5c;"><i
                            class="fa fa-users"></i></div>
                    <div>
                        <div class="ag-stat-label text-muted">Total Students</div>
                        <div class="ag-stat-value">{{ $totalStudents ?? 0 }}</div>
                    </div>
                </a>
                <a href="{{ route('agent.applications.index') }}" class="ag-stat-card card"
                    style="--sc-color:rgba(26,2,98,0.08);">
                    <div class="ag-stat-icon" style="background:rgba(26,2,98,0.1);color:#1a0262;"><i
                            class="fa fa-file-alt"></i></div>
                    <div>
                        <div class="ag-stat-label text-muted">Applications</div>
                        <div class="ag-stat-value">{{ $totalApplications ?? 0 }}</div>
                    </div>
                </a>
                <a href="{{ route('agent.universities.index') }}" class="ag-stat-card card"
                    style="--sc-color:rgba(6,182,212,0.08);">
                    <div class="ag-stat-icon" style="background:rgba(6,182,212,0.1);color:#0891b2;"><i
                            class="fa fa-university"></i></div>
                    <div>
                        <div class="ag-stat-label text-muted">Universities</div>
                        <div class="ag-stat-value">{{ \App\Models\University::count() }}</div>
                    </div>
                </a>
            </div>

            {{-- FILTER --}}
            <div class="ag-filter-section card">
                <h6><i class="fas fa-search me-1"></i> Find Universities & Courses</h6>
                @include('partials.uni_filter')
            </div>

            {{-- MAIN GRID --}}
            <div class="ag-main-grid">

                {{-- LEFT COLUMN --}}
                <div class="ag-left">
                    <div class="card">
                        <div class="ag-card-header">
                            <h5 class="ag-card-title"><span class="ag-card-icon"><i class="fas fa-university"></i></span>
                                Applications by University</h5>
                        </div>
                        <div class="ag-card-body"><canvas id="universityChart" height="120"></canvas></div>
                    </div>
                    <div class="card">
                        <div class="ag-card-header">
                            <h5 class="ag-card-title"><span class="ag-card-icon"><i class="fas fa-stream"></i></span>
                                Application Pipeline</h5>
                        </div>
                        <div class="ag-card-body" style="padding:1rem;">
                            <img src="{{ asset('images/pipeline.png') }}" class="pipeline-img" alt="Pipeline">
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="card ">
                            <div class="ag-card-header">
                                <h5 class="ag-card-title"><span class="ag-card-icon"><i class="fas fa-calendar"></i></span>
                                    Upcoming Trainings</h5>
                            </div>
                            <div class="ag-card-body" style="padding:1rem;">
                                <ul class="ag-event-list">
                                    <li class="ag-event-item">
                                        <div class="ag-event-dot-primary"></div> Students Meetup – Feb 19 Idea Baneshwor
                                    </li>
                                    <li class="ag-event-item">
                                        <div class="ag-event-dot-primary"></div> PFH University 1-to-1 Counselling – Feb 19
                                    </li>
                                    <li class="ag-event-item">
                                        <div class="ag-event-dot-primary"></div> Agent Meetup – Feb 21 Sip &amp; Skip
                                    </li>
                                    <li class="ag-event-item">
                                        <div class="ag-event-dot-primary"></div> Agent Portal Training – March 10
                                    </li>
                                    <li class="ag-event-item">
                                        <div class="ag-event-dot-primary"></div> Embassy Portal Training – March 11
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="card col-md-6">
                            <div class="ag-card-header">
                                <h5 class="ag-card-title"><span class="ag-card-icon"
                                        style="background:linear-gradient(135deg,#0d9488,#06b6d4);"><i
                                            class="fas fa-comments"></i></span> Upcoming Counselling</h5>
                            </div>
                            <div class="ag-card-body" style="padding:1rem;">
                                <ul class="ag-event-list">
                                    <li class="ag-event-item">
                                        <div class="ag-event-dot"></div> Interview Preparation
                                        –
                                        Feb 23-28
                                    </li>
                                    <li class="ag-event-item">
                                        <div class="ag-event-dot"></div> Germany Counselling –
                                        Feb
                                        28
                                    </li>
                                    <li class="ag-event-item">
                                        <div class="ag-event-dot"></div> Dubai Counselling –
                                        March
                                        11
                                    </li>
                                    <li class="ag-event-item">
                                        <div class="ag-event-dot"></div> Dubai University
                                        Counselling – March 13
                                    </li>
                                    <li class="ag-event-item">
                                        <div class="ag-event-dot"></div> Other Countries
                                        Counselling – March 16
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN --}}
                <div class="ag-right">
                    <div class="card">
                        <div class="ag-card-header">
                            <h5 class="ag-card-title"><span class="ag-card-icon"><i
                                        class="fas fa-check-circle"></i></span>
                                Visa Conversion</h5>
                        </div>
                        <div class="ag-card-body">
                            <div class="visa-conversion-ring">
                                <div class="ring-value">{{ $visaConversionPercent }}%</div>
                                <div class="ring-label text-muted">Visa Approved Rate</div>
                                <div style="margin-top:0.5rem;font-size:0.8rem;" class="text-muted">{{ $visaApproved }}
                                    out
                                    of {{ $totalApplications }} applications</div>
                            </div>
                            <div
                                style="height:8px;background:var(--bg-hover);border-radius:8px;overflow:hidden;margin-top:0.75rem;">
                                <div
                                    style="height:100%;width:{{ $visaConversionPercent }}%;background:linear-gradient(90deg,#10b981,#059669);border-radius:8px;transition:width 1s ease;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="ag-card-header">
                            <h5 class="ag-card-title"><span class="ag-card-icon"><i class="fas fa-chart-pie"></i></span>
                                Application Progress</h5>
                        </div>
                        <div class="ag-card-body">
                            <canvas id="progressChart" height="120"></canvas>
                            <div class="status-grid-2">
                                @foreach ($statuses as $status)
                                    <div class="ag-status-pill"
                                        style="background:{{ $status->bg_color ?? '#6b7280' }};color:{{ $status->text_color ?? '#fff' }};">
                                        {{ $status->name }}: {{ $statusCounts[$loop->index] ?? 0 }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="ag-card-header">
                            <h5 class="ag-card-title"><span class="ag-card-icon"><i
                                        class="fas fa-book"></i></span>Applications By Course Type</h5>
                        </div>
                        <div class="ag-card-body"><canvas id="courseTypeChart" height="180"></canvas></div>
                    </div>

                    <div class="card">
                        <div class="ag-card-header">
                            <h5 class="ag-card-title"><span class="ag-card-icon"><i class="fas fa-trophy"></i></span>
                                Your Top Universities</h5>
                        </div>
                        <div class="ag-card-body" style="padding:1rem;">
                            @forelse($topUniversities as $i => $uni)
                                <div class="ag-uni-item">
                                    <div class="ag-uni-rank">{{ $i + 1 }}</div>
                                    <div style="flex:1;min-width:0;">
                                        <div class="ag-uni-name">{{ $uni->name }}</div>
                                        <div class="ag-uni-short text-muted">{{ $uni->short_name }}</div>
                                    </div>
                                    <div class="ag-uni-count">{{ $uni->applications_count }} apps</div>
                                </div>
                            @empty
                                <p class="text-center text-muted py-3">No data yet</p>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>

            {{-- WIDGETS ROW --}}
            <div class="ag-widget-row">
                <div class="ag-widget card">
                    <div class="ag-widget-label text-muted">Today's Activities</div>
                    <div class="ag-widget-value">{{ $todayActivitiesCount ?? 0 }}</div>
                    <div class="text-muted" style="font-size:0.75rem;margin-top:0.25rem;">all actions combined</div>
                </div>
                <div class="ag-widget card">
                    <div class="ag-widget-label text-muted">Applications This Month</div>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <div class="ag-widget-value">{{ $totalApplications }}</div>
                        <a href="{{ route('agent.applications.index') }}" class="text-primary"
                            style="font-size:0.8rem;font-weight:600;text-decoration:none;">View all →</a>
                    </div>
                </div>
                <div class="ag-widget card">
                    <div class="ag-widget-label text-muted">Quick Actions</div>
                    <div style="display:flex;gap:0.5rem;margin-top:0.5rem;flex-wrap:wrap;">
                        <a href="{{ route('agent.applications.index') }}" class="btn btn-primary btn-sm">Check Apps</a>
                        <a href="{{ route('agent.students.index') }}" class="btn btn-secondary btn-sm">Students</a>
                    </div>
                </div>
            </div>

            {{-- ACTIVITIES GRID --}}
            <div class="ag-activity-grid">
                <div class="card">
                    <div class="ag-card-header">
                        <h5 class="ag-card-title"><span class="ag-card-icon"><i class="fas fa-graduation-cap"></i></span>
                            Student Activities</h5>
                    </div>
                    <div class="ag-card-body" style="padding:0.75rem 1.25rem;">
                        <ul class="ag-activity-list">
                            @forelse($studentActivities as $act)
                                <li class="ag-activity-item">
                                    @if ($act->notifiable_id)
                                        <a href="{{ route('agent.students.show', $act->notifiable_id) }}"
                                            class="ag-activity-link text-secondary">{{ $act->description }}</a>
                                    @else
                                        <span>{{ $act->description }}</span>
                                    @endif
                                    <div class="ag-activity-time text-muted">{{ $act->created_at->diffForHumans() }}</div>
                                </li>
                            @empty
                                <li class="ag-activity-item text-muted">No student activities</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="ag-card-header">
                        <h5 class="ag-card-title"><span class="ag-card-icon"
                                style="background:linear-gradient(135deg,#0d9488,#06b6d4);"><i
                                    class="fas fa-folder-open"></i></span> Documents</h5>
                    </div>
                    <div class="ag-card-body" style="padding:0.75rem 1.25rem;">
                        <ul class="ag-activity-list">
                            @forelse($documentActivities as $act)
                                <li class="ag-activity-item">
                                    @if ($act->notifiable_id)
                                        <a href="{{ route('agent.documents.index', $act->notifiable_id) }}"
                                            class="ag-activity-link text-secondary">{{ $act->description }}</a>
                                    @else
                                        <span>{{ $act->description }}</span>
                                    @endif
                                    <div class="ag-activity-time text-muted">{{ $act->created_at->diffForHumans() }}</div>
                                </li>
                            @empty
                                <li class="ag-activity-item text-muted">No document activities</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="ag-card-header">
                        <h5 class="ag-card-title"><span class="ag-card-icon"
                                style="background:linear-gradient(135deg,#f59e0b,#ef4444);"><i
                                    class="fas fa-file-alt"></i></span> Applications</h5>
                    </div>
                    <div class="ag-card-body" style="padding:0.75rem 1.25rem;">
                        <ul class="ag-activity-list">
                            @forelse($applicationActivities as $act)
                                <li class="ag-activity-item">
                                    @if ($act->notifiable_id)
                                        <a href="{{ route('agent.applications.show', $act->notifiable_id) }}"
                                            class="ag-activity-link text-secondary">{{ $act->description }}</a>
                                    @else
                                        <span>{{ $act->description }}</span>
                                    @endif
                                    <div class="ag-activity-time text-muted">{{ $act->created_at->diffForHumans() }}</div>
                                </li>
                            @empty
                                <li class="ag-activity-item text-muted">No application activities</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // (Chart.js initialization remains exactly the same as your original)
        document.addEventListener('DOMContentLoaded', function() {
            Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
            Chart.defaults.color = '#64748b';

            function fallbackDoughnut(ctx, msg = 'No Data') {
                return new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: [msg],
                        datasets: [{
                            data: [1],
                            backgroundColor: ['#e5e7eb'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        cutout: '70%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: false
                            }
                        }
                    }
                });
            }

            // Progress Chart
            const statusCounts = @json($statusCounts);
            const statusLabels = @json($statusLabels);
            const statusColors = @json($statusColors);
            const hasProgress = statusCounts.length && statusCounts.reduce((a, b) => a + b, 0) > 0;
            if (!hasProgress) {
                fallbackDoughnut(document.getElementById('progressChart'));
            } else {
                new Chart(document.getElementById('progressChart'), {
                    type: 'doughnut',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            data: statusCounts,
                            backgroundColor: statusColors,
                            borderWidth: 2,
                            borderColor: '#fff',
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        cutout: '0%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => {
                                        const total = statusCounts.reduce((a, b) => a + b, 0);
                                        const p = total ? Math.round(ctx.raw / total * 100) : 0;
                                        return `${ctx.label}: ${ctx.raw} (${p}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Course Type
            const ctLabels = @json($courseTypeLabels);
            const ctValues = @json($courseTypeValues);
            const hasCT = ctValues.length && ctValues.reduce((a, b) => a + b, 0) > 0;
            if (!hasCT) {
                fallbackDoughnut(document.getElementById('courseTypeChart'));
            } else {
                new Chart(document.getElementById('courseTypeChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ctLabels,
                        datasets: [{
                            data: ctValues,
                            backgroundColor: ['#1a0262', '#820b5c', '#f97316', '#0ea5e9', '#ef4444',
                                '#10b981'
                            ],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        cutout: '60%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: {
                                        size: 11
                                    },
                                    padding: 10
                                }
                            }
                        }
                    }
                });
            }

            // University
            const uniData = @json($universityChartData ?? ['labels' => [], 'datasets' => [['data' => []]]]);
            if (uniData.labels && uniData.labels.length > 0) {
                if (uniData.datasets[0]) {
                    uniData.datasets[0].maxBarThickness = 40;
                    uniData.datasets[0].categoryPercentage = 0.6;
                    uniData.datasets[0].barPercentage = 0.7;
                }
                new Chart(document.getElementById('universityChart'), {
                    type: 'bar',
                    data: uniData,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => `${ctx.parsed.y} Applications`
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.04)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                document.getElementById('universityChart').closest('.ag-card-body').innerHTML =
                    '<p class="text-center text-muted py-3">No university data yet</p>';
            }
        });
    </script>
@endsection
