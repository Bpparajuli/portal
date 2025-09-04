@extends('layouts.app')

@section('content')
<div class="agent-applications">
    <div class="page-header">
        <h2>Application Details</h2>
        <a href="{{ route('agent.applications.index') }}" class="btn btn-secondary app-btn">← Back</a>
    </div>

    <div class="app-card">
        <div class="app-row"><strong>Student:</strong> {{ $application->student->first_name }} {{ $application->student->last_name }}</div>
        <div class="app-row"><strong>University:</strong> {{ $application->university->name }}</div>
        <div class="app-row"><strong>Course:</strong> {{ $application->course?->title ?? '-' }}</div>
        <div class="app-row"><strong>Status:</strong> <span class="status-badge {{ $application->application_status_class }}">{{ ucfirst($application->application_status) }}</span></div>
        <div class="app-row"><strong>Remarks:</strong> {{ $application->remarks ?? '-' }}</div>
        <div class="app-row"><strong>Created At:</strong> {{ $application->created_at->format('Y-m-d') }}</div>
        <div class="app-row"><strong>Updated At:</strong> {{ $application->updated_at->format('Y-m-d') }}</div>
    </div>
</div>
@endsection
