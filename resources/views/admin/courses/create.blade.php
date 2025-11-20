@extends('layouts.app')

@section('content')
<div class="p-4">
    <h2>Add New Course</h2>

    <form action="{{ route('admin.courses.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class=" mb-3">
                <label>University <span class="text-danger">*</span></label>

                @if($selectedUniversity)
                {{-- READONLY display --}}
                <input type="text" class="form-control" value="{{ $selectedUniversity->name }}" readonly>

                {{-- Hidden field to submit actual value --}}
                <input type="hidden" name="university_id" value="{{ $selectedUniversity->id }}">

                @else
                {{-- Normal dropdown when no selected university --}}
                <select name="university_id" class="form-control" required>
                    <option value="">-- Select University --</option>
                    @foreach($universities as $uni)
                    <option value="{{ $uni->id }}" {{ old('university_id') == $uni->id ? 'selected' : '' }}>
                        {{ $uni->name }}
                    </option>
                    @endforeach
                </select>
                @endif

                @error('university_id')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Title <span class="text-danger">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" class="form-control" required>
                @error('title')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label>Course Code <span class="text-danger">*</span></label>
                <input type="text" name="course_code" value="{{ old('course_code') }}" class="form-control" required>
                @error('course_code')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Duration</label>
                <input type="text" name="duration" value="{{ old('duration') }}" class="form-control">
            </div>
            <div class="col-md-4 mb-3">
                <label>Fee</label>
                <input type="text" name="fee" value="{{ old('fee') }}" class="form-control">
            </div>

            <div class="col-md-4 mb-3">
                <label>Intakes <span class="text-danger">*</span></label>
                <input type="text" name="intakes" value="{{ old('intakes') }}" class="form-control" required>
                @error('intakes')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Course Type <span class="text-danger">*</span></label>
                <select name="course_type" class="form-control" required>
                    <option value="">-- Select Type --</option>
                    <option value="UG" {{ old('course_type') == 'UG' ? 'selected' : '' }}>Undergraduate</option>
                    <option value="PG" {{ old('course_type') == 'PG' ? 'selected' : '' }}>Postgraduate</option>
                    <option value="Diploma" {{ old('course_type') == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                </select>
                @error('course_type')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label>MOI Requirement <span class="text-danger">*</span></label>
                <select name="moi_requirement" class="form-control" required>
                    <option value="">-- Select --</option>
                    <option value="Yes" {{ old('moi_requirement') == 'Yes' ? 'selected' : '' }}>Yes</option>
                    <option value="No" {{ old('moi_requirement') == 'No' ? 'selected' : '' }}>No</option>
                </select>
                @error('moi_requirement')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Course Link</label>
                <input type="text" name="course_link" class="form-control">{{ old('course_link') }}</textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label>Academic Requirement</label>
                <input type="text" name="academic_requirement" class="form-control">{{ old('academic_requirement') }}</textarea>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>IELTS/PTE/Other Languages</label>
                <input type="text" name="ielts_pte_other_languages" value="{{ old('ielts_pte_other_languages') }}" class="form-control">
            </div>
            <div class="col-md-4 mb-3">
                <label>Application Fee</label>
                <input type="text" name="application_fee" value="{{ old('application_fee') }}" class="form-control">
            </div>

            <div class="col-md-4 mb-3">
                <label>Scholarships</label>
                <input type="text" name="scholarships" value="{{ old('scholarships') }}" class="form-control">
            </div>
        </div>
        <div class="row">
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control">{{ old('description') }}</textarea>
            </div>

        </div>
        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-success">Add Course</button>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-danger">Cancel</a>

        </div>
    </form>
</div>
@endsection
