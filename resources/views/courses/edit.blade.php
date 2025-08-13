@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Edit Course for University: {{ $university->name }}</h2>

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

    <form action="{{ route('courses.update', $course->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="course_code" class="form-label">Course Code</label>
            <input type="text" name="course_code" id="course_code" class="form-control" value="{{ old('course_code', $course->course_code) }}" required>
        </div>

        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $course->title) }}" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" rows="4" class="form-control">{{ old('description', $course->description) }}</textarea>
        </div>

        <div class="mb-3">
            <label for="duration" class="form-label">Duration</label>
            <input type="text" name="duration" id="duration" class="form-control" value="{{ old('duration', $course->duration) }}">
        </div>

        <div class="mb-3">
            <label for="fee" class="form-label">Fee</label>
            <input type="number" name="fee" id="fee" class="form-control" step="0.01" min="0" value="{{ old('fee', $course->fee) }}">
        </div>

        <div class="mb-3">
            <label for="intakes" class="form-label">Intakes</label>
            <input type="text" name="intakes" id="intakes" class="form-control" value="{{ old('intakes', $course->intakes) }}">
        </div>

        <div class="mb-3">
            <label for="moi_requirement" class="form-label">MOI Requirement</label>
            <input type="text" name="moi_requirement" id="moi_requirement" class="form-control" value="{{ old('moi_requirement', $course->moi_requirement) }}">
        </div>

        <button type="submit" class="btn btn-success">Update Course</button>
        <a href="{{ route('universities.edit', $university->id) }}" class="btn btn-secondary">Back to University</a>
    </form>
</div>
@endsection
