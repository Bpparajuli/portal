@extends('layouts.agent')

@section('agent-content')
<div class="container p-4">
    <h3>📑 My Applications</h3>
    <div class="table-responsive">
        <table class="table table-bordered mt-3 align-middle text-center">
            <thead class="table-primary">
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
                    <td>
                        <a href="{{ route('agent.students.show', $app->student->id) }}">
                            {{ optional($app->student)->first_name }} {{ optional($app->student)->last_name }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('agent.universities.show', $app->university->id) }}">
                            {{ $app->university->name ?? 'N/A' }}
                        </a>
                    </td>
                    <td>{{ $app->course->title ?? 'N/A' }}</td>
                    <td>
                        <a href="{{ route('agent.applications.show', $app->id) }}">
                            <span class="badge {{ $app->status_class ?? 'bg-secondary' }}">
                                {{ $app->application_status }}
                            </span>
                        </a>
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
                        <div class="d-flex align-items-center justify-content-center">
                            <a href="{{ route('agent.applications.edit', $app->id) }}" title="Edit">
                                <i class="fa fa-pencil-square" style="font-size: 30px;"></i>
                            </a>

                            <a href="{{ route('agent.applications.show', $app->id) }}" class="btn btn-sm p-2 btn-outline-secondary" title="View">
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
    </div>
    <div class="mt-3">
        {{ $applications->links() }}
    </div>
</div>
@endsection
