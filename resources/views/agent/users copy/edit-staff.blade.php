{{-- @extends('layouts.agent')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">Edit Staff Member</h4>
                    <small>Update staff information</small>
                </div>

                <div class="card-body">
                    <form action="{{ route('agent.staff.update', $staff->slug) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $staff->name) }}" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $staff->email) }}" required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Number</label>
                                <input type="text" class="form-control @error('contact') is-invalid @enderror" name="contact" value="{{ old('contact', $staff->contact) }}">
                                @error('contact')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror" name="address" value="{{ old('address', $staff->address) }}">
                                @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label d-block">Status</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="status" value="0">
                                    <input class="form-check-input" type="checkbox" name="status" id="statusToggle" value="1" {{ $staff->active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="statusToggle">
                                        <span class="badge {{ $staff->active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $staff->active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </label>
                                </div>
                                <small class="text-muted">Inactive staff cannot log in to the system</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password (Optional)</label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="passwordField" placeholder="Leave blank to keep current">
                                    <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                                        <i id="passwordIcon" class="fas fa-eye"></i>
                                    </span>
                                </div>
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password_confirmation" id="confirmPassword" placeholder="Confirm new password">
                                    <span class="input-group-text">
                                        <span id="passwordMatchIcon"></span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('agent.users.show', $agent->slug) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Staff
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Status toggle label update
    const toggleSwitch = document.getElementById('statusToggle');
    const statusLabel = toggleSwitch ? .nextElementSibling ? .querySelector('span');

    if (toggleSwitch && statusLabel) {
        toggleSwitch.addEventListener('change', function() {
            if (this.checked) {
                statusLabel.className = 'badge bg-success';
                statusLabel.textContent = 'Active';
            } else {
                statusLabel.className = 'badge bg-danger';
                statusLabel.textContent = 'Inactive';
            }
        });
    }

    function togglePassword() {
        const field = document.getElementById('passwordField');
        const icon = document.getElementById('passwordIcon');

        if (field.type === "password") {
            field.type = "text";
            icon.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            field.type = "password";
            icon.classList.replace("fa-eye-slash", "fa-eye");
        }
    }

    // Password match checking
    const passwordField = document.getElementById("passwordField");
    const confirmPassword = document.getElementById("confirmPassword");
    const matchIcon = document.getElementById("passwordMatchIcon");

    function checkPasswordMatch() {
        if (!confirmPassword.value) {
            matchIcon.textContent = "";
            return;
        }
        if (passwordField.value === confirmPassword.value) {
            matchIcon.textContent = "✅";
        } else {
            matchIcon.textContent = "❌";
        }
    }

    if (passwordField && confirmPassword) {
        passwordField.addEventListener("input", checkPasswordMatch);
        confirmPassword.addEventListener("input", checkPasswordMatch);
    }

</script>
@endpush
@endsection --}}
