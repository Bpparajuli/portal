{{-- resources/views/dashboard/agent-dashboard.blade.php --}}
{{-- Unified Partner Dashboard: agent + agent_staff share this view --}}
@extends('layouts.agent')
@section('title', 'Partner Dashboard')
@section('page-title', 'Partner Dashboard')
@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .chart-fill canvas { width: 100% !important; min-height: 200px !important; }
    </style>
@endpush

@php
    $isStaffSub = auth()->user()->is_agent_staff;
    $totalApplied = $statuses->where('name', 'Application started')->first()?->applications_count ?? 0;
    $admittedEnrolled = $statuses
        ->filter(
            fn($s) => str_contains(strtolower($s->name ?? ''), 'admit') ||
                str_contains(strtolower($s->name ?? ''), 'enroll') ||
                str_contains(strtolower($s->name ?? ''), 'offer'),
        )
        ->sum('applications_count');

@endphp

@section('agent-content')

    {{-- ═══════ HERO ═══════ --}}
    <div class="dash-hero">
        <div class="dash-hero-inner">
            <div>
                <div class="dash-hero-title">👋 {{ $isStaffSub ? 'Team' : 'Welcome back' }}, {{ Auth::user()->name }}</div>
                <div class="dash-hero-sub">
                    {{ $isStaffSub ? 'Supporting students across your agency pipeline' : 'Here\'s everything across your pipeline right now' }}
                </div>
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
                @if (!$isStaffSub)
                    <a href="{{ route('agent.students.create') }}" class="btn btn-sm btn-glass">
                        <i class="fas fa-plus"></i><i class="fas fa-user-graduate"></i>Student</a>
                    <a href="{{ route('agent.applications.create') }}" class="btn btn-sm btn-glass-accent"><i
                            class="fas fa-plus"></i> <i class="fas fa-file-alt"></i>Application</a>
                @endif
            </div>
        </div>
    </div>

    {{-- THIS MONTH SUMMARY --}}
    <div class="dash-month-strip">
        <div class="dash-month-item">
            <div class="month-icon" style="background:var(--primary-soft);color:var(--primary);">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div>
                <div class="month-num" style="color:var(--primary);">{{ number_format($thisMonthStudents) }}</div>
                <div class="month-lbl">Students This Month</div>
            </div>
        </div>
        <div class="dash-month-item">
            <div class="month-icon" style="background:var(--warning-soft);color:var(--warning);">
                <i class="fas fa-file-alt"></i>
            </div>
            <div>
                <div class="month-num" style="color:var(--warning);">{{ number_format($thisMonthApps) }}</div>
                <div class="month-lbl">Applications This Month</div>
            </div>
        </div>
        <div class="dash-month-item">
            <div class="month-icon" style="background:var(--success-soft);color:var(--success);">
                <i class="fas fa-chart-line"></i>
            </div>
            <div>
                <div class="month-num" style="color:var(--success);">{{ $appGrowth >= 0 ? '+' : '' }}{{ $appGrowth }}%
                </div>
                <div class="month-lbl">Growth vs Last Month</div>
            </div>
        </div>
    </div>

    {{-- CHARTS ROW 1 --}}
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
            <div class="sc-body chart-fill">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>
        <div class="card uni-stat" style="padding:0;">
            <div class="sc-head">
                <h5 class="sc-title"><span class="sc-icon"><i class="fas fa-university"></i></span> Applications by
                    University</h5>
            </div>
            <div class="sc-body chart-fill">
                <canvas id="universityChart"></canvas>
            </div>
        </div>
    </div>

    {{-- CHARTS ROW 2 --}}
    <div class="stat-row reverse">
        <div class="card" style="padding:0;">
            <div class="sc-head">
                <h5 class="sc-title"><span class="sc-icon"><i class="fas fa-chart-pie"></i></span> Applications By Status
                </h5>
            </div>
            <div class="sc-body chart-fill">
                <canvas id="statusChart"></canvas>
                <div class="status-pills">
                    @foreach ($statusChartData['statuses'] ?? [] as $st)
                        <div class="s-pill" style="background:{{ $st->bg_color }}cc;color:{{ $st->text_color }};">
                            {{ $st->name }}</div>
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
            <div class="sc-body chart-fill"><canvas id="applicationsChart"></canvas></div>
        </div>
    </div>

    {{-- TOP LISTS --}}
    <div class="top-row">
        <div class="card" style="padding:0;">
            <div class="sc-head">
                <h5 class="sc-title"><span class="sc-icon"><i class="fas fa-university"></i></span> Top Universities</h5>
                <a href="{{ route('agent.universities.index') }}" class="view-all">View all <i
                        class="fas fa-arrow-right"></i></a>
            </div>
            <div class="m-3">
                @php $maxApps = $topUniversities->first()?->applications_count ?? 1; @endphp
                @forelse($topUniversities as $i => $uni)
                    <div class="rank-row">
                        <div class="rank-no {{ ['rk-gold', 'rk-silver', 'rk-bronze'][$i] ?? 'rk-other' }}">
                            {{ $i + 1 }}</div>
                        <div class="rank-name">{{ $uni->short_name ?? $uni->name }}</div>
                        <span class="rank-count">{{ $uni->applications_count }}</span>
                    </div>
                @empty
                    <p class="text-muted text-center py-3" style="font-size:.83rem;">No data yet</p>
                @endforelse
            </div>
        </div>
        <div class="card" style="padding:0;">
            <div class="sc-head">
                <h5 class="sc-title"><span class="sc-icon"><i class="fas fa-book-open"></i></span> Top Courses</h5>
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
        <div class="card" style="padding:0;">
            <div class="sc-head">
                <h5 class="sc-title"><span class="sc-icon"><i class="fas fa-user-tie"></i></span> Quick Stats</h5>
            </div>
            <div class="m-3">
                <div class="rank-row">
                    <div class="rank-no rk-gold"><i class="fas fa-check"></i></div>
                    <div class="rank-name">Total Students</div>
                    <span class="rank-count">{{ number_format($totalStudents) }}</span>
                </div>
                <div class="rank-row">
                    <div class="rank-no rk-silver"><i class="fas fa-file-alt"></i></div>
                    <div class="rank-name">Applications</div>
                    <span class="rank-count">{{ number_format($totalApplications) }}</span>
                </div>
                <div class="rank-row">
                    <div class="rank-no rk-other"><i class="fas fa-check-circle"></i></div>
                    <div class="rank-name">Visa Approved</div>
                    <span class="rank-count">{{ number_format($visaApproved) }}</span>
                </div>
                <div class="rank-row">
                    <div class="rank-no rk-bronze"><i class="fas fa-percent"></i></div>
                    <div class="rank-name">Visa Conversion</div>
                    <span class="rank-count">{{ $visaConversionPercent }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- RECENT APPLICATIONS --}}
    <div class="card" style="padding:0;margin-bottom:1.25rem;">
        <div class="sc-head">
            <h5 class="sc-title"><span class="sc-icon"><i class="fas fa-file-alt"></i></span> Recent Applications</h5>
            <a href="{{ route('agent.applications.index') }}" class="view-all">View all <i
                    class="fas fa-arrow-right"></i></a>
        </div>
        <div style="overflow-x:auto;">
            <table class="apps-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Applicant</th>
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
                            <td><a href="{{ $app->student ? route('agent.students.show', $app->student->id) : '#' }}"
                                    class="app-name">{{ $app->student?->first_name ?? '' }}
                                    {{ $app->student?->last_name ?? 'N/A' }}</a></td>
                            <td style="font-size:.78rem;color:var(--text-muted);white-space:nowrap;">
                                {{ $app->created_at->timezone('Asia/Kathmandu')->format('d M Y') }}</td>
                            <td>
                                <div style="font-weight:600;font-size:.8rem;">{{ $app->university->name ?? 'N/A' }}</div>
                                <div style="font-size:.72rem;color:var(--text-muted);">{{ $app->course->title ?? 'N/A' }}
                                </div>
                            </td>
                            <td><a href="{{ route('agent.applications.show', $app->id) }}"
                                    style="text-decoration:none;"><span class="status-badge"
                                        style="background:{{ $app->status?->bg_color ?? '#6c757d' }};color:{{ $app->status?->text_color ?? '#fff' }};font-size:.7rem;">{{ $app->status?->name ?? 'N/A' }}</span></a>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown"><i
                                            class="fas fa-ellipsis-h"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item"
                                                href="{{ route('agent.applications.show', $app->id) }}"><i
                                                    class="fas fa-eye text-info me-2"></i> View</a></li>
                                        <li><a class="dropdown-item"
                                                href="{{ route('agent.applications.edit', $app->id) }}"><i
                                                    class="fas fa-edit text-warning me-2"></i> Edit</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item"
                                                href="{{ $app->student ? route('agent.students.show', $app->student->id) : '#' }}"><i
                                                    class="fas fa-user-graduate me-2"></i> Student Profile</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><x-confirm-delete
                                                url="{{ route('agent.applications.destroy', $app->id) }}"
                                                label="Delete"
                                                title="Delete Application #{{ $app->id }}?"
                                                message="This action cannot be undone."
                                                class="dropdown-item text-danger"
                                            /></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No applications yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ACTIVITIES --}}
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
                            <a href="{{ $act->link ?? ($act['link'] ?? '#') }}" class="act-item">
                                <div class="act-dot {{ $sec['dot'] }}"></div>
                                <div>
                                    <div class="act-desc">{!! $act->description ?? ($act['description'] ?? '') !!}</div>

                                </div>
                            </a>
                        @empty
                            <li style="list-style:none;font-size:.82rem;" class="text-muted text-center py-3">No
                                {{ strtolower($sec['title']) }} yet</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endforeach
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var gridColor = 'rgba(0,0,0,0.04)';
            Chart.defaults.font.size = 11;
            Chart.defaults.color = '#6b7280';

            new Chart(document.getElementById('weeklyChart'), {
                data: {
                    labels: @json($weeklyLabels),
                    datasets: [{
                        type: 'bar',
                        label: 'Applications',
                        data: @json($weeklyAppsData),
                        backgroundColor: 'rgba(130,11,92,.18)',
                        borderColor: '#820b5c',
                        borderWidth: 1.5,
                        borderRadius: 4
                    }, {
                        type: 'line',
                        label: 'Students',
                        data: @json($weeklyStudentsData),
                        borderColor: '#1a0262',
                        backgroundColor: 'rgba(26,2,98,.06)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#1a0262'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                font: { size: 10 },
                                padding: 8,
                                boxWidth: 12
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });

            new Chart(document.getElementById('applicationsChart'), {
                type: 'line',
                data: @json($monthlyApps),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });

            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: @json($statusChartData),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '0%',
                    plugins: {
                        legend: { display: false }
                    }
                }
            });

            var uniChart = new Chart(document.getElementById('universityChart'), {
                type: 'bar',
                data: @json($universityChartData),
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    indexAxis: 'y',
                    onClick: function(e, els) {
                        if (els.length) {
                            var l = uniChart.data.labels[els[0].index];
                            window.location.href = '/agent/applications?university=' +
                                encodeURIComponent(l);
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.parsed.x + ' Applications';
                                }
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

            function updateClock() {
                var now = new Date();
                var h = now.getHours(),
                    m = now.getMinutes(),
                    s = now.getSeconds();
                var ampm = h >= 12 ? 'PM' : 'AM';
                h = h % 12 || 12;
                h = String(h).padStart(2, '0');
                m = String(m).padStart(2, '0');
                s = String(s).padStart(2, '0');
                document.getElementById('main-time').innerText = h + ':' + m;
                document.getElementById('seconds').innerText = s;
                document.getElementById('live-date').innerText = now.toLocaleDateString('en-US', {
                    weekday: 'short',
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
            }
            setInterval(updateClock, 1000);
            updateClock();
        });
    </script>

@endsection
