@php
    $__user = auth()->user();
    $__isAgent = $__user->is_agent;
    $__isStaff = $__user->is_staff && !$__user->is_admin_staff;
    $__layout = $__isAgent ? 'layouts.agent' : ($__isStaff ? 'layouts.staff' : 'layouts.admin');
    $__section = $__isAgent ? 'agent-content' : ($__isStaff ? 'staff-content' : 'admin-content');
    $role = $__user->is_admin_staff ? 'admin' : $__user->role;
@endphp

@extends($__layout)

@section('title', 'Applications for ' . $student->first_name . ' ' . $student->last_name)
@section('page-title', 'Applications for ' . $student->first_name . ' ' . $student->last_name)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
@endpush

@section($__section)
<div class="container-fluid px-3 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1f2937;"><i class="fas fa-file-alt text-primary me-2"></i>Applications</h4>
            <p class="text-muted mb-0 small">For <strong>{{ $student->first_name }} {{ $student->last_name }}</strong></p>
        </div>
        <a href="{{ route($role . '.students.show', $student) }}" class="btn btn-outline-secondary" style="font-size:0.82rem;padding:8px 20px;border-radius:10px;"><i class="fas fa-arrow-left me-1"></i>Back to Student</a>
    </div>

    @if($applications->count())
        <div class="row g-3">
            @foreach($applications as $app)
                <div class="col-lg-6">
                    <div class="app-show-card">
                        <div class="app-show-card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1" style="color:var(--primary);font-size:0.95rem;">{{ $app->university?->name ?? 'N/A' }}</h5>
                                    <small class="text-muted">{{ $app->university?->city ?? '' }}, {{ $app->university?->country ?? '' }}</small>
                                </div>
                                <span class="app-status-badge" style="background:{{ $app->status?->bg_color ?? '#6c757d' }}20;color:{{ $app->status?->bg_color ?? '#6c757d' }};">
                                    <span style="width:6px;height:6px;border-radius:50%;background:{{ $app->status?->bg_color ?? '#6c757d' }};display:inline-block;"></span>
                                    {{ $app->status?->name ?? 'N/A' }}
                                </span>
                            </div>
                            <p style="font-size:0.82rem;margin-bottom:0.3rem;"><span class="fw-semibold">Course:</span> {{ $app->course?->title ?? 'N/A' }}</p>
                            <p style="font-size:0.82rem;margin-bottom:0.3rem;"><span class="fw-semibold">App #:</span> {{ $app->application_number ?? $app->id }}</p>
                            <p style="font-size:0.82rem;margin-bottom:0.8rem;"><span class="fw-semibold">Agent:</span> {{ $app->agent?->business_name ?? 'N/A' }}</p>
                            <div class="d-flex gap-2">
                                <a href="{{ route($role . '.applications.show', $app) }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:0.75rem;"><i class="fas fa-eye me-1"></i>View</a>
                                @can('update', $app)
                                    <a href="{{ route($role . '.applications.edit', $app) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:0.75rem;"><i class="fas fa-edit me-1"></i>Edit</a>
                                @endcan
                                @if($app->sop_file)
                                    <a href="{{ Storage::url($app->sop_file) }}" target="_blank" class="btn btn-sm btn-outline-info previewable" style="border-radius:8px;font-size:0.75rem;" data-url="{{ Storage::url($app->sop_file) }}" data-filename="SOP_{{ $app->application_number }}" data-preview-type="application"><i class="fas fa-file-alt me-1"></i>SOP</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5" style="color:#9ca3af;">
            <i class="fas fa-file-alt fa-3x mb-3" style="color:#d1d5db;"></i>
            <p style="font-size:0.9rem;">No applications found for this student.</p>
            @can('create', App\Models\Application::class)
                <a href="{{ route($role . '.applications.create', ['student_id' => $student->id]) }}" class="btn btn-primary" style="border-radius:10px;font-size:0.82rem;padding:8px 24px;"><i class="fas fa-plus me-1"></i>Create Application</a>
            @endcan
        </div>
    @endif
</div>
@endsection
