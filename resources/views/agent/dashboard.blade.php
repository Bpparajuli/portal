{{-- resources/views/agent/dashboard.blade.php --}}
@extends('layouts.agent')
@section('title', 'Agent Dashboard')

@section('agent-content')

<div class="full-width">
    <div>
        <h2>Hi {{ auth()->user()->name }}, Welcome Back!</h2>
        <p class="sub-text">Here's a quick overview of your students & applications.</p>
    </div>
    <div>
        <img src="{{ asset('images/pfh-notice.png') }}" alt="Application Pipeline">
    </div>
    <div class="actions">
        <a href="{{ route('agent.students.create') }}" class="btn primary"><i class="fa fa-user"></i> Add Student</a>
        <a href="{{ route('agent.applications.create') }}" class="btn secondary"><i class="fa fa-vcard"></i> New Application</a>
    </div>
</div>
<div class="uni-filter p-4">
    @include('partials.uni_filter')
</div>

<div class="container">
    <div class="content">
        {{-- LEFT COLUMN --}}
        <div class="left-column">
            {{-- STAT CARDS --}}
            <div class="card">
                <div class="stats-row">
                    <a href="{{ route('agent.students.index') }}" class="stat-link">
                        <div class="stat-card">
                            <div class="stat-left">
                                <h6>Total Students</h6>
                                <h2>{{ $totalStudents ?? 0 }}</h2>
                            </div>
                            <div class="icon text-primary"><i class="fa fa-users"></i></div>
                        </div>
                    </a>
                    <a href="{{ route('agent.applications.index') }}" class="stat-link">
                        <div class="stat-card">
                            <div class="stat-left">
                                <h6>Applications Submitted</h6>
                                <h2>{{ $totalApplications ?? 0 }}</h2>
                            </div>
                            <div class="icon text-secondary"><i class="fa fa-vcard"></i></div>
                        </div>
                    </a>
                    <a href="{{ route('agent.universities.index') }}" class="stat-link">
                        <div class="stat-card">
                            <div class="stat-left">
                                <h6>Available Universities</h6>
                                <h2>{{ $totalUniversities ?? 0 }}</h2>
                            </div>
                            <div class="icon text-primary"><i class="fa fa-university"></i></div>
                        </div>
                    </a>
                </div>
            </div>
            {{-- MONTHLY CHART --}}
            <div class="card chart-card">
                <h6>Monthly Applications (Last 12 months)</h6>
                <canvas id="monthlyChart" class="canvas-medium"></canvas>
            </div>
            {{-- COUNTRY CHART --}}
            <div class="card chart-card">
                <h6>Applications by Country</h6>
                <canvas id="countryChart" class="canvas-medium"></canvas>
            </div>
            {{-- APPLICATION PIPELINE --}}
            <div class="card pipeline-card">
                <h6>Student Application Pipeline</h6>
                <img src="{{ asset('images/pipeline.png') }}" alt="Application Pipeline">
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="right-content">
            {{-- APPLICATION PROGRESS --}}
            <div class="card progress-card">
                <h6>Application Progress</h6>
                <canvas id="progressChart" class="canvas-small"></canvas>
                <div class="progress-stats grid-two-columns">
                    @foreach($statuses as $i => $s)
                    <div class="stat" style="background-color: {{ $statusColors[$i] }}; color: #fff; font-weight: bold;">
                        {{ $s }}
                    </div>
                    @endforeach
                </div>
            </div>
            {{-- VISA APPROVED CONVERSION
            <div class="card">
                <h6>Visa Approved Conversion</h6>
                <canvas id="conversionChart" class="canvas-small"></canvas>
                <p class="sub-text-bold">{{ $visaConversionPercent ?? 0 }}% Visa Approved</p>
        </div> --}}


        {{-- Course Type Comparison --}}

        <div class="card chart-card">
            <h6>Applications by Course Type</h6>
            <canvas id="courseTypeChart" class="canvas-small"></canvas>
        </div>

        {{-- UPCOMING EVENTS --}}
        <div class="card events-card">
            <h6 class="gradient section-title">Upcoming Trainings & Counselling</h6>
            <div class="events-grid">
                <div>
                    <h6>Trainings</h6>
                    <ul>
                        <li>Agent Portal Training – Nov 29</li>
                        <li>Embassy Portal Training – Dec 2</li>
                        <li>University Portal Training – Dec 7</li>
                        <li>Agent Portal Training – Dec 10</li>
                        <li>Embassy Portal Training – Dec 12</li>
                        <li>University Portal Training – Dec 15</li>
                    </ul>
                </div>
                <div>
                    <h6>Counselling</h6>
                    <ul>
                        <li>Interview Preperation – Nov 23-28</li>
                        <li>Germany Counselling – Nov 29</li>
                        <li>German University Counselling– Dec 3</li>
                        <li>Dubai Counselling – Dec 5</li>
                        <li>Dubai University Counselling– Dec 9</li>
                        <li>Other Countries Counselling– Dec 15</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- WIDGETS ROW --}}
