@extends('layout.app')

@section('content')
<div class="p-2">
    <h3>Applications for {{ $student->first_name }} {{ $student->last_name }}</h3>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>University</th>
                <th>Course</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($applications as $app)
            <tr>
                <td>{{ $app->university->name ?? 'N/A' }}</td>
                <td>{{ $app->course->title ?? 'N/A' }}</td>
                <td>{{ ucfirst($app->application_status) }}</td>
                <td>{{ $app->created_at }}</td>
                <td>
                    <a href="{{ route('students.documents', $app->id) }}" class="btn btn-sm btn-primary">Documents</a>
                    <a href="{{ route('students.chat', $app->id) }}" class="btn btn-sm btn-secondary">Chat</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
