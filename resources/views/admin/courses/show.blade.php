@extends('layouts.agent')

@section('title', 'Course Details')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Course Details</h4>
        <a href="{{ route('agent.courses.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Courses
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">{{ $course->title }}</h3>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <h6 class="fw-bold">University</h6>
                    <p class="text-muted fw-semibold">{{ $course->university->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold">City</h6>
                    <p class="text-muted fw-semibold">{{ $course->university->city ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold">Country</h6>
                    <p class="text-muted fw-semibold">{{ $course->university->country ?? 'N/A' }}</p>
                </div>
            </div>

            <hr>

            <div class="row mb-3">
                <div class="col-md-4">
                    <h6 class="fw-bold">Course Code</h6>
                    <p class="text-muted fw-semibold">{{ $course->course_code }}</p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold">Course Type</h6>
                    <p class="text-muted fw-semibold">{{ $course->course_type }}</p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold">Duration</h6>
                    <p class="text-muted fw-semibold">{{ $course->duration ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <h6 class="fw-bold">Tuition Fee</h6>
                    <p class="text-muted fw-semibold">
                        {{ $course->fee }}
                    </p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold">Application Fee</h6>
                    <p class="text-muted fw-semibold">{{ $course->application_fee ? '$' . $course->application_fee : 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold">MOI Requirement</h6>
                    <p class="text-muted fw-semibold">{{ $course->moi_requirement }}</p>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <h6 class="fw-bold">Intakes</h6>
                    <p class="text-muted fw-semibold">{{ $course->intakes ?? 'N/A' }}</p>
                </div>

                <div class="col-md-4">
                    <h6 class="fw-bold">Academic Requirements</h6>
                    <p class="text-muted fw-semibold">{{ $course->academic_requirement ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold">Scholarships</h6>
                    <p class="text-muted fw-semibold">{{ $course->scholarships ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="row mb-3">

                <div class="col-md-6">
                    <h6 class="fw-bold">IELTS / PTE / Other Language Requirements</h6>
                    <p class="text-muted fw-semibold">{{ $course->ielts_pte_other_languages ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="text-muted">Course Link</h6>
                    @if($course->course_link)
                    <a href="{{ $course->course_link }}" target="_blank" class="text-decoration-underline">{{ $course->course_link }}</a>
                    @else
                    N/A
                    @endif
                </div>
            </div>
            <hr>
            <div class="mb-3">
                <h6 class="fw-bold">Description</h6>
                <p class="text-muted fw-semibold">{{ $course->description ?? 'No description available.' }}</p>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-primary me-2">
            <i class="fas fa-edit"></i> Edit Course
        </a>

        @if(Auth::id() === 1)
        <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this course?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Delete
            </button>
        </form>
        @endif
    </div>
</div>
</div>
</div>
@endsection
