@extends('layouts.app')

@section('content')
<div class="agent-applications">
    <div class="page-header">
        <h2>New Application</h2>
        <a href="{{ route('agent.applications.index') }}" class="btn btn-secondary app-btn">← Back</a>
    </div>

    <form action="{{ route('agent.applications.store') }}" method="POST" class="app-form">
        @csrf
        <div class="form-group">
            <label>Student</label>
            <select name="student_id" class="form-select">
                <option value="">Select Student</option>
                @foreach($students as $student)
                <option value="{{ $student->id }}">{{ $student->first_name }} {{ $student->last_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>University</label>
            <select name="university_id" class="form-select">
                <option value="">Select University</option>
                @foreach($universities as $uni)
                <option value="{{ $uni->id }}">{{ $uni->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Course</label>
            <select name="course_id" class="form-select">
                <option value="">Select Course</option>
                @foreach($courses as $course)
                <option value="{{ $course->id }}">{{ $course->title }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Remarks</label>
            <textarea name="remarks" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-primary app-btn">Submit Application</button>
    </form>
</div>
@endsection
