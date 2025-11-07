@extends('layouts.admin')

@section('title', 'Course Details')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Course Details</h4>
        <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Courses
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">{{ $course->title }}</h5>
            <small>{{ $course->university->name ?? 'Unknown University' }}</small>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <h6 class="text-muted">University</h6>
                    <p class="fw-semibold">{{ $course->university->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted">City</h6>
                    <p class="fw-semibold">{{ $course->university->city ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted">Country</h6>
                    <p class="fw-semibold">{{ $course->university->country ?? 'N/A' }}</p>
                </div>
            </div>

            <hr>

            <div class="row mb-3">
                <div class="col-md-4">
                    <h6 class="text-muted">Course Code</h6>
                    <p class="fw-semibold">{{ $course->course_code }}</p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted">Course Type</h6>
                    <p class="fw-semibold">{{ $course->course_type }}</p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted">Duration</h6>
                    <p class="fw-semibold">{{ $course->duration ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <h6 class="text-muted">Tuition Fee</h6>
                    <p class="fw-semibold">
                        {{ $course->fee ? '$' . number_format((float) $course->fee, 2) : 'N/A' }}
                    </p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted">Application Fee</h6>
                    <p class="fw-semibold">{{ $course->application_fee ? '$' . $course->application_fee : 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted">MOI Requirement</h6>
                    <p class="fw-semibold">{{ $course->moi_requirement }}</p>
                </div>
            </div>

            <div class="mb-3">
                <h6 class="text-muted">Intakes</h6>
                <p class="fw-semibold">{{ $course->intakes ?? 'N/A' }}</p>
            </div>

            <div class="mb-3">
                <h6 class="text-muted">Description</h6>
                <p>{{ $course->description ?? 'No description available.' }}</p>
            </div>

            <div class="mb-3">
                <h6 class="text-muted">IELTS / PTE / Other Language Requirements</h6>
                <p>{{ $course->ielts_pte_other_languages ?? 'N/A' }}</p>
            </div>

            <div class="mb-3">
                <h6 class="text-muted">Scholarships</h6>
                <p>{{ $course->scholarships ?? 'N/A' }}</p>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted">Created At</h6>
                    <p>{{ $course->created_at ? $course->created_at->format('Y-m-d H:i') : 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted">Last Updated</h6>
                    <p>{{ $course->updated_at ? $course->updated_at->format('Y-m-d H:i') : 'N/A' }}</p>
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
