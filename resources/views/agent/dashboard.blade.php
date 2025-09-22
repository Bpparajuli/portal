@extends('layouts.agent')

@section('agent-content')
<link rel="stylesheet" href="{{ asset('css/agdashboard.css') }}">
<section class="full-width p-4">
    <h2 class="text-success bold">Hi {{ auth()->user()->name }}, Welcome Back!</h2>
    <div class="actions">
        <a href="{{ route('agent.students.create') }}" class="btn btn-primary"><i class="fa fa-user"></i> + Add Student</a>
        <a href="{{ route('agent.applications.create') }}" class="btn btn-secondary"><i class="fa fa-vcard"></i> +New Application</a>
        {{-- <a href="{{ route('agent.documents.create') }}" class="btn btn-active"><i class='fas fa-folder-open'></i> Upload Document</a> --}}
    </div>
</section>
<section class="uni-filter p-4">
    @include('partials.uni_filter')
</section>
<section class="content p-4">
    {{-- LEFT --}}
    <div class="left-content">
        <div class="stats-row">
            <div class="stat-card blue">
                <i class="fa fa-users"></i>
                <h6>Total Students</h6>
                <h2>{{ $totalStudents ?? 0 }}</h2>
            </div>
            <div class="stat-card green">
                <i class="fa fa-vcard"></i>
                <h6>Applications Submitted</h6>
                <h2>{{ $totalApplications ?? 0 }}</h2>
            </div>
            <div class="stat-card maroon">
                <i class="fa fa-university"></i>
                <h6>Available Universities</h6>
                <h2>{{ $totalUniversities ?? 0 }}</h2>
            </div>
        </div>

        <div class="charts-row">
            <div class="chart-card">
                <h6>Application Status</h6>
                <canvas id="statusChart"></canvas>
            </div>

            <div class="chart-card">
                <h6>Monthly Applications (Last 12 months)</h6>
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
        <div class="activities-row">
            <div class="activity-card">
                <h6>New Students</h6>
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
                    <li>No new students</li>
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
            <div class="activity-card">
                <h6>Recent Activities</h6>
                <ul>
                    @php $recent = $activities ?? collect(); @endphp
                    @forelse($recent->take(10) as $act)
                    <li>
                        {{-- If it's linked to a student or application, attempt to link; else plain text --}}
                        @if($act->type === 'newstudentadded' && $act->notifiable_id)
                        <a href="{{ route('agent.students.show', $act->notifiable_id) }}">{{ $act->description }}</a>
                        @elseif(in_array($act->type, ['newapplicationsubmitted','applicationstatusupdated']) && $act->notifiable_id)
                        <a href="{{ route('agent.applications.show', $act->notifiable_id) }}">{{ $act->description }}</a>
                        @else
                        {{ $act->description }}
                        @endif
                        <span class="time">{{ $act->created_at->diffForHumans() }}</span>
                    </li>
                    @empty
                    <li>No activities</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="pipeline-card">
            <h6>Student Application Pipeline</h6>
            <img src="{{ asset('images/pipeline.png') }}" alt="Application Pipeline" style="width:100%; height:auto;">
        </div>
    </div>
    {{-- RIGHT --}}
    <div class="right-content">
        <div class="stat-card big">
            <h6>Conversion Rate</h6>
            <canvas id="conversionChart"></canvas>
            <p>{{ round((($totalApplications ?? 0) / max(($totalStudents ?? 1),1)) * 100, 1) }}% Applied</p>
        </div>
        <div class="progress-card">
            <h6>Application Progress</h6>
            <canvas id="progressChart"></canvas>
            <div class="progress-stats">
                <div><strong>{{ $totalApproved ?? 0 }}</strong><span>Approved</span></div>
                <div><strong>{{ $totalRejected ?? 0 }}</strong><span>Rejected</span></div>
                <div><strong>{{ $totalApplications ?? 0 }}</strong><span>Total</span></div>
            </div>
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

</section>


<!-- Chart.js (load once) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

@php
// safe server-side arrays for JS
$tApps = $totalApplications ?? 0;
$tStudents = $totalStudents ?? 0;

$appStatusCounts = $applicationStatusCounts ?? collect();
$statusLabels = $appStatusCounts->keys()->toArray();
$statusData = array_values($appStatusCounts->toArray());

// monthly array for 1..12
$monthlyArr = [];
for ($m = 1; $m <= 12; $m++) { $monthlyArr[]=($monthlyApplications && isset($monthlyApplications[$m])) ? (int)$monthlyApplications[$m] : 0; } @endphp <script>
    document.addEventListener('DOMContentLoaded', function () {
    // conversion donut
    const convCtx = document.getElementById('conversionChart').getContext('2d');
    new Chart(convCtx, {
    type: 'doughnut',
    data: {
    labels: ['Applied', 'Remaining'],
    datasets: [{
    data: [@json($tApps), Math.max(@json($tStudents) - @json($tApps), 0)],
    backgroundColor: ['#22c55e', '#e5e7eb']
    }]
    },
    options: { cutout: '70%', maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // status pie
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
    type: 'pie',
    data: {
    labels: @json($statusLabels),
    datasets: [{
    data: @json($statusData),
    backgroundColor: ['#fbbf24', '#22c55e', '#ef4444', '#3b82f6', '#60a5fa', '#a78bfa']
    }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // monthly (line / area)
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
    type: 'line',
    data: {
    labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
    datasets: [{
    label: 'Applications',
    data: @json($monthlyArr),
    borderColor: '#4f46e5',
    backgroundColor: 'rgba(79,70,229,0.12)',
    fill: true,
    tension: 0.32,
    pointRadius: 3
    }]
    },
    options: {
    responsive: true,
    scales: { y: { beginAtZero: true } },
    plugins: { legend: { display: false } }
    }
    });

    // progress donut (Approved / Rejected / Other)
    const progCtx = document.getElementById('progressChart').getContext('2d');
    new Chart(progCtx, {
    type: 'doughnut',
    data: {
    labels: ['Approved','Rejected','Other'],
    datasets: [{
    data: [@json($totalApproved ?? 0), @json($totalRejected ?? 0), Math.max(@json($tApps) - (@json($totalApproved ?? 0) + @json($totalRejected ?? 0)), 0)],
    backgroundColor: ['#22c55e','#ef4444','#fbbf24']
    }]
    },
    options: { cutout: '60%', plugins: { legend: { position: 'bottom' } } }
    });
    });
    </script>

    @endsection
