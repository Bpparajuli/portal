@extends('layouts.agent')

@section('agent-content')
    <style>
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

        .status-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
    </style>

    <div class="container py-4">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('agent.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('agent.applications.index') }}">Applications</a></li>
                        <li class="breadcrumb-item active">Edit Application</li>
                    </ol>
                </nav>
                <h1 class="display-6 fw-bold mb-0">✏️ Edit Application</h1>
                <p class="text-muted mt-2">Application #{{ $application->application_number }}</p>
            </div>
            <a href="{{ route('agent.applications.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i>Cancel
            </a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-5">
                        <form id="updateForm" action="{{ route('agent.applications.update', $application->id) }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            {{-- Student Info (Read Only) --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Student</label>
                                <input type="text" class="form-control bg-light rounded-3"
                                    value="{{ $application->student->first_name }} {{ $application->student->last_name }}"
                                    readonly>
                                <input type="hidden" name="student_id" value="{{ $application->student->id }}">
                                <input type="hidden" id="student_name"
                                    value="{{ $application->student->first_name }} {{ $application->student->last_name }}">
                            </div>

                            {{-- University Selection --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">University</label>
                                <select name="university_id" class="form-select rounded-3" id="university_select" required>
                                    <option value="">-- Select University --</option>
                                    @foreach ($universities as $uni)
                                        <option value="{{ $uni->id }}" data-name="{{ $uni->name }}"
                                            {{ $application->university_id == $uni->id ? 'selected' : '' }}>
                                            {{ $uni->name }} - {{ $uni->city }}, {{ $uni->country }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Course Selection --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Course</label>
                                <select name="course_id" class="form-select rounded-3" id="course_select" required>
                                    <option value="">-- Select Course --</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}" data-name="{{ $course->title }}"
                                            {{ $application->course_id == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- SOP Upload --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Upload New SOP (Optional)</label>
                                <input type="file" name="sop_file" class="form-control rounded-3" id="sop_file"
                                    accept=".pdf,.doc,.docx">
                                <small class="text-muted">Leave empty to keep current SOP</small>
                                <div id="fileInfo" class="mt-2"></div>
                            </div>

                            {{-- Current SOP --}}
                            @if ($application->sop_file)
                                <div class="mb-4 p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold">Current SOP</label>
                                    <div>
                                        <a href="{{ Storage::url($application->sop_file) }}" target="_blank"
                                            class="btn btn-sm btn-outline-primary rounded-pill">
                                            <i class="fas fa-eye me-1"></i>View Current SOP
                                        </a>
                                        <a href="{{ Storage::url($application->sop_file) }}" download
                                            class="btn btn-sm btn-outline-secondary rounded-pill">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                    </div>
                                </div>
                            @endif

                            {{-- Form Actions --}}
                            <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-warning rounded-pill px-4 withdraw-btn"
                                    data-id="{{ $application->id }}"
                                    data-name="{{ $application->student->first_name }} {{ $application->student->last_name }}">
                                    <i class="fas fa-times-circle me-2"></i>Withdraw Application
                                </button>
                                <button type="submit" class="btn btn-success rounded-pill px-4">
                                    <i class="fas fa-save me-2"></i>Update Application
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                {{-- Live Preview Card --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-eye me-2 text-primary"></i>
                            Live Preview
                        </h5>
                        <div id="livePreview">
                            <div class="preview-item">
                                <div class="preview-label">Student</div>
                                <div class="preview-value">{{ $application->student->first_name }}
                                    {{ $application->student->last_name }}</div>
                            </div>
                            <div class="preview-item">
                                <div class="preview-label">University</div>
                                <div class="preview-value">{{ $application->university->name ?? 'Not selected' }}</div>
                            </div>
                            <div class="preview-item">
                                <div class="preview-label">Course</div>
                                <div class="preview-value">{{ $application->course->title ?? 'Not selected' }}</div>
                            </div>
                            <div class="preview-item">
                                <div class="preview-label">Status</div>
                                <div class="preview-value">
                                    <span class="status-badge"
                                        style="background: {{ $application->status->bg_color ?? '#6c757d' }}20; color: {{ $application->status->bg_color ?? '#6c757d' }};">
                                        {{ $application->status->name ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="preview-item">
                                <div class="preview-label">SOP</div>
                                <div class="preview-value">
                                    @if ($application->sop_file)
                                        <span class="text-success">
                                            <i class="fas fa-check-circle me-1"></i>Uploaded
                                        </span>
                                    @else
                                        <span class="text-danger">
                                            <i class="fas fa-times-circle me-1"></i>Not uploaded
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if ($application->sop_file)
                                <div class="alert alert-info mt-2 mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Current SOP file is attached
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Info Card --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">ℹ️ Information</h5>
                        <div class="mb-3">
                            <small class="text-muted d-block">Agent</small>
                            <span class="fw-semibold">{{ $application->agent->business_name ?? 'N/A' }}</span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Created</small>
                            <span class="fw-semibold">{{ $application->created_at->format('F j, Y') }}</span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Last Updated</small>
                            <span class="fw-semibold">{{ $application->updated_at->format('F j, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Withdraw Modal --}}
    <div class="modal fade" id="withdrawModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-warning">
                        <i class="fas fa-times-circle me-2"></i>Withdraw Application
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="withdrawForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span id="withdrawText"></span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-lock me-2"></i>Password <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="password" class="form-control rounded-3" required>
                            <small class="text-muted">Please enter your password to confirm withdrawal</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-comment me-2"></i>Reason (optional)
                            </label>
                            <textarea name="reason" class="form-control rounded-3" rows="3"
                                placeholder="Please provide a reason for withdrawing this application..."></textarea>
                        </div>

                        <div class="alert alert-danger small">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> This action cannot be undone. The application will be marked as
                            withdrawn.
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-warning rounded-pill px-4">
                            <i class="fas fa-check-circle me-2"></i>Confirm Withdraw
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Dynamic Course Loading & Live Preview --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uniSelect = document.getElementById('university_select');
            const courseSelect = document.getElementById('course_select');
            const sopFile = document.getElementById('sop_file');
            const fileInfo = document.getElementById('fileInfo');
            const livePreview = document.getElementById('livePreview');
            const studentName = document.getElementById('student_name')?.value ||
                '{{ $application->student->first_name }} {{ $application->student->last_name }}';

            // Withdraw button handler
            const withdrawBtns = document.querySelectorAll('.withdraw-btn');
            if (withdrawBtns.length > 0) {
                withdrawBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const name = this.dataset.name;

                        document.getElementById('withdrawText').innerHTML =
                            `You are about to withdraw the application for <strong>${name}</strong>.`;

                        document.getElementById('withdrawForm').action =
                            `{{ route('agent.applications.index') }}/${id}/withdraw`;

                        new bootstrap.Modal(document.getElementById('withdrawModal')).show();
                    });
                });
            }

            // Function to update live preview
            function updateLivePreview() {
                if (!livePreview) return;

                // Get selected values
                let universityName = '';
                let courseName = '';
                let sopStatus = '{{ $application->sop_file ? 'uploaded' : 'not_uploaded' }}';
                let sopFileName = '';

                // Get university name
                if (uniSelect && uniSelect.value) {
                    const selectedOption = uniSelect.options[uniSelect.selectedIndex];
                    universityName = selectedOption.getAttribute('data-name') || selectedOption.text.split(' -')[0];
                }

                // Get course name
                if (courseSelect && courseSelect.value) {
                    const selectedOption = courseSelect.options[courseSelect.selectedIndex];
                    courseName = selectedOption.getAttribute('data-name') || selectedOption.text;
                }

                // Check if new SOP is uploaded
                if (sopFile && sopFile.files.length > 0) {
                    const file = sopFile.files[0];
                    sopStatus = 'new_uploaded';
                    sopFileName = file.name;
                }

                // Build preview HTML
                let previewHtml = `
                    <div class="preview-item">
                        <div class="preview-label">Student</div>
                        <div class="preview-value">${studentName}</div>
                    </div>
                    <div class="preview-item">
                        <div class="preview-label">University</div>
                        <div class="preview-value">${universityName || 'Not selected'}</div>
                    </div>
                    <div class="preview-item">
                        <div class="preview-label">Course</div>
                        <div class="preview-value">${courseName || 'Not selected'}</div>
                    </div>
                    <div class="preview-item">
                        <div class="preview-label">SOP</div>
                        <div class="preview-value">
                `;

                if (sopStatus === 'uploaded') {
                    previewHtml +=
                        `<span class="text-success"><i class="fas fa-check-circle me-1"></i>Current SOP uploaded</span>`;
                    if (sopFile && sopFile.files.length === 0) {
                        previewHtml += `<div class="small text-muted mt-1">Keep existing SOP</div>`;
                    }
                } else if (sopStatus === 'new_uploaded') {
                    previewHtml +=
                        `<span class="text-primary"><i class="fas fa-file-upload me-1"></i>New SOP: ${sopFileName}</span>`;
                } else {
                    previewHtml +=
                        `<span class="text-danger"><i class="fas fa-times-circle me-1"></i>No SOP uploaded</span>`;
                }

                previewHtml += `
                        </div>
                    </div>
                `;

                // Add warning if required fields missing
                if (!universityName || !courseName) {
                    previewHtml += `
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ⚠ Please select university and course
                        </div>
                    `;
                } else {
                    previewHtml += `
                        <div class="alert alert-success mt-3 mb-0">
                            <i class="fas fa-check-circle me-2"></i>
                            ✓ Application ready to update
                        </div>
                    `;
                }

                livePreview.innerHTML = previewHtml;
            }

            // File upload handler
            if (sopFile) {
                sopFile.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const validTypes = ['application/pdf', 'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                        ];
                        const fileSize = file.size / 1024 / 1024;

                        if (!validTypes.includes(file.type)) {
                            fileInfo.innerHTML =
                                '<div class="alert alert-danger">Please upload PDF or DOC/DOCX file only.</div>';
                            sopFile.value = '';
                            updateLivePreview();
                            return;
                        }

                        if (fileSize > 10) {
                            fileInfo.innerHTML =
                                '<div class="alert alert-danger">File size must be less than 10MB.</div>';
                            sopFile.value = '';
                            updateLivePreview();
                            return;
                        }

                        fileInfo.innerHTML = `
                            <div class="alert alert-success mt-2">
                                <i class="fas fa-check-circle me-2"></i>
                                New SOP selected: ${file.name} (${fileSize.toFixed(2)} MB)
                            </div>
                        `;
                        updateLivePreview();
                    } else {
                        fileInfo.innerHTML = '';
                        updateLivePreview();
                    }
                });
            }

            // Course loading on university change
            if (uniSelect && courseSelect) {
                // Store original courses for fallback
                const originalCourses = Array.from(courseSelect.options).map(opt => ({
                    id: opt.value,
                    title: opt.text,
                    name: opt.getAttribute('data-name')
                }));

                uniSelect.addEventListener('change', function() {
                    const uniId = this.value;

                    if (!uniId) {
                        // Restore original courses if available
                        if (originalCourses.length > 1) {
                            let options = '<option value="">-- Select Course --</option>';
                            originalCourses.forEach(course => {
                                if (course.id) {
                                    options +=
                                        `<option value="${course.id}" data-name="${course.name || course.title}">${course.title}</option>`;
                                }
                            });
                            courseSelect.innerHTML = options;
                            courseSelect.disabled = false;
                        } else {
                            courseSelect.innerHTML = '<option value="">-- Select Course --</option>';
                            courseSelect.disabled = true;
                        }
                        updateLivePreview();
                        return;
                    }

                    courseSelect.innerHTML = '<option value="">Loading courses...</option>';
                    courseSelect.disabled = true;

                    fetch(`/agent/applications/get-courses/${uniId}`)
                        .then(res => res.ok ? res.json() : [])
                        .then(data => {
                            let options = '<option value="">-- Select Course --</option>';
                            if (Array.isArray(data) && data.length > 0) {
                                data.forEach(course => {
                                    const isSelected = (course.id ==
                                        {{ $application->course_id }});
                                    options +=
                                        `<option value="${course.id}" data-name="${course.title}" ${isSelected ? 'selected' : ''}>${course.title}</option>`;
                                });
                                courseSelect.disabled = false;
                            } else {
                                options =
                                    '<option value="">No courses available for this university</option>';
                                courseSelect.disabled = true;
                            }
                            courseSelect.innerHTML = options;
                            updateLivePreview();
                        })
                        .catch(() => {
                            courseSelect.innerHTML =
                                '<option value="">Error loading courses. Please try again.</option>';
                            courseSelect.disabled = true;
                            updateLivePreview();
                        });
                });
            }

            // Add event listeners for real-time preview updates
            if (uniSelect) uniSelect.addEventListener('change', updateLivePreview);
            if (courseSelect) courseSelect.addEventListener('change', updateLivePreview);

            // Initial preview update
            updateLivePreview();
        });
    </script>
@endsection
