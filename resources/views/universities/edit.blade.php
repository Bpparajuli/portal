@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Edit University</h2>

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="alert alert-danger">
        <strong>There were some errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('universities.update', $university->id) }}" method="POST">
        @csrf
        @method('PUT')

        @include('universities._form')

        <button type="submit" class="btn btn-success">Update University</button>
        <a href="{{ route('universities.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

    <hr>

    <h3>Courses</h3>

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

    <hr>

    <h3>Add New Course</h3>
    <form action="{{ route('courses.store') }}" method="POST">
        @csrf

        <input type="hidden" name="university_id" value="{{ $university->id }}">

        <div class="mb-3">
            <label for="course_code" class="form-label">Course Code</label>
            <input type="text" class="form-control" id="course_code" name="course_code" required>
        </div>

        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>

        <div class="mb-3">
            <label for="duration" class="form-label">Duration</label>
            <input type="text" class="form-control" id="duration" name="duration">
        </div>

        <div class="mb-3">
            <label for="fee" class="form-label">Fee</label>
            <input type="number" class="form-control" id="fee" name="fee" step="0.01" min="0">
        </div>

        <div class="mb-3">
            <label for="intakes" class="form-label">Intakes</label>
            <input type="text" class="form-control" id="intakes" name="intakes">
        </div>

        <div class="mb-3">
            <label for="moi_requirement" class="form-label">MOI Requirement</label>
            <input type="text" class="form-control" id="moi_requirement" name="moi_requirement">
        </div>

        <button type="submit" class="btn btn-primary">Add Course</button>
    </form>
</div>
@endsection
