@extends('layout.app')

@section('content')
<div class="p-2">
    <h3>Apply Now: {{ $student->first_name }} {{ $student->last_name }}</h3>

    <form action="{{ route('student.submitApplication', $student->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label>University</label>
            <select name="university_id" class="form-select" required>
                <option value="">Select University</option>
                @foreach($universities as $uni)
                <option value="{{ $uni->id }}">{{ $uni->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Course</label>
            <select name="course_id" class="form-select" required>
                <option value="">Select Course</option>
                @foreach($courses as $course)
                <option value="{{ $course->id }}">{{ $course->title }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Upload Documents</label>
            <input type="file" name="documents[]" multiple class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Submit Application</button>
    </form>
</div>
@endsection
