@extends('layouts.admin')

@section('admin-content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* General Styling */
    .card {
        border: none;
        border-radius: 1.25rem;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.04), 0 2px 6px rgba(0, 0, 0, 0.02);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08), 0 3px 10px rgba(0, 0, 0, 0.04);
    }

    .card-body {
        padding: 2rem;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 2rem;
    }

    /* Dashboard Widgets */
    .widget-card {
        background: linear-gradient(135deg, var(--gray) 0%, #f9f9ff 100%);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: all 0.3s ease-in-out;
    }

    .widget-card .icon-and-link {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }

    .widget-icon {
        background-color: var(--secondary);
        color: white;
        border-radius: 0.75rem;
        width: 60px;
        height: 60px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 1.75rem;
        box-shadow: 0 4px 8px rgba(74, 69, 255, 0.2);
    }

    .widget-icon.bg-secondary-theme {
        background-color: #64748b;
        box-shadow: 0 4px 8px rgba(100, 116, 139, 0.2);
    }

    .widget-icon.bg-success-theme {
        background-color: #10b981;
        box-shadow: 0 4px 8px rgba(16, 185, 129, 0.2);
    }

    .widget-title {
        font-size: 1rem;
        font-weight: 500;
        color: var(--text-color);
        margin: 0;
    }

    .widget-value {
        font-size: 2.25rem;
        font-weight: 700;
        color: var(--dark);
    }

    . {
        font-size: 0.875rem;
        color: var(--primary);
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease-in-out;
    }

    .:hover {
        text-decoration: underline;
        color: #3b37d4;
    }

    /* Chart Cards */
    .chart-card {
        min-height: 300px;
    }

    /* Activity Feed & Top Agents */
    .activity-feed,
    .top-agents {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .activity-list,
    .agent-list {
        list-style: none;
        padding: 0;
        margin: 0;
        flex-grow: 1;
        overflow-y: auto;
    }

    .activity-item,
    .agent-item {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        padding: 1rem 0;
        border-bottom: 1px solid #e2e8f0;
        opacity: 0;
        /* Initial state for animation */
        animation: fadeIn 0.5s ease-in-out forwards;
    }

    .activity-item:last-child,
    .agent-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background-color: var(--secondary);
        display: flex;
        justify-content: center;
        align-items: center;
        color: var(--primary);
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .agent-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--primary);
        flex-shrink: 0;
    }

    .activity-item-content,
    .agent-item-content {
        flex-grow: 1;
    }

    .activity-text {
        font-size: 0.95rem;
        color: var(--text-color);
        line-height: 1.4;
    }

    .activity-text strong {
        color: var(--dark);
        font-weight: 600;
    }

    .activity-timestamp,
    .agent-location {
        font-size: 0.8rem;
        color: #94a3b8;
        margin-top: 0.25rem;
    }

    /* Tables */
    .table-responsive {
        overflow-x: auto;
    }

    .table {
        min-width: 800px;
    }

    .table th,
    .table td {
        white-space: nowrap;
        vertical-align: middle;
        padding: 1rem;
    }

    .table thead th {
        border-bottom: 2px solid #e2e8f0;
        font-weight: 600;
        color: var(--dark);
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.05em;
        background-color: #f1f5f9;
    }

    .table tbody tr {
        transition: background-color 0.2s ease-in-out;
    }

    .table tbody tr:hover {
        background-color: #fafbfd;
    }

    .badge {
        font-weight: 600;
        padding: 0.5em 1em;
        border-radius: 1rem;
        font-size: 0.8rem;
        text-transform: capitalize;
    }

    /* Quick Actions */
    .action-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        text-align: center;
        background: #ffffff;
        border: 2px solid #e2e8f0;
        border-radius: 1rem;
        transition: all 0.2s ease-in-out;
    }

    .action-card:hover {
        transform: translateY(-3px);
        border-color: var(--primary);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .action-icon {
        font-size: 2rem;
        color: var(--primary);
        margin-bottom: 0.75rem;
    }

    /* Keyframes for animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.02);
        }

        100% {
            transform: scale(1);
        }
    }

</style>

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
                        <a href="{{ route('admin.students.create') ?? '#' }}" class="btn btn-success btn-sm me-2 mb-2"><i class="fas fa-users"></i>+ Add Student</a>
                        <a href="{{ route('admin.universities.create') ?? '#' }}" class="btn btn-success btn-sm me-2 mb-2"><i class="fas fa-university"></i>+ Add University</a>
                        <a href="{{ route('admin.users.create') ?? '#' }}" class="btn btn-success btn-sm me-2 mb-2"><i class="fas fa-user"></i>+ Add Users</a>
                        <a href="{{ route('admin.applications.create') ?? '#' }}" class="btn btn-primary btn-sm me-2 mb-2"><i class="fas fa-file-signature"></i> Create Application</a>
                        <a href="{{ route('admin.users.index') ?? '#' }}" class="btn btn-dark btn-sm me-2 mb-2"><i class="fas fa-tools"></i> Manage Users</a>
                        <a href="{{ route('admin.users.waiting') ?? '#' }}" class="btn btn-warning btn-sm me-2 mb-2"><i class="fas fa-bell"></i> Review New Users</a>
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
                            <img src="{{ $agent->profile_photo_url ?? 'https://placehold.co/45x45/64748b/ffffff?text=U' }}" alt="{{ $agent->name }}'s avatar" class="agent-avatar">
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
