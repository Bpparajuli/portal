@extends('layouts.admin')

@section('admin-content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/admindashboard.css') }}">


<div class="dashboard-container p-2">
    <div class="row g-3 mb-2">
        @php
        // Card data array remains the same, ensuring all data links are available
        $card_data = [
        ['title' => 'Agents', 'count' => $totalAgents, 'icon' => 'fas fa-users', 'bg' => 'bg-primary', 'text' => 'text-white', 'link' => route('admin.users.index') ?? '#'],
        ['title' => 'Students', 'count' => $totalStudents, 'icon' => 'fas fa-graduation-cap', 'bg' => 'bg-info', 'text' => 'text-dark', 'link' => route('admin.students.index') ?? '#'],
        ['title' => 'Universities', 'count' => $totalUniversities, 'icon' => 'fas fa-building', 'bg' => 'bg-success', 'text' => 'text-white', 'link' => route('admin.universities.index') ?? '#'],
        ['title' => 'Courses', 'count' => $totalCourses, 'icon' => 'fas fa-book-open', 'bg' => 'bg-warning', 'text' => 'text-dark', 'link' => route('admin.courses.index') ?? '#'],
        ['title' => 'Applications', 'count' => $totalApplications, 'icon' => 'fas fa-file-alt', 'bg' => 'bg-danger', 'text' => 'text-white', 'link' => route('admin.applications.index') ?? '#'],
        ['title' => 'Waiting Users', 'count' => $totalWaitingUsers, 'icon' => 'fas fa-clock', 'bg' => 'bg-dark', 'text' => 'text-white', 'link' => route('admin.users.waiting') ?? '#'],
        ];
        @endphp

        @foreach($card_data as $card)
        <div class="col-md-6 col-lg-3 col-xl-2">
            <a href="{{ $card['link'] }}" class="count-card-link">
                <div class="card {{ $card['bg'] }} {{ $card['text'] }} shadow-sm">
                    <div class="card-body d-flex flex-column justify-content-between align-items-center">
                        <div>
                            <p class="mb-0 text-opacity-75">{{ $card['title'] }}</p>
                        </div>
                        <div class="d-flex gap-3 justify-content-between align-items-center">
                            <i class="{{ $card['icon'] }} fa-2x opacity-50"></i>
                            <h4 class="card-title font-weight-bold mt-1">{{ number_format($card['count']) }}</h4>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    {{-- 2. QUICK ACTIONS ROW --}}
    <div class="row g-4 ">
        <div class="col-12">
            <div class="card shadow-sm dashboard-card p-3">
                <div class="card-body">
                    <h5 class="card-title chart-header">Quick Actions</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.universities.create') ?? '#' }}" class="btn btn-success btn-sm me-2 mb-2"><i class="fas fa-university"></i>+ Add University</a>
                        <a href="{{ route('admin.universities.index') ?? '#' }}" class="btn btn-gray btn-sm me-2 mb-2"><i class="fas fa-tools"></i> Manage Universities</a>
                        <a href="{{ route('admin.courses.create') ?? '#' }}" class="btn btn-success btn-sm me-2 mb-2"><i class="fas fa-book-open"></i>+ Add Courses</a>
                        <a href="{{ route('admin.applications.index') ?? '#' }}" class="btn btn-warning text-dark btn-sm me-2 mb-2"><i class="fas fa-tools"></i> Manage Applications</a>
                        <a href="{{ route('admin.users.create') ?? '#' }}" class="btn btn-success btn-sm me-2 mb-2"><i class="fas fa-user"></i>+ Add Users</a>
                        <a href="{{ route('admin.users.index') ?? '#' }}" class="btn btn-gray btn-sm me-2 mb-2"><i class="fas fa-tools"></i> Manage Users</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-6 d-flex flex-column gap-4">
            {{-- Monthly Applications --}}
            <div class="card chart-card flex-fill">
                <div class="card-body">
                    <h5 class="section-title">📅 Monthly Applications</h5>
                    <canvas id="applicationsChart"></canvas>
                </div>
            </div>

            {{-- Applications by Country --}}
            <div class="card chart-card flex-fill">
                <div class="card-body">
                    <h5 class="section-title">🌍 Applications by Country</h5>
                    <canvas id="countryChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Right Column: Applications by Status --}}
        <div class="col-lg-6">
            <div class="card chart-card h-40">
                <div class="card-body">
                    <h5 class="section-title">📊 Applications by Status</h5>

                    {{-- Doughnut Chart --}}
                    <canvas id="statusChart"></canvas>

                    {{-- Custom Legend --}}
                    <div class="status-legend mt-3 d-flex flex-wrap gap-3 justify-content-between">
                        @foreach($statusChartData['labels'] as $index => $label)
                        <div class="legend-item d-flex align-items-center" style="width: 48%;">
                            <span class="badge me-2" style="background-color: {{ $statusChartData['datasets'][0]['backgroundColor'][$index] }};">
                                &nbsp;</span>
                            <span>{{ $label }} ({{ $statusChartData['datasets'][0]['data'][$index] }})</span>
                        </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="card activity-feed">
                <div class="card-body">
                    <h5 class="section-title">Recent Activity</h5>
                    <ul class="activity-list">
                        @forelse($activities as $activity)
                        <li class="activity-item" style="animation-delay: {{ $loop->index * 0.1 }}s;">
                            <div class="activity-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="activity-item-content">
                                <p class="activity-text">
                                    <strong>{{ $activity->user->name ?? 'Unknown User' }}</strong>
                                    {{ $activity->description }}
                                </p>
                                <span class="activity-timestamp">{{ $activity->created_at->diffForHumans() }}</span>
                            </div>
                        </li>
                        @empty
                        <li class="text-center text-muted py-5">No recent activity.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card top-agents">
                <div class="card-body">
                    <h5 class="section-title">Latest Agents</h5>
                    <ul class="agent-list">
                        @forelse($latestAgents as $agent)
                        <li class="agent-item" style="animation-delay: {{ $loop->index * 0.1 }}s;">
                            <a href="{{ route('admin.users.show', $agent->id) }}">
                                @if($agent->business_logo)
                                <img src="{{ Storage::url($agent->business_logo) }}" alt="Logo" width="45" height="45" class="rounded-3">
                                @else
                                <span class="text-muted">No Logo</span>
                                @endif
                            </a>
                            <div class="agent-item-content">
                                <h6 class="mb-1">{{ $agent->name }}</h6>
                                <p class="agent-location">{{ $agent->city ?? 'City not specified' }}, {{ $agent->country ?? 'Country not specified' }}</p>
                            </div>
                            <span class="badge bg-primary rounded-pill">{{ $agent->applications_count ?? 0 }} Apps</span>
                        </li>
                        @empty
                        <li class="text-center text-muted py-5">No agents found.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="section-title">Recent Applications</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-borderless table-rounded">
                            <thead>
                                <tr>
                                    <th>Applicant</th>
                                    <th>Course</th>
                                    <th>University</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latestApplications as $application)
                                <tr>
                                    <td>{{ $application->student->first_name ?? 'N/A' }} {{ $application->student->last_name ?? 'N/A' }}</td>
                                    <td>{{ $application->course->title ?? 'N/A' }}</td>
                                    <td>{{ $application->university->name ?? 'N/A' }}</td>
                                    <td>
                                        {{-- Uses the Status Class accessor from Application Model (e.g., bg-success, bg-danger) --}}
                                        <span class="badge badge-status {{ $application->status_class }}">
                                            {{ $application->application_status }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.applications.show', $application->id) }}" class="btn btn-sm btn-primary rounded-pill"><i class="fas fa-eye"></i></a>
                                        <button class="btn btn-sm btn-danger rounded-pill"><i class="fas fa-trash-alt"></i></button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">No recent applications found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Applications Trend
        new Chart(document.getElementById('applicationsChart'), {
            type: 'line'
            , data: @json($applicationsChartData)
            , options: {
                responsive: true
                , tension: 0.3
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

        // Applications by Status
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut'
            , data: @json($statusChartData)
            , options: {
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Applications by Country
        new Chart(document.getElementById('countryChart'), {
            type: 'bar'
            , data: @json($countryChartData)
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
