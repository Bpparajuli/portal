@extends('layouts.app')
@php $role = auth()->user()->role; @endphp

@section('title', 'Create Application')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route($role . '.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route($role . '.applications.index') }}">Applications</a></li>
                    <li class="breadcrumb-item active">Create Application</li>
                </ol>
            </nav>
            <h1 class="display-6 fw-bold mb-0">➕ Create New Application</h1>
            <p class="text-muted mt-2">Fill in the details below to submit a new student application</p>
        </div>
        <a href="{{ route($role . '.applications.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Back to Applications
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route($role . '.applications.store') }}" method="POST" enctype="multipart/form-data" id="applicationForm">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-semibold"><i class="fas fa-user-graduate me-2 text-primary"></i>Student <span class="text-danger">*</span></label>
                            @if (isset($student))
                                <div class="alert alert-success rounded-3">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Creating application for: <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>
                                </div>
                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                            @else
                                <select name="student_id" id="student_select" class="form-select form-select-lg rounded-3 @error('student_id') is-invalid @enderror" required>
                                    <option value="">-- Select Student --</option>
                                    @forelse($students as $s)
                                        <option value="{{ $s->id }}" {{ old('student_id', $selectedStudent->id ?? '') == $s->id ? 'selected' : '' }}>
                                            {{ $s->first_name }} {{ $s->last_name }}@if ($s->preferred_country) - {{ $s->preferred_country }}@endif
                                        </option>
                                    @empty
                                        <option value="" disabled>No students with complete documents available.</option>
                                    @endforelse
                                </select>
                                @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @endif
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold"><i class="fas fa-university me-2 text-primary"></i>University <span class="text-danger">*</span></label>
                            <select name="university_id" class="form-select form-select-lg rounded-3 @error('university_id') is-invalid @enderror" id="university_select" required>
                                <option value="">-- Select University --</option>
                                @foreach ($universities as $uni)
                                    <option value="{{ $uni->id }}" {{ old('university_id', $selectedUniversityId ?? '') == $uni->id ? 'selected' : '' }}>
                                        {{ $uni->name }} - {{ $uni->city }}, {{ $uni->country }}
                                    </option>
                                @endforeach
                            </select>
                            @error('university_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold"><i class="fas fa-book-open me-2 text-primary"></i>Course <span class="text-danger">*</span></label>
                            <select name="course_id" class="form-select form-select-lg rounded-3 @error('course_id') is-invalid @enderror" id="course_select" {{ empty($selectedUniversityId) ? 'disabled' : '' }} required>
                                <option value="">-- {{ empty($selectedUniversityId) ? 'First select a university' : 'Select Course' }} --</option>
                                @if (!empty($selectedUniversityId))
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id', $selectedCourseId ?? '') == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('course_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold"><i class="fas fa-file-alt me-2 text-primary"></i>Statement of Purpose (SOP) <span class="text-danger">*</span></label>
                            <div class="bg-light rounded-4 p-4 text-center" id="uploadArea">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                <p class="mb-2">Drag & drop your SOP file here or <strong class="text-primary cursor-pointer">click to browse</strong></p>
                                <small class="text-muted">Supported formats: PDF, DOC, DOCX (Max 10MB)</small>
                                <input type="file" name="sop_file" id="sop_file" class="d-none" accept=".pdf,.doc,.docx" required>
                                <div id="fileInfo" class="mt-3"></div>
                            </div>
                            @error('sop_file')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        </div>

                        <div class="mt-5 pt-3 border-top">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route($role . '.applications.index') }}" class="btn btn-outline-danger rounded-pill px-4 py-2"><i class="fas fa-times me-2"></i>Cancel</a>
                                <button type="submit" class="btn rounded px-5 py-2 btn-light" id="submitBtn" disabled>
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <span id="submitBtnText">Incomplete</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Important Information</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Ensure all details are correct</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>SOP must be in PDF or DOC format</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Maximum file size: 10MB</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Double-check university and course selection</li>
                    </ul>
                </div>
            </div>
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-eye me-2 text-primary"></i>Live Preview</h5>
                    <div id="livePreview">
                        <div class="text-muted text-center py-3">
                            <i class="fas fa-arrow-up fa-2x mb-2 d-block"></i>
                            Select student, university, and course to see preview
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const uniSelect = document.getElementById('university_select');
    const courseSelect = document.getElementById('course_select');
    const studentSelect = document.getElementById('student_select');
    const studentIdHidden = document.querySelector('input[name="student_id"]');
    const uploadArea = document.getElementById('uploadArea');
    const sopFile = document.getElementById('sop_file');
    const fileInfo = document.getElementById('fileInfo');
    const submitBtn = document.getElementById('submitBtn');
    const submitBtnText = document.getElementById('submitBtnText');
    const form = document.getElementById('applicationForm');
    const livePreview = document.getElementById('livePreview');
    const getCoursesUrl = '{{ route($role . '.applications.get-courses', ['universityId' => '__ID__']) }}';

    function checkFormCompletion() {
        const studentId = studentSelect ? studentSelect.value : (studentIdHidden ? studentIdHidden.value : null);
        const uniId = uniSelect?.value;
        const courseId = courseSelect?.value;
        const sopFileValue = sopFile?.value;
        const isComplete = !!(studentId && uniId && courseId && sopFileValue);

        if (isComplete) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-light');
            submitBtn.classList.add('btn-success');
            submitBtnText.innerHTML = 'Submit Application';
            submitBtn.querySelector('i').className = 'fas fa-check-circle me-2';
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.remove('btn-success');
            submitBtn.classList.add('btn-light');
            submitBtnText.innerHTML = 'Incomplete';
            submitBtn.querySelector('i').className = 'fas fa-exclamation-triangle me-2';
        }
    }

    function loadCourses(uniId) {
        if (!courseSelect) return;
        if (!uniId) {
            courseSelect.innerHTML = '<option value="">-- First select a university --</option>';
            courseSelect.disabled = true;
            updatePreview(); checkFormCompletion();
            return;
        }
        courseSelect.innerHTML = '<option value="">Loading courses...</option>';
        courseSelect.disabled = true;
        fetch(getCoursesUrl.replace('__ID__', uniId))
            .then(r => r.json())
            .then(data => {
                let options = '<option value="">-- Select Course --</option>';
                if (Array.isArray(data) && data.length) {
                    data.forEach(c => { options += `<option value="${c.id}">${c.title}</option>`; });
                    courseSelect.disabled = false;
                } else {
                    options = '<option value="">No courses available</option>';
                    courseSelect.disabled = true;
                }
                courseSelect.innerHTML = options;
                updatePreview(); checkFormCompletion();
            });
    }

    function getSelectedText(el) {
        return el && el.value && el.options[el.selectedIndex] ? el.options[el.selectedIndex].text : '';
    }

    function updatePreview() {
        if (!livePreview) return;
        const sName = studentSelect ? getSelectedText(studentSelect).split(' -')[0] : (studentIdHidden?.value || '');
        const uName = getSelectedText(uniSelect).split(' -')[0];
        const cName = getSelectedText(courseSelect);
        if (sName || uName || cName) {
            let h = '';
            if (sName) h += `<div class="mb-2"><small class="text-muted">Student</small><div class="fw-semibold">${sName}</div></div>`;
            if (uName) h += `<div class="mb-2"><small class="text-muted">University</small><div class="fw-semibold">${uName}</div></div>`;
            if (cName) h += `<div class="mb-2"><small class="text-muted">Course</small><div class="fw-semibold">${cName}</div></div>`;
            if (sName && uName && cName) h += `<div class="text-success mt-2"><i class="fas fa-check-circle me-1"></i>All set — upload SOP and submit</div>`;
            livePreview.innerHTML = h;
        } else {
            livePreview.innerHTML = '<div class="text-muted text-center py-3"><i class="fas fa-arrow-up fa-2x mb-2 d-block"></i>Select student, university, and course to see preview</div>';
        }
    }

    if (uniSelect) {
        if (uniSelect.value) loadCourses(uniSelect.value);
        uniSelect.addEventListener('change', function() { loadCourses(this.value); updatePreview(); checkFormCompletion(); });
    }
    if (studentSelect) studentSelect.addEventListener('change', function() { updatePreview(); checkFormCompletion(); });
    if (courseSelect) courseSelect.addEventListener('change', function() { updatePreview(); checkFormCompletion(); });

    if (uploadArea && sopFile) {
        uploadArea.addEventListener('click', function(e) { if (e.target !== sopFile) sopFile.click(); });
        ['dragenter','dragover','dragleave','drop'].forEach(ev => uploadArea.addEventListener(ev, e => { e.preventDefault(); e.stopPropagation(); }));
        uploadArea.addEventListener('drop', function(e) { sopFile.files = e.dataTransfer.files; handleFile(sopFile.files[0]); });
        sopFile.addEventListener('change', function() { handleFile(this.files[0]); });
    }

    function handleFile(file) {
        if (!file) { fileInfo.innerHTML = ''; checkFormCompletion(); return; }
        const valid = ['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!valid.includes(file.type) || file.size/1024/1024 > 10) {
            fileInfo.innerHTML = '<div class="alert alert-danger">Invalid file. Use PDF/DOC/DOCX under 10MB.</div>';
            sopFile.value = '';
        } else {
            fileInfo.innerHTML = `<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>${file.name} (${(file.size/1024/1024).toFixed(2)} MB)</div>`;
        }
        checkFormCompletion();
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            const sid = studentSelect ? studentSelect.value : (studentIdHidden?.value || '');
            if (!sid || !uniSelect?.value || !courseSelect?.value || !sopFile?.value) {
                e.preventDefault(); alert('Please complete all required fields.');
            } else {
                submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
            }
        });
    }

    updatePreview(); checkFormCompletion();
});
</script>
@endpush
