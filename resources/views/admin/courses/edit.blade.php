@extends('layouts.app')

@section('content')
    <div class="p-4">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center">
            <h2>Edit Course :</h2>
            <h4 class="fw-bold text-primary">
                {{ $course->title }} ({{ $course->id }})
            </h4>

            <div class="d-flex gap-2">
                @if ($previous)
                    <a href="{{ route('admin.courses.edit', $previous->id) }}" class="btn btn-outline-secondary">
                        ← Previous
                    </a>
                @endif

                @if ($next)
                    <a href="{{ route('admin.courses.edit', $next->id) }}" class="btn btn-outline-primary">
                        Next →
                    </a>
                @endif
            </div>
        </div>

        <!-- DELETE COURSE -->
        <div class="mt-3 mb-3">
            @if (in_array(auth()->id(), [1, 2]))
                <x-confirm-delete
                    action="admin.courses.destroy"
                    :id="$course->id"
                    label="Delete Course"
                    message="Are you sure you want to delete course {{ $course->course_code }} - {{ $course->title }}? This action cannot be undone!"
                    mode="native"
                    class="btn btn-danger btn-sm"
                />
            @endif
        </div>

        <!-- UPDATE FORM -->
        <form action="{{ route('admin.courses.update', $course->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- UNIVERSITY -->
            <div class="mb-3">
                <label>University <span class="text-danger">*</span></label>
                <input type="text" class="form-control" value="{{ $course->university->name }}" readonly>
                <input type="hidden" name="university_id" value="{{ $course->university_id }}">
            </div>

            <!-- TITLE & CODE -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $course->title) }}" class="form-control"
                        required>
                    @error('title')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label>Course Code <span class="text-danger">*</span></label>
                    <input type="text" name="course_code" value="{{ old('course_code', $course->course_code) }}"
                        class="form-control" required>
                    @error('course_code')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- DURATION / FEE / INTAKES -->
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Duration</label>
                    <input type="text" name="duration" value="{{ old('duration', $course->duration) }}"
                        class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Fee</label>
                    <input type="text" name="fee" value="{{ old('fee', $course->fee) }}" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Intakes <span class="text-danger">*</span></label>
                    <input type="text" name="intakes" value="{{ old('intakes', $course->intakes) }}"
                        class="form-control" required>
                    @error('intakes')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- COURSE TYPE / MOI -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Course Type</label>
                    <select name="course_type" class="form-control">
                        <option value="">-- Select Type --</option>
                        <option value="UG" {{ old('course_type', $course->course_type) == 'UG' ? 'selected' : '' }}>
                            Undergraduate</option>
                        <option value="PG" {{ old('course_type', $course->course_type) == 'PG' ? 'selected' : '' }}>
                            Postgraduate</option>
                        <option value="Diploma"
                            {{ old('course_type', $course->course_type) == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                    </select>
                    @error('course_type')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label>MOI Acceptance</label>
                    <input type="text" name="moi_acceptance" class="form-control"
                        value="{{ old('moi_acceptance', $course->moi_acceptance) }}">
                    @error('moi_acceptance')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- LINKS & REQUIREMENTS -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Course Link</label>
                    <input type="text" name="course_link" class="form-control"
                        value="{{ old('course_link', $course->course_link) }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Academic Requirement</label>
                    <input type="text" name="academic_requirement" class="form-control"
                        value="{{ old('academic_requirement', $course->academic_requirement) }}">
                </div>
            </div>

            <!-- IELTS / FEE / SCHOLARSHIPS -->
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>IELTS/PTE/Other</label>
                    <input type="text" name="ielts_pte_other_languages"
                        value="{{ old('ielts_pte_other_languages', $course->ielts_pte_other_languages) }}"
                        class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Application Fee</label>
                    <input type="text" name="application_fee"
                        value="{{ old('application_fee', $course->application_fee) }}" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Scholarships</label>
                    <input type="text" name="scholarships" value="{{ old('scholarships', $course->scholarships) }}"
                        class="form-control">
                </div>
            </div>

            <!-- DESCRIPTION -->
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control">{{ old('description', $course->description) }}</textarea>
            </div>
            <div class="d-flex justify-content-end">
                <!-- SUBMIT -->
                <button type="submit" class="btn btn-success">
                    Update Course
                </button>

            </div>
        </form>

    </div>

@endsection
