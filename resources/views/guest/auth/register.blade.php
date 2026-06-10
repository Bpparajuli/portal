@extends('layouts.guest')
@section('title', 'Become an IDEA Agent')

@section('content')

    <style>
        .register-page {
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
        }

        /* Animated Background */
        .register-page::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(26, 2, 98, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            animation: floatBackground 25s infinite ease-in-out;
            pointer-events: none;
        }

        .register-page::after {
            content: '';
            position: absolute;
            bottom: -15%;
            left: -5%;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(130, 11, 92, 0.06) 0%, transparent 70%);
            border-radius: 50%;
            animation: floatBackground 20s infinite reverse ease-in-out;
            pointer-events: none;
        }

        @keyframes floatBackground {

            0%,
            100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(30px, 20px);
            }
        }

        /* Main Card */
        .register-card {
            border-radius: 16px !important;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .register-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
        }

        /* Card Header */
        .card-header-custom {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            position: relative;
            overflow: hidden;
            border-radius: 16px 16px 0 0 !important;
        }

        .card-header-custom::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -20%;
            width: 150%;
            height: 200%;
            background: linear-gradient(115deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: rotate(25deg);
            animation: shimmer 8s infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%) rotate(25deg);
            }

            100% {
                transform: translateX(100%) rotate(25deg);
            }
        }

        .header-icon {
            animation: gentleFloat 3s ease-in-out infinite;
        }

        @keyframes gentleFloat {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        /* Progress Steps */
        .step-wrapper {
            background: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .step-container {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .step-circle {
            width: 48px;
            height: 48px;
            background: var(--light-gray);
            border-radius: 50%;
            font-weight: 700;
            font-size: 1.1rem;
            transition: var(--transition-default);
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .step-item.active .step-circle {
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            color: white;
            box-shadow: 0 4px 12px rgba(26, 2, 98, 0.3);
            transform: scale(1.05);
        }

        .step-item.completed .step-circle {
            background: var(--success);
            color: white;
        }

        .step-item.completed .step-number {
            display: none;
        }

        .step-item.completed .step-check {
            display: inline !important;
        }

        .step-label {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--dark-gray);
            transition: var(--transition-default);
            margin-top: 6px;
        }

        .step-item.active .step-label {
            color: var(--secondary);
            font-weight: 600;
        }

        .step-item.completed .step-label {
            color: var(--success);
        }

        .progress-line {
            position: absolute;
            top: 24px;
            left: 24px;
            right: 24px;
            height: 3px;
            background: var(--light-gray);
            border-radius: 3px;
            z-index: 1;
        }

        .progress-line-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--secondary), var(--primary));
            border-radius: 3px;
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            width: 0%;
        }

        /* Form Styles */
        .form-card-body {
            background: white;
        }

        .step-header {
            border-bottom-color: #e5e7eb !important;
        }

        .step-icon i {
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1f2937;
            font-size: 0.9rem;
        }

        .form-control,
        .input-group-text {
            border-color: #e5e7eb;
            transition: var(--transition-default);
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(74, 29, 140, 0.1);
        }

        .input-group-text {
            background-color: #f9fafb;
        }

        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .form-text {
            font-size: 0.75rem;
            color: var(--dark-gray);
            margin-top: 0.25rem;
        }

        /* File Upload */
        .file-upload-area {
            border: 2px dashed #e5e7eb;
            border-radius: 20px;
            padding: 1.5rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition-default);
            background: #fafafa;
        }

        .file-upload-area:hover {
            border-color: var(--primary);
            background: #f5f3ff;
            transform: translateY(-2px);
        }

        .file-preview {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .file-preview img {
            max-width: 100%;
            max-height: 70px;
            object-fit: contain;
        }

        .upload-icon i {
            font-size: 1.5rem;
            margin-top: 0.5rem;
        }

        .file-info {
            font-size: 0.7rem;
            color: var(--success);
            margin-top: 0.5rem;
            word-break: break-all;
        }

        /* Password Strength */
        .password-strength {
            height: 4px;
            border-radius: 4px;
            transition: var(--transition-default);
            background: #e5e7eb;
        }

        .strength-weak {
            width: 33%;
            background: var(--danger);
        }

        .strength-fair {
            width: 66%;
            background: var(--warning);
        }

        .strength-strong {
            width: 100%;
            background: var(--success);
        }

        /* Requirement List */
        .requirement-list {
            border-radius: 16px;
        }

        .requirement-item {
            font-size: 0.8rem;
            color: #6b7280;
            transition: var(--transition-default);
        }

        .requirement-item.valid {
            color: var(--success);
        }

        .requirement-item.valid i {
            color: var(--success);
        }

        .requirement-item i {
            width: 18px;
        }

        /* Buttons */
        .btn-nav {
            border-radius: 40px;
            font-weight: 600;
            transition: var(--transition-default);
            font-size: 0.95rem;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            border: none;
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26, 2, 98, 0.3);
            color: white;
        }

        .btn-success-custom {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            border: none;
            color: white;
        }

        .btn-success-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-outline-secondary {
            border: 2px solid #e5e7eb;
            color: #4b5563;
        }

        .btn-outline-secondary:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            transform: translateY(-2px);
        }

        /* Alert */
        .alert-info-custom {
            background: linear-gradient(135deg, #eef2ff 0%, #faf5ff 100%);
            border: none;
            border-radius: 20px;
            color: var(--secondary);
            border-left: 4px solid var(--primary);
        }

        /* Animation */
        .form-step {
            animation: fadeSlideIn 0.4s ease-out;
        }

        @keyframes fadeSlideIn {
            from {
                opacity: 0;
                transform: translateX(15px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Modal Gradient */
        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
        }

        /* Loading State */
        .btn-loading {
            opacity: 0.7;
            cursor: not-allowed;
            pointer-events: none;
        }

        input::placeholder,
        textarea::placeholder {
            color: #cccccc !important;
        }

        /* Password Toggle Button */
        .password-toggle {
            cursor: pointer;
            transition: var(--transition-default);
        }

        .password-toggle:hover {
            opacity: 0.7;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .step-circle {
                width: 40px;
                height: 40px;
                font-size: 0.9rem;
            }

            .step-label {
                font-size: 0.7rem;
            }

            .progress-line {
                top: 20px;
            }

            .form-card-body {
                padding: 1.25rem !important;
            }

            .card-header-custom {
                padding: 1.5rem !important;
            }

            .display-6 {
                font-size: 1.5rem;
            }
        }
    </style>


    <div class="register-page">
        <div class="container p-2 py-md-2">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    {{-- Main Card --}}
                    <div class="card register-card border-0 shadow-lg overflow-hidden">
                        {{-- Header Section --}}
                        <div
                            class="card-header-custom d-flex justify-content-center gap-5 align-items-center text-white text-center p-2">
                            <div class="header-icon ">
                                <i class="fas fa-handshake fa-3x"></i>
                            </div>
                            <div>
                                <h1 class="fw-bold display-6 mb-2">Become an IDEA Agent</h1>
                                <p class="lead mb-0 opacity-90">Join our global network of trusted education partners</p>
                            </div>
                        </div>

                        {{-- Progress Steps --}}
                        <div class="step-wrapper px-3 px-md-4 pt-4 pb-2 bg-white">
                            <div class="step-container position-relative mb-2">

                                <div class="step-item" id="step1Indicator">
                                    <div class="step-circle d-flex align-items-center justify-content-center">
                                        <span class="step-number">1</span>
                                        <i class="fas fa-check step-check d-none"></i>
                                    </div>
                                    <div class="step-label mt-2">Personal Info</div>
                                </div>

                                <div class="step-item" id="step2Indicator">
                                    <div class="step-circle d-flex align-items-center justify-content-center">
                                        <span class="step-number">2</span>
                                        <i class="fas fa-check step-check d-none"></i>
                                    </div>
                                    <div class="step-label mt-2">Documents</div>
                                </div>

                                <div class="step-item" id="step3Indicator">
                                    <div class="step-circle d-flex align-items-center justify-content-center">
                                        <span class="step-number">3</span>
                                        <i class="fas fa-check step-check d-none"></i>
                                    </div>
                                    <div class="step-label mt-2">Security</div>
                                </div>

                                <div class="progress-line">
                                    <div class="progress-line-fill" id="stepProgress"></div>
                                </div>

                            </div>
                        </div>

                        {{-- Form Body --}}
                        <div class="form-card-body p-3 p-md-4 p-lg-5 bg-white">
                            <form method="POST" action="{{ route('auth.register') }}" enctype="multipart/form-data"
                                id="registrationForm" novalidate>
                                @csrf

                                {{-- Step 1: Basic Information --}}
                                <div id="step1" class="form-step">
                                    <div class="step-header d-flex align-items-center mb-4 pb-2 border-bottom">
                                        <div class="step-icon me-3">
                                            <i class="fas fa-user-circle fa-2x text-primary"></i>
                                        </div>
                                        <h2 class="h4 fw-bold mb-0">Basic Information</h2>
                                    </div>

                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                Business Name <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-building text-secondary"></i>
                                                </span>
                                                <input type="text" name="business_name"
                                                    class="form-control border-start-0 ps-0 @error('business_name') is-invalid @enderror"
                                                    value="{{ old('business_name') }}"
                                                    placeholder="e.g., EduGlobal Consulting">
                                            </div>
                                            <div class="form-text">Your official registered business name</div>
                                            @error('business_name')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                Owner Name <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-user-tie text-secondary"></i>
                                                </span>
                                                <input type="text" name="owner_name"
                                                    class="form-control border-start-0 ps-0 @error('owner_name') is-invalid @enderror"
                                                    value="{{ old('owner_name') }}"
                                                    placeholder="Full name of business owner">
                                            </div>
                                            <div class="form-text">Legal owner as per registration documents</div>
                                            @error('owner_name')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                Contact Person <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-user text-secondary"></i>
                                                </span>
                                                <input type="text" name="name"
                                                    class="form-control border-start-0 ps-0 @error('name') is-invalid @enderror"
                                                    value="{{ old('name') }}" placeholder="Primary contact person">
                                            </div>
                                            <div class="form-text">Person responsible for daily communication</div>
                                            @error('name')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                Contact Number <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-phone-alt text-secondary"></i>
                                                </span>
                                                <input type="tel" name="contact"
                                                    class="form-control border-start-0 ps-0 @error('contact') is-invalid @enderror"
                                                    value="{{ old('contact') }}" placeholder="+1 234 567 8900">
                                            </div>
                                            <div class="form-text">Include country code for international calls</div>
                                            @error('contact')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-semibold">
                                                Business Address <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-map-marker-alt text-secondary"></i>
                                                </span>
                                                <textarea name="address" class="form-control border-start-0 ps-0 @error('address') is-invalid @enderror"
                                                    rows="3" placeholder="Street address, city, state/province, postal code, country">{{ old('address') }}</textarea>
                                            </div>
                                            <div class="form-text">Complete physical address of your business</div>
                                            @error('address')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Step 2: Documents --}}
                                <div id="step2" class="form-step d-none">
                                    <div class="step-header d-flex align-items-center mb-4 pb-2 border-bottom">
                                        <div class="step-icon me-3">
                                            <i class="fas fa-file-alt fa-2x text-primary"></i>
                                        </div>
                                        <h2 class="h4 fw-bold mb-0">Business Documents</h2>
                                    </div>

                                    <div class="alert alert-info-custom mb-4 d-flex align-items-start">
                                        <i class="fas fa-info-circle me-3 mt-1 fs-5"></i>
                                        <div>
                                            <strong class="d-block mb-1">Document Requirements</strong>
                                            Please upload clear, legible copies of your business documents.
                                            Supported formats: <strong>PDF, JPG, JPEG, PNG</strong> (Max 10MB each file)
                                        </div>
                                    </div>

                                    <div class="row g-4">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">
                                                Business Logo <span class="text-danger">*</span>
                                            </label>
                                            <div class="file-upload-area"
                                                onclick="document.getElementById('business_logo').click()">
                                                <div class="file-preview mb-2">
                                                    <img id="logoPreview"
                                                        src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='48' height='48' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='1.5'%3E%3Crect x='2' y='3' width='20' height='14' rx='2'/%3E%3Cline x1='8' y1='21' x2='16' y2='21'/%3E%3Cline x1='12' y1='17' x2='12' y2='21'/%3E%3C/svg%3E"
                                                        alt="Upload Logo">
                                                </div>
                                                <div class="upload-icon">
                                                    <i class="fas fa-cloud-upload-alt text-secondary"></i>
                                                    <span class="d-block small">Click or drag to upload</span>
                                                </div>
                                                <div class="file-info" id="logoInfo"></div>
                                            </div>
                                            <input type="file" name="business_logo" id="business_logo"
                                                class="d-none @error('business_logo') is-invalid @enderror"
                                                accept=".pdf,image/jpeg,image/png,image/jpg"
                                                onchange="previewFile(this, 'logoPreview', 'logoInfo')">
                                            @error('business_logo')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">
                                                Registration Certificate <span class="text-danger">*</span>
                                            </label>
                                            <div class="file-upload-area"
                                                onclick="document.getElementById('registration').click()">
                                                <div class="file-preview mb-2">
                                                    <img id="regPreview"
                                                        src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='48' height='48' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='1.5'%3E%3Cpath d='M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z'/%3E%3Cpolyline points='14 2 14 8 20 8'/%3E%3C/svg%3E"
                                                        alt="Upload Certificate">
                                                </div>
                                                <div class="upload-icon">
                                                    <i class="fas fa-cloud-upload-alt text-secondary"></i>
                                                    <span class="d-block small">Click or drag to upload</span>
                                                </div>
                                                <div class="file-info" id="regInfo"></div>
                                            </div>
                                            <input type="file" name="registration" id="registration"
                                                class="d-none @error('registration') is-invalid @enderror"
                                                accept=".pdf,image/jpeg,image/png,image/jpg"
                                                onchange="previewFile(this, 'regPreview', 'regInfo')">
                                            @error('registration')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">
                                                PAN / Tax Certificate <span class="text-danger">*</span>
                                            </label>
                                            <div class="file-upload-area"
                                                onclick="document.getElementById('pan').click()">
                                                <div class="file-preview mb-2">
                                                    <img id="panPreview"
                                                        src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='48' height='48' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='1.5'%3E%3Cpath d='M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z'/%3E%3Cpolyline points='14 2 14 8 20 8'/%3E%3Cline x1='16' y1='13' x2='8' y2='13'/%3E%3Cline x1='16' y1='17' x2='8' y2='17'/%3E%3C/svg%3E"
                                                        alt="Upload PAN">
                                                </div>
                                                <div class="upload-icon">
                                                    <i class="fas fa-cloud-upload-alt text-secondary"></i>
                                                    <span class="d-block small">Click or drag to upload</span>
                                                </div>
                                                <div class="file-info" id="panInfo"></div>
                                            </div>
                                            <input type="file" name="pan" id="pan"
                                                class="d-none @error('pan') is-invalid @enderror"
                                                accept=".pdf,image/jpeg,image/png,image/jpg"
                                                onchange="previewFile(this, 'panPreview', 'panInfo')">
                                            @error('pan')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Step 3: Security & Agreement --}}
                                <div id="step3" class="form-step d-none">
                                    <div class="step-header d-flex align-items-center mb-4 pb-2 border-bottom">
                                        <div class="step-icon me-3">
                                            <i class="fas fa-lock fa-2x text-primary"></i>
                                        </div>
                                        <h2 class="h4 fw-bold mb-0">Security & Agreement</h2>
                                    </div>

                                    <div class="row g-4">
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">
                                                Email Address <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-envelope text-secondary"></i>
                                                </span>
                                                <input type="email" name="email"
                                                    class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror"
                                                    value="{{ old('email') }}" placeholder="contact@yourcompany.com">
                                            </div>
                                            <div class="form-text">This will be your login email for accessing the agent
                                                portal</div>
                                            @error('email')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                Password <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-key text-secondary"></i>
                                                </span>
                                                <input type="password" name="password" id="password"
                                                    class="form-control border-start-0 @error('password') is-invalid @enderror"
                                                    placeholder="Create a strong password">
                                                <button class="btn btn-outline-secondary password-toggle" type="button"
                                                    onclick="togglePassword('password', this)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <div class="password-strength mt-2" id="passwordStrength"></div>
                                            @error('password')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                Confirm Password <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-check-double text-secondary"></i>
                                                </span>
                                                <input type="password" name="password_confirmation"
                                                    id="password_confirmation" class="form-control border-start-0"
                                                    placeholder="Confirm your password">
                                                <button class="btn btn-outline-secondary password-toggle" type="button"
                                                    onclick="togglePassword('password_confirmation', this)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <span class="input-group-text bg-light" id="password-match-icon">
                                                    <i class="fas fa-question-circle text-muted"></i>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="requirement-list p-3 bg-light rounded-3">
                                                <h6 class="mb-3 fw-semibold">
                                                    <i class="fas fa-shield-alt me-2 text-primary"></i>Password
                                                    Requirements:
                                                </h6>
                                                <div class="row g-2">
                                                    <div class="col-md-4">
                                                        <div class="requirement-item" id="req-length">
                                                            <i class="far fa-circle me-2"></i>
                                                            At least 6 characters
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="requirement-item" id="req-number">
                                                            <i class="far fa-circle me-2"></i>
                                                            Contains a number
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="requirement-item" id="req-letter">
                                                            <i class="far fa-circle me-2"></i>
                                                            Contains a letter
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 mt-3">
                                            <div class="form-check">
                                                <input class="form-check-input @error('terms') is-invalid @enderror"
                                                    type="checkbox" name="terms" id="terms" required>
                                                <label class="form-check-label" for="terms">
                                                    I agree to the <a href="{{ route('auth.terms') }}" target="_blank"
                                                        class="fw-semibold text-decoration-none">Terms & Conditions</a>
                                                    and <a href="#" class="fw-semibold text-decoration-none"
                                                        data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy
                                                        Policy</a>
                                                </label>
                                                @error('terms')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Navigation Buttons --}}
                                <div class="row mt-5 pt-3 g-3">
                                    <div class="col-6">
                                        <button type="button" id="prevBtn"
                                            class="btn btn-outline-secondary btn-nav w-100 py-2" style="display: none;"
                                            onclick="changeStep(-1)">
                                            <i class="fas fa-arrow-left me-2"></i> Previous
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button type="button" id="nextBtn" class="btn btn-primary btn-nav w-100 py-2"
                                            onclick="changeStep(1)">
                                            Next <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                        <button type="submit" id="submitBtn" class="btn btn-success btn-nav w-100 py-2"
                                            style="display: none;">
                                            <i class="fas fa-check-circle me-2"></i> Complete Registration
                                        </button>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Privacy Modal --}}
    <div class="modal fade" id="privacyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-0 bg-gradient-primary text-white">
                    <h5 class="modal-title fw-semibold">
                        <i class="fas fa-shield-alt me-2"></i>Privacy Policy
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-3">Your privacy matters to us. We collect and process your information for:</p>
                    <ul class="mb-3">
                        <li class="mb-2">✓ Agent registration and verification purposes</li>
                        <li class="mb-2">✓ Communication regarding your application status</li>
                        <li class="mb-2">✓ Legal and compliance requirements</li>
                    </ul>
                    <p class="mb-0 text-muted small">For any privacy concerns, please contact our support team.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-primary-custom px-4" data-bs-dismiss="modal">I
                        Understand</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 3;

        // Toggle password visibility
        function togglePassword(fieldId, button) {
            const field = document.getElementById(fieldId);
            const icon = button.querySelector('i');

            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Check password match in real-time
        function checkPasswordMatch() {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('password_confirmation');
            const matchIcon = document.getElementById('password-match-icon');

            if (!password || !confirmPassword || !matchIcon) return;

            const icon = matchIcon.querySelector('i');

            if (confirmPassword.value === '') {
                icon.className = 'fas fa-question-circle text-muted';
                matchIcon.style.backgroundColor = '#f9fafb';
                confirmPassword.classList.remove('is-invalid');
            } else if (password.value === confirmPassword.value) {
                icon.className = 'fas fa-check-circle text-success';
                matchIcon.style.backgroundColor = '#f0fff4';
                confirmPassword.classList.remove('is-invalid');
            } else {
                icon.className = 'fas fa-times-circle text-danger';
                matchIcon.style.backgroundColor = '#fff5f5';
                confirmPassword.classList.add('is-invalid');
            }
        }

        function updateStepUI() {
            // Hide all steps
            for (let i = 1; i <= totalSteps; i++) {
                const step = document.getElementById(`step${i}`);
                if (step) step.classList.add('d-none');
            }

            // Show current step
            const currentStepElement = document.getElementById(`step${currentStep}`);
            if (currentStepElement) currentStepElement.classList.remove('d-none');

            // Update step indicators
            for (let i = 1; i <= totalSteps; i++) {
                const indicator = document.getElementById(`step${i}Indicator`);
                if (indicator) {
                    indicator.classList.remove('active', 'completed');
                    const circle = indicator.querySelector('.step-circle');
                    const numberSpan = circle.querySelector('.step-number');
                    const checkIcon = circle.querySelector('.step-check');

                    if (i < currentStep) {
                        indicator.classList.add('completed');
                        if (numberSpan) numberSpan.style.display = 'none';
                        if (checkIcon) checkIcon.classList.remove('d-none');
                    } else if (i === currentStep) {
                        indicator.classList.add('active');
                        if (numberSpan) numberSpan.style.display = 'flex';
                        if (checkIcon) checkIcon.classList.add('d-none');
                    } else {
                        if (numberSpan) numberSpan.style.display = 'flex';
                        if (checkIcon) checkIcon.classList.add('d-none');
                    }
                }
            }

            // Update progress bar
            const progressPercent = ((currentStep - 1) / (totalSteps - 1)) * 100;
            const progressFill = document.getElementById('stepProgress');
            if (progressFill) progressFill.style.width = `${progressPercent}%`;

            // Update navigation buttons
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitBtn');

            if (currentStep === 1) {
                if (prevBtn) prevBtn.style.display = 'none';
                if (nextBtn) nextBtn.style.display = 'flex';
                if (submitBtn) submitBtn.style.display = 'none';
            } else if (currentStep === totalSteps) {
                if (prevBtn) prevBtn.style.display = 'flex';
                if (nextBtn) nextBtn.style.display = 'none';
                if (submitBtn) submitBtn.style.display = 'flex';
            } else {
                if (prevBtn) prevBtn.style.display = 'flex';
                if (nextBtn) nextBtn.style.display = 'flex';
                if (submitBtn) submitBtn.style.display = 'none';
            }
        }

        function validateStep(step) {
            const form = document.getElementById('registrationForm');
            let isValid = true;
            let firstInvalidField = null;

            if (step === 1) {
                const fields = ['business_name', 'owner_name', 'name', 'contact', 'address'];
                for (let field of fields) {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input && !input.value.trim()) {
                        input.classList.add('is-invalid');
                        isValid = false;
                        if (!firstInvalidField) firstInvalidField = input;
                    } else if (input) {
                        input.classList.remove('is-invalid');
                    }
                }

                if (!isValid) {
                    showErrorToast('Please fill in all required fields in Personal Information');
                    if (firstInvalidField) firstInvalidField.focus();
                }
            } else if (step === 2) {
                const fields = ['business_logo', 'registration', 'pan'];
                for (let field of fields) {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input && (!input.files || input.files.length === 0)) {
                        input.classList.add('is-invalid');
                        isValid = false;
                        if (!firstInvalidField) firstInvalidField = input;
                    } else if (input) {
                        input.classList.remove('is-invalid');
                    }
                }

                if (!isValid) {
                    showErrorToast('Please upload all required business documents');
                }
            } else if (step === 3) {
                const email = form.querySelector('[name="email"]');
                const password = form.querySelector('[name="password"]');
                const confirmPassword = form.querySelector('[name="password_confirmation"]');
                const terms = form.querySelector('[name="terms"]');

                if (!email.value.trim()) {
                    email.classList.add('is-invalid');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = email;
                    showErrorToast('Please enter your email address');
                } else {
                    email.classList.remove('is-invalid');
                }

                if (!password.value) {
                    password.classList.add('is-invalid');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = password;
                    showErrorToast('Please create a password');
                } else if (password.value.length < 6) {
                    password.classList.add('is-invalid');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = password;
                    showErrorToast('Password must be at least 6 characters');
                } else {
                    password.classList.remove('is-invalid');
                }

                if (password.value !== confirmPassword.value) {
                    confirmPassword.classList.add('is-invalid');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = confirmPassword;
                    showErrorToast('Passwords do not match');
                } else if (confirmPassword.value !== '') {
                    confirmPassword.classList.remove('is-invalid');
                }

                if (!terms.checked) {
                    terms.classList.add('is-invalid');
                    isValid = false;
                    showErrorToast('Please accept the Terms & Conditions');
                } else {
                    terms.classList.remove('is-invalid');
                }
            }

            return isValid;
        }

        function showErrorToast(message) {
            alert(message);
        }

        function changeStep(direction) {
            if (direction === 1 && !validateStep(currentStep)) {
                return;
            }

            const newStep = currentStep + direction;
            if (newStep >= 1 && newStep <= totalSteps) {
                currentStep = newStep;
                updateStepUI();

                const formBody = document.querySelector('.form-card-body');
                if (formBody) {
                    formBody.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        }

        // File preview function
        function previewFile(input, previewId, infoId) {
            const preview = document.getElementById(previewId);
            const infoDiv = document.getElementById(infoId);

            if (input.files && input.files[0]) {
                const file = input.files[0];
                const fileSize = (file.size / 1024 / 1024).toFixed(2);

                if (infoDiv) {
                    infoDiv.innerHTML =
                        `<i class="fas fa-check-circle text-success me-1"></i> ${file.name} (${fileSize} MB)`;
                }

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } else if (file.type === 'application/pdf') {
                    preview.src =
                        "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='48' height='48' viewBox='0 0 24 24' fill='none' stroke='%2310b981' stroke-width='1.5'%3E%3Cpath d='M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z'/%3E%3Cpolyline points='14 2 14 8 20 8'/%3E%3Cline x1='16' y1='13' x2='8' y2='13'/%3E%3Cline x1='16' y1='17' x2='8' y2='17'/%3E%3C/svg%3E";
                }

                input.classList.remove('is-invalid');
            }
        }

        // Password strength checker
        const passwordInput = document.getElementById('password');
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const strengthBar = document.getElementById('passwordStrength');

                const hasLength = password.length >= 6;
                const hasNumber = /\d/.test(password);
                const hasLetter = /[a-zA-Z]/.test(password);

                // Update requirement indicators
                const reqLength = document.getElementById('req-length');
                const reqNumber = document.getElementById('req-number');
                const reqLetter = document.getElementById('req-letter');

                const updateRequirement = (element, isValid) => {
                    if (element) {
                        if (isValid) {
                            element.classList.add('valid');
                            element.querySelector('i').className = 'fas fa-check-circle me-2';
                        } else {
                            element.classList.remove('valid');
                            element.querySelector('i').className = 'far fa-circle me-2';
                        }
                    }
                };

                updateRequirement(reqLength, hasLength);
                updateRequirement(reqNumber, hasNumber);
                updateRequirement(reqLetter, hasLetter);

                // Calculate strength
                let strength = 0;
                if (hasLength) strength++;
                if (hasNumber) strength++;
                if (hasLetter) strength++;

                if (strengthBar) {
                    strengthBar.className = 'password-strength';
                    if (strength === 1) {
                        strengthBar.classList.add('strength-weak');
                    } else if (strength === 2) {
                        strengthBar.classList.add('strength-fair');
                    } else if (strength === 3) {
                        strengthBar.classList.add('strength-strong');
                    }
                }

                // Re-check password match when password changes
                checkPasswordMatch();
            });
        }

        // Password match checker with real-time updates
        const confirmPasswordInput = document.getElementById('password_confirmation');
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        }

        // Form submission
        const registrationForm = document.getElementById('registrationForm');
        const submitBtn = document.getElementById('submitBtn');

        if (registrationForm) {
            registrationForm.addEventListener('submit', function(e) {
                if (!validateStep(3)) {
                    e.preventDefault();
                } else if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Registering...';
                    submitBtn.classList.add('btn-loading');
                }
            });
        }

        // Initialize on DOM load
        document.addEventListener('DOMContentLoaded', function() {
            updateStepUI();
        });
    </script>

@endsection
