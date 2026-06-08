@extends('layouts.app')
@php $role = auth()->user()->role; @endphp

@section('title', 'Applications for ' . $student->first_name . ' ' . $student->last_name)

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Applications</h4>
            <p class="text-muted mb-0">For <strong>{{ $student->first_name }} {{ $student->last_name }}</strong></p>
        </div>
        <a href="{{ route($role . '.students.show', $student) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Student</a>
    </div>

    @if($applications->count())
        <div class="row g-3">
            @foreach($applications as $app)
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1 text-primary">{{ $app->university?->name ?? 'N/A' }}</h5>
                                    <small class="text-muted">{{ $app->university?->city ?? '' }}, {{ $app->university?->country ?? '' }}</small>
                                </div>
                                <span class="badge rounded-pill" style="background:{{ $app->status?->bg_color ?? '#6c757d' }};">{{ $app->status?->name ?? 'N/A' }}</span>
                            </div>
                            <p class="mb-2"><span class="fw-semibold">Course:</span> {{ $app->course?->title ?? 'N/A' }}</p>
                            <p class="mb-2"><span class="fw-semibold">App #:</span> {{ $app->application_number ?? $app->id }}</p>
                            <p class="mb-3"><span class="fw-semibold">Agent:</span> {{ $app->agent?->business_name ?? 'N/A' }}</p>
                            <div class="d-flex gap-2">
                                <a href="{{ route($role . '.applications.show', $app) }}" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-eye me-1"></i>View</a>
                                @can('update', $app)
                                    <a href="{{ route($role . '.applications.edit', $app) }}" class="btn btn-sm btn-outline-secondary rounded-pill"><i class="fas fa-edit me-1"></i>Edit</a>
                                @endcan
                                @if($app->sop_file)
                                    <a href="{{ Storage::url($app->sop_file) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill previewable" data-url="{{ Storage::url($app->sop_file) }}" data-filename="SOP_{{ $app->application_number }}" data-preview-type="application"><i class="fas fa-file-alt me-1"></i>SOP</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5 text-muted">
            <i class="fas fa-file-alt fa-3x mb-3 opacity-50"></i>
            <p>No applications found for this student.</p>
            @can('create', App\Models\Application::class)
                <a href="{{ route($role . '.applications.create', ['student_id' => $student->id]) }}" class="btn btn-primary rounded-pill"><i class="fas fa-plus me-1"></i>Create Application</a>
            @endcan
        </div>
    @endif
</div>
@endsection
