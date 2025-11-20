@extends('layouts.admin')

@section('admin-content')
<div class="container p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>📑 All Applications</h3>
        <form method="GET" action="{{ route('admin.applications.index') }}">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by student, course, or university" value="{{ request('search') }}">
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class=" table table-striped align-middle">
            <thead class="table-primary">
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
                        @if($app->sop_file)
                        <a href="#" data-preview="{{ Storage::url($app->sop_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            👁️ SOP
                        </a>
                        @else
                        <span class="text-muted">Not uploaded</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.applications.show', $app->id) }}" class="p-2 btn btn-sm btn-secondary">
                                View
                            </a>
                            <a href="{{ route('admin.applications.edit', $app->id) }}" class="p-2 btn btn-sm btn-dark">
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

</div>
@endsection
