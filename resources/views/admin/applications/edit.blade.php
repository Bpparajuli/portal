@extends('layouts.admin')

@section('content')
<h2>Edit Application - {{ $application->application_number }}</h2>

<form action="{{ route('admin.applications.update', $application->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label>Student</label>
        <select name="student_id" class="form-control">
            @foreach($students as $student)
            <option value="{{ $student->id }}" {{ $application->student_id == $student->id ? 'selected' : '' }}>
                {{ $student->first_name }} {{ $student->last_name }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label>University</label>
        <select name="university_id" class="form-control">
            @foreach($universities as $university)
            <option value="{{ $university->id }}" {{ $application->university_id == $university->id ? 'selected' : '' }}>
                {{ $university->name }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label>Course</label>
        <select name="course_id" class="form-control">
            <option value="">--None--</option>
            @foreach($courses as $course)
            <option value="{{ $course->id }}" {{ $application->course_id == $course->id ? 'selected' : '' }}>
                {{ $course->name }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label>Agent</label>
        <select name="agent_id" class="form-control">
            @foreach($agents as $agent)
            <option value="{{ $agent->id }}" {{ $application->agent_id == $agent->id ? 'selected' : '' }}>
                {{ $agent->username }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label>Status</label>
        <select name="application_status" class="form-control">
            @foreach([
            'Application created',
            'Application viewed by Admin',
            'Applied to University',
            'Need to give the test',
            'Accepted by the University',
            'Rejected by the University',
            'Applied to another university',
            'Application forwarded to embassy',
            'Is on waiting list on Embassy',
            'Visa Approved',
            'Visa Rejected',
            'Lost'
            ] as $status)
            <option value="{{ $status }}" {{ $application->application_status == $status ? 'selected' : '' }}>{{ $status }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label>Remarks</label>
        <textarea name="remarks" class="form-control">{{ $application->remarks }}</textarea>
    </div>

    <button type="submit" class="btn btn-success">Update</button>
    <a href="{{ route('admin.applications.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
