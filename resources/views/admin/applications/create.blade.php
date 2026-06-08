@extends('layouts.admin')

@section('admin-content')
    <style>
        .upload-area {
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
            border-color: #dee2e6 !important;
        }

        .upload-area:hover {
            background: #f1f3f5;
            border-color: #0d6efd !important;
        }

        .upload-area.drag-over {
            background: #e3f2fd;
            border-color: #0d6efd !important;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .form-select-lg,
        .form-select {
            padding: 0.75rem 1rem;
        }

        .preview-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .preview-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
        }

        .preview-value {
            font-size: 14px;
            font-weight: 500;
            color: #212529;
            word-break: break-word;
        }

        /* Button Styles */
        .btn-submit {
            transition: all 0.3s ease;
            min-width: 180px;
        }

        .btn-submit.btn-incomplete {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        .btn-submit.btn-incomplete:hover {
            background-color: #bb2d3b;
            border-color: #bb2d3b;
            transform: translateY(-2px);
        }

        .btn-submit.btn-ready {
            background-color: #198754;
            border-color: #198754;
            color: white;
        }

        .btn-submit.btn-ready:hover {
            background-color: #157347;
            border-color: #146c43;
            transform: translateY(-2px);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-submit i {
            margin-right: 8px;
        }

        /* Form Actions Container */
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        @media (max-width: 576px) {
            .form-actions {
                flex-direction: column-reverse;
            }

            .form-actions .btn {
                width: 100%;
            }
        }
    </style>
    <div>
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.applications.index') }}">Applications</a></li>
                        <li class="breadcrumb-item active">Create Application</li>
                    </ol>
                </nav>
                <h1 class="display-6 fw-bold mb-0">➕ Create New Application</h1>
                <p class="text-muted mt-2">Fill in the details below to submit a new student application</p>
            </div>
            <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i>Back to Applications
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body p-4 p-md-5">
                        <form action="{{ route('admin.applications.store') }}" method="POST" enctype="multipart/form-data"
                            id="applicationForm">
                            @csrf

                            {{-- Student Selection --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-user-graduate me-2 text-primary"></i>
                                    Student <span class="text-danger">*</span>
                                </label>

                                @if (isset($student))
                                    <div class="alert alert-success rounded-3">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Creating application for: <strong>{{ $student->first_name }}
                                            {{ $student->last_name }}</strong>
                                    </div>
                                    <input type="hidden" name="student_id" id="student_id_hidden"
                                        value="{{ $student->id }}">
                                    <input type="hidden" name="student_name" id="student_name_hidden"
                                        value="{{ $student->first_name }} {{ $student->last_name }}">
                                @else
                                    <select name="student_id" id="student_select"
                                        class="form-select form-select-lg rounded-3 @error('student_id') is-invalid @enderror"
                                        required>
                                        <option value="">-- Select Student --</option>
                                        @forelse($students as $s)
                                            <option value="{{ $s->id }}"
                                                data-name="{{ $s->first_name }} {{ $s->last_name }}"
                                                {{ old('student_id') == $s->id ? 'selected' : '' }}>
                                                {{ $s->first_name }} {{ $s->last_name }} -
                                                {{ $s->email }}
                                                @if ($s->agent)
                                                    (Agent: {{ $s->agent->business_name ?? $s->agent->username }})
                                                @endif
                                            </option>
                                        @empty
                                            <option value="" disabled>No students available. Please add students
                                                first.</option>
                                        @endforelse
                                    </select>
                                    @error('student_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if ($students && $students->count() == 0)
                                        <div class="alert alert-warning mt-2">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            No students found. <a href="{{ route('admin.students.create') }}">Create a
                                                student first</a>
                                        </div>
                                    @endif
                                @endif
                            </div>

                            {{-- University Selection --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-university me-2 text-primary"></i>
                                    University <span class="text-danger">*</span>
                                </label>
                                <select name="university_id"
                                    class="form-select form-select-lg rounded-3 @error('university_id') is-invalid @enderror"
                                    id="university_select" required>
                                    <option value="">-- Select University --</option>
                                    @foreach ($universities as $uni)
                                        <option value="{{ $uni->id }}" data-city="{{ $uni->city }}"
                                            data-country="{{ $uni->country }}"
                                            {{ old('university_id') == $uni->id ? 'selected' : '' }}>
                                            {{ $uni->name }} - {{ $uni->city }}, {{ $uni->country }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('university_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Course Selection --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-book-open me-2 text-primary"></i>
                                    Course <span class="text-danger">*</span>
                                </label>
                                <select name="course_id"
                                    class="form-select form-select-lg rounded-3 @error('course_id') is-invalid @enderror"
                                    id="course_select" required disabled>
                                    <option value="">-- First select a university --</option>
                                </select>
                                @error('course_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- SOP Upload --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-file-alt me-2 text-primary"></i>
                                    Statement of Purpose (SOP) <span class="text-danger">*</span>
                                </label>
                                <div class="upload-area border-2 border-dashed rounded-4 p-4 text-center" id="uploadArea">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                    <p class="mb-2">Drag & drop your SOP file here or <strong
                                            class="text-primary cursor-pointer">click to browse</strong></p>
                                    <small class="text-muted">Supported formats: PDF, DOC, DOCX (Max 10MB)</small>
                                    <input type="file" name="sop_file" id="sop_file" class="d-none"
                                        accept=".pdf,.doc,.docx" required>
                                    <div id="fileInfo" class="mt-3"></div>
                                </div>
                                @error('sop_file')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Form Actions --}}
                            <div class="mt-5 pt-3 border-top">
                                <div class="form-actions">
                                    <a href="{{ route('admin.applications.index') }}"
                                        class="btn btn-light rounded-pill px-4 py-2">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-submit rounded-pill px-5 py-2 btn-incomplete"
                                        id="submitBtn" disabled>
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
                {{-- Info Card --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-info-circle me-2 text-primary"></i>
                            Important Information
                        </h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Ensure all details are correct
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                SOP must be in PDF or DOC format
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Maximum file size: 10MB
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Double-check university and course selection
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Selected Preview Card --}}
                <div class="card mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-eye me-2 text-primary"></i>
                            Live Preview
                        </h5>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get elements
            const uniSelect = document.getElementById('university_select');
            const courseSelect = document.getElementById('course_select');
            const studentSelect = document.getElementById('student_select');
            const studentIdHidden = document.getElementById('student_id_hidden');
            const studentNameHidden = document.getElementById('student_name_hidden');
            const uploadArea = document.getElementById('uploadArea');
            const sopFile = document.getElementById('sop_file');
            const fileInfo = document.getElementById('fileInfo');
            const submitBtn = document.getElementById('submitBtn');
            const submitBtnText = document.getElementById('submitBtnText');
            const form = document.getElementById('applicationForm');
            const livePreview = document.getElementById('livePreview');

            // Function to check if all fields are completed
            function checkFormCompletion() {
                let isComplete = true;

                // Check student selection
                const studentId = studentSelect ? studentSelect.value : (studentIdHidden ? studentIdHidden.value :
                    null);
                if (!studentId) {
                    isComplete = false;
                }

                // Check university selection
                const uniId = uniSelect ? uniSelect.value : null;
                if (!uniId) {
                    isComplete = false;
                }

                // Check course selection
                const courseId = courseSelect ? courseSelect.value : null;
                if (!courseId) {
                    isComplete = false;
                }

                // Check SOP file
                const sopFileValue = sopFile ? sopFile.value : null;
                if (!sopFileValue) {
                    isComplete = false;
                }

                // Update button state
                if (isComplete) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('btn-incomplete');
                    submitBtn.classList.add('btn-ready');
                    submitBtnText.innerHTML = 'Submit Application';
                    const icon = submitBtn.querySelector('i');
                    if (icon) {
                        icon.className = 'fas fa-check-circle me-2';
                    }
                } else {
                    submitBtn.disabled = true;
                    submitBtn.classList.remove('btn-ready');
                    submitBtn.classList.add('btn-incomplete');
                    submitBtnText.innerHTML = 'Incomplete';
                    const icon = submitBtn.querySelector('i');
                    if (icon) {
                        icon.className = 'fas fa-exclamation-triangle me-2';
                    }
                }

                return isComplete;
            }

            // Load courses if university is pre-selected (for validation errors)
            if (uniSelect && uniSelect.value) {
                loadCourses(uniSelect.value);
            }

            // University change event
            if (uniSelect) {
                uniSelect.addEventListener('change', function() {
                    const uniId = this.value;
                    loadCourses(uniId);
                    updatePreview();
                    checkFormCompletion();
                });
            }

            // Student change event
            if (studentSelect) {
                studentSelect.addEventListener('change', function() {
                    updatePreview();
                    checkFormCompletion();
                });
            }

            // Course change event
            if (courseSelect) {
                courseSelect.addEventListener('change', function() {
                    updatePreview();
                    checkFormCompletion();
                });
            }

            // Function to load courses
            function loadCourses(uniId) {
                if (!courseSelect) return;

                courseSelect.innerHTML = '<option value="">Loading courses...</option>';
                courseSelect.disabled = true;

                if (!uniId) {
                    courseSelect.innerHTML = '<option value="">-- First select a university --</option>';
                    courseSelect.disabled = true;
                    updatePreview();
                    checkFormCompletion();
                    return;
                }

                // Make AJAX request
                fetch(`/admin/applications/get-courses/${uniId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        let options = '<option value="">-- Select Course --</option>';

                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(course => {
                                const selected = ({{ old('course_id', 'null') }} == course.id) ?
                                    'selected' : '';
                                options +=
                                    `<option value="${course.id}" ${selected}>${course.title}</option>`;
                            });
                            courseSelect.disabled = false;
                        } else {
                            options = '<option value="">No courses available for this university</option>';
                            courseSelect.disabled = true;
                        }

                        courseSelect.innerHTML = options;
                        updatePreview();
                        checkFormCompletion();
                    })
                    .catch(error => {
                        console.error('Error loading courses:', error);
                        courseSelect.innerHTML =
                            '<option value="">Error loading courses. Please try again.</option>';
                        courseSelect.disabled = true;
                        updatePreview();
                        checkFormCompletion();
                    });
            }

            // Function to get selected student name
            function getSelectedStudentName() {
                // Check if we're in "passed student" mode (from controller)
                if (studentNameHidden && studentNameHidden.value) {
                    return studentNameHidden.value;
                }

                // Check if we're in normal select mode
                if (studentSelect && studentSelect.value) {
                    const selectedOption = studentSelect.options[studentSelect.selectedIndex];
                    // Get name from data-name attribute or parse from text
                    const dataName = selectedOption.getAttribute('data-name');
                    if (dataName) {
                        return dataName;
                    }
                    // Fallback: extract name from option text (before the dash)
                    const optionText = selectedOption.text;
                    return optionText.split(' -')[0];
                }

                return '';
            }

            // Function to get selected university name
            function getSelectedUniversityName() {
                if (uniSelect && uniSelect.value) {
                    const selectedOption = uniSelect.options[uniSelect.selectedIndex];
                    const optionText = selectedOption.text;
                    return optionText.split(' -')[0];
                }
                return '';
            }

            // Function to get selected course name
            function getSelectedCourseName() {
                if (courseSelect && courseSelect.value && courseSelect.options[courseSelect.selectedIndex]) {
                    return courseSelect.options[courseSelect.selectedIndex].text;
                }
                return '';
            }

            // Update live preview
            function updatePreview() {
                if (!livePreview) return;

                const studentName = getSelectedStudentName();
                const universityName = getSelectedUniversityName();
                const courseName = getSelectedCourseName();

                if (studentName || universityName || courseName) {
                    let previewHtml = '';

                    if (studentName) {
                        previewHtml += `
                            <div class="preview-item">
                                <div class="preview-label">Student</div>
                                <div class="preview-value">${studentName}</div>
                            </div>
                        `;
                    }

                    if (universityName) {
                        previewHtml += `
                            <div class="preview-item">
                                <div class="preview-label">University</div>
                                <div class="preview-value">${universityName}</div>
                            </div>
                        `;
                    }

                    if (courseName) {
                        previewHtml += `
                            <div class="preview-item">
                                <div class="preview-label">Course</div>
                                <div class="preview-value">${courseName}</div>
                            </div>
                        `;
                    }

                    // Show missing selections warning
                    if (!studentName) {
                        previewHtml += `
                            <div class="preview-item bg-warning bg-opacity-10">
                                <div class="preview-label text-warning">⚠ Missing</div>
                                <div class="preview-value">Student not selected</div>
                            </div>
                        `;
                    }

                    if (!universityName) {
                        previewHtml += `
                            <div class="preview-item bg-warning bg-opacity-10">
                                <div class="preview-label text-warning">⚠ Missing</div>
                                <div class="preview-value">University not selected</div>
                            </div>
                        `;
                    }

                    if (!courseName) {
                        previewHtml += `
                            <div class="preview-item bg-warning bg-opacity-10">
                                <div class="preview-label text-warning">⚠ Missing</div>
                                <div class="preview-value">Course not selected</div>
                            </div>
                        `;
                    }

                    // Add status
                    if (studentName && universityName && courseName) {
                        previewHtml += `
                            <div class="alert alert-success mt-3 mb-0">
                                <i class="fas fa-check-circle me-2"></i>✓ Ready to submit
                            </div>
                        `;
                    } else {
                        previewHtml += `
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>⚠ Please complete all selections
                            </div>
                        `;
                    }

                    livePreview.innerHTML = previewHtml;
                } else {
                    livePreview.innerHTML = `
                        <div class="text-muted text-center py-3">
                            <i class="fas fa-arrow-up fa-2x mb-2 d-block"></i>
                            Select student, university, and course to see preview
                        </div>
                    `;
                }
            }

            // File upload area click handler
            if (uploadArea && sopFile) {
                uploadArea.addEventListener('click', function(e) {
                    if (e.target !== sopFile) {
                        sopFile.click();
                    }
                });

                // Drag and drop handlers
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    uploadArea.addEventListener(eventName, preventDefaults, false);
                });

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                ['dragenter', 'dragover'].forEach(eventName => {
                    uploadArea.addEventListener(eventName, highlight, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    uploadArea.addEventListener(eventName, unhighlight, false);
                });

                function highlight() {
                    uploadArea.classList.add('drag-over');
                }

                function unhighlight() {
                    uploadArea.classList.remove('drag-over');
                }

                uploadArea.addEventListener('drop', handleDrop, false);

                function handleDrop(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    sopFile.files = files;
                    handleFileSelect(files[0]);
                }

                // File selection handler
                sopFile.addEventListener('change', function(e) {
                    handleFileSelect(e.target.files[0]);
                    checkFormCompletion();
                });

                function handleFileSelect(file) {
                    if (!file) {
                        fileInfo.innerHTML = '';
                        checkFormCompletion();
                        return;
                    }

                    const validTypes = ['application/pdf', 'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ];
                    const fileSize = file.size / 1024 / 1024;

                    if (!validTypes.includes(file.type)) {
                        fileInfo.innerHTML =
                            '<div class="alert alert-danger">Please upload PDF or DOC/DOCX file only.</div>';
                        sopFile.value = '';
                        checkFormCompletion();
                        return;
                    }

                    if (fileSize > 10) {
                        fileInfo.innerHTML =
                            '<div class="alert alert-danger">File size must be less than 10MB.</div>';
                        sopFile.value = '';
                        checkFormCompletion();
                        return;
                    }

                    fileInfo.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Selected: ${file.name} (${fileSize.toFixed(2)} MB)
                        </div>
                    `;
                    checkFormCompletion();
                }
            }

            // Form validation before submit
            if (form) {
                form.addEventListener('submit', function(e) {
                    let isValid = true;
                    let errorMessage = '';

                    const studentId = document.querySelector('select[name="student_id"]')?.value || document
                        .querySelector('input[name="student_id"]')?.value;
                    const uniId = uniSelect?.value;
                    const courseId = courseSelect?.value;
                    const sopFileValue = sopFile?.value;

                    if (!studentId) {
                        errorMessage = 'Please select a student';
                        isValid = false;
                    } else if (!uniId) {
                        errorMessage = 'Please select a university';
                        isValid = false;
                    } else if (!courseId) {
                        errorMessage = 'Please select a course';
                        isValid = false;
                    } else if (!sopFileValue) {
                        errorMessage = 'Please upload the SOP file';
                        isValid = false;
                    }

                    if (!isValid) {
                        e.preventDefault();
                        alert(errorMessage);
                        return false;
                    }

                    // Disable submit button to prevent double submission
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
                    }
                });
            }

            // Initial preview update and button state
            updatePreview();
            checkFormCompletion();
        });
    </script>
@endsection
