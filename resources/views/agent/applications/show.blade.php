@extends('layouts.agent')
@section('agent-content')
<h2>Application Details</h2>

<div class="card p-3 mb-3">
    <p><strong>Student:</strong> {{ $application->student->first_name }} {{ $application->student->last_name }}</p>
    <p><strong>University:</strong> {{ $application->university->name }}</p>
    <p><strong>Course:</strong> {{ $application->course->name ?? '-' }}</p>
    <p><strong>Status:</strong> {{ $application->application_status }}</p>
    <p><strong>Remarks:</strong> {{ $application->remarks ?? '-' }}</p>
</div>

<h4>SOP Document</h4>
@if($application->sop)
<p>
    <a href="{{ route('agent.documents.download', $application->sop->id) }}" class="btn btn-sm btn-success">
        Download SOP
    </a>
</p>
@else
<p>No SOP uploaded.</p>
@endif
@endsection
