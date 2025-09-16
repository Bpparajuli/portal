@extends('layouts.app')

@section('content')
<h2>Create Application</h2>
<form action="{{ route('agent.applications.store') }}" method="POST">
    @csrf
    <label>Student</label>
    <select name="student_id" required>
        <option value="">Select Student</option>
        @foreach($students as $student)
        <option value="{{ $student->id }}">{{ $student->first_name }} {{ $student->last_name }}</option>
        @endforeach
    </select>

    <label>University</label>
    <select name="university_id" required>
        <option value="">Select University</option>
        @foreach($universities as $uni)
        <option value="{{ $uni->id }}">{{ $uni->name }}</option>
        @endforeach
    </select>

    <label>Course</label>
    <select name="course_id">
        <option value="">Select Course</option>
        @foreach($courses as $course)
        <option value="{{ $course->id }}">{{ $course->title }}</option>
        @endforeach
    </select>

    <label>Remarks</label>
    <textarea name="remarks"></textarea>

    <button type="submit">Submit Application</button>
</form>
@endsection
