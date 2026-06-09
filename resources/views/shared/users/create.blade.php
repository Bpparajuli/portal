@php
    $__user = auth()->user();
    $__isAdmin = $__user->is_admin;
    $__isAgent = $__user->is_agent;
    $__isStaff = $__user->is_staff && !$__user->is_admin_staff;
    $__layout = $__isAdmin ? 'layouts.admin' : ($__isAgent ? 'layouts.agent' : 'layouts.staff');
    $__section = $__isAdmin ? 'admin-content' : ($__isAgent ? 'agent-content' : 'staff-content');
    $__routePrefix = $__isAdmin ? 'admin' : ($__isAgent ? 'agent' : 'staff');
@endphp

@extends($__layout)

@section('title', 'Create New User')

@section($__section)
<div class="container-lg py-4">
    <div class="rounded-4 p-4 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3" style="background:linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);box-shadow:0 4px 20px rgba(26,2,98,0.2);">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:rgba(255,255,255,0.15);">
                <i class="fas fa-user-plus fa-lg text-white"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-white">Create New User</h4>
                <p class="mb-0 small" style="color:rgba(255,255,255,0.7);">Add a new user to the system</p>
            </div>
        </div>
        <a href="{{ route($__routePrefix . '.users.index') }}" class="btn btn-sm px-3" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.2);">
            <i class="fas fa-arrow-left me-1"></i> Back to Users
        </a>
    </div>

    <form action="{{ route($__routePrefix . '.users.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('shared.users._form', ['user' => new \App\Models\User()])
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleSwitch = document.getElementById('statusToggle');
    const statusLabel = document.getElementById('statusLabel');
    if (toggleSwitch && statusLabel) {
        toggleSwitch.addEventListener('change', function() {
            const span = statusLabel.querySelector('span');
            span.className = 'fw-semibold ' + (this.checked ? 'text-success' : 'text-danger');
            span.innerHTML = '<i class="fas fa-circle me-1" style="font-size: 0.75rem;"></i> ' + (this.checked ? 'Active' : 'Inactive');
        });
    }

    const roleSelect = document.getElementById('role');
    if (roleSelect) {
        roleSelect.addEventListener('change', toggleParentField);
        toggleParentField();
    }

    const passwordField = document.getElementById('passwordField');
    const confirmPassword = document.getElementById('confirmPassword');
    if (passwordField && confirmPassword) {
        passwordField.addEventListener('input', checkPasswordMatch);
        confirmPassword.addEventListener('input', checkPasswordMatch);
    }
});

function toggleParentField() {
    const roleVal = document.getElementById('role')?.value;
    const c = document.getElementById('parentFieldContainer');
    if (c) c.style.display = roleVal === 'staff' ? 'block' : 'none';
}

function checkPasswordMatch() {
    const pass = document.getElementById('passwordField')?.value || '';
    const confirm = document.getElementById('confirmPassword')?.value || '';
    const icon = document.getElementById('passwordMatchIcon');
    if (!icon) return;
    if (!confirm) { icon.innerHTML = ''; return; }
    icon.innerHTML = pass === confirm
        ? '<i class="fas fa-check-circle text-success"></i>'
        : '<i class="fas fa-times-circle text-danger"></i>';
}

function togglePassword() {
    const field = document.getElementById('passwordField');
    const icon = document.getElementById('passwordIcon');
    if (!field || !icon) return;
    const isPassword = field.type === 'password';
    field.type = isPassword ? 'text' : 'password';
    icon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
}

function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!input.files || !input.files[0] || !preview) return;
    const file = input.files[0];
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; };
        reader.readAsDataURL(file);
    } else {
        preview.src = 'https://placehold.co/100?text=FILE';
    }
}
</script>
@endpush
