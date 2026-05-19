@extends('layouts.app')

@section('content')
    <style>
        .btn-gradient {
            background: var(--active);
            color: var(--white);
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            color: var(--white);
        }

        .progress-steps {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }

        .step {
            text-align: center;
            flex: 1;
            position: relative;
            z-index: 2;
        }

        .step-icon {
            width: 50px;
            height: 50px;
            background: var(--white);
            border: 2px solid var(--border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            transition: all 0.3s ease;
        }

        .step.completed .step-icon {
            background: var(--success);
            border-color: var(--success);
            color: var(--white);
        }

        .step.active .step-icon {
            background: var(--warning);
            border-color: var(--warning);
            color: var(--white);
            transform: scale(1.1);
        }

        .step-text {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-color);
        }

        .step-date {
            font-size: 0.7rem;
            color: var(--muted);
        }

        .step-line {
            flex: 1;
            height: 2px;
            background: var(--border);
            position: relative;
            z-index: 1;
        }

        .step-line.completed {
            background: var(--success);
        }

        .instruction-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 12px;
            background: var(--white);
            border-radius: var(--radius);
            margin-bottom: 10px;
            transition: transform 0.2s;
            border: 1px solid var(--border);
        }

        .instruction-item:hover {
            transform: translateX(5px);
            box-shadow: var(--soft-shadow);
        }

        .step-number {
            width: 30px;
            height: 30px;
            background: var(--active);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }

        .dropzone-area {
            position: relative;
            border: 2px dashed var(--border);
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.2s ease;
            background: var(--bg-light);
            min-height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dropzone-area:hover {
            border-color: var(--primary);
            background: var(--light-blue);
        }

        .dropzone-area.dragover {
            border-color: var(--primary);
            background: #f0f4ff;
            border-width: 3px;
        }

        #agreement_file {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 10;
        }

        .dropzone-content {
            text-align: center;
            padding: 40px 20px;
            position: relative;
            z-index: 1;
            pointer-events: none;
        }

        .dropzone-content i {
            font-size: 48px;
            color: var(--primary-light);
            margin-bottom: 15px;
        }

        .selected-file {
            background: #d4edda;
            border-radius: var(--sradius);
            margin: 10px;
            position: relative;
            z-index: 1;
            pointer-events: none;
            width: calc(100% - 20px);
        }

        .file-preview {
            background: var(--gray);
            border-radius: var(--sradius);
            padding: 12px;
            border-left: 4px solid var(--primary);
        }

        #removeFile {
            pointer-events: auto;
            position: relative;
            z-index: 20;
            cursor: pointer;
        }

        .status-badge {
            font-size: 0.9rem;
        }

        .current-file {
            background: var(--gray);
            border-radius: var(--radius);
            border: 1px solid var(--border);
        }

        .alert {
            animation: fadeIn 0.5s ease-out;
            border-radius: var(--radius);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <div class="waiting-dashboard p-2"
        style="background: linear-gradient(135deg, var(--bg-light) 0%, var(--light-blue) 100%); min-height: 100vh;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    {{-- Status Card --}}
                    <div class="card shadow-lg border-0 rounded-4 mb-4 overflow-hidden">
                        <div class="card-header text-white py-4" style="background: var(--active);">
                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                <div>
                                    <h3 class="mb-1 fw-bold">
                                        @if ($user->agreement_status === 'verified')
                                            <i class="fas fa-check-circle me-2"></i>Account Verified!
                                        @elseif($user->agreement_status === 'uploaded')
                                            <i class="fas fa-clock me-2"></i>Under Review
                                        @else
                                            <i class="fas fa-file-upload me-2"></i>Action Required
                                        @endif
                                    </h3>
                                    <p class="mb-0 opacity-75">{{ $user->business_name ?? $user->name }} -
                                        {{ $user->name }}</p>
                                </div>
                                <div class="text-center mt-2 mt-sm-0">
                                    <div class="status-badge bg-white bg-opacity-25 rounded-pill px-4 py-2">
                                        <i class="fas fa-user-check me-1"></i>
                                        Status: {{ ucfirst($user->agreement_status) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            @if ($user->agreement_status === 'not_uploaded')
                                <div class="alert alert-info border-0 rounded-3 mb-4"
                                    style="background: var(--light-blue);">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-info-circle fa-2x text-info"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="alert-heading">Registration Almost Complete!</h5>
                                            <p class="mb-0">Please upload your signed agreement to activate your agent
                                                account.</p>
                                        </div>
                                    </div>
                                </div>
                            @elseif($user->agreement_status === 'uploaded')
                                <div class="alert alert-warning border-0 rounded-3 mb-4" style="background: #fff3e0;">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-hourglass-half fa-2x text-warning"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="alert-heading">Awaiting Verification</h5>
                                            <p class="mb-0">Your agreement is under review. You'll receive a notification
                                                once verified.</p>
                                        </div>
                                    </div>
                                </div>
                            @elseif($user->agreement_status === 'verified')
                                <div class="alert alert-success border-0 rounded-3 mb-4" style="background: #d4edda;">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-check-circle fa-2x text-success"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="alert-heading">Congratulations!</h5>
                                            <p class="mb-0">Your account is fully active. You can now access the agent
                                                dashboard.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Progress Steps --}}
                            <div class="mb-5">
                                <h5 class="mb-4">Registration Progress</h5>
                                <div class="progress-steps">
                                    <div class="step completed">
                                        <div class="step-icon"><i class="fas fa-user-plus"></i></div>
                                        <div class="step-text">Registration</div>
                                        <div class="step-date">{{ $user->created_at->format('M d, Y') }}</div>
                                    </div>
                                    <div
                                        class="step-line {{ $user->agreement_status != 'not_uploaded' ? 'completed' : '' }}">
                                    </div>
                                    <div
                                        class="step {{ $user->agreement_status == 'uploaded' ? 'active' : ($user->agreement_status == 'verified' ? 'completed' : '') }}">
                                        <div class="step-icon"><i class="fas fa-file-signature"></i></div>
                                        <div class="step-text">Agreement Upload</div>
                                        @if ($user->agreement_file)
                                            <div class="step-date">Uploaded</div>
                                        @endif
                                    </div>
                                    <div class="step-line {{ $user->agreement_status == 'verified' ? 'completed' : '' }}">
                                    </div>
                                    <div class="step {{ $user->agreement_status == 'verified' ? 'completed' : '' }}">
                                        <div class="step-icon"><i class="fas fa-check-circle"></i></div>
                                        <div class="step-text">Verification</div>
                                        @if ($user->agreement_status == 'verified')
                                            <div class="step-date">Verified</div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Instructions --}}
                            @if ($user->agreement_status != 'verified')
                                <div class="instruction-box p-4 mb-4"
                                    style="background: var(--gray); border-radius: var(--radius);">
                                    <h5 class="mb-3"><i class="fas fa-clipboard-list me-2"
                                            style="color: var(--primary);"></i>Steps to Complete Registration:</h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="instruction-item">
                                                <div class="step-number">1</div>
                                                <div class="step-content">
                                                    <strong>Download Agreement</strong>
                                                    <p class="mb-0 small">Download the standard agreement File</p>
                                                    <a href="{{ asset('storage/agreement_file.docx') }}"
                                                        class="btn btn-sm btn-outline-primary mt-2" download
                                                        style="border-color: var(--primary); color: var(--primary);">
                                                        <i class="fas fa-download me-1"></i>Download Agreement
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="instruction-item">
                                                <div class="step-number">2</div>
                                                <div class="step-content">
                                                    <strong>Fill & Sign</strong>
                                                    <p class="mb-0 small">Update fields, sign, and stamp</p>
                                                    <span class="badge mt-2" style="background: var(--warning);">Digital or
                                                        Physical Signature</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="instruction-item">
                                                <div class="step-number">3</div>
                                                <div class="step-content">
                                                    <strong>Scan Document</strong>
                                                    <p class="mb-0 small">Convert to PDF if physically signed</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="instruction-item">
                                                <div class="step-number">4</div>
                                                <div class="step-content">
                                                    <strong>Upload Here</strong>
                                                    <p class="mb-0 small">Submit for verification</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Upload Form --}}
                            @if ($user->agreement_status != 'verified')
                                <div class="upload-section">
                                    <h5 class="mb-3">
                                        @if ($user->agreement_file)
                                            <i class="fas fa-upload me-2" style="color: var(--primary);"></i>Update
                                            Agreement
                                        @else
                                            <i class="fas fa-cloud-upload-alt me-2"
                                                style="color: var(--primary);"></i>Upload Signed Agreement
                                        @endif
                                    </h5>

                                    @if ($user->agreement_file)
                                        <div class="current-file mb-4 p-3 rounded-3">
                                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                                <div>
                                                    <i class="fas fa-file-pdf me-2" style="color: var(--danger);"></i>
                                                    <strong>Current File:</strong>
                                                    <a href="{{ asset('storage/' . $user->agreement_file) }}"
                                                        target="_blank" class="ms-2" style="color: var(--primary);">
                                                        View Uploaded Agreement <i
                                                            class="fas fa-external-link-alt fa-xs"></i>
                                                    </a>
                                                </div>
                                                <span class="badge mt-2 mt-sm-0"
                                                    style="background: {{ $user->agreement_status === 'uploaded' ? 'var(--warning)' : 'var(--muted)' }};">
                                                    {{ ucfirst($user->agreement_status) }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('auth.agreement.upload') }}"
                                        enctype="multipart/form-data" id="uploadForm">
                                        @csrf
                                        <div class="mb-4">
                                            <label class="form-label fw-semibold">Agreement File (PDF format
                                                preferred)</label>

                                            <div class="dropzone-area" id="dropzone">
                                                <input type="file" name="agreement_file" id="agreement_file"
                                                    accept=".pdf,.jpg,.jpeg,.png"
                                                    {{ !$user->agreement_file ? 'required' : '' }}>

                                                <div class="dropzone-content" id="dropzoneContent">
                                                    <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                                                    <p class="mb-1">Drag & drop your file here or <strong>click to
                                                            browse</strong></p>
                                                    <small>Supported: PDF, JPG, PNG (Max 10MB)</small>
                                                </div>

                                                <div class="selected-file d-none p-3" id="selectedFile">
                                                    <div class="file-preview">
                                                        <i class="fas fa-file-pdf me-2"></i>
                                                        <span class="file-name"></span>
                                                        <button type="button"
                                                            class="btn btn-sm btn-link text-danger float-end"
                                                            id="removeFile">
                                                            <i class="fas fa-times"></i> Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            @error('agreement_file')
                                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <button type="submit" class="btn btn-gradient w-100 py-3 fw-bold"
                                            id="uploadBtn">
                                            <i class="fas fa-upload me-2"></i>
                                            {{ $user->agreement_file ? 'Update Agreement' : 'Upload Agreement' }}
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="text-center">
                                    <a href="{{ route('agent.dashboard') }}" class="btn btn-gradient btn-lg px-5 py-3">
                                        <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Support Card --}}
                    <div class="card border-0 rounded-4 shadow-sm">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <i class="fas fa-headset me-2" style="color: var(--primary);"></i>
                                    <strong>Need Help?</strong> If you haven't received the agreement or have questions,
                                    please contact our support team.
                                </div>
                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                    <a href="mailto:support@ideacs.com" class="btn btn-outline-primary"
                                        style="border-color: var(--primary);">
                                        <i class="fas fa-envelope me-1"></i>Contact Support
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // FAST file preview and upload - minimal but complete
        (function() {
            const fileInput = document.getElementById('agreement_file');
            const dropzone = document.getElementById('dropzone');
            const dropzoneContent = document.getElementById('dropzoneContent');
            const selectedFile = document.getElementById('selectedFile');
            const fileNameSpan = document.querySelector('#selectedFile .file-name');
            const removeBtn = document.getElementById('removeFile');
            const uploadForm = document.getElementById('uploadForm');
            const uploadBtn = document.getElementById('uploadBtn');

            // File selection preview
            function updateFilePreview(file) {
                if (file) {
                    const fileSize = (file.size / 1024 / 1024).toFixed(2);
                    const fileType = file.type;
                    let icon = 'fa-file-pdf';

                    if (fileType.includes('image')) {
                        icon = 'fa-file-image';
                    } else if (fileType.includes('pdf')) {
                        icon = 'fa-file-pdf';
                    } else {
                        icon = 'fa-file';
                    }

                    fileNameSpan.innerHTML = `<i class="fas ${icon} me-2"></i>${file.name} (${fileSize} MB)`;
                    selectedFile.classList.remove('d-none');
                    dropzoneContent.classList.add('d-none');
                } else {
                    selectedFile.classList.add('d-none');
                    dropzoneContent.classList.remove('d-none');
                }
            }

            // Handle file selection
            fileInput.addEventListener('change', function(e) {
                updateFilePreview(this.files[0]);
            });

            // Remove file
            if (removeBtn) {
                removeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    fileInput.value = '';
                    updateFilePreview(null);
                });
            }

            // Drag and drop
            dropzone.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            dropzone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });

            dropzone.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    updateFilePreview(files[0]);
                }
            });

            // Fast form submission
            if (uploadForm) {
                uploadForm.addEventListener('submit', function(e) {
                    // Don't submit if no file and no existing file
                    if (!fileInput.files.length && !{{ $user->agreement_file ? 'true' : 'false' }}) {
                        e.preventDefault();
                        alert('Please select a file to upload');
                        return;
                    }

                    // Show loading state
                    const originalText = uploadBtn.innerHTML;
                    uploadBtn.disabled = true;
                    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';

                    // Auto-recover after 30 seconds
                    setTimeout(() => {
                        if (uploadBtn.disabled) {
                            uploadBtn.disabled = false;
                            uploadBtn.innerHTML = originalText;
                        }
                    }, 30000);
                });
            }
        })();
    </script>
@endsection
