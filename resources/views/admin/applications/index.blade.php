@extends('layouts.admin')

@section('admin-content')
<div class="container p-4">
    <h3>📑 All Applications</h3>

    <table class="table table-hover table-striped align-middle shadow-sm rounded text-center">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Student</th>
                <th>University</th>
                <th>Course</th>
                <th>Agent</th>
                <th>Status</th>
                <th>SOP</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applications as $app)
            <tr>
                <td>{{ $app->id }}</td>
                <td>
                    {{ $app->student ? $app->student->first_name . ' ' . $app->student->last_name : 'N/A' }}
                </td>
                <td>{{ $app->university->name ?? 'N/A' }}</td>
                <td>{{ $app->course->title ?? 'N/A' }}</td>
                <td>
                    {{ $app->agent ? $app->agent->business_name ?? $app->agent->username : 'Admin Added' }}
                </td>
                <td>
                    <span class="badge {{ $app->status_class ?? 'bg-light text-muted' }}">
                        {{ $app->application_status }}
                    </span>
                </td>
                <td>
                    @if($app->sop && $app->sop->file_path)
                    <a href="#" data-preview="{{ Storage::url($app->sop->file_path) }}" class="btn btn-light btn-sm  p-1">
                        👁️ SOP
                    </a>
                    @else
                    <span class="text-muted">Not uploaded</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex">
                        <a href="{{ route('admin.applications.show', $app->id) }}" class="btn btn-sm btn-secondary">
                            View
                        </a>
                        <a href="{{ route('admin.applications.edit', $app->id) }}" class="btn btn-sm btn-dark">
                            edit
                        </a>
                    </div>

                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted">No applications found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
