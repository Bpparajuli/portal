@extends('layouts.admin')

@section('content')
<h2>All Applications</h2>
<table class="table table-striped table-applications">
    <thead>
        <tr>
            <th>App Number</th>
            <th>Student</th>
            <th>University</th>
            <th>Course</th>
            <th>Agent</th>
            <th>Status</th>
            <th>Submitted At</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($applications as $app)
        <tr>
            <td>{{ $app->application_number }}</td>
            <td>{{ $app->student->first_name }} {{ $app->student->last_name }}</td>
            <td>{{ $app->university->name }}</td>
            <td>{{ $app->course?->name ?? '-' }}</td>
            <td>{{ $app->agent->username }}</td>
            <td>{{ $app->application_status }}</td>
            <td>{{ $app->created_at->format('Y-m-d') }}</td>
            <td>
                <a href="{{ route('admin.applications.show', $app->id) }}" class="btn btn-info btn-sm">View</a>
                <a href="{{ route('admin.applications.edit', $app->id) }}" class="btn btn-warning btn-sm">Edit</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
