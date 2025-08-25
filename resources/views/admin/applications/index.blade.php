@extends('layouts.admin')

@section('content')
<h2>Applications (Admin)</h2>
<a href="{{ route('admin.applications.create') }}" class="btn btn-primary">Add Application</a>
@include('partials.alerts')
<table class="table mt-3">
    <thead>
        <tr>
            <th>ID</th>
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
            <td>{{ $app->id }}</td>
            <td>{{ $app->student->name ?? '-' }}</td>
            <td>{{ $app->university->name ?? '-' }}</td>
            <td>{{ $app->course->name ?? '-' }}</td>
            <td>{{ ucfirst($app->status) }}</td>
            <td>
                <a href="{{ route('admin.applications.edit', $app->id) }}" class="btn btn-sm btn-warning">Edit</a>
                <form method="POST" action="{{ route('admin.applications.destroy', $app->id) }}" style="display:inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
