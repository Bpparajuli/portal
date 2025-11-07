@extends('layouts.agent')

@section('agent-content')
<link rel="stylesheet" href="{{ asset('css/agdashboard.css') }}">

<div class="full-width p-4">
    <h2 class="text-success bold">Hi {{ auth()->user()->name }}, Welcome Back!</h2>
    <div class="actions">
        <a href="{{ route('agent.students.create') }}" class="btn btn-primary"><i class="fa fa-user"></i> + Add Student</a>
        <a href="{{ route('agent.applications.create') }}" class="btn btn-secondary"><i class="fa fa-vcard"></i> +New Application</a>
    </div>
</div>

<div class="uni-filter p-4">
    @include('partials.uni_filter')
</div>

<div class="content p-4">
    {{-- LEFT --}}
    <div class="left-content">
        <div class="stats-row">
            <a href="{{ route('agent.students.index') }}" class="stat-link">
                <div class="stat-card blue">
                    <i class="fa fa-users"></i>
                    <h6>Total Students</h6>
                    <h2>{{ $totalStudents ?? 0 }}</h2>
                </div>
            </a>

            <a href="{{ route('agent.applications.index') }}" class="stat-link">
                <div class="stat-card green">
                    <i class="fa fa-vcard"></i>
                    <h6>Applications Submitted</h6>
                    <h2>{{ $totalApplications ?? 0 }}</h2>
                </div>
            </a>

            <a href="{{ route('agent.universities.index') }}" class="stat-link">
                <div class="stat-card maroon">
                    <i class="fa fa-university"></i>
                    <h6>Available Universities</h6>
                    <h2>{{ $totalUniversities ?? 0 }}</h2>
                </div>
            </a>
        </div>

        <div class="charts-row">
            {{-- Application Progress --}}
            <div class="progress-card">
                <h6>Application Progress</h6>
                <canvas id="progressChart"></canvas>
                <div class="progress-stats"></div>
            </div>

            {{-- Monthly Applications --}}
            <div class="chart-card">
                <h6>Monthly Applications (Last 12 months)</h6>
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        {{-- Country-wise Applications --}}
        <div class="chart-card">
            <h6>Applications by Country</h6>
            <canvas id="countryChart"></canvas>
        </div>

        <div class="pipeline-card">
            <h6>Student Application Pipeline</h6>
            <img src="{{ asset('images/pipeline.png') }}" alt="Application Pipeline" style="width:100%; height:auto;">
        </div>
    </div>

    {{-- RIGHT --}}
    <div class="right-content">
        {{-- Visa Approved Conversion --}}
        <div class="stat-card big">
            <h6>Visa Approved Conversion</h6>
            <canvas id="conversionChart"></canvas>
            <p>{{ $visaConversionPercent ?? 0 }}% Visa Approved</p>
        </div>
        <div class="events-card">
            <h6><strong>Upcoming Trainings</strong></h6>
            <ul>
                <li>⚙️ Embassy Training – Sept 15</li>
                <li>⚙️ Agent portal Training – Sept 20</li>
                <li>⚙️ University Portal Training – Sept 25</li>
            </ul>
            <h6><strong>University Counselling</strong></h6>
            <ul>
                <li>🎓 Gisma Counselling – Sept 15</li>
                <li>🎓 PFH Counselling – Sept 20</li>
                <li>🎓 SRH Counselling – Sept 25</li>
            </ul>
            <h6><strong>Countries</strong></h6>
            <ul>
                <li>🏳️ UK Counselling – Sept 15</li>
                <li>🏳️ Dubai Counselling – Sept 20</li>
                <li>🏳️ Germany Counselling – Sept 25</li>
            </ul>
        </div>
    </div>
</div>
<div class="p-4">
    <div class="activities-row">
        <div class="activity-card">
            <h6>Students Activities </h6>
            <ul>
                @forelse($studentActivities as $act)
                <li>
                    @if($act->notifiable_id)
                    <a href="{{ route('agent.students.show', $act->notifiable_id) }}">{{ $act->description }}</a>
                    @else
                    {{ $act->description }}
                    @endif
                    <span class="time">{{ $act->created_at->diffForHumans() }}</span>
                </li>
                @empty
                <li>No students Activities</li>
                @endforelse
            </ul>
        </div>

        <div class="activity-card">
            <h6>Documents</h6>
            <ul>
                @forelse($documentActivities as $act)
                <li>
                    {{ $act->description }}
                    <span class="time">{{ $act->created_at->diffForHumans() }}</span>
                </li>
                @empty
                <li>No document activities</li>
                @endforelse
            </ul>
        </div>

        <div class="activity-card">
            <h6>Applications</h6>
            <ul>
                @forelse($applicationActivities as $act)
                <li>
                    @if($act->notifiable_id)
                    <a href="{{ route('agent.applications.show', $act->notifiable_id) }}">{{ $act->description }}</a>
                    @else
                    {{ $act->description }}
                    @endif
                    <span class="time">{{ $act->created_at->diffForHumans() }}</span>
                </li>
                @empty
                <li>No applications yet</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

@php
// Application status data
$statuses = [
'Application started','Application viewed by Admin','Applied to University','Need to give the test',
'Accepted by the University','Rejected by the University','Applied to another university',
'Application forwarded to embassy','Is on waiting list on Embassy','Visa Approved','Visa Rejected','Lost'
];

$statusCounts = [];
foreach ($statuses as $s) { $statusCounts[] = $applicationStatusCounts->get($s, 0); }

$statusColors = [
'#22c55e','#3b82f6','#6366f1','#facc15','#10b981','#ef4444','#8b5cf6','#f97316','#0ea5e9','#22c55e','#ef4444','#6b7280'
];

$monthlyArr = [];
for ($m=1; $m<=12; $m++) { $monthlyArr[]=$monthlyApplications->get($m,0); }
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Visa Approved Donut
            new Chart(document.getElementById('conversionChart').getContext('2d'), {
                type: 'doughnut'
                , data: {
                    labels: ['Visa Approved', 'Remaining']
                    , datasets: [{
                        data: [@json($visaApproved), Math.max(@json($totalApplications) - @json($visaApproved), 0)]
                        , backgroundColor: ['#22c55e', '#e5e7eb']
                    }]
                }
                , options: {
                    cutout: '70%'
                    , plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Application Progress Donut
            new Chart(document.getElementById('progressChart').getContext('2d'), {
                type: 'doughnut'
                , data: {
                    labels: @json($statuses)
                    , datasets: [{
                        data: @json($statusCounts)
                        , backgroundColor: @json($statusColors)
                    }]
                }
                , options: {
                    cutout: '60%'
                    , plugins: {
                        legend: {
                            position: 'bottom'
                        }
                        , tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.label + ': ' + ctx.raw;
                                }
                            }
                        }
                    }
                }
            });

            // Monthly Applications Line Chart
            new Chart(document.getElementById('monthlyChart').getContext('2d'), {
                type: 'line'
                , data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                    , datasets: [{
                        label: 'Applications'
                        , data: @json($monthlyArr)
                        , borderColor: '#4f46e5'
                        , backgroundColor: 'rgba(79,70,229,0.12)'
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

            // Country-wise Bar Chart
            new Chart(document.getElementById('countryChart').getContext('2d'), {
                type: 'bar'
                , data: {
                    labels: @json($countryLabels)
                    , datasets: [{
                        label: 'Applications'
                        , data: @json($countryCounts)
                        , backgroundColor: '#3b82f6'
                    }]
                }
                , options: {
                    responsive: true
                    , plugins: {
                        legend: {
                            display: false
                        }
                    }
                    , scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

        });

    </script>

    @endsection
