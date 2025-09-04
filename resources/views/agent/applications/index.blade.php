@extends('layouts.app')

@section('content')
<div class="agent-applications">
    <div class="page-header">
        <h2>My Applications</h2>
        <a href="{{ route('agent.applications.create') }}" class="btn btn-primary app-btn">+ New Application</a>
    </div>

    <div class="app-table">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>University</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $app)
                <tr>
                    <td>{{ $app->student->first_name }} {{ $app->student->last_name }}</td>
                    <td>{{ $app->university->name }}</td>
                    <td>{{ $app->course?->title ?? '-' }}</td>
                    <td>
                        <span class="status-badge {{ $app->application_status_class }}">
                            {{ ucfirst($app->application_status) }}
                        </span>
                    </td>
                    <td>{{ $app->remarks ?? '-' }}</td>
                    <td>{{ $app->created_at->format('Y-m-d') }}</td>
                    <td>
                        <a href="{{ route('agent.applications.show', $app->id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('agent.applications.edit', $app->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">No applications found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
