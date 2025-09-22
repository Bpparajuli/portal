@extends('layouts.agent')

@section('agent-content')
<div class="container p-4">
    <h3>📑 My Applications</h3>
    <table class="table table-bordered mt-3">
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
                <td>{{ isset($app->student) ? $app->student->first_name . ' ' . $app->student->last_name : 'N/A' }}</td>
                <td>{{ $app->university->name ?? 'N/A' }}</td>
                <td>{{ $app->course->title ?? 'N/A' }}</td>
                <td>
                    <span class="badge {{ $app->status_class ?? 'bg-light text-muted' }}">
                        {{ $app->application_status }}
                    </span>
                </td>
                <td>
                    @if($app->sop && $app->sop->file_path)
                    <a href="#" data-preview="{{ Storage::url($app->sop->file_path) }}" class="btn m-2 rounded-2 p-2 btn-sm">
                        👁️ View SOP
                    </a> @else
                    <span class="text-muted">Not uploaded</span>
                    @endif
                </td>


                <td>
                    <a href="{{ route('agent.applications.show',$app->id) }}" class="btn btn-sm btn-secondary">View Application</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No applications yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
