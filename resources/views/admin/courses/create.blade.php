@extends('layouts.app')

@section('content')
    <div class="p-4">
        <h2>Add New Course</h2>

        <form action="{{ route('admin.courses.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class=" mb-3">
                    <label>University <span class="text-danger">*</span></label>

                    @if ($selectedUniversity)
                        {{-- READONLY display --}}
                        <input type="text" class="form-control"
                            value="{{ $selectedUniversity->name }}-{{ $selectedUniversity->city }}" readonly>

                        {{-- Hidden field to submit actual value --}}
                        <input type="hidden" name="university_id" value="{{ $selectedUniversity->id }}">
                    @else
                        {{-- Normal dropdown when no selected university --}}
                        <select name="university_id" class="form-control" required>
                            <option value="">-- Select University --</option>
                            @foreach ($universities as $uni)
                                <option value="{{ $uni->id }}"
                                    {{ old('university_id') == $uni->id ? 'selected' : '' }}>
                                    {{ $uni->name }}-{{ $uni->city }}
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
                    @error('title')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label>Course Code <span class="text-danger">*</span></label>
                    <input type="text" name="course_code" value="{{ old('course_code') }}" class="form-control"
                        required>
                    @error('course_code')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
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
                    @error('intakes')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Course Type <span class="text-danger">*</span></label>
                    <input type="text" name="course_type" value="{{ old('course_type') }}" class="form-control"
                        required>
                    @error('course_type')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label>MOI Acceptance <span class="text-danger">*</span></label>
                    <input type="text" name="moi_acceptance" value="{{ old('moi_acceptance') }}" class="form-control"
                        required>
                    @error('moi_acceptance')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Course Link</label>
                    <input type="text" name="course_link" class="form-control">{{ old('course_link') }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Academic Requirement</label>
                    <input type="text" name="academic_requirement"
                        class="form-control">{{ old('academic_requirement') }}</textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>IELTS/PTE/Other Languages</label>
                    <input type="text" name="ielts_pte_other_languages" value="{{ old('ielts_pte_other_languages') }}"
                        class="form-control">
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
                <a href="{{ route('admin.courses.index') }}" class="btn btn-danger">Cancel</a>
                <button type="submit" class="btn btn-success">Add Course</button>

            </div>
        </form>
    </div>
@endsection