<div class="card widgets-card">
    <div class="widgets-row">
        <div class="widget">
            <div>
                <div class="widget-title">Today's Activities</div>
                <div class="widget-value">{{ $todayActivitiesCount ?? 0 }}</div>
            </div>
        </div>

        <div class="widget">
            <div class="widget-title">Applications this month</div>
            <div class="d-flex justify-content-between">
                <div class="widget-value">{{ $recentApplications->count() ?? 0 }}</div>
                <div class="widget-link">
                    <a href="{{ route('agent.applications.index') }}">View</a>
                </div>
            </div>
        </div>
        <div class="widget">
            <div>
                <div class="widget-title">Quick Actions</div>
                <div class="quick-actions">
                    <a href="{{ route('agent.applications.index') }}" class="btn primary mini-btn">Check Applications</a>
                    <a href="{{ route('agent.students.index') }}" class="btn secondary mini-btn">Student Status</a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ACTIVITIES ROW --}}
<div class="activities-row">
    <div class="activity-card card">
        <h6>Students Activities</h6>
        <ul>
            @forelse($studentActivities as $act)
            <li>
                <div>
                    @if($act->notifiable_id)
                    <a href="{{ route('agent.students.show', $act->notifiable_id) }}">{{ $act->description }}</a>
                    @else
                    {{ $act->description }}
                    @endif
                    <div class="time-text">{{ $act->created_at->diffForHumans() }}</div>
                </div>
            </li>
            @empty
            <li>No students activities</li>
            @endforelse
        </ul>
    </div>

    <div class="activity-card card">
        <h6>Documents</h6>
        <ul>
            @forelse($documentActivities as $act)
            <li>
                <div>
                    @if($act->notifiable_id)
                    <a href="{{ route('agent.documents.index', $act->notifiable_id) }}">
                        {{ $act->description }}
                    </a>
                    @else
                    {{ $act->description }}
                    @endif
                    <div class="time-text">{{ $act->created_at->diffForHumans() }}</div>
                </div>
            </li>
            @empty
            <li>No document activities</li>
            @endforelse
        </ul>
    </div>



    <div class="activity-card card">
        <h6>Applications</h6>
        <ul>
            @forelse($applicationActivities as $act)
            <li>
                <div>
                    @if($act->notifiable_id)
                    <a href="{{ route('agent.applications.show', $act->notifiable_id) }}">{{ $act->description }}</a>
                    @else
                    {{ $act->description }}
                    @endif
                    <div class="time-text">{{ $act->created_at->diffForHumans() }}</div>
                </div>
            </li>
            @empty
            <li>No applications yet</li>
            @endforelse
        </ul>
    </div>
</div>

{{-- CALENDAR + TASKS
    <div class="grid-two-columns">
        <div class="card calendar-card">
            <h6>Calendar</h6>
            <div class="calendar">Calendar placeholder</div>
        </div>
        <div class="card tasks-card">
            <h6>Tasks / Reminders</h6>
            <div class="tasks">
                <div class="task"><input type="checkbox"> Follow up with University A — Sept 12</div>
                <div class="task"><input type="checkbox"> Send documents to student B — Sept 11</div>
                <div class="task"><input type="checkbox"> Prepare SOP template review</div>
            </div>
        </div>
    </div> --}}
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

