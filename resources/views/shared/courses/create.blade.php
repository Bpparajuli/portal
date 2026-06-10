@php
    $__user = auth()->user();
    $__isAgent = $__user->is_agent;
    $__isStaff = $__user->is_staff && !$__user->is_admin_staff;
    $__layout = $__isAgent ? 'layouts.agent' : ($__isStaff ? 'layouts.staff' : 'layouts.admin');
    $__section = $__isAgent ? 'agent-content' : ($__isStaff ? 'staff-content' : 'admin-content');
    $role = $__user->role;
@endphp

@extends($__layout)

@section('title', 'Add Course')
@section('page-title', 'Add Course')

@section($__section)
<div class="container-fluid px-3 py-3">
    <h4 class="fw-bold mb-3"><i class="fas fa-plus-circle text-primary me-2"></i>Add New Course</h4>

    <form action="{{ route($role . '.courses.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-12 mb-3">
                <label for="university_id" class="form-label">University <span class="text-danger">*</span></label>
                @if(isset($selectedUniversity))
                    <input type="text" class="form-control" value="{{ $selectedUniversity->name }} - {{ $selectedUniversity->city }}" readonly>
                    <input type="hidden" name="university_id" value="{{ $selectedUniversity->id }}">
                @else
                    <select id="university_id" name="university_id" class="form-select @error('university_id') is-invalid @enderror" required>
                        <option value="">-- Select University --</option>
                        @foreach($universities as $uni)
                        <option value="{{ $uni->id }}" {{ old('university_id') == $uni->id ? 'selected' : '' }}>
                            {{ $uni->name }} - {{ $uni->city }}
                        </option>
                        @endforeach
                    </select>
                    @error('university_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="course_code" class="form-label">Course Code <span class="text-danger">*</span></label>
                <input type="text" id="course_code" name="course_code" value="{{ old('course_code') }}" class="form-control @error('course_code') is-invalid @enderror" required>
                @error('course_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="duration" class="form-label">Duration</label>
                <input type="text" id="duration" name="duration" value="{{ old('duration') }}" class="form-control @error('duration') is-invalid @enderror">
                @error('duration')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="fee" class="form-label">Fee</label>
                <input type="text" id="fee" name="fee" value="{{ old('fee') }}" class="form-control @error('fee') is-invalid @enderror">
                @error('fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="intakes" class="form-label">Intakes <span class="text-danger">*</span></label>
                <input type="text" id="intakes" name="intakes" value="{{ old('intakes') }}" class="form-control @error('intakes') is-invalid @enderror" required>
                @error('intakes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="course_type" class="form-label">Course Type</label>
                <select id="course_type" name="course_type" class="form-select @error('course_type') is-invalid @enderror">
                    <option value="">-- Select Type --</option>
                    <option value="UG" {{ old('course_type') == 'UG' ? 'selected' : '' }}>Undergraduate</option>
                    <option value="PG" {{ old('course_type') == 'PG' ? 'selected' : '' }}>Postgraduate</option>
                    <option value="Diploma" {{ old('course_type') == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                </select>
                @error('course_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="moi_requirement" class="form-label">MOI Requirement</label>
                <input type="text" id="moi_requirement" name="moi_requirement" value="{{ old('moi_requirement') }}" class="form-control @error('moi_requirement') is-invalid @enderror">
                @error('moi_requirement')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="application_fee" class="form-label">Application Fee</label>
                <input type="text" id="application_fee" name="application_fee" value="{{ old('application_fee') }}" class="form-control @error('application_fee') is-invalid @enderror">
                @error('application_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="academic_requirement" class="form-label">Academic Requirement</label>
                <input type="text" id="academic_requirement" name="academic_requirement" value="{{ old('academic_requirement') }}" class="form-control @error('academic_requirement') is-invalid @enderror">
                @error('academic_requirement')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="ielts_pte_other_languages" class="form-label">IELTS / PTE / Other</label>
                <input type="text" id="ielts_pte_other_languages" name="ielts_pte_other_languages" value="{{ old('ielts_pte_other_languages') }}" class="form-control @error('ielts_pte_other_languages') is-invalid @enderror">
                @error('ielts_pte_other_languages')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="scholarships" class="form-label">Scholarships</label>
                <input type="text" id="scholarships" name="scholarships" value="{{ old('scholarships') }}" class="form-control @error('scholarships') is-invalid @enderror">
                @error('scholarships')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="course_link" class="form-label">Course Link</label>
                <input type="url" id="course_link" name="course_link" value="{{ old('course_link') }}" class="form-control @error('course_link') is-invalid @enderror">
                @error('course_link')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route($role === 'staff' ? 'staff.courses' : $role . '.courses.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-success">Add Course</button>
        </div>
    </form>
</div>
@endsection
