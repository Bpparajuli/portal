@extends('layouts.admin')

@section('content')

<div class="container py-4">

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">
            <strong>Edit Profile</strong>
        </div>

        <div class="card-body">

            <form action="{{ route('admin.users.update', $user->business_name_slug) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-4">
                        <!-- LEFT COLUMN -->
                        <!-- Logo Preview + Upload -->
                        <div class=" text-center">
                            <div class="d-flex gap-4">
                                @if($user->business_logo)
                                <img id="logoPreview" src="{{ Storage::url($user->business_logo) }}" class="rounded border shadow-sm mb-2" width="100" height="100">
                                @else
                                <img id="logoPreview" src="https://via.placeholder.com/100?text=Logo" class="rounded border shadow-sm mb-2" width="100" height="100">
                                @endif
                                <label class="fw-bold d-block mb-2">Change/Upload Business Logo</label>
                            </div>
                            <input type="file" name="business_logo" class="form-control" accept="image/*" onchange="previewLogo(this)">
                        </div>
                        <hr class="my-4">

                        <div class="text-center">
                            <div class="d-flex gap-4">
                                @if($user->agreement_file)
                                <a href="{{ Storage::url($user->agreement_file) }}" target="_blank" class="btn btn-secondary p-2">
                                    📄 View Current Agreement
                                </a>
                                @else
                                <p class="text-muted mb-2"><em>No agreement uploaded yet.</em></p>
                                @endif
                                <label class="fw-bold d-block mb-2">Agreement File</label>
                            </div>

                            {{-- Status --}}
                            @if($user->agreement_status)
                            <p class="text-start mt-2">
                                <strong>Status:</strong>
                                @if($user->agreement_status === 'verified')
                                <span class="badge bg-success">Verified</span>
                                @elseif($user->agreement_status === 'uploaded')
                                <span class="badge bg-info">Uploaded</span>
                                @else
                                <span class="badge bg-secondary">Not Uploaded</span>
                                @endif
                            </p>
                            @endif
                            {{-- Upload New File --}}
                            <input type="file" name="agreement_file" class="form-control mt-2">
                            <small class="text-muted d-block mt-1">Allowed: any file — Max 10MB</small>
                            @if($user->agreement_file)
                            <button type="button" class="btn btn-danger btn-sm" onclick="if(confirm('Delete this agreement file?')) document.getElementById('deleteAgreementForm').submit();">
                                Delete File
                            </button>
                            @endif
                        </div>
                    </div>

                    <!-- RIGHT COLUMN -->
                    <div class="col-md-8">
                        <!-- Locked Fields -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Business Name</label>
                                <input type="text" class="form-control" name="business_name" value="{{ $user->business_name }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Owner Name</label>
                                <input type="text" class="form-control" name="owner_name" value="{{ $user->owner_name }}">
                            </div>
                        </div>
                        <!-- User name -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">User Name</label>
                                <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}">
                            </div>
                            <!-- Email -->
                            <div class="col-md-6 mb-3"> <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
                            </div>
                        </div>
                        <!-- Contact -->
                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact</label>
                                <input type="text" class="form-control" name="contact" value="{{ old('contact', $user->contact) }}">
                            </div>
                            <!-- Contact -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" name="address" value="{{ old('address', $user->address) }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Role *</label>
                                <select name="role" class="form-control" required>
                                    <option value="">Select Role</option>
                                    <option value="admin" {{ $user->is_admin ? 'selected' : '' }}>Admin</option>
                                    <option value="agent" {{ $user->is_agent ? 'selected' : '' }}>Agent</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status *</label>
                                <select name="status" class="form-control" required>
                                    <option value="">Select Status</option>
                                    <option value="1" {{ $user->active ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ !$user->active ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            {{-- Status Change (Admin Only) --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label ">Change Agreement Status</label>
                                <select name="agreement_status" class="form-control">
                                    <option value="not_uploaded" {{ $user->agreement_status == 'not_uploaded' ? 'selected' : '' }}>
                                        Not Uploaded
                                    </option>
                                    <option value="uploaded" {{ $user->agreement_status == 'uploaded' ? 'selected' : '' }}>
                                        Uploaded
                                    </option>
                                    <option value="verified" {{ $user->agreement_status == 'verified' ? 'selected' : '' }}>
                                        Verified
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- New Password -->
                        <div class="row">
                            <span class="text-muted text-small">(Only If you like to change your password)</span>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" id="passwordField">
                                    <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                                        <i id="passwordIcon" class="fa fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                            <!-- Confirm Password -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirmPasswordField" name="password_confirmation">
                                    <span class="input-group-text">
                                        <span id="password-match-icon"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-success mt-3">💾 Update User</button>
                    </div>
                </div>
            </form>

            @if($user->agreement_file)
            <form id="deleteAgreementForm" action="{{ route('admin.users.agreement.delete', $user->business_name) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
            @endif

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
            if (confirm.value === "") {
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
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            field.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }

    // ------------------ LOGO PREVIEW ------------------
    function previewLogo(input) {
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = e => document.getElementById('logoPreview').src = e.target.result;
            reader.readAsDataURL(input.files[0]);
        }
    }

</script>
@endpush
