@extends('layouts.admin')

@section('admin-content')
<div class="dashboard-container p-3">

    {{-- KPI CARDS WITH ACTION LINKS --}}
    <div class="row g-3 mb-4">
        @php
        $cards = [
        ['title'=>'Active Agents', 'count'=>$activeAgents, 'icon'=>'fas fa-users', 'theme'=>'bg-primary-theme', 'link'=>route('admin.users.index')],
        ['title'=>'Students', 'count'=>$totalStudents, 'icon'=>'fas fa-graduation-cap', 'theme'=>'bg-secondary-theme', 'link'=>route('admin.students.index')],
        ['title'=>'Universities', 'count'=>$totalUniversities, 'icon'=>'fas fa-building', 'theme'=>'bg-success-theme', 'link'=>route('admin.universities.index')],
        ['title'=>'Courses', 'count'=>$totalCourses, 'icon'=>'fas fa-book-open', 'theme'=>'bg-primary-theme', 'link'=>route('admin.courses.index')],
        ['title'=>'Applications', 'count'=>$totalApplications, 'icon'=>'fas fa-file-alt', 'theme'=>'bg-success-theme', 'link'=>route('admin.applications.index')],
        ['title'=>'Waiting Users', 'count'=>$totalWaitingUsers, 'icon'=>'fas fa-clock', 'theme'=>'bg-secondary-theme', 'link'=>route('admin.users.waiting')],
        ];
        @endphp

        @foreach($cards as $card)
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ $card['link'] }}" class="text-decoration-none">
                <div class="widget-card rounded p-3 hover-scale">
                    <div class="icon-and-link mb-2">
                        <div>
                            <p class="widget-title mb-1">{{ $card['title'] }}</p>
                            <div class="widget-value">{{ number_format($card['count']) }}</div>
                        </div>
                        <div class="widget-icon {{ $card['theme'] }}">
                            <i class="{{ $card['icon'] }}"></i>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted">View details</small>
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    {{-- QUICK ACTIONS --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="action-card d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0">Quick Actions</h5>
                    <small class="text-muted">Create or manage core items quickly</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.universities.create') }}" class="btn primary btn-sm"><i class="fas fa-university me-1"></i> Add University</a>
                    <a href="{{ route('admin.courses.create') }}" class="btn secondary btn-sm"><i class="fas fa-book-open me-1"></i> Add Course</a>
                    <a href="{{ route('admin.users.create') }}" class="btn success btn-sm"><i class="fas fa-user me-1"></i> Add User</a>
                    <a href="{{ route('admin.applications.index') }}" class="btn warning btn-sm"><i class="fas fa-tools me-1"></i> Manage Applications</a>
                </div>
            </div>
        </div>
    </div>

    {{-- CHARTS & STATS --}}
    <div class="row g-4 mb-4">
        {{-- LEFT: Charts --}}
        <div class="col-lg-8 d-flex flex-column gap-4">
            <div class="card chart-card flex-fill shadow-sm">
                <div class="card-body">
                    <h5>Monthly Applications</h5>
                    <canvas id="applicationsChart"></canvas>
                </div>
            </div>
            <div class="card chart-card flex-fill shadow-sm">
                <div class="card-body">
                    <h5>Applications by Country</h5>
                    <canvas id="countryChart"></canvas>
                </div>
            </div>
        </div>

        {{-- RIGHT: Applications by Status --}}
        <div class="col-lg-4 d-flex flex-column gap-4">
            <div class="card chart-card flex-fill shadow-sm">
                <div class="card-body">
                    <h5>Applications by Status</h5>
                    <canvas id="statusChart"></canvas>
                    <div class="progress-stats grid-two-columns mt-3">
                        @foreach($statusChartData['labels'] as $i => $s)
                        <div class="stat" style="background-color: {{ $statusChartData['datasets'][0]['backgroundColor'][$i] }}; color: #fff; font-weight: bold;">
                            {{ $s }}
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Top Agents --}}
            <div class="card top-agents-card flex-fill shadow-sm">
                <div class="card-body">
                    <h5>🏆 Top Agents</h5>
                    <ul class="list-group list-group-flush">
                        @forelse($topAgents as $agent)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                @if($agent->business_logo)
                                <img src="{{ Storage::url($agent->business_logo) }}" class="rounded-circle" width="40" height="40">
                                @endif
                                <span>{{ $agent->name }}</span>
                            </div>
                            <span class="badge bg-primary rounded-pill">{{ $agent->applications_count }} Apps</span>
                        </li>
                        @empty
                        <li class="list-group-item text-center text-muted">No top agents yet</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- RECENT APPLICATIONS --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5>Recent Applications</h5>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-borderless align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Applicant</th>
                            <th>Agent</th>
                            <th>Course</th>
                            <th>University</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestApplications as $app)
                        <tr>
                            <td>
                                <a href="{{ route('admin.students.show', $app->student->id) }}">
                                    {{ $app->student->first_name ?? 'N/A' }} {{ $app->student->last_name ?? 'N/A' }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.users.show', $app->agent->id) }}">
                                    {{ $app->agent->name ?? 'N/A' }}
                                </a>
                            </td>
                            <td>{{ $app->course->title ?? 'N/A' }}</td>
                            <td>{{ $app->university->name ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('admin.applications.show', $app->id) }}">
                                    <span class="badge {{ $app->status_class }}">
                                        {{ $app->application_status }}
                                    </span>
                                </a> </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.applications.show', $app->id) }}" class="p-2 btn btn-sm primary">View</a>
                                    <a href="{{ route('admin.applications.edit', $app->id) }}" class="p-2 btn btn-sm secondary">Edit</a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No recent applications</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- RECENT ACTIVITIES --}}
    <div class="row g-3">

        {{-- Students --}}
        <div class="col-md-4">
            <div class="card p-3 h-100">
                <h6 class="mb-3">Student Activities</h6>

                <ul class="list-unstyled m-0">

                    @forelse ($studentActivities as $act)
                    <a href="{{ $act->link }}" class="activity-link d-flex justify-content-between align-items-center">
                        <li class="mb-3">

                            {{-- Row 1: Full description --}}
                            <div class="fw-semibold">
                                {!! $act->description !!}
                            </div>

                            {{-- Row 2: Agent + Time --}}
                            <div class="d-flex justify-content-between text-muted small mt-1">
                                <span>{{ $act->user->business_name }}</span>
                                <span>{{ $act->created_at->diffForHumans() }}</span>
                            </div>

                        </li>
                        @empty
                        <li>No student activities</li>
                        @endforelse
                    </a>
                </ul>

            </div>
        </div>


        {{-- Applications --}}
        <div class="col-md-4">
            <div class="card p-3 h-100">
                <h6 class="mb-3">Application Activities</h6>

                <ul class="list-unstyled m-0">
                    @forelse ($applicationActivities as $act)
                    <a href="{{ $act->link }}" class="activity-link d-flex justify-content-between align-items-center">
                        <li class="mb-3">

                            {{-- Row 1 --}}
                            <div class="fw-semibold">
                                {!! $act->description !!}
                            </div>

                            {{-- Row 2 --}}
                            <div class="d-flex justify-content-between text-muted small mt-1">
                                <span>{{ $act->user->business_name }}</span>
                                <span>{{ $act->created_at->diffForHumans() }}</span>
                            </div>

                        </li>
                        @empty
                        <li>No application activities</li>
                        @endforelse
                    </a>
                </ul>
            </div>
        </div>


        {{-- Documents --}}
        <div class="col-md-4">
            <div class="card p-3 h-100">
                <h6 class="mb-3">Document Activities</h6>

                <ul class="list-unstyled m-0">
                    @forelse ($documentActivities as $act)
                    <a href="{{ $act->link }}" class="activity-link d-flex justify-content-between align-items-center">
                        <li class="mb-3">

                            {{-- Row 1 --}}
                            <div class="fw-semibold">
                                {!! $act->description !!}
                            </div>

                            {{-- Row 2 --}}
                            <div class="d-flex justify-content-between text-muted small mt-1">
                                <span>{{ $act->user->business_name }}</span>
                                <span>{{ $act->created_at->diffForHumans() }}</span>
                            </div>

                        </li>
                        @empty
                        <li>No document activities</li>
                        @endforelse
                    </a>
                </ul>

            </div>
        </div>

    </div>

</div>
{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const applicationsChart = new Chart(
            document.getElementById('applicationsChart'), {
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
            }
        );

        const statusChart = new Chart(
            document.getElementById('statusChart'), {
                type: 'doughnut'
                , data: @json($statusChartData)
                , options: {
                    responsive: true
                    , plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            }
        );

        const countryChart = new Chart(
            document.getElementById('countryChart'), {
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
            }
        );
    });

</script>


@endsection
