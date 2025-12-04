@extends('layouts.admin')

@section('content')

<div class="py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white text-center py-4">
            <h3 class="fw-bold mb-1">Edit User</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.users.update', $user->slug) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">

                    <!-- LEFT COLUMN -->
                    <div class="col-md-4">
                        <h6 class="text-secondary bold mb-3">Allowed: Any file type — Max 10MB</h6>

                        <!-- Business Logo -->
                        <div class="d-flex gap-1 align-items-center mb-2">
                            <img id="logoPreview" src="{{ $user->business_logo ? Storage::url($user->business_logo) : 'https://placehold.co/100?text=Logo' }}" class="rounded border shadow-sm" width="100" height="100">
                            <div>
                                <label class="fw-bold d-block mb-1">Business Logo</label>
                                <input type="file" name="business_logo" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.bmp,.tiff,.webp,image/*" onchange="previewImage(this, 'logoPreview')">
                            </div>
                        </div>
                        <hr>

                        <!-- Registration File -->
                        <div class="d-flex gap-1 align-items-center mb-2">
                            <a href="{{ Storage::url($user->registration) }}" target="_blank">
                                <img id="regPreview" src="{{ $user->registration ? Storage::url($user->registration) : 'https://placehold.co/100?text=File'}}" class="rounded border shadow-sm" width="100" height="100">
                            </a>
                            <div>
                                <label class="fw-bold d-block mb-1">Registration File</label>
                                @if($user->registration)
                                @endif
                                <input type="file" name="registration" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.bmp,.tiff,.webp,image/*" onchange="previewImage(this, 'regPreview')">
                            </div>
                        </div>
                        <hr>

                        <!-- PAN File -->
                        <div class="d-flex gap-1 align-items-center mb-2">
                            <a href="{{ Storage::url($user->pan) }}" target="_blank">
                                <img id="panPreview" src="{{ $user->pan ? Storage::url($user->pan) : 'https://placehold.co/100?text=File'}}" class="rounded border shadow-sm" width="100" height="100">
                            </a>
                            <div>
                                <label class="fw-bold d-block mb-1">PAN Registration</label>
                                <input type="file" name="pan" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.bmp,.tiff,.webp,image/*" onchange="previewImage(this, 'panPreview')">
                            </div>
                        </div>
                        <hr>

                        <!-- Agreement File -->
                        <div class="d-flex gap-1 align-items-center mb-2">
                            <a href="{{ Storage::url($user->agreement_file) }}" target="_blank">
                                <img id="agreementPreview" src="{{ $user->agreement_file ? Storage::url($user->agreement_file) : 'https://placehold.co/100?text=File'}}" class="rounded border shadow-sm" width="100" height="100">
                            </a>
                            <div>
                                <label class="fw-bold d-block mb-1">Agreement File</label>
                                <input type="file" name="agreement_file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.bmp,.tiff,.webp,image/*" onchange="previewImage(this, 'agreementPreview')">
                            </div>
                        </div>
                        <hr>
                    </div>

                    <!-- RIGHT COLUMN -->
                    <div class="col-md-8">

                        <!-- Business + Owner -->
                        <div class="row mb-1">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Business Name</label>
                                <input type="text" class="form-control" name="business_name" value="{{ old('business_name', $user->business_name) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Owner Name</label>
                                <input type="text" class="form-control" name="owner_name" value="{{ old('owner_name', $user->owner_name) }}">
                            </div>
                        </div>

                        <!-- Username + Email -->
                        <div class="row mb-1">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">User Name</label>
                                <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
                            </div>
                        </div>

                        <!-- Contact + Address -->
                        <div class="row mb-1">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact</label>
                                <input type="text" class="form-control" name="contact" value="{{ old('contact', $user->contact) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" name="address" value="{{ old('address', $user->address) }}">
                            </div>
                        </div>

                        <!-- Role + Status + Agreement Status -->
                        <div class="row mb-1">
                            <div class="col-md-4 mb-3">
                                <label>Role</label>
                                <select name="role" class="form-control">
                                    <option value="">Select Role</option>
                                    <option value="admin" {{ $user->is_admin ? 'selected' : '' }}>Admin</option>
                                    <option value="agent" {{ $user->is_agent ? 'selected' : '' }}>Agent</option>
                                    <option value="staff" {{ $user->role === 'staff' ? 'selected' : '' }}>Staff</option>
                                    <option value="university" {{ $user->role === 'university' ? 'selected' : '' }}>University</option>
                                    <option value="student" {{ $user->role === 'student' ? 'selected' : '' }}>Student</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="1" {{ $user->active ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ !$user->active ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Agreement Status</label>
                                <select name="agreement_status" class="form-control">
                                    <option value="not_uploaded" {{ $user->agreement_status === 'not_uploaded' ? 'selected' : '' }}>Not Uploaded</option>
                                    <option value="uploaded" {{ $user->agreement_status === 'uploaded' ? 'selected' : '' }}>Uploaded</option>
                                    <option value="verified" {{ $user->agreement_status === 'verified' ? 'selected' : '' }}>Verified</option>
                                </select>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="row mb-1">
                            <span class="text-muted">(Only if you want to change the password)</span>
                            <div class="col-md-6 mb-3">
                                <label>New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" id="passwordField">
                                    <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                                        <i id="passwordIcon" class="fa fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password_confirmation" id="confirmPassword">
                                    <span class="input-group-text"><span id="passwordMatchIcon"></span></span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button class="btn btn-success mt-3">💾 Update User</button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // PASSWORD MATCH CHECK
    function checkPasswordMatch() {
        const password = document.getElementById("passwordField");
        const confirm = document.getElementById("confirmPassword");
        const icon = document.getElementById("passwordMatchIcon");

        if (!password.value || !confirm.value) {
            icon.textContent = "";
            return;
        }
        icon.textContent = password.value === confirm.value ? "✅" : "❌";
    }

    // TOGGLE PASSWORD VISIBILITY
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

    // UNIVERSAL FILE PREVIEW
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (!input.files || !input.files[0]) return;

        const file = input.files[0];
        const fileType = file.type;

        if (fileType.startsWith("image/")) {
            const reader = new FileReader();
            reader.onload = e => preview.src = e.target.result;
            reader.readAsDataURL(file);
        } else {
            preview.src = "https://via.placeholder.com/100?text=FILE";
        }
    }

    // EVENT LISTENERS
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("passwordField").addEventListener("input", checkPasswordMatch);
        document.getElementById("confirmPassword").addEventListener("input", checkPasswordMatch);
    });

</script>
@endpush
