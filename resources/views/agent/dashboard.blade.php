@extends('layouts.agent')

@section('agent-content')
<link rel="stylesheet" href="{{ asset('css/agentdashboard.css') }}">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="ad-container">

    <!-- Header -->
    <div class="ad-header mb-4">
        <h2>Hi {{ auth()->user()->name }}, Welcome Back!</h2>
        <div class="ad-quick-actions">
            <a href="{{ route('agent.students.create') }}" class="ad-btn ad-btn-primary">+ Add Student</a>
            <a href="{{ route('agent.applications.create') }}" class="ad-btn ad-btn-secondary">New Application</a>
            <a href="{{ route('agent.documents.create') }}" class="ad-btn ad-btn-tertiary">Upload Document</a>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="ad-metrics row mb-4">
        <div class="col-md-3 mb-3">
            <div class="ad-card ad-card-primary">
                <h6>Total Students</h6>
                <h2>{{ $totalStudents ?? 0 }}</h2>
                <a href="{{ route('agent.students.index') }}" class="ad-link">View All →</a>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="ad-card ad-card-success">
                <h6>Applications Submitted</h6>
                <h2>{{ $totalApplications ?? 0 }}</h2>
                <a href="{{ route('agent.applications.index') }}" class="ad-link">View Applications →</a>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="ad-card ad-card-info text-center">
                <h6>Conversion Rate</h6>
                <canvas id="adConversionChart" width="80" height="80"></canvas>
                <p class="ad-small-text">{{ round(($totalApplications / max($totalStudents,1)) * 100, 1) }}% Applied</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="ad-card ad-card-light">
                <h6>Recent Activity</h6>
                <ul class="ad-activity-list">
                    <li>👤 New student added</li>
                    <li>📄 Docs uploaded</li>
                    <li>🎓 Application submitted</li>
                </ul>
                <a href="{{ route('agent.students.index') }}" class="ad-link">See All →</a>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="uni-filter-card">
        <form method="GET" action="{{ route('agent.universities.index') }}" class="uni-filter-form">
            <div class="uni-filter-grid">
                {{-- Search --}}
                <div class="uni-filter-field">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" value="{{ old('search', request('search')) }}" class="uni-input" placeholder="University or Course">
                </div>

                {{-- Country --}}
                <div class="uni-filter-field">
                    <label for="country">Country</label>
                    <select id="country" name="country" class="uni-select" data-cities-url="{{ route('agent.get-cities', ':country') }}">
                        <option value="">All Countries</option>
                        @foreach($countries as $country)
                        <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>
                            {{ $country }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- City --}}
                <div class="uni-filter-field">
                    <label for="city">City</label>
                    <select id="city" name="city" class="uni-select" data-universities-url="{{ route('agent.get-universities', ':city') }}">
                        <option value="">All Cities</option>
                    </select>
                </div>

                {{-- University --}}
                <div class="uni-filter-field">
                    <label for="university_id">University</label>
                    <select id="university_id" name="university_id" class="uni-select" data-courses-url="{{ route('agent.get-courses', ':universityId') }}">
                        <option value="">All Universities</option>
                    </select>
                </div>

                {{-- Course --}}
                <div class="uni-filter-field">
                    <label for="course_id">Course</label>
                    <select id="course_id" name="course_id" class="uni-select">
                        <option value="">All Courses</option>
                    </select>
                </div>
            </div>

            {{-- Actions --}}
            <div class="uni-filter-actions">
                <a href="{{ route('agent.universities.index') }}" class="uni-btn-clear">Reset</a>
                <button type="submit" class="uni-btn-apply">Filter</button>
            </div>
        </form>
    </div>
    <!-- Charts Section -->
    <div class="ad-charts row mb-4">
        <div class="col-md-6 mb-3">
            <div class="ad-card ad-card-light">
                <h6>Applications Trend</h6>
                <canvas id="adApplicationsTrend"></canvas>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="ad-card ad-card-light">
                <h6>Application Status</h6>
                <canvas id="adStatusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Activity, Tasks, Notifications -->
    <div class="ad-row row mb-4">
        <div class="col-md-4 mb-3">
            <div class="ad-card ad-card-light">
                <h6>Tasks / Reminders</h6>
                <ul class="ad-tasks">
                    <li><input type="checkbox"> Follow up with Ramesh</li>
                    <li><input type="checkbox"> Upload missing transcripts</li>
                    <li><input type="checkbox"> Send offer letters</li>
                </ul>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="ad-card ad-card-light">
                <h6>Notifications</h6>
                <ul class="ad-notifications">
                    <li>📢 New university added (UK Partner)</li>
                    <li>📅 Visa prep session on Friday</li>
                    <li>💡 Submit pending documents by 10th Sept</li>
                </ul>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="ad-card ad-card-light">
                <h6>Upcoming Events</h6>
                <ul class="ad-events">
                    <li>🎓 Agent Training – Sept 15</li>
                    <li>🌍 Study Abroad Fair – Sept 20</li>
                    <li>💬 Webinar with German Universities – Sept 25</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Workflow / Pipeline -->
    <div class="ad-card ad-card-light mb-4">
        <h6>Student Application Pipeline</h6>
        <div class="ad-pipeline">
            <div class="ad-step completed">Registered</div>
            <div class="ad-step completed">Docs Uploaded</div>
            <div class="ad-step active">Application Submitted</div>
            <div class="ad-step">University Review</div>
            <div class="ad-step">Visa Process</div>
            <div class="ad-step">Final Decision</div>
        </div>
    </div>

</div>

<!-- Charts JS -->
<script>
    // Conversion Chart
    const ctxConversion = document.getElementById('adConversionChart');
    new Chart(ctxConversion, {
        type: 'doughnut'
        , data: {
            labels: ['Applied', 'Remaining']
            , datasets: [{
                data: [{
                    {
                        $totalApplications ? ? 0
                    }
                }, {
                    {
                        max(($totalStudents ? ? 0) - ($totalApplications ? ? 0), 0)
                    }
                }]
                , backgroundColor: ['#22c55e', '#e5e7eb']
                , borderWidth: 0
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

    // Applications Trend
    new Chart(document.getElementById('adApplicationsTrend'), {
        type: 'line'
        , data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul']
            , datasets: [{
                label: 'Applications'
                , data: [5, 10, 7, 14, 20, 18, 25]
                , borderColor: '#4f46e5'
                , backgroundColor: 'rgba(79,70,229,0.2)'
                , tension: 0.3
                , fill: true
            }]
        }
    });

    // Status Chart
    new Chart(document.getElementById('adStatusChart'), {
        type: 'doughnut'
        , data: {
            labels: ['Pending', 'Approved', 'Rejected', 'Interview']
            , datasets: [{
                data: [12, 8, 3, 5]
                , backgroundColor: ['#fbbf24', '#22c55e', '#ef4444', '#3b82f6']
            }]
        }
    });

</script>

@endsection
