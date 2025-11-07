@extends('layouts.agent')

@section('agent-content')
<div class="container p-4">
    <h3>📑 My Applications</h3>

    <table class="table table-bordered mt-3 align-middle text-center">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Student</th>
                <th>University</th>
                <th>Course</th>
                <th>Status</th>
                <th>SOP</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applications as $app)
            <tr>
                <td>{{ $app->id }}</td>
                <td>{{ optional($app->student)->first_name }} {{ optional($app->student)->last_name }}</td>
                <td>{{ $app->university->name ?? 'N/A' }}</td>
                <td>{{ $app->course->title ?? 'N/A' }}</td>
                <td>
                    <span class="badge {{ $app->status_class ?? 'bg-secondary' }}">
                        {{ $app->application_status }}
                    </span>
                </td>
                <td>
                    @if($app->sop_file)
                    <a href="#" data-preview="{{ Storage::url($app->sop_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        👁️ SOP
                    </a>
                    @else
                    <span class="text-muted">Not uploaded</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex">
                        <a href="{{ route('agent.applications.edit', $app->id) }}" class="btn btn-sm m-1 btn-dark">
                            ✏️
                        </a>
                        <a href="{{ route('agent.applications.show', $app->id) }}" class="btn btn-sm m-1 btn-secondary">
                            👁️
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-3">
                    No applications yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">
        {{ $applications->links() }}
    </div>
</div>
@endsection
