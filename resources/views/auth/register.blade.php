@extends('layouts.app')
@section('title', 'Become an IDEA Agent')

@section('content')
<div class="register-page py-5">
    <div class="row justify-content-center">
        <div class="col-lg-12">

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">

                {{-- Header --}}
                <div class="card-header bg-primary text-white text-center py-4">
                    <h2 class="fw-bold mb-1">Become an IDEA Agent</h2>
                    <p class="mb-0 small">Please fill the form below to register your agency</p>
                </div>

                {{-- Body --}}
                <div class="card-body p-5">
                    <form method="POST" action="{{ route('auth.register') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-4">

                            {{-- Business Name --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Business Name *</label>
                                <input type="text" name="business_name" class="form-control @error('business_name') is-invalid @enderror" value="{{ old('business_name') }}">
                                @error('business_name')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Owner Name --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Owner Name *</label>
                                <input type="text" name="owner_name" class="form-control @error('owner_name') is-invalid @enderror" value="{{ old('owner_name') }}">
                                @error('owner_name')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- User Name --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">User Name *</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                                @error('name')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Contact --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Contact *</label>
                                <input type="text" name="contact" class="form-control @error('contact') is-invalid @enderror" value="{{ old('contact') }}">
                                @error('contact')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Address --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Address *</label>
                                <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address') }}">
                                @error('address')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email *</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                                @error('email')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Password --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password *</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Confirm Password --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Confirm Password *</label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" class="form-control">
                                    <span id="password-match-icon" class="input-group-text"></span>
                                </div>
                            </div>

                            {{-- Business Logo --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Business Logo *<sup>pdf,jpg,jpeg,png only</sup></label>
                                <input type="file" name="business_logo" class="form-control @error('business_logo') is-invalid @enderror">
                                @error('business_logo')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Registration Certificate --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Registration Certificate *<sup>pdf,jpg,jpeg,png only</sup></label>
                                <input type="file" name="registration" class="form-control @error('registration') is-invalid @enderror">
                                @error('registration')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- PAN --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">PAN Certificate *<sup>pdf,jpg,jpeg,png only</sup></label>
                                <input type="file" name="pan" class="form-control @error('pan') is-invalid @enderror">
                                @error('pan')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Terms --}}
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" name="terms">
                                    <label class="form-check-label">
                                        I agree to the <a href="{{ route('auth.terms') }}" target="_blank">Terms & Conditions</a>
                                    </label>
                                </div>
                                @error('terms')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Submit --}}
                            <div class="col-12">
                                <button type="submit" class="btn btn-success w-100 py-2">
                                    Register
                                </button>
                            </div>

                            <div class="col-12 text-center">
                                <small>Already have an account?
                                    <a href="{{ route('login') }}">Login</a>
                                </small>
                            </div>

                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const password = document.querySelector('input[name="password"]');
        const confirmPassword = document.querySelector('input[name="password_confirmation"]');
        const matchIcon = document.getElementById('password-match-icon');

        if (!password || !confirmPassword || !matchIcon) return;

        confirmPassword.addEventListener('input', function() {
            if (confirmPassword.value === '') {
                matchIcon.textContent = '';
            } else if (confirmPassword.value === password.value) {
                matchIcon.textContent = '✓';
                matchIcon.style.color = 'green';
            } else {
                matchIcon.textContent = '✕';
                matchIcon.style.color = 'red';
            }
        });
    });

</script>
@endpush

@endsection
