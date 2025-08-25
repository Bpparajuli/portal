@extends('layout.app')

@section('content')
<div class=" mt-4">
    <h2>Edit University</h2>

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Success Message --}}
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('universities.update', $university->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('universities.form')

        <button type="submit" class="btn btn-success">Update University</button>
        <a href="{{ route('universities.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

    <hr>
    <h3>Courses at {{ $university->short_name ?? $university->name }}</h3>
    <a href="{{ route('courses.create') }}" class="btn btn-primary mb-3">Add New Course</a>

    @if($university->courses->count())
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Course Code</th>
                <th>Title</th>
                <th>Duration</th>
                <th>Fee</th>
                <th>Intakes</th>
                <th>MOI Requirement</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($university->courses as $course)
            <tr>
                <td>{{ $course->course_code }}</td>
                <td>{{ $course->title }}</td>
                <td>{{ $course->duration }}</td>
                <td>${{ number_format($course->fee, 2) }}</td>
                <td>{{ $course->intakes }}</td>
                <td>{{ $course->moi_requirement }}</td>
                <td>
                    <a href="{{ route('courses.edit', $course->id) }}" class="btn btn-sm btn-info">Edit</a>
                    <form action="{{ route('courses.destroy', $course->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this course?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>No courses found for this university.</p>
    @endif
</div>
@endsection
