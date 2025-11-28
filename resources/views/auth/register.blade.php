@extends('layouts.app')
@section('title', 'Become an IDEA Agent')

@section('content')
<div class="register-page py-5">

    <div class="row justify-content-center">
        <div class="col-lg-10">
            {{-- Card --}}
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">

                {{-- Header --}}
                <div class="card-header bg-primary text-white text-center py-4">
                    <h2 class="fw-bold mb-1">Become an IDEA Agent</h2>
                    <p class="mb-0 small">Fill the form below to register your agency with us</p>
                </div>

                {{-- Body --}}
                <div class="card-body p-5">
                    {{-- Errors --}}
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('auth.register') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-4">

                            {{-- Business Name --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Business Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                    <input type="text" name="business_name" class="form-control" placeholder="Business Name" value="{{ old('business_name') }}" required>
                                </div>
                            </div>

                            {{-- Owner Name --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Owner Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                    <input type="text" name="owner_name" class="form-control" placeholder="Owner Name" value="{{ old('owner_name') }}" required>
                                </div>
                            </div>

                            {{-- Name --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">User Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" name="name" class="form-control" placeholder="Full Name" value="{{ old('name') }}" required>
                                </div>
                            </div>

                            {{-- Contact --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Contact <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" name="contact" class="form-control" placeholder="Contact Number" value="{{ old('contact') }}" required>
                                </div>
                            </div>

                            {{-- Address --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <input type="text" name="address" class="form-control" placeholder="Address" value="{{ old('address') }}" required>
                                </div>
                            </div>

                            {{-- Email --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="Email Address" value="{{ old('email') }}" required>
                                </div>
                            </div>

                            {{-- Password --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                                </div>
                            </div>

                            {{-- Confirm Password --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                    <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password" required>
                                    <span id="password-match-icon" class="input-group-text"></span>
                                </div>
                            </div>

                            {{-- Business Logo --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">Business Logo <small>( image must be less than 10mb )</small></label>
                                <input type="file" name="business_logo" class="form-control" accept="image/*">
                            </div>

                            {{-- Terms --}}
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="{{ route('auth.terms') }}" target="_blank">Terms & Conditions</a>
                                    </label>
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="col-12">
                                <button type="submit" class="btn btn-success w-100 py-2">
                                    <i class="fas fa-user-plus me-2"></i> Register
                                </button>
                            </div>

                            {{-- Login Link --}}
                            <div class="col-12 text-center">
                                <small class="text-muted">Already have an account? <a href="{{ route('login') }}">Login</a></small>
                            </div>

                        </div>
                    </form>
                </div>
            </div> {{-- /card --}}

        </div>
    </div>
</div>
@endsection
{{-- Password Match Script --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const password = document.querySelector('input[name="password"]');
        const confirmPassword = document.querySelector('input[name="password_confirmation"]');
        const matchIcon = document.getElementById('password-match-icon');

        confirmPassword.addEventListener('input', () => {
            if (confirmPassword.value === "") {
                matchIcon.innerHTML = "";
            } else if (confirmPassword.value === password.value) {
                matchIcon.innerHTML = "✅";
                matchIcon.style.color = "green";
            } else {
                matchIcon.innerHTML = "❌";
                matchIcon.style.color = "red";
            }
        });
    });

</script>
@endpush