@php
// Status chart data
$statusCounts = [];
foreach ($statuses as $s) { $statusCounts[] = $applicationStatusCounts->get($s, 0); }
$statusColors = [
'#3b82f6', // Application started - Blue
'#60a5fa', // Viewed by Admin - Light Blue
'#818cf8', // Applied to University - Indigo
'#facc15', // Need to give the test - Yellow
'#22c55e', // Accepted by University - Green
'#ef4444', // Rejected by University - Red
'#8b5cf6', // Applied to another university - Purple
'#f97316', // Forwarded to embassy - Orange
'#0ea5e9', // On waiting list at embassy - Sky Blue
'#16a34a', // Visa Approved - Dark Green
'#b91c1c', // Visa Rejected - Dark Red
'#6b7280' // Lost - Gray
];
@endphp
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // ==========================================================
        // Helper Function: Fallback Doughnut (for empty datasets)
        // ==========================================================
        function createFallbackDoughnut(ctx, message = "No Data") {
            return new Chart(ctx, {
                type: "doughnut"
                , data: {
                    labels: [message]
                    , datasets: [{
                        data: [1], // single slice
                        backgroundColor: ["#e5e7eb"], // light gray
                        borderWidth: 0
                    }]
                }
                , options: {
                    cutout: "70%"
                    , plugins: {
                        legend: {
                            display: false
                        }
                        , tooltip: {
                            enabled: false
                        }
                    }
                }
            });
        }

        // ==========================================================
        // VISA CONVERSION CHART
        // ==========================================================
        // const visaData = [
        //     @json($visaApproved)
        //     , Math.max(@json($totalApplications) - @json($visaApproved), 0)
        // ];

        // const hasVisaData = visaData.reduce((a, b) => a + b, 0) > 0;

        // if (!hasVisaData) {
        //     createFallbackDoughnut(document.getElementById('conversionChart'), "No Data");
        // } else {
        //     new Chart(document.getElementById('conversionChart'), {
        //         type: 'doughnut'
        //         , data: {
        //             labels: ['Visa Approved', 'Remaining']
        //             , datasets: [{
        //                 data: visaData
        //                 , backgroundColor: ['#22c55e', '#e5e7eb']
        //             }]
        //         }
        //         , options: {
        //             cutout: '70%'
        //             , plugins: {
        //                 legend: {
        //                     display: false
        //                 }
        //             }
        //         }
        //     });
        // }

        // ==========================================================
        // APPLICATION PROGRESS CHART
        // ==========================================================
        const statusCounts = @json($statusCounts);
        const statusLabels = @json($statuses);
        const statusColors = @json($statusColors);

        const hasProgressData = statusCounts.reduce((a, b) => a + b, 0) > 0;

        if (!hasProgressData) {
            createFallbackDoughnut(document.getElementById('progressChart'), "No Applications");
        } else {
            new Chart(document.getElementById('progressChart'), {
                type: 'doughnut'
                , data: {
                    labels: statusLabels
                    , datasets: [{
                        data: statusCounts
                        , backgroundColor: statusColors
                        , borderWidth: 1
                        , borderColor: '#fff'
                        , hoverOffset: 8
                    }]
                }
                , options: {
                    cutout: '0%'
                    , plugins: {
                        legend: {
                            display: false
                        }
                        , tooltip: {
                            callbacks: {
                                label: ctx => {
                                    const total = statusCounts.reduce((a, b) => a + b, 0);
                                    const pct = total ? Math.round(ctx.raw / total * 100) : 0;
                                    return `${ctx.label}: ${ctx.raw} (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // ==========================================================
        // COURSE TYPE DONUT CHART
        // ==========================================================
        const courseTypeLabels = @json($courseTypeLabels);
        const courseTypeValues = @json($courseTypeValues);

        if (!courseTypeValues || courseTypeValues.reduce((a, b) => a + b, 0) === 0) {
            createFallbackDoughnut(document.getElementById('courseTypeChart'), "No Data");
        } else {
            new Chart(document.getElementById('courseTypeChart'), {
                type: 'doughnut'
                , data: {
                    labels: courseTypeLabels
                    , datasets: [{
                        data: courseTypeValues
                        , backgroundColor: [
                            '#1a0262', '#820b5c', '#f97316', '#0ea5e9', '#ef4444', '#f97316', '#10b981'
                        ]
                    }]
                }
                , options: {
                    cutout: '65%'
                    , plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });
        }

        // ==========================================================
        // MONTHLY APPLICATION LINE CHART
        // ==========================================================
        new Chart(document.getElementById('monthlyChart'), {
            type: 'line'
            , data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                , datasets: [{
                    label: 'Applications'
                    , data: @json($monthlyArr)
                    , borderColor: '#820b5c'
                    , backgroundColor: 'rgba(79,70,229,0.18)'
                    , fill: true
                    , tension: 0.32
                    , pointRadius: 3
                }]
            }
            , options: {
                responsive: true
                , scales: {
                    y: {
                        beginAtZero: true
                    }
                }
                , plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // ==========================================================
        // COUNTRY BAR CHART
        // ==========================================================
        new Chart(document.getElementById('countryChart'), {
            type: 'bar'
            , data: {
                labels: @json($countryLabels)
                , datasets: [{
                    label: 'Applications'
                    , data: @json($countryCounts)
                    , backgroundColor: '#1a0262'
                }]
            }
            , options: {
                responsive: true
                , scales: {
                    y: {
                        beginAtZero: true
                    }
                }
                , plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

    });

</script>
@endsection
