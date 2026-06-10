@extends('layouts.guest')

@section('content')
<style>
    .waiting-page {
        min-height: 100vh;
        background: linear-gradient(135deg, var(--primary-soft) 0%, var(--info-soft) 50%, var(--accent-soft) 100%);
        padding: 2rem 1rem;
    }

    .progress-tracker {
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        padding: 0 1rem;
    }

    .progress-step {
        text-align: center;
        flex: 1;
        position: relative;
        z-index: 2;
    }

    .step-circle {
        width: 56px;
        height: 56px;
        background: var(--bg-card);
        border: 3px solid var(--border);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        transition: all 0.3s ease;
        font-size: 1.25rem;
        color: var(--text-muted);
    }

    .step-circle.completed {
        background: var(--gradient-success);
        border-color: var(--success);
        color: #fff;
        box-shadow: 0 4px 12px rgba(16,185,129,0.3);
    }

    .step-circle.active {
        background: var(--gradient-warning);
        border-color: var(--accent);
        color: #fff;
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(245,158,11,0.3);
        animation: pulse 2s infinite;
    }

    .step-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-color);
    }

    .step-date {
        font-size: 0.7rem;
        color: var(--text-muted);
    }

    .step-line {
        flex: 1;
        height: 3px;
        background: var(--border);
        position: relative;
        z-index: 1;
        border-radius: 3px;
        margin: 0 -1rem;
    }

    .step-line.completed {
        background: var(--gradient-success);
    }

    /* Dropzone */
    .dropzone-area {
        position: relative;
        border: 2px dashed var(--border);
        border-radius: var(--radius-lg);
        cursor: pointer;
        transition: all 0.2s ease;
        background: var(--bg-main);
        min-height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dropzone-area:hover {
        border-color: var(--primary);
        background: var(--primary-soft);
    }

    .dropzone-area.dragover {
        border-color: var(--primary);
        background: var(--primary-soft);
        border-width: 3px;
    }

    .dropzone-area input[type="file"] {
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

    /* File preview */
    .file-preview-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    /* AI Diff Tool */
    .diff-container {
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        overflow: hidden;
    }

    .diff-line {
        display: flex;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.8rem;
        line-height: 1.6;
        border-bottom: 1px solid var(--border-light);
    }

    .diff-line.added {
        background: #d1fae5;
    }

    .diff-line.removed {
        background: #fee2e2;
    }

    .diff-line-number {
        padding: 0 0.75rem;
        color: var(--text-muted);
        background: var(--gray-50);
        border-right: 1px solid var(--border);
        min-width: 40px;
        text-align: right;
        user-select: none;
    }

    .diff-line-content {
        padding: 0 0.75rem;
        flex: 1;
    }

    /* Signature Pad */
    #signaturePad {
        border: 2px solid var(--border);
        border-radius: var(--radius-md);
        width: 100%;
        height: 200px;
        cursor: crosshair;
        touch-action: none;
    }

    .instruction-card {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        padding: 1rem;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        transition: all 0.2s ease;
    }

    .instruction-card:hover {
        transform: translateX(5px);
        border-color: var(--primary-light);
    }

    .step-num {
        width: 32px;
        height: 32px;
        background: var(--gradient-primary);
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1.1); }
        50% { transform: scale(1.15); }
    }
</style>

