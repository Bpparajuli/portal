@php
    $__user = auth()->user();
    $__isAgent = $__user->is_agent;
    $__isStaff = $__user->is_staff && !$__user->is_admin_staff;
    $__layout = $__isAgent ? 'layouts.agent' : ($__isStaff ? 'layouts.staff' : 'layouts.admin');
    $__section = $__isAgent ? 'agent-content' : ($__isStaff ? 'staff-content' : 'admin-content');
    $role = $__user->role;
@endphp

@extends($__layout)

@section('title', $course->title . ' - Course Details')
@section('page-title', $course->title)

@section($__section)
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route($role === 'staff' ? 'staff.courses' : $role . '.courses.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Courses
        </a>
        <div class="d-flex gap-2">
            @can('update', $course)
            <a href="{{ route($role . '.courses.edit', $course) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcan
            @can('delete', $course)
            <x-confirm-delete
                action="{{ $role }}.courses.destroy"
                :id="$course->id"
                label="Delete Course"
                title="Delete {{ $course->course_code }} - {{ $course->title }}?"
                message="This action cannot be undone."
            />
            @endcan
        </div>
    </div>

    <div class="card shadow-sm border-0" data-aos="fade-up">
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-3 col-lg-2 text-center mb-4 mb-md-0">
                    @if($course->university && $course->university->university_logo)
                        <img src="{{ asset('storage/uni_logo/' . $course->university->university_logo) }}"
                             alt="{{ $course->university->name }}"
                             class="img-fluid rounded border p-2" style="max-height:120px;">
                    @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center p-4">
                            <i class="fas fa-university fa-3x text-muted"></i>
                        </div>
                    @endif
                </div>
                <div class="col-md-9 col-lg-10">
                    <h2 class="fw-bold mb-1">{{ $course->title }}</h2>
                    @if($course->university)
                        <p class="text-muted mb-2">
                            <i class="fas fa-university me-1"></i>
                            <a href="{{ route($role . '.universities.show', $course->university->id) }}">{{ $course->university->name }}</a>
                            @if($course->university->city || $course->university->country)
                                &middot; {{ $course->university->city ?? '' }}, {{ $course->university->country ?? '' }}
                            @endif
                        </p>
                    @endif
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-secondary">{{ $course->course_type }}</span>
                        @if($course->course_code)<span class="badge bg-info">Code: {{ $course->course_code }}</span>@endif
                    </div>
                </div>
            </div>

            <hr>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <small class="text-muted d-block">Duration</small>
                        <strong class="break-word">{{ $course->duration ?? 'N/A' }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <small class="text-muted d-block">Tuition Fee</small>
                        <strong>{{ $course->fee ? $course->fee : 'N/A' }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <small class="text-muted d-block">Application Fee</small>
                        <strong>{{ $course->application_fee ? '$' . $course->application_fee : 'N/A' }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <small class="text-muted d-block">Intakes</small>
                        <strong class="break-word">{{ $course->intakes ?? 'N/A' }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <small class="text-muted d-block">MOI Requirement</small>
                        <strong class="break-word">{{ $course->moi_requirement ?? 'N/A' }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <small class="text-muted d-block">Scholarships</small>
                        <strong>{{ $course->scholarships ?? 'N/A' }}</strong>
                    </div>
                </div>
            </div>

            <hr>

            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="fw-bold">Academic Requirements</h6>
                    <p class="text-muted">{{ $course->academic_requirement ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold">IELTS / PTE / Language Requirements</h6>
                    <p class="text-muted break-word">{{ $course->ielts_pte_other_languages ?? 'N/A' }}</p>
                </div>
            </div>

            <hr>

            <div class="mb-3">
                <h6 class="fw-bold">Description</h6>
                <p class="text-muted">{{ $course->description ?? 'No description available.' }}</p>
            </div>

            @if($course->course_link)
            <a href="{{ $course->course_link }}" target="_blank" class="btn btn-primary">
                <i class="fas fa-external-link-alt me-1"></i> Visit Course Page
            </a>
            @endif
        </div>
    </div>

    <style>
    .break-word {
        word-break: break-word;
        overflow-wrap: break-word;
    }
    </style>
</div>
@endsection
