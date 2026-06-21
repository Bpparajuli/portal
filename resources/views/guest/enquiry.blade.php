@extends('layouts.guest')

@section('title', 'Contact Us')

@push('styles')
<style>
    :root { --primary: #1a0262; --secondary: #820b5c; --accent: #820b5c; }
    * { font-family: 'Inter', sans-serif; }
    body { background: #f4f5f7; min-height: 100vh; }
    .gradient-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        padding: 60px 0 80px;
        position: relative;
    }
    .gradient-header h1 { color: #fff; font-weight: 800; font-size: 2.5rem; }
    .gradient-header p { color: rgba(255,255,255,0.8); font-size: 1.1rem; max-width: 600px; }
    .form-card {
        margin-top: -60px;
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }
    .form-card .card-body { padding: 2.5rem; }
    .form-control, .form-select {
        border-radius: 10px;
        padding: 0.7rem 1rem;
        border: 1.5px solid #e5e7eb;
        font-size: 0.95rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(26,2,98,0.1);
    }
    .form-control.is-invalid, .form-select.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 3px rgba(220,53,69,0.1);
    }
    .btn-submit {
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        color: #fff;
        border: none;
        padding: 0.85rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 1rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(26,2,98,0.3);
        color: #fff;
    }
    .contact-info-card {
        border: none;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
    .contact-info-card .icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .back-link { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 0.9rem; }
    .back-link:hover { color: #fff; }
</style>
@endpush

@section('content')
    <div class="gradient-header">
        <div class="container">
            <a href="{{ route('home') }}" class="back-link mb-3 d-inline-block">
                <i class="fas fa-arrow-left me-2"></i>Back to Home
            </a>
            <h1>Get in Touch</h1>
            <p>Have a question or want to learn more? Send us a message and we'll get back to you shortly.</p>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card form-card" data-aos="fade-up">
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('guest.enquiries.store') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                                        class="form-control @error('name') is-invalid @enderror"
                                        placeholder="John Doe" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                                        class="form-control @error('email') is-invalid @enderror"
                                        placeholder="john@example.com" required>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label fw-semibold">Phone Number</label>
                                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                                        class="form-control @error('phone') is-invalid @enderror"
                                        placeholder="+1 234 567 890">
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="subject" class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                                    <input type="text" id="subject" name="subject" value="{{ old('subject') }}"
                                        class="form-control @error('subject') is-invalid @enderror"
                                        placeholder="How can we help you?" required>
                                    @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                                    <textarea id="message" name="message" rows="5"
                                        class="form-control @error('message') is-invalid @enderror"
                                        placeholder="Tell us more about your inquiry..." required>{{ old('message') }}</textarea>
                                    @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-submit">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="contact-info-card p-4">
                    <h5 class="fw-bold mb-3">Contact Information</h5>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="icon-circle" style="background: rgba(26,2,98,0.1); color: var(--primary);">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <div class="fw-semibold small text-muted">Address</div>
                            <div class="fw-medium">123 Business Park</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="icon-circle" style="background: rgba(130,11,92,0.1); color: var(--accent);">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <div class="fw-semibold small text-muted">Email</div>
                            <div class="fw-medium">info@ideaconsultancy.com</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle" style="background: rgba(16,185,129,0.1); color: #10b981;">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div>
                            <div class="fw-semibold small text-muted">Phone</div>
                            <div class="fw-medium">+1234567890</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
