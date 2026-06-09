@extends('layouts.staff')

@section('staff-content')
<div class="container-fluid p-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h5 class="fw-bold mb-0" style="color: var(--primary);">Staff Dashboard</h5>
            <p class="text-muted mb-0 small">Overview of your portal data</p>
        </div>
        <span class="text-muted small"><i class="far fa-calendar me-1"></i>{{ now()->format('F d, Y') }}</span>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3 col-6">
            <x-stat-card icon="fa-user-graduate" value="{{ $totalStudents }}" label="Total Students" color="primary" />
        </div>
        <div class="col-md-3 col-6">
            <x-stat-card icon="fa-file-alt" value="{{ $totalApplications }}" label="Applications" color="info" />
        </div>
        <div class="col-md-3 col-6">
            <x-stat-card icon="fa-university" value="{{ $totalUniversities }}" label="Universities" color="success" />
        </div>
        <div class="col-md-3 col-6">
            <x-stat-card icon="fa-check-circle" value="{{ $docCompletionRate }}%" label="Docs Completion" color="warning" />
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Monthly Applications ({{ now()->year }})</h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-2">
                    <h6 class="fw-bold mb-0">Application Pipeline</h6>
                </div>
                <div class="card-body p-2">
                    @foreach($statuses->where('count', '>', 0) as $status)
                    <div class="d-flex align-items-center justify-content-between p-2 border-bottom">
                        <span class="small">{{ $status->name }}</span>
                        <span class="badge rounded-pill" style="background:{{ $status->bg_color }};">{{ $status->count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Recent Students</h6>
                    <a href="{{ route('staff.students.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr><th class="ps-3">Name</th><th>Email</th><th class="pe-3">Joined</th></tr>
                        </thead>
                        <tbody>
                            @forelse($recentStudents as $student)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('staff.students.show', $student) }}" class="text-decoration-none fw-medium">{{ $student->full_name }}</a>
                                </td>
                                <td class="text-muted">{{ $student->email }}</td>
                                <td class="text-muted small pe-3">{{ $student->created_at?->format('M d, Y') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-3 text-muted">No students found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Recent Applications</h6>
                    <a href="{{ route('staff.applications.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr><th class="ps-3">Student</th><th>University</th><th>Status</th><th class="pe-3">Date</th></tr>
                        </thead>
                        <tbody>
                            @forelse($recentApplications as $app)
                            <tr>
                                <td class="ps-3 fw-medium">{{ $app->student?->name ?? '—' }}</td>
                                <td class="text-muted">{{ $app->university?->name ?? '—' }}</td>
                                <td><span class="badge" style="background:{{ $app->status?->bg_color ?? '#6c757d' }};">{{ $app->status?->name ?? 'N/A' }}</span></td>
                                <td class="text-muted small pe-3">{{ $app->created_at?->format('M d, Y') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-3 text-muted">No applications found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: @json($monthlyData->pluck('month')),
        datasets: [{
            label: 'Applications',
            data: @json($monthlyData->pluck('total')),
            backgroundColor: 'rgba(26, 2, 98, 0.15)',
            borderColor: 'rgba(26, 2, 98, 0.6)',
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f0f0f0' }, ticks: { stepSize: 1 } },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endpush
@endsection
