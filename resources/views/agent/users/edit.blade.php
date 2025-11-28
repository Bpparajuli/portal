@extends('layouts.agent')

@section('content')

<div class="container py-4">

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">
            <strong>Edit Profile</strong>
        </div>

        <div class="card-body">

            <form action="{{ route('agent.users.update', $user->business_name_slug) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Locked Fields -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Business Name</label>
                            <input type="text" class="form-control" value="{{ $user->business_name }}" disabled>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="{{ $user->name }}" disabled>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Owner Name</label>
                            <input type="text" class="form-control" value="{{ $user->owner_name }}" disabled>
                        </div>
                    </div>
                    <hr>
                    <!-- LEFT COLUMN -->
                    <div class="col-md-4">

                        <!-- Logo Preview + Upload -->
                        <div class="text-center">
                            <label class="fw-bold d-block mb-2">Business Logo</label>

                            @if($user->business_logo)
                            <img id="logoPreview" src="{{ Storage::url($user->business_logo) }}" class="rounded border shadow-sm mb-2" width="120" height="120">
                            @else
                            <img id="logoPreview" src="https://via.placeholder.com/120?text=Logo" class="rounded border shadow-sm mb-2" width="120" height="120">
                            @endif

                            <input type="file" name="business_logo" class="form-control" accept="image/*" onchange="previewLogo(this)">
                        </div>
                        <hr class="my-4">

                        <!-- Agreement File (Agent Upload Only) -->
                        <div class="text-center">
                            <label class="fw-bold d-block mb-2">Agreement File</label>
                            {{-- If file exists, show link --}}
                            @if($user->agreement_file)
                            <a href="{{ Storage::url($user->agreement_file) }}" target="_blank" class="btn btn-secondary m-2 p-2">
                                <i class="fa fa-file m-2 " style="font-size: 30px"> </i> View Agreement
                            </a>
                            @else
                            <p class="text-muted"><em>No agreement uploaded yet.</em></p>
                            @endif

                            {{-- Upload Field --}}
                            <input type="file" name="agreement_file" class="form-control">

                            <small class="text-muted d-block mt-1">
                                Allowed: any file — Max 10MB
                            </small>
                        </div>


                    </div>

                    <!-- RIGHT COLUMN -->
                    <div class="col-md-8">

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
                        </div>

                        <!-- Contact -->
                        <div class="mb-3">
                            <label class="form-label">Contact</label>
                            <input type="text" class="form-control" name="contact" value="{{ old('contact', $user->contact) }}">
                        </div>

                        <!-- Address -->
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" value="{{ old('address', $user->address) }}">
                        </div>

                        <!-- New Password -->
                        <div class="mb-3">
                            <label class="form-label">New Password <span class="text-muted text-small">(Only If you like to change your password)</span> </label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" id="passwordField">
                                <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                                    <i id="passwordIcon" class="fa fa-eye"></i>
                                </span>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirmPasswordField" name="password_confirmation">
                                <span class="input-group-text">
                                    <span id="password-match-icon"></span>
                                </span>
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
