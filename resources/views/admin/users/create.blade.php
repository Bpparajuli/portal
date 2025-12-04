@extends('layouts.admin')

@section('content')

<div class="py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white text-center py-4">
            <h3 class="fw-bold mb-1">Create New User</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">

                    <!-- LEFT COLUMN -->
                    <div class="col-md-4">
                        <h6 class="text-secondary bold mb-3">Allowed: Any file type — Max 10MB</h6>

                        <!-- Business Logo -->
                        <div class="d-flex gap-1 align-items-center mb-2">
                            <img id="logoPreview" src="https://placehold.co/400" class="rounded border shadow-sm" width="100" height="100">
                            <div>
                                <label class="fw-bold d-block mb-1">Business Logo <span class="text-danger">*</span></label>
                                <input type="file" name="business_logo" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.bmp,.tiff,.webp,image/*" onchange="previewImage(this, 'logoPreview')" required>
                            </div>
                        </div>
                        <hr>

                        <!-- Registration File -->
                        <div class="d-flex gap-1 align-items-center mb-2">
                            <img id="regPreview" src="https://placehold.co/400" class="rounded border shadow-sm" width="100" height="100">
                            <div>
                                <label class="fw-bold d-block mb-1">Registration File <span class="text-danger">*</span></label>
                                <input type="file" name="registration" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.bmp,.tiff,.webp,image/*" onchange="previewImage(this, 'regPreview')">
                            </div>
                        </div>
                        <hr>

                        <!-- PAN File -->
                        <div class="d-flex gap-1 align-items-center mb-2">
                            <img id="panPreview" src="https://placehold.co/400" class="rounded border shadow-sm" width="100" height="100">
                            <div>
                                <label class="fw-bold d-block mb-1">PAN Registration <span class="text-danger">*</span></label>
                                <input type="file" name="pan" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.bmp,.tiff,.webp,image/*" onchange="previewImage(this, 'panPreview')">
                            </div>
                        </div>
                        <hr>

                        <!-- Agreement File -->
                        <div class="d-flex gap-1 align-items-center mb-2">
                            <img id="agreementPreview" src="https://placehold.co/400" class="rounded border shadow-sm" width="100" height="100">
                            <div>
                                <label class="fw-bold d-block mb-1">Agreement File </label>
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
                                <label class="form-label">Business Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="business_name" value="{{ old('business_name') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Owner Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="owner_name" value="{{ old('owner_name') }}">
                            </div>
                        </div>

                        <!-- Username + Email -->
                        <div class="row mb-1">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">User Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                            </div>
                        </div>

                        <!-- Contact + Address -->
                        <div class="row mb-1">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="contact" value="{{ old('contact') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="address" value="{{ old('address') }}">
                            </div>
                        </div>

                        <!-- Role -->
                        <div class="row mb-1">
                            <div class="col-md-4 mb-3">
                                <label>Role <span class="text-danger">*</span></label>
                                <select name="role" class="form-control" required>
                                    <option value="">Select user type </option>
                                    <option value="admin">Admin</option>
                                    <option value="agent">Agent</option>
                                    <option value="staff">Staff</option>
                                    <option value="university">University</option>
                                    <option value="university">Student</option>
                                </select>
                            </div>

                            <!-- User Status -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" required>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <!-- Agreement Status -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Agreement Status <span class="text-danger">*</span></label>
                                <select name="agreement_status" class="form-control">
                                    <option value="not_uploaded">Not Uploaded</option>
                                    <option value="uploaded">Uploaded</option>
                                    <option value="verified">Verified</option>
                                </select>
                            </div>
                        </div>


                        <!-- Password -->
                        <div class="row mb-1">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" id="passwordField">
                                    <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                                        <i id="passwordIcon" class="fa fa-eye"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password_confirmation" id="confirmPassword">
                                    <span class="input-group-text">
                                        <span id="passwordMatchIcon"></span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button class="btn btn-success mt-3">➕ Create User</button>
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
        const confirmPassword = document.getElementById("confirmPassword");
        const icon = document.getElementById("passwordMatchIcon");

        if (!password.value || !confirmPassword.value) {
            icon.textContent = "";
            return;
        }

        icon.textContent = password.value === confirmPassword.value ? "✅" : "❌";
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
