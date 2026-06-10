@php
    $__user = auth()->user();
    $__isAgent = $__user->is_agent;
    $__isStaff = $__user->is_staff && !$__user->is_admin_staff;
    $__layout = $__isAgent ? 'layouts.agent' : ($__isStaff ? 'layouts.staff' : 'layouts.admin');
    $__section = $__isAgent ? 'agent-content' : ($__isStaff ? 'staff-content' : 'admin-content');
    $role = $__user->role;
@endphp

@extends($__layout)

@section('title', 'Create Application')
@section('page-title', 'Create Application')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
@endpush

@section($__section)
<div class="container-fluid px-3 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route($role . '.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route($role . '.applications.index') }}" class="text-decoration-none">Applications</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0" style="color:#1f2937;"><i class="fas fa-plus-circle text-primary me-2"></i>New Application</h4>
        </div>
        <a href="{{ route($role . '.applications.index') }}" class="btn btn-outline-secondary" style="font-size:0.82rem;padding:8px 20px;border-radius:10px;">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <form action="{{ route($role . '.applications.store') }}" method="POST" enctype="multipart/form-data" id="applicationForm">
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="app-form-section">
                    <div class="app-form-section-header" style="border-left-color:#6366f1;"><i class="fas fa-user-graduate" style="color:#6366f1;"></i>Student</div>
                    <div class="app-form-section-body">
                        @if (isset($student))
                            <div class="p-3 rounded-3" style="background:#eef2ff;border:1px solid #c7d2fe;font-size:0.82rem;">
                                <i class="fas fa-check-circle text-primary me-2"></i>
                                Creating application for: <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>
                            </div>
                            <input type="hidden" name="student_id" value="{{ $student->id }}">
                        @else
                            <label class="app-form-label">Select Student <span class="text-danger">*</span></label>
                            <select name="student_id" id="student_select" class="app-form-control form-select @error('student_id') is-invalid @enderror" required>
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
                </div>

                <div class="app-form-section">
                    <div class="app-form-section-header" style="border-left-color:#8b5cf6;"><i class="fas fa-university" style="color:#8b5cf6;"></i>University &amp; Course</div>
                    <div class="app-form-section-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="app-form-label">University <span class="text-danger">*</span></label>
                                <select name="university_id" class="app-form-control form-select @error('university_id') is-invalid @enderror" id="university_select" required>
                                    <option value="">-- Select University --</option>
                                    @foreach ($universities as $uni)
                                        <option value="{{ $uni->id }}" {{ old('university_id', $selectedUniversityId ?? '') == $uni->id ? 'selected' : '' }}>
                                            {{ $uni->name }} - {{ $uni->city }}, {{ $uni->country }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('university_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="app-form-label">Course <span class="text-danger">*</span></label>
                                <select name="course_id" class="app-form-control form-select @error('course_id') is-invalid @enderror" id="course_select" {{ empty($selectedUniversityId) ? 'disabled' : '' }} required>
                                    <option value="">-- {{ empty($selectedUniversityId) ? 'Select university first' : 'Select Course' }} --</option>
                                </select>
                                @error('course_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="app-form-section">
                    <div class="app-form-section-header" style="border-left-color:#f59e0b;"><i class="fas fa-file-alt" style="color:#f59e0b;"></i>Statement of Purpose (SOP)</div>
                    <div class="app-form-section-body">
                        <label class="app-form-label">Upload SOP <span class="text-danger">*</span></label>
                        <div class="p-4 text-center rounded-3" id="uploadArea" style="background:#f8faff;border:2px dashed #d1d5db;cursor:pointer;transition:border-color 0.2s,background 0.2s;" onmouseover="this.style.borderColor='var(--primary)';this.style.background='#eef2ff'" onmouseout="this.style.borderColor='#d1d5db';this.style.background='#f8faff'">
                            <i class="fas fa-cloud-upload-alt fa-3x mb-3" style="color:var(--primary);"></i>
                            <p class="mb-1" style="font-size:0.85rem;">Drag &amp; drop your SOP file here or <strong style="color:var(--primary);">click to browse</strong></p>
                            <small class="text-muted">PDF, DOC, DOCX (Max 10MB)</small>
                            <input type="file" name="sop_file" id="sop_file" class="d-none" accept=".pdf,.doc,.docx" required>
                            <div id="fileInfo" class="mt-3"></div>
                        </div>
                        @error('sop_file')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route($role . '.applications.index') }}" class="btn btn-outline-danger" style="font-size:0.82rem;padding:8px 24px;border-radius:10px;"><i class="fas fa-times me-1"></i>Cancel</a>
                    <button type="submit" class="btn" id="submitBtn" disabled style="font-size:0.82rem;padding:8px 28px;border-radius:10px;background:#e5e7eb;color:#9ca3af;border:none;">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <span id="submitBtnText">Incomplete</span>
                    </button>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="app-show-card">
                    <div class="app-show-card-header"><i class="fas fa-info-circle" style="color:#3b82f6;"></i>Important</div>
                    <div class="app-show-card-body">
                        <ul class="list-unstyled mb-0" style="font-size:0.78rem;">
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Verify all details before submitting</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>SOP must be PDF, DOC, or DOCX</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Max file size: 10MB</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Double-check university &amp; course</li>
                        </ul>
                    </div>
                </div>
                <div class="app-show-card">
                    <div class="app-show-card-header"><i class="fas fa-eye" style="color:#3b82f6;"></i>Live Preview</div>
                    <div class="app-show-card-body">
                        <div id="livePreview">
                            <div class="text-muted text-center py-3" style="font-size:0.78rem;">
                                <i class="fas fa-arrow-up fa-2x mb-2 d-block"></i>
                                Select student, university, and course to see preview
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
            submitBtn.style.background = 'var(--primary)';
            submitBtn.style.color = '#fff';
            submitBtnText.innerHTML = 'Submit Application';
            submitBtn.querySelector('i').className = 'fas fa-check-circle me-1';
        } else {
            submitBtn.disabled = true;
            submitBtn.style.background = '#e5e7eb';
            submitBtn.style.color = '#9ca3af';
            submitBtnText.innerHTML = 'Incomplete';
            submitBtn.querySelector('i').className = 'fas fa-exclamation-triangle me-1';
        }
    }

    function loadCourses(uniId) {
        if (!courseSelect) return;
        if (!uniId) {
            courseSelect.innerHTML = '<option value="">-- Select university first --</option>';
            courseSelect.disabled = true;
            updatePreview(); checkFormCompletion();
            return;
        }
        courseSelect.innerHTML = '<option value="">Loading...</option>';
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
            if (sName) h += `<div class="mb-2"><small class="text-muted">Student</small><div class="fw-semibold" style="font-size:0.85rem;">${sName}</div></div>`;
            if (uName) h += `<div class="mb-2"><small class="text-muted">University</small><div class="fw-semibold" style="font-size:0.85rem;">${uName}</div></div>`;
            if (cName) h += `<div class="mb-2"><small class="text-muted">Course</small><div class="fw-semibold" style="font-size:0.85rem;">${cName}</div></div>`;
            if (sName && uName && cName) h += `<div class="text-success mt-2" style="font-size:0.78rem;"><i class="fas fa-check-circle me-1"></i>All set — upload SOP and submit</div>`;
            livePreview.innerHTML = h;
        } else {
            livePreview.innerHTML = '<div class="text-muted text-center py-3" style="font-size:0.78rem;"><i class="fas fa-arrow-up fa-2x mb-2 d-block"></i>Select student, university, and course to see preview</div>';
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
            fileInfo.innerHTML = '<div class="alert alert-danger py-2" style="font-size:0.78rem;">Invalid file. Use PDF/DOC/DOCX under 10MB.</div>';
            sopFile.value = '';
        } else {
            fileInfo.innerHTML = `<div class="alert alert-success py-2" style="font-size:0.78rem;"><i class="fas fa-check-circle me-1"></i>${file.name} (${(file.size/1024/1024).toFixed(2)} MB)</div>`;
        }
        checkFormCompletion();
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            const sid = studentSelect ? studentSelect.value : (studentIdHidden?.value || '');
            if (!sid || !uniSelect?.value || !courseSelect?.value || !sopFile?.value) {
                e.preventDefault(); alert('Please complete all required fields.');
            } else {
                submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';
            }
        });
    }

    updatePreview(); checkFormCompletion();
});
</script>
@endpush