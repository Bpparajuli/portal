@extends('layouts.staff')

@section('title', 'Create Staff Member')
@section('page-title', 'Create Staff Member')

@section('content')
<div class="container-lg py-4">

    <div class="rounded-4 p-4 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3"
        style="background:linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);box-shadow:0 4px 20px rgba(13,110,253,0.2);">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center"
                style="width:48px;height:48px;background:rgba(255,255,255,0.15);">
                <i class="fas fa-user-plus fa-lg text-white"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-white">Create Staff Member</h4>
                <p class="mb-0 small" style="color:rgba(255,255,255,0.7);">Add staff to help manage your students</p>
            </div>
        </div>
        <a href="{{ route('agent.users.show', $agent->slug) }}" class="btn btn-sm px-3"
            style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.2);">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="px-4 py-3" style="background:linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                    <h6 class="fw-bold mb-0 text-white"><i class="fas fa-user-circle me-2"></i>Staff Details</h6>
                    <small class="text-white-50">Enter the details of the new staff member</small>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('agent.staff.store') }}" method="POST">
                        @csrf

                        <div class="p-3 rounded-3 mb-4 d-flex align-items-center gap-3"
                            style="background:#e7f3ff;border-left:3px solid #0d6efd;">
                            <i class="fas fa-info-circle text-primary"></i>
                            <small class="text-primary">Staff members will have access to student management and daily operations.</small>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold small">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    name="name" value="{{ old('name') }}" placeholder="Enter staff name" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold small">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    name="email" value="{{ old('email') }}" placeholder="staff@example.com" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Contact Number</label>
                                <input type="text" class="form-control @error('contact') is-invalid @enderror"
                                    name="contact" value="{{ old('contact') }}" placeholder="Phone number">
                                @error('contact')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Address</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror"
                                    name="address" value="{{ old('address') }}" placeholder="Office address">
                                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-key text-primary"></i></span>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        name="password" id="passwordField" placeholder="Enter password" required>
                                    <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                                        <i id="passwordIcon" class="fas fa-eye"></i>
                                    </span>
                                </div>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-check-circle text-primary"></i></span>
                                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                                        name="password_confirmation" id="confirmPassword" placeholder="Confirm password" required>
                                    <span class="input-group-text"><span id="passwordMatchIcon"></span></span>
                                </div>
                                @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <a href="{{ route('agent.users.show', $agent->slug) }}" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn px-4 text-white"
                                style="background:linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);border:none;">
                                <i class="fas fa-save me-2"></i> Create Staff
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePassword() {
    const field = document.getElementById('passwordField');
    const icon = document.getElementById('passwordIcon');
    if (!field || !icon) return;
    field.type = field.type === 'password' ? 'text' : 'password';
    icon.className = field.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

const passwordField = document.getElementById('passwordField');
const confirmPassword = document.getElementById('confirmPassword');
const matchIcon = document.getElementById('passwordMatchIcon');

function checkMatch() {
    if (!passwordField || !confirmPassword || !matchIcon) return;
    if (!passwordField.value || !confirmPassword.value) { matchIcon.innerHTML = ''; return; }
    matchIcon.innerHTML = passwordField.value === confirmPassword.value
        ? '<i class="fas fa-check-circle text-success"></i>'
        : '<i class="fas fa-times-circle text-danger"></i>';
}

if (passwordField && confirmPassword) {
    passwordField.addEventListener('input', checkMatch);
    confirmPassword.addEventListener('input', checkMatch);
}
</script>
@endpush
