@extends('layouts.agent')

@section('title', 'Edit Profile - ' . ($user->business_name ?? $user->name))

@section('content')

    <style>
        .form-section {
            background: var(--card);
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .form-section-header {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-bottom: none;
        }

        .form-section-header h4 {
            color: white;
            margin: 0;
            font-weight: 600;
        }

        .form-section-header p {
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
            font-size: 0.875rem;
        }

        .file-upload-card {
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 1rem;
            transition: all 0.2s ease;
            background: var(--bg-light);
        }

        .file-upload-card:hover {
            border-color: var(--primary);
            background: rgba(130, 11, 92, 0.02);
        }


        .file-preview-box {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid var(--border);
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);

        }

        .info-badge {
            background: rgba(130, 11, 92, 0.1);
            color: var(--primary);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .btn-update {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }

        .disabled-field {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
    </style>

    <div class="container-fluid py-4">

        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1 fw-bold">Edit Profile</h2>
                <p class="text-muted mb-0">Update your business information and documents</p>
            </div>
            <div>
                <span class="info-badge">
                    <i class="fas fa-user-check me-1"></i> {{ ucfirst($user->role) }}
                </span>
            </div>
        </div>

        <form action="{{ route('agent.users.update', $user->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                {{-- Left Column: File Uploads --}}
                <div class="col-lg-4">
                    <div class="form-section">
                        <div class="form-section-header">
                            <h4><i class="fas fa-cloud-upload-alt me-2"></i>Documents & Logos</h4>
                            <p>Upload or update your business documents</p>
                        </div>
                        <div class="p-4">
                            {{-- Business Logo --}}
                            <div class="file-upload-card mb-4">
                                <label class="fw-semibold mb-2 d-block">
                                    <i class="fas fa-image text-primary me-2"></i>Business Logo
                                </label>
                                <div class="d-flex gap-3 align-items-start">
                                    <img id="logoPreview"
                                        src="{{ $user->business_logo ? Storage::url($user->business_logo) : 'https://placehold.co/80x80?text=Logo' }}"
                                        class="file-preview-box">
                                    <div class="flex-grow-1">
                                        <input type="file" name="business_logo" class="form-control form-control-sm"
                                            accept="image/*" onchange="previewImage(this, 'logoPreview')">
                                        <small class="text-muted">Recommended: Square image, max 10MB</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Registration File --}}
                            <div class="file-upload-card mb-4">
                                <label class="fw-semibold mb-2 d-block">
                                    <i class="fas fa-building text-primary me-2"></i>Registration Certificate
                                </label>
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="text-center">
                                        @if ($user->registration)
                                            <a href="{{ Storage::url($user->registration) }}" target="_blank">
                                                <img id="regPreview" src="{{ Storage::url($user->registration) }}"
                                                    class="file-preview-box" style="object-fit: cover;">
                                            </a>
                                        @else
                                            <img id="regPreview" src="https://placehold.co/80x80?text=PDF"
                                                class="file-preview-box">
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <input type="file" name="registration" class="form-control form-control-sm"
                                            accept=".pdf,.jpg,.jpeg,.png" onchange="previewImage(this, 'regPreview')">
                                        @if ($user->registration)
                                            <small class="text-success d-block mt-1">
                                                <i class="fas fa-check-circle"></i> Current file uploaded
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- PAN File --}}
                            <div class="file-upload-card mb-4">
                                <label class="fw-semibold mb-2 d-block">
                                    <i class="fas fa-file-invoice text-primary me-2"></i>PAN Certificate
                                </label>
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="text-center">
                                        @if ($user->pan)
                                            <a href="{{ Storage::url($user->pan) }}" target="_blank">
                                                <img id="panPreview" src="{{ Storage::url($user->pan) }}"
                                                    class="file-preview-box" style="object-fit: cover;">
                                            </a>
                                        @else
                                            <img id="panPreview" src="https://placehold.co/80x80?text=PDF"
                                                class="file-preview-box">
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <input type="file" name="pan" class="form-control form-control-sm"
                                            accept=".pdf,.jpg,.jpeg,.png" onchange="previewImage(this, 'panPreview')">
                                        @if ($user->pan)
                                            <small class="text-success d-block mt-1">
                                                <i class="fas fa-check-circle"></i> Current file uploaded
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Agreement File --}}
                            <div class="file-upload-card">
                                <label class="fw-semibold mb-2 d-block">
                                    <i class="fas fa-file-contract text-primary me-2"></i>Agreement Document
                                </label>
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="text-center">
                                        @php
                                            $file = $user->agreement_file;
                                            $ext = $file ? strtolower(pathinfo($file, PATHINFO_EXTENSION)) : null;
                                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                                        @endphp

                                        @if ($file && $isImage)
                                            <a href="{{ Storage::url($file) }}" target="_blank">
                                                <img id="agreementPreview" src="{{ Storage::url($file) }}"
                                                    class="file-preview-box">
                                            </a>
                                        @elseif($file)
                                            <a href="{{ Storage::url($file) }}" target="_blank"
                                                class="text-decoration-none">
                                                <div
                                                    class="file-preview-box d-flex align-items-center justify-content-center bg-light">
                                                    <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                                </div>
                                            </a>
                                        @else
                                            <img id="agreementPreview" src="https://placehold.co/80x80?text=PDF"
                                                class="file-preview-box">
                                        @endif
                                    </div>
                                    <p class="text-success">
                                        Read only file
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Basic Info & Security --}}
                <div class="col-lg-8">
                    {{-- Basic Information Section --}}
                    <div class="form-section mb-2">
                        <div class="form-section-header">
                            <h4><i class="fas fa-user-circle me-2"></i>Basic Information</h4>
                            <p>Your business and contact details</p>
                        </div>
                        <div class="p-3">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Business Name</label>
                                    <input type="text" class="form-control disabled-field"
                                        value="{{ $user->business_name }}" disabled>
                                    <small class="text-muted">Contact admin to change</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Owner Name</label>
                                    <input type="text" class="form-control disabled-field"
                                        value="{{ $user->owner_name }}" disabled>
                                    <small class="text-muted">Contact admin to change</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Contact Person</label>
                                    <input type="text" class="form-control disabled-field"
                                        value="{{ $user->name }}" disabled>
                                    <small class="text-muted">Contact admin to change</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email Address <span
                                            class="text-danger">*</span></label>
                                    <input type="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Contact Number</label>
                                    <input type="text" name="contact"
                                        class="form-control @error('contact') is-invalid @enderror"
                                        value="{{ old('contact', $user->contact) }}" placeholder="+977 XXXXXXXXX">
                                    @error('contact')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Address</label>
                                    <input type="text" name="address"
                                        class="form-control @error('address') is-invalid @enderror"
                                        value="{{ old('address', $user->address) }}" placeholder="Your business address">
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-section-header">
                            <h4><i class="fas fa-lock me-2"></i>Security Settings</h4>
                            <p>Update your password (optional)</p>
                        </div>
                        <div class="p-3">
                            <div class="alert alert-info border-0 rounded-3 mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Leave password fields blank if you don't want to change your password.
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-key"></i>
                                        </span>
                                        <input type="password" name="password" id="passwordField" class="form-control"
                                            placeholder="Enter new password">
                                        <span class="input-group-text" onclick="togglePassword()"
                                            style="cursor: pointer;">
                                            <i id="passwordIcon" class="fas fa-eye"></i>
                                        </span>
                                    </div>
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-check-circle"></i>
                                        </span>
                                        <input type="password" name="password_confirmation" id="confirmPasswordField"
                                            class="form-control" placeholder="Confirm new password">
                                        <span class="input-group-text">
                                            <span id="password-match-icon"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2 pt-3 border-top">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('agent.users.show', $user->slug) }}" class="btn btn-light px-4">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-update text-white px-4">
                                        <i class="fas fa-save me-2"></i>Save Changes
                                    </button>
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
        // Password visibility toggle
        function togglePassword() {
            const field = document.getElementById('passwordField');
            const icon = document.getElementById('passwordIcon');

            if (field.type === "password") {
                field.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                field.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }

        // Password match checking
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('passwordField');
            const confirm = document.getElementById('confirmPasswordField');
            const matchIcon = document.getElementById('password-match-icon');

            function checkMatch() {
                if (!confirm.value) {
                    matchIcon.innerHTML = "";
                    return;
                }

                if (password.value === confirm.value) {
                    matchIcon.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
                } else {
                    matchIcon.innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
                }
            }

            if (password && confirm) {
                password.addEventListener('input', checkMatch);
                confirm.addEventListener('input', checkMatch);
            }
        });

        // File preview
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (!input.files || !input.files[0]) return;

            const file = input.files[0];
            const fileType = file.type;

            if (fileType.startsWith("image/")) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                // For non-image files, show a file icon
                preview.src = "https://placehold.co/80x80?text=FILE";
            }
        }
    </script>
@endpush
