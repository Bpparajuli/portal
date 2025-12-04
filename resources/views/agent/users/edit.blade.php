@extends('layouts.agent')

@section('content')

<div class="py-4">

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white text-center py-3">
            <h3 class="fw-bold mb-0">Edit Profile</h3>
        </div>

        <div class="card-body">

            <form action="{{ route('agent.users.update', $user->slug) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">

                    <!-- LEFT COLUMN: Files / Logos -->
                    <div class="col-md-4">
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

                    </div>

                    <!-- RIGHT COLUMN: Basic Info + Password -->
                    <div class="col-md-8">
                        <!-- Locked Fields -->
                        <div class="row">
                            <div class="col-md-4 mb-3"> <label class="form-label">Business Name</label> <input type="text" class="form-control" value="{{ $user->business_name }}" disabled> </div>
                            <div class="col-md-4 mb-3"> <label class="form-label">Username</label> <input type="text" class="form-control" value="{{ $user->name }}" disabled> </div>
                            <div class="col-md-4 mb-3"> <label class="form-label">Owner Name</label> <input type="text" class="form-control" value="{{ $user->owner_name }}" disabled> </div>
                        </div>

                        {{-- Address --}}
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" value="{{ old('address', $user->address) }}">
                        </div>
                        {{-- Contact --}}
                        <div class="mb-3">
                            <label class="form-label">Contact</label>
                            <input type="text" class="form-control" name="contact" value="{{ old('contact', $user->contact) }}">
                        </div>
                        {{-- Email --}}
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
                        </div>

                        <div class="row">
                            <span class="text-muted">(Only if you want to change your password)</span>
                            {{-- New Password --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" id="passwordField">
                                    <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                                        <i id="passwordIcon" class="fa fa-eye"></i>
                                    </span>
                                </div>
                            </div>

                            {{-- Confirm Password --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password_confirmation" id="confirmPasswordField">
                                    <span class="input-group-text"><span id="password-match-icon"></span></span>
                                </div>
                            </div>
                        </div>
                        {{-- Submit --}}
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-success mt-3">💾 Update Profile</button>
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
    // ------------------ PASSWORD MATCH CHECK ------------------
    document.addEventListener('DOMContentLoaded', function() {
        const pass = document.getElementById('passwordField');
        const confirm = document.getElementById('confirmPasswordField');
        const icon = document.getElementById('password-match-icon');

        confirm.addEventListener('input', function() {
            if (!confirm.value) {
                icon.innerHTML = "";
            } else if (confirm.value === pass.value) {
                icon.innerHTML = "✅";
                icon.style.color = "green";
            } else {
                icon.innerHTML = "❌";
                icon.style.color = "red";
            }
        });
    });

    // ------------------ PASSWORD SHOW/HIDE ------------------
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

    // ------------------ UNIVERSAL FILE PREVIEW ------------------
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
            preview.src = "https://via.placeholder.com/120?text=FILE";
        }
    }

</script>
@endpush
