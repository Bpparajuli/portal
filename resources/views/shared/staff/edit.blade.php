@extends('layouts.staff')

@section('title', 'Edit Staff Member - ' . $staff->name)
@section('page-title', 'Edit Staff Member')

@section('content')
<div class="container-lg py-4">

    <div class="rounded-4 p-4 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3"
        style="background:linear-gradient(135deg, #ffc107 0%, #e0a800 100%);box-shadow:0 4px 20px rgba(255,193,7,0.2);">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center"
                style="width:48px;height:48px;background:rgba(255,255,255,0.2);">
                <i class="fas fa-user-pen fa-lg text-white"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-white">Edit Staff Member</h4>
                <p class="mb-0 small" style="color:rgba(255,255,255,0.7);">{{ $staff->name }}</p>
            </div>
        </div>
        <a href="{{ route('agent.users.show', $agent->slug) }}" class="btn btn-sm px-3"
            style="background:rgba(255,255,255,0.2);color:#fff;border:1px solid rgba(255,255,255,0.3);">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="px-4 py-3" style="background:linear-gradient(135deg, #ffc107 0%, #e0a800 100%);">
                    <h6 class="fw-bold mb-0 text-white"><i class="fas fa-user-circle me-2"></i>Staff Information</h6>
                    <small class="text-white-50">Update staff details and permissions</small>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('agent.staff.update', $staff->slug) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold small">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    name="name" value="{{ old('name', $staff->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold small">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    name="email" value="{{ old('email', $staff->email) }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Contact Number</label>
                                <input type="text" class="form-control @error('contact') is-invalid @enderror"
                                    name="contact" value="{{ old('contact', $staff->contact) }}">
                                @error('contact')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Address</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror"
                                    name="address" value="{{ old('address', $staff->address) }}">
                                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold small d-block">Status</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="status" value="0">
                                    <input class="form-check-input" type="checkbox" name="status" id="statusToggle"
                                        value="1" {{ $staff->active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="statusToggle">
                                        <span id="statusBadge" class="badge {{ $staff->active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $staff->active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </label>
                                </div>
                                <small class="text-muted">Inactive staff cannot log in to the system</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">New Password <span class="text-muted">(Optional)</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-key text-warning"></i></span>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        name="password" id="passwordField" placeholder="Leave blank to keep current">
                                    <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                                        <i id="passwordIcon" class="fas fa-eye"></i>
                                    </span>
                                </div>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Confirm New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-check-circle text-warning"></i></span>
                                    <input type="password" class="form-control" name="password_confirmation" id="confirmPassword" placeholder="Confirm new password">
                                    <span class="input-group-text"><span id="passwordMatchIcon"></span></span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <a href="{{ route('agent.users.show', $agent->slug) }}" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn px-4 text-white"
                                style="background:linear-gradient(135deg, #ffc107 0%, #e0a800 100%);border:none;">
                                <i class="fas fa-save me-2"></i> Update Staff
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
const toggleSwitch = document.getElementById('statusToggle');
const statusBadge = document.getElementById('statusBadge');

if (toggleSwitch && statusBadge) {
    toggleSwitch.addEventListener('change', function() {
        if (this.checked) {
            statusBadge.className = 'badge bg-success';
            statusBadge.textContent = 'Active';
        } else {
            statusBadge.className = 'badge bg-danger';
            statusBadge.textContent = 'Inactive';
        }
    });
}

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
