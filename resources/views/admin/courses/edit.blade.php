@extends('layouts.app')

@section('content')
<h2>Edit Course for University: {{ $course->university->name ?? '' }}</h2>

<form action="{{ route('courses.update', $course->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label>University</label>
        <input type="text" class="form-control" value="{{ $course->university->name ?? '' }}" disabled>
    </div>

    <div class="mb-3">
        <label>Course Code</label>
        <input type="text" name="course_code" value="{{ $course->course_code ?? '' }}" class="form-control">
    </div>

    <div class="mb-3">
        <label>Title</label>
        <input type="text" name="title" value="{{ $course->title ?? '' }}" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Course Type</label>
        <select name="course_type" class="form-control" required>
            <option value="ug" {{ $course->course_type == 'ug' ? 'selected' : '' }}>Undergraduate</option>
            <option value="pg" {{ $course->course_type == 'pg' ? 'selected' : '' }}>Postgraduate</option>
            <option value="diploma" {{ $course->course_type == 'diploma' ? 'selected' : '' }}>Diploma</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Description</label>
        <textarea name="description" class="form-control">{{ $course->description ?? '' }}</textarea>
    </div>

    <div class="mb-3">
        <label>Duration</label>
        <input type="text" name="duration" value="{{ $course->duration ?? '' }}" class="form-control">
    </div>

    <div class="mb-3">
        <label>Fee</label>
        <input type="number" step="0.01" name="fee" value="{{ $course->fee ?? '' }}" class="form-control">
    </div>

    <div class="mb-3">
        <label>Intakes</label>
        <input type="text" name="intakes" value="{{ $course->intakes ?? '' }}" class="form-control">
    </div>

    <div class="mb-3">
        <label>IELTS/PTE/Other Languages</label>
        <input type="text" name="ielts_pte_other_languages" value="{{ $course->ielts_pte_other_languages ?? '' }}" class="form-control">
    </div>

    <div class="mb-3">
        <label>MOI Requirement</label>
        <input type="text" name="moi_requirement" value="{{ $course->moi_requirement ?? '' }}" class="form-control">
    </div>

    <div class="mb-3">
        <label>Application Fee</label>
        <input type="number" name="application_fee" value="{{ $course->application_fee ?? '' }}" class="form-control">
    </div>

    <button type="submit" class="btn btn-primary">Update</button>
    <a href="{{ route('universities.edit', $course->university_id) }}" class="btn btn-secondary">Back to University</a>
</form>
@endsection
