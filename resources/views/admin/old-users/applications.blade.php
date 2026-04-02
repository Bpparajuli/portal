@extends('layouts.admin')
@section('admin-content')

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="fa fa-file-alt text-success me-2"></i>Applications of {{ $agent->business_name }}</h3>
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by uni,student...">
            <button class="btn btn-success"><i class="fa fa-search me-1"></i>Search</button>
        </form>
    </div>
    @if($applications->count())
    <div class="table-wrapper">
        <table class="table table-hover table-striped align-middle shadow-sm rounded text-center">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>University</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Submitted On</th>
                </tr>
            </thead>
            <tbody>
                @foreach($applications as $app)
                <tr>
                    <td>
                        <a href="{{ route('admin.applications.show', $app->id) }}" class="text-decoration-none">
                            {{ $app->id }}
                        </a>
                    </td>
                    <td>{{ $app->student->first_name ?? 'N/A' }} {{ $app->student->last_name ?? '' }}</td>
                    <td>{{ $app->course->university->name ?? 'N/A' }}</td>
                    <td>{{ $app->course->name ?? 'N/A' }}</td>
                    <td>
                        @php
                        $statusClass = match($app->application_status) {
                        'Accepted by the University' => 'success',
                        'Rejected by the University' => 'danger',
                        'Need to give the test' => 'warning',
                        'Application started' => 'secondary',
                        default => 'primary',
                        };
                        @endphp
                        <a href="{{ route('admin.applications.show', $app->id) }}" class="badge bg-{{ $statusClass }} text-decoration-none">
                            {{ $app->application_status }}
                        </a>
                    </td>
                    <td>{{ $app->created_at->format('d M Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $applications->links('pagination::bootstrap-5') }}
    </div>
    @else
    <div class="alert alert-info text-center">No applications found for this agent.</div>
    @endif
</div>

@endsection
