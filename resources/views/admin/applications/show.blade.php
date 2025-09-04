@extends('layouts.admin')

@section('content')
<h2>Application Details - {{ $application->application_number }}</h2>

<div class="application-details">
    <p><strong>Student:</strong> {{ $application->student->first_name }} {{ $application->student->last_name }}</p>
    <p><strong>University:</strong> {{ $application->university->name }}</p>
    <p><strong>Course:</strong> {{ $application->course?->name ?? '-' }}</p>
    <p><strong>Agent:</strong> {{ $application->agent->username }}</p>
    <p><strong>Status:</strong> {{ $application->application_status }}</p>
    <p><strong>Remarks:</strong> {{ $application->remarks ?? '-' }}</p>
    <p><strong>Submitted At:</strong> {{ $application->created_at->format('Y-m-d H:i') }}</p>
</div>

<a href="{{ route('admin.applications.index') }}" class="btn btn-secondary">Back</a>
<a href="{{ route('admin.applications.edit', $application->id) }}" class="btn btn-primary">Edit</a>
@endsection
