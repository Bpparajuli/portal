@extends('layouts.agent')
@section('agent-content')
<h2>Applications</h2>

<a href="{{ route('agent.applications.create') }}" class="btn btn-success mb-3">Create New Application</a>

@if($applications->isEmpty())
<p>No applications found.</p>
@else
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Application #</th>
            <th>Student</th>
            <th>University</th>
            <th>Course</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($applications as $app)
        <tr>
            <td>{{ $app->application_number ?? '-' }}</td>
            <td>{{ $app->student->first_name }} {{ $app->student->last_name }}</td>
            <td>{{ $app->university->name }}</td>
            <td>{{ $app->course->name ?? '-' }}</td>
            <td>{{ $app->application_status }}</td>
            <td>
                <a href="{{ route('agent.applications.show', $app->id) }}" class="btn btn-sm btn-info">View</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif
@endsection