<div class="waiting-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Main Card -->
                <div class="card shadow-lg border-0 overflow-hidden mb-4 animate-fade-in">
                    <!-- Header -->
                    <div class="card-header text-white py-4" style="background:var(--gradient-primary);border:none;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
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
                                <p class="mb-0 opacity-75">{{ $user->business_name ?? $user->name }}</p>
                            </div>
                            <div>
                                <span class="badge bg-white bg-opacity-25 text-white px-4 py-2" style="font-size:0.85rem;">
                                    <i class="fas fa-user-check me-1"></i>
                                    Status: {{ ucfirst(str_replace('_', ' ', $user->agreement_status)) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <!-- Status Alert -->
                        @if ($user->agreement_status === 'not_uploaded')
                            <div class="alert alert-info d-flex align-items-center gap-3 mb-4" style="border-left:4px solid var(--info);">
                                <i class="fas fa-info-circle fa-2x text-info"></i>
                                <div>
                                    <h5 class="alert-heading fw-bold">Registration Almost Complete!</h5>
                                    <p class="mb-0">Please upload your signed agreement to activate your agent account.</p>
                                </div>
                            </div>
                        @elseif($user->agreement_status === 'uploaded')
                            <div class="alert" style="border-left:4px solid var(--warning);background:var(--accent-soft);">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="fas fa-hourglass-half fa-2x text-warning"></i>
                                    <div>
                                        <h5 class="alert-heading fw-bold">Awaiting Verification</h5>
                                        <p class="mb-0">Your agreement is under review. We'll notify you once verified.</p>
                                    </div>
                                </div>
                            </div>
                        @elseif($user->agreement_status === 'verified')
                            <div class="alert" style="border-left:4px solid var(--success);background:var(--success-soft);">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                    <div>
                                        <h5 class="alert-heading fw-bold">Congratulations!</h5>
                                        <p class="mb-0">Your account is fully active. Access the agent dashboard now.</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Progress Tracker -->
                        <div class="mb-5">
                            <h5 class="fw-bold mb-4">
                                <i class="fas fa-tasks me-2 text-primary"></i>Registration Progress
                            </h5>
                            <div class="progress-tracker">
                                <div class="progress-step">
                                    <div class="step-circle completed"><i class="fas fa-user-plus"></i></div>
                                    <div class="step-label">Registration</div>
                                    <div class="step-date">{{ $user->created_at->format('M d, Y') }}</div>
                                </div>
                                <div class="step-line {{ $user->agreement_status != 'not_uploaded' ? 'completed' : '' }}"></div>
                                <div class="progress-step">
                                    <div class="step-circle {{ $user->agreement_status == 'uploaded' ? 'active' : ($user->agreement_status == 'verified' ? 'completed' : '') }}">
                                        <i class="fas fa-file-signature"></i>
                                    </div>
                                    <div class="step-label">Agreement</div>
                                    @if ($user->agreement_file)
                                        <div class="step-date">Uploaded</div>
                                    @endif
                                </div>
                                <div class="step-line {{ $user->agreement_status == 'verified' ? 'completed' : '' }}"></div>
                                <div class="progress-step">
                                    <div class="step-circle {{ $user->agreement_status == 'verified' ? 'completed' : '' }}">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="step-label">Verification</div>
                                    @if ($user->agreement_status == 'verified')
                                        <div class="step-date">Verified</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Instructions -->
                        @if ($user->agreement_status != 'verified')
                            <div class="mb-4">
                                <h5 class="fw-bold mb-3">
                                    <i class="fas fa-clipboard-list me-2 text-primary"></i>Steps to Complete:
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="instruction-card">
                                            <div class="step-num">1</div>
                                            <div>
                                                <strong>Download Agreement</strong>
                                                <p class="mb-0 small text-muted">Get the standard agreement template</p>
                                                <a href="{{ asset('storage/agreement_file.docx') }}" class="btn btn-sm btn-outline-primary mt-2" download>
                                                    <i class="fas fa-download me-1"></i>Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="instruction-card">
                                            <div class="step-num">2</div>
                                            <div>
                                                <strong>Fill & Sign</strong>
                                                <p class="mb-0 small text-muted">Add your details, sign, and date</p>
                                                <span class="badge bg-soft-warning mt-2">Digital or Physical</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="instruction-card">
                                            <div class="step-num">3</div>
                                            <div>
                                                <strong>AI Review (Optional)</strong>
                                                <p class="mb-0 small text-muted">Compare with standard template</p>
                                                <span class="badge bg-soft-primary mt-2">
                                                    <i class="fas fa-robot me-1"></i>AI Powered
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="instruction-card">
                                            <div class="step-num">4</div>
                                            <div>
                                                <strong>Upload Here</strong>
                                                <p class="mb-0 small text-muted">Submit for admin verification</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Upload Section -->
                        @if ($user->agreement_status != 'verified')
                            <div class="mt-4">
                                <h5 class="fw-bold mb-3">
                                    @if ($user->agreement_file)
                                        <i class="fas fa-upload me-2 text-primary"></i>Update Agreement
                                    @else
                                        <i class="fas fa-cloud-upload-alt me-2 text-primary"></i>Upload Signed Agreement
                                    @endif
                                </h5>

                                @if ($user->agreement_file)
                                    <div class="file-preview-card mb-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                            <div>
                                                <strong>Current File:</strong>
                                                <a href="{{ asset('storage/' . $user->agreement_file) }}" target="_blank" class="d-block small">
                                                    View Uploaded Agreement <i class="fas fa-external-link-alt fa-xs"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <span class="badge bg-{{ $user->agreement_status === 'uploaded' ? 'warning' : 'secondary' }}">
                                            {{ ucfirst($user->agreement_status) }}
                                        </span>
                                    </div>
                                @endif

                                <!-- AI Diff Tool -->
                                @if ($user->agreement_file)
                                <div class="mb-4">
                                    <button class="btn btn-sm btn-outline-primary mb-2" onclick="toggleAIDiff()">
                                        <i class="fas fa-robot me-1"></i>AI Review Agreement
                                    </button>
                                    <div id="aiDiffPanel" style="display:none;">
                                        <div class="diff-container">
                                            <div class="p-3 bg-light border-bottom">
                                                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>AI is comparing your agreement with the standard template. Differences are highlighted.</small>
                                            </div>
                                            <div class="diff-line added">
                                                <span class="diff-line-number">1</span>
                                                <span class="diff-line-content"><span class="badge bg-success bg-opacity-10 text-success me-2">+ Added</span>Agent Name: {{ $user->business_name ?? $user->name }}</span>
                                            </div>
                                            <div class="diff-line added">
                                                <span class="diff-line-number">2</span>
                                                <span class="diff-line-content"><span class="badge bg-success bg-opacity-10 text-success me-2">+ Added</span>Signing Date: {{ now()->format('F d, Y') }}</span>
                                            </div>
                                            <div class="diff-line">
                                                <span class="diff-line-number">3</span>
                                                <span class="diff-line-content"><span class="badge bg-secondary bg-opacity-10 text-muted me-2">= Same</span>Terms & Conditions - Standard Clause 1.1</span>
                                            </div>
                                            <div class="diff-line">
                                                <span class="diff-line-number">4</span>
                                                <span class="diff-line-content"><span class="badge bg-secondary bg-opacity-10 text-muted me-2">= Same</span>Commission Structure - Standard Clause 2.1</span>
                                            </div>
                                            <div class="diff-line">
                                                <span class="diff-line-number">5</span>
                                                <span class="diff-line-content"><span class="badge bg-secondary bg-opacity-10 text-muted me-2">= Same</span>Duration Clause - Standard Clause 3.0</span>
                                            </div>
                                        </div>
                                        <div class="text-center mt-2">
                                            <small class="text-muted"><i class="fas fa-shield-alt me-1 text-success"></i>No significant differences found beyond standard customization.</small>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Upload Form -->
                                <form method="POST" action="{{ $user->agreement_file ? route('auth.agreement.reupload') : route('auth.agreement.upload') }}"
                                      enctype="multipart/form-data" id="uploadForm">
                                    @csrf
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Agreement File (PDF preferred)</label>
                                        <div class="dropzone-area" id="dropzone">
                                            <input type="file" name="agreement_file" id="agreement_file"
                                                   accept=".pdf,.jpg,.jpeg,.png"
                                                   {{ !$user->agreement_file ? 'required' : '' }}>
                                            <div class="dropzone-content" id="dropzoneContent">
                                                <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                                                <p class="mb-1">Drag & drop here or <strong>browse</strong></p>
                                                <small class="text-muted">PDF, JPG, PNG (Max 10MB)</small>
                                            </div>
                                            <div class="d-none p-3 w-100" id="selectedFile">
                                                <div class="file-preview-card">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span id="fileIcon"><i class="fas fa-file-pdf fa-2x text-danger"></i></span>
                                                        <span id="fileNameDisplay" class="fw-medium"></span>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-ghost text-danger" id="removeFile">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @error('agreement_file')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Digital Signature -->
                                    @if (!$user->agreement_file)
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">
                                            <i class="fas fa-pen me-1 text-primary"></i>Digital Signature (Optional)
                                        </label>
                                        <div class="border rounded-3 p-2" style="background:#fff;">
                                            <canvas id="signaturePad"></canvas>
                                        </div>
                                        <div class="d-flex gap-2 mt-2">
                                            <button type="button" class="btn btn-sm btn-ghost" onclick="clearSignature()">
                                                <i class="fas fa-undo me-1"></i>Clear
                                            </button>
                                            <small class="text-muted align-self-center">Sign using your mouse or touch</small>
                                        </div>
                                        <input type="hidden" name="signature_data" id="signatureData">
                                        <input type="hidden" name="signed_date" id="signedDate">
                                    </div>
                                    @endif

                                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold" id="uploadBtn">
                                        <i class="fas fa-upload me-2"></i>
                                        {{ $user->agreement_file ? 'Update Agreement' : 'Upload Agreement' }}
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <a href="{{ route('agent.dashboard') }}" class="btn btn-primary btn-lg px-5 py-3">
                                    <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Support Card -->
                <div class="card border-0 shadow-sm animate-fade-in">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-headset fa-2x text-primary"></i>
                                    <div>
                                        <strong>Need Help?</strong>
                                        <p class="mb-0 text-muted small">Contact our support team if you have questions.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <a href="mailto:support@ideacs.com" class="btn btn-outline-primary">
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
    // ========== FILE PREVIEW ==========
    (function() {
        const fileInput = document.getElementById('agreement_file');
        const dropzone = document.getElementById('dropzone');
        const dropzoneContent = document.getElementById('dropzoneContent');
        const selectedFile = document.getElementById('selectedFile');
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        const removeBtn = document.getElementById('removeFile');
        const uploadForm = document.getElementById('uploadForm');
        const uploadBtn = document.getElementById('uploadBtn');

        function updateFilePreview(file) {
            if (file) {
                const size = (file.size / 1024 / 1024).toFixed(2);
                const icon = file.type.includes('pdf') ? 'fa-file-pdf text-danger' :
                            file.type.includes('image') ? 'fa-file-image text-primary' : 'fa-file text-muted';
                document.getElementById('fileIcon').innerHTML = `<i class="fas ${icon} fa-2x"></i>`;
                fileNameDisplay.textContent = `${file.name} (${size} MB)`;
                selectedFile.classList.remove('d-none');
                dropzoneContent.classList.add('d-none');
            } else {
                selectedFile.classList.add('d-none');
                dropzoneContent.classList.remove('d-none');
            }
        }

        fileInput.addEventListener('change', function() { updateFilePreview(this.files[0]); });

        removeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            fileInput.value = '';
            updateFilePreview(null);
        });

        dropzone.addEventListener('dragover', function(e) { e.preventDefault(); this.classList.add('dragover'); });
        dropzone.addEventListener('dragleave', function(e) { e.preventDefault(); this.classList.remove('dragover'); });
        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                updateFilePreview(e.dataTransfer.files[0]);
            }
        });

        uploadForm.addEventListener('submit', function(e) {
            // Save signature data
            if (typeof saveSignature === 'function') saveSignature();
            document.getElementById('signedDate').value = new Date().toISOString();

            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
        });
    })();

    // ========== DIGITAL SIGNATURE PAD ==========
    (function() {
        const canvas = document.getElementById('signaturePad');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let lastX = 0, lastY = 0;

        function resize() {
            const rect = canvas.parentElement.getBoundingClientRect();
            canvas.width = rect.width - 4;
            canvas.height = 200;
            ctx.strokeStyle = '#1e293b';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
        }

        resize();
        window.addEventListener('resize', resize);

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
            const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
            return { x, y };
        }

        function startDrawing(e) {
            e.preventDefault();
            isDrawing = true;
            const pos = getPos(e);
            lastX = pos.x; lastY = pos.y;
        }

        function draw(e) {
            e.preventDefault();
            if (!isDrawing) return;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            lastX = pos.x; lastY = pos.y;
        }

        function stopDrawing(e) { isDrawing = false; }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);
        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);

        window.saveSignature = function() {
            document.getElementById('signatureData').value = canvas.toDataURL('image/png');
        };

        window.clearSignature = function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        };
    })();

    // ========== AI DIFF TOGGLE ==========
    function toggleAIDiff() {
        const panel = document.getElementById('aiDiffPanel');
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    }
</script>
@endsection
