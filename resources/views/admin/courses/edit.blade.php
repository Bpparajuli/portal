@extends('layouts.app')

@section('content')
<div class="p-4">
    <h2>Edit Course</h2>

    <form action="{{ route('admin.courses.update', $course->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- UNIVERSITY (Readonly like create page) -->
        <div class="row">
            <div class="mb-3">
                <label>University <span class="text-danger">*</span></label>

                <input type="text" class="form-control" value="{{ $course->university->name }}" readonly>

                <input type="hidden" name="university_id" value="{{ $course->university_id }}">
            </div>
        </div>

        <!-- TITLE & COURSE CODE -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Title <span class="text-danger">*</span></label>
                <input type="text" name="title" value="{{ old('title', $course->title) }}" class="form-control" required>
                @error('title')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="col-md-6 mb-3">
                <label>Course Code <span class="text-danger">*</span></label>
                <input type="text" name="course_code" value="{{ old('course_code', $course->course_code) }}" class="form-control" required>
                @error('course_code')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>

        <!-- DURATION / FEE / INTAKES -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Duration</label>
                <input type="text" name="duration" value="{{ old('duration', $course->duration) }}" class="form-control">
            </div>

            <div class="col-md-4 mb-3">
                <label>Fee</label>
                <input type="text" name="fee" value="{{ old('fee', $course->fee) }}" class="form-control">
            </div>

            <div class="col-md-4 mb-3">
                <label>Intakes <span class="text-danger">*</span></label>
                <input type="text" name="intakes" value="{{ old('intakes', $course->intakes) }}" class="form-control" required>
                @error('intakes')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>

        <!-- COURSE TYPE / MOI -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Course Type <span class="text-danger">*</span></label>
                <select name="course_type" class="form-control" required>
                    <option value="">-- Select Type --</option>
                    <option value="UG" {{ old('course_type', $course->course_type) == 'UG' ? 'selected' : '' }}>Undergraduate</option>
                    <option value="PG" {{ old('course_type', $course->course_type) == 'PG' ? 'selected' : '' }}>Postgraduate</option>
                    <option value="Diploma" {{ old('course_type', $course->course_type) == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                </select>
                @error('course_type')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="col-md-6 mb-3">
                <label>MOI Requirement <span class="text-danger">*</span></label>
                <select name="moi_requirement" class="form-control" required>
                    <option value="Yes" {{ old('moi_requirement', $course->moi_requirement) == 'Yes' ? 'selected' : '' }}>Yes</option>
                    <option value="No" {{ old('moi_requirement', $course->moi_requirement) == 'No' ? 'selected' : '' }}>No</option>
                </select>
                @error('moi_requirement')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>

        <!-- COURSE LINK & ACADEMIC REQUIREMENT -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Course Link</label>
                <input type="text" name="course_link" class="form-control" value="{{ old('course_link', $course->course_link) }}">
            </div>

            <div class="col-md-6 mb-3">
                <label>Academic Requirement</label>
                <input type="text" name="academic_requirement" class="form-control" value="{{ old('academic_requirement', $course->academic_requirement) }}">
            </div>
        </div>

        <!-- IELTS / APPLICATION FEE / SCHOLARSHIPS -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label>IELTS/PTE/Other Languages</label>
                <input type="text" name="ielts_pte_other_languages" value="{{ old('ielts_pte_other_languages', $course->ielts_pte_other_languages) }}" class="form-control">
            </div>

            <div class="col-md-4 mb-3">
                <label>Application Fee</label>
                <input type="text" name="application_fee" value="{{ old('application_fee', $course->application_fee) }}" class="form-control">
            </div>

            <div class="col-md-4 mb-3">
                <label>Scholarships</label>
                <input type="text" name="scholarships" value="{{ old('scholarships', $course->scholarships) }}" class="form-control">
            </div>
        </div>

        <!-- DESCRIPTION -->
        <div class="row">
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control">{{ old('description', $course->description) }}</textarea>
            </div>
        </div>

        <!-- BUTTONS -->
        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Update Course</button>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-danger">Cancel</a>
        </div>
    </form>
</div>
@endsection
