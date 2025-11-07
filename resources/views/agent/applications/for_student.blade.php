@extends('layouts.agent')

@section('agent-content')
<div class="container py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">🎓 Applications for {{ $student->first_name }} {{ $student->last_name }}</h4>
        <a href="{{ route('agent.students.index') }}" class="btn btn-outline-secondary btn-sm">
            ← Back to Students
        </a>
    </div>

    @if($student->applications->count())
    <div class="d-flex flex-column gap-4">
        @foreach($student->applications as $application)
        <div class="card shadow-sm border-0 rounded-3 p-3">
            <div class="row align-items-start g-3">
                {{-- University / Course Info --}}
                <div class="col-md-3">
                    <h6 class="fw-bold text-primary mb-1">{{ $application->university->name ?? 'N/A' }}</h6>
                    <p class="mb-1 small">{{ $application->course->title ?? 'N/A' }}</p>
                    <span class="badge bg-info text-dark">{{ ucfirst($application->application_status ?? 'N/A') }}</span>
                </div>

                {{-- SOP --}}
                <div class="col-md-4">
                    <label class="fw-semibold mb-2">📑 SOP (Statement of Purpose)</label>

                    @if($application->sop_file)
                    <div class="d-flex align-items-center gap-3 border rounded p-2 bg-light">
                        <a href="#" data-preview="{{ asset('storage/' . $application->sop_file) }}" target="_blank" class="d-flex gap-3 align-items-center w-100">
                            <div style="width:90px; height:110px; overflow:hidden; border-radius:6px; border:1px solid #ddd; display:flex; align-items:center; justify-content:center;">
                                <iframe src="{{ asset('storage/' . $application->sop_file) }}" style="width:100%; height:100%; border:none;" loading="lazy">
                                    <p class="small text-muted">Preview not available</p>
                                </iframe>
                            </div>
                            @php
                            $ext = strtoupper(pathinfo($application->sop_file, PATHINFO_EXTENSION));
                            @endphp
                            <div class="flex-grow-1">
                                <p class="mb-1"><strong>{{ basename($application->sop_file) }}</strong></p>
                                <p class="small text-muted mb-2">{{ $ext }}</p>
                            </div>
                        </a>
                    </div>
                    @else
                    <span class="text-danger small">⚠️ SOP not uploaded</span>
                    @endif
                </div>

                {{-- Submitted Info --}}
                <div class="col-md-2">
                    <small class="text-muted d-block">Submitted On</small>
                    <span>{{ $application->created_at->format('d M, Y') }}</span>
                </div>

                {{-- Actions --}}
                <div class="col-md-3 d-flex flex-column align-items-end gap-2">
                    <a href="{{ route('agent.applications.show', $application->id) }}" class="btn btn-sm btn-primary w-100">
                        <i class="fa-solid fa-eye me-1"></i> View Details
                    </a>
                    @if(!$application->sop_file)
                    <span class="text-danger small">⚠️ SOP Missing</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="alert alert-info">No applications found for this student.</div>
    @endif
</div>
@endsection
