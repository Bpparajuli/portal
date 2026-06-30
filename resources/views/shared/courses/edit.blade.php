@php
    $__user = auth()->user();
    $__isAgent = $__user->is_agent;
    $__isStaff = $__user->is_staff && !$__user->is_admin_staff;
    $__layout = $__isAgent ? 'layouts.agent' : ($__isStaff ? 'layouts.staff' : 'layouts.admin');
    $__section = $__isAgent ? 'agent-content' : ($__isStaff ? 'staff-content' : 'admin-content');
    $role = $__user->role;
@endphp

@extends($__layout)

@section('title', 'Edit Course')
@section('page-title', 'Edit Course')

@section($__section)
    <div class="p-4">

        {{-- HEADER --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                @if ($previous)
                    <a href="{{ route($role . '.courses.edit', $previous->id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                @endif
            </div>
            <div class="text-center">
                <h2 class="fw-bold mb-0">Edit Course :</h2>
                <h3 class="fw-bold text-primary mb-0">{{ $course->title }} ({{ $course->id }})</h3>
            </div>
            <div>
                @if ($next)
                    <a href="{{ route($role . '.courses.edit', $next->id) }}" class="btn btn-outline-primary">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                @endif
            </div>
        </div>

        {{-- FORM --}}
        <form action="{{ route($role . '.courses.update', $course->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- UNIVERSITY --}}
            <div class="mb-4">
                <label class="form-label fw-semibold">University <span class="text-danger">*</span></label>
                @if ($course->university_id)
                    <input type="text" class="form-control"
                        value="{{ $course->university->name }} - {{ $course->university->city }}" readonly>
                    <input type="hidden" name="university_id" value="{{ $course->university_id }}">
                @else
                    <select name="university_id" class="form-select @error('university_id') is-invalid @enderror" required>
                        <option value="">-- Select University --</option>
                        @foreach ($universities as $uni)
                            <option value="{{ $uni->id }}"
                                {{ old('university_id', $course->university_id) == $uni->id ? 'selected' : '' }}>
                                {{ $uni->name }} - {{ $uni->city }}</option>
                        @endforeach
                    </select>
                    @error('university_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                @endif
            </div>

            {{-- TITLE & CODE --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $course->title) }}" class="form-control"
                        required>
                    @error('title')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Course Code <span class="text-danger">*</span></label>
                    <input type="text" name="course_code" value="{{ old('course_code', $course->course_code) }}"
                        class="form-control" required>
                    @error('course_code')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            {{-- DURATION / FEE / INTAKES --}}
            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Duration</label>
                    <input type="text" name="duration" value="{{ old('duration', $course->duration) }}"
                        class="form-control" placeholder="e.g. 3 years">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Fee</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="text" name="fee" value="{{ old('fee', $course->fee) }}" class="form-control"
                            placeholder="25,000">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Intakes <span class="text-danger">*</span></label>
                    <input type="text" name="intakes" value="{{ old('intakes', $course->intakes) }}"
                        class="form-control" required placeholder="e.g. Jan, May, Sep">
                    @error('intakes')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            {{-- COURSE TYPE / MOI --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Course Type</label>
                    <select name="course_type" class="form-select">
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
                <div class="col-md-6">
                    <label class="form-label fw-semibold">MOI Acceptance</label>
                    <input type="text" name="moi_acceptance" class="form-control"
                        value="{{ old('moi_acceptance', $course->moi_acceptance) }}" placeholder="e.g. Accepted">
                    @error('moi_acceptance')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            {{-- COURSE LINK / ACADEMIC REQUIREMENT --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Course Link</label>
                    <input type="text" name="course_link" class="form-control"
                        value="{{ old('course_link', $course->course_link) }}" placeholder="https://">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Academic Requirement</label>
                    <input type="text" name="academic_requirement" class="form-control"
                        value="{{ old('academic_requirement', $course->academic_requirement) }}"
                        placeholder="e.g. 60% in 12th">
                </div>
            </div>

            {{-- IELTS / APPLICATION FEE / SCHOLARSHIPS --}}
            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">IELTS / PTE / Other</label>
                    <input type="text" name="ielts_pte_other_languages"
                        value="{{ old('ielts_pte_other_languages', $course->ielts_pte_other_languages) }}"
                        class="form-control" placeholder="e.g. IELTS 6.0">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Application Fee</label>
                    <input type="text" name="application_fee"
                        value="{{ old('application_fee', $course->application_fee) }}" class="form-control"
                        placeholder="$150">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Scholarships</label>
                    <input type="text" name="scholarships" value="{{ old('scholarships', $course->scholarships) }}"
                        class="form-control" placeholder="e.g. Up to 30%">
                </div>
            </div>

            {{-- DESCRIPTION --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Course description...">{{ old('description', $course->description) }}</textarea>
                @error('description')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- STATUS TOGGLES --}}
            <div class="mb-4 status-box">
                <div class="d-flex gap-5">
                    <div class="form-check form-switch mb-0">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input"
                            {{ old('is_active', $course->is_active ?? true) ? 'checked' : '' }}>
                        <label for="is_active" class="form-check-label fw-semibold">Active</label>
                        <div class="text-muted small">Visible on website and listings</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" id="is_featured" name="is_featured" value="1"
                            class="form-check-input"
                            {{ old('is_featured', $course->is_featured ?? false) ? 'checked' : '' }}>
                        <label for="is_featured" class="form-check-label fw-semibold">Featured</label>
                        <div class="text-muted small">Highlighted as featured course</div>
                    </div>
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="d-flex justify-content-between align-items-center bottom-bar">
                <a href="{{ route($role === 'staff' ? 'staff.courses' : $role . '.courses.index') }}"
                    class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
                <div class="d-flex gap-2">
                    @can('delete', $course)
                        <x-confirm-delete action="{{ $role }}.courses.destroy" :id="$course->id"
                            label="Delete Course" title="Delete {{ $course->course_code }} - {{ $course->title }}?"
                            message="This action cannot be undone." class="btn btn-outline-danger" />
                    @endcan
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-save me-1"></i> Update Course
                    </button>
                </div>
            </div>
        </form>

    </div>

    <style>
        .form-control,
        .form-select {
            padding: 0.7rem 0.9rem;
            border: 1.5px solid #d1d5db;
            border-radius: var(--radius-sm);
            transition: all 0.25s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            font-size: 0.9rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(130, 11, 92, 0.12), 0 1px 3px rgba(0, 0, 0, 0.05);
            transform: translateY(-1px);
        }

        .form-control:hover,
        .form-select:hover {
            border-color: #9ca3af;
        }

        .form-label {
            font-size: 0.8rem;
            letter-spacing: 0.02em;
            color: var(--gray-700);
            margin-bottom: 0.3rem;
        }

        .input-group-text {
            background: #f3f4f6;
            border: 1.5px solid #d1d5db;
            color: var(--text-muted);
            font-weight: 600;
            padding: 0.15rem 0.25rem;
        }

        .input-group .form-control {
            border-left: none;
            padding-left: 0.5rem;
        }

        .input-group .form-control:focus {
            border-left: none;
        }

        textarea.form-control {
            line-height: 1.6;
        }

        .status-box {
            background: var(--bg-card);
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 1rem 1.25rem;
            transition: all 0.2s ease;
        }

        .status-box:hover {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(130, 11, 92, 0.06);
        }

        .bottom-bar {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 1rem 1.25rem;
        }
    </style>
@endsection
