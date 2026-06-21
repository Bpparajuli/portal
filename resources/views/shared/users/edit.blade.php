@php
    $__user = auth()->user();
    $__isAdmin = $__user->is_admin;
    $__isAgent = $__user->is_agent;
    $__isStaff = $__user->is_staff && !$__user->is_admin_staff;
    $__layout = $__isAdmin ? 'layouts.admin' : ($__isAgent ? 'layouts.agent' : 'layouts.staff');
    $__section = $__isAdmin ? 'admin-content' : ($__isAgent ? 'agent-content' : 'staff-content');
    $__routePrefix = $__isAdmin ? 'admin' : ($__isAgent ? 'agent' : 'staff');
    $__isOwnProfile = $__user->id === $user->id;
@endphp

@extends($__layout)

@section('title', $__isAdmin ? 'Edit User - ' . ($user->business_name ?? $user->name) : 'Edit Profile - ' . ($user->business_name ?? $user->name))
@section('page-title', $__isAdmin ? 'Edit User' : 'Edit Profile')

@push('styles')
<style>
    .disabled-field { background-color: #f8f9fa; cursor: not-allowed; }
</style>
@endpush

@section($__section)
<div class="container-lg py-4">

    {{-- Header --}}
    <div class="rounded-4 p-4 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3"
        style="background:linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);box-shadow:0 4px 20px rgba(26,2,98,0.2);">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center"
                style="width:48px;height:48px;background:rgba(255,255,255,0.15);">
                <i class="fas {{ $__isAdmin ? 'fa-user-pen' : 'fa-user-edit' }} fa-lg text-white"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-white">{{ $__isAdmin ? 'Edit User' : 'Edit Profile' }}</h4>
                <p class="mb-0 small" style="color:rgba(255,255,255,0.7);">{{ $user->business_name ?? $user->name }}</p>
            </div>
        </div>
        <a href="{{ route($__routePrefix . '.users.show', $user->slug) }}" class="btn btn-sm px-3"
            style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.2);">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <form action="{{ route($__routePrefix . '.users.update', $user->slug) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @if($__isAdmin)
            {{-- Admin: full form --}}
            @include('shared.users._form')
        @else
            {{-- Agent/Staff: limited profile edit --}}
            <div class="row g-4">

                {{-- Left: Documents --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="px-4 py-3" style="background:linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);">
                            <h6 class="fw-bold mb-0 text-white"><i class="fas fa-cloud-upload-alt me-2"></i>Documents &amp; Logos</h6>
                            <small class="text-white-50">Update your business documents</small>
                        </div>
                        <div class="card-body p-4">
                            @php
                                $files = [
                                    ['key' => 'business_logo', 'label' => 'Business Logo', 'preview' => 'logoPreview', 'accept' => 'image/*', 'placeholder' => 'Logo'],
                                    ['key' => 'registration', 'label' => 'Registration Certificate', 'preview' => 'regPreview', 'accept' => '.pdf,.jpg,.jpeg,.png', 'placeholder' => 'PDF'],
                                    ['key' => 'pan', 'label' => 'PAN Certificate', 'preview' => 'panPreview', 'accept' => '.pdf,.jpg,.jpeg,.png', 'placeholder' => 'PDF'],
                                ];
                            @endphp

                            @foreach($files as $f)
                            <div class="border rounded-3 p-3 mb-3" style="background:#fafafa;transition:all 0.2s;"
                                onmouseover="this.style.borderColor='var(--primary)';this.style.boxShadow='0 2px 8px rgba(26,2,98,0.1)'"
                                onmouseout="this.style.borderColor='';this.style.boxShadow=''">
                                <label class="fw-semibold small mb-2" style="color:var(--primary);">
                                    <i class="fas fa-file me-1"></i> {{ $f['label'] }}
                                </label>
                                <div class="d-flex gap-3 align-items-start">
                                    <img id="{{ $f['preview'] }}"
                                        src="{{ $user->{$f['key']} ? Storage::url($user->{$f['key']}) : 'https://placehold.co/70x70?text=' . $f['placeholder'] }}"
                                        style="width:70px;height:70px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6;flex-shrink:0;">
                                    <div class="flex-grow-1">
                                        <input type="file" name="{{ $f['key'] }}" class="form-control form-control-sm"
                                            accept="{{ $f['accept'] }}" onchange="previewImage(this, '{{ $f['preview'] }}')">
                                        <small class="text-muted">Max 10MB</small>
                                        @error($f['key'])<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                            @endforeach

                            {{-- Agreement (read-only) --}}
                            <div class="border rounded-3 p-3" style="background:#fafafa;">
                                <label class="fw-semibold small mb-2" style="color:var(--primary);">
                                    <i class="fas fa-file-contract me-1"></i> Agreement Document
                                </label>
                                <div class="d-flex gap-3 align-items-start">
                                    @php
                                        $agFile = $user->agreement_file;
                                        $agExt = $agFile ? strtolower(pathinfo($agFile, PATHINFO_EXTENSION)) : null;
                                        $agIsImg = in_array($agExt, ['jpg','jpeg','png','gif','webp']);
                                    @endphp
                                    @if($agFile && $agIsImg)
                                        <img src="{{ Storage::url($agFile) }}" style="width:70px;height:70px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6;flex-shrink:0;">
                                    @elseif($agFile)
                                        <div style="width:70px;height:70px;border-radius:8px;border:1px solid #dee2e6;background:#f8f9fa;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                        </div>
                                    @else
                                        <img src="https://placehold.co/70x70?text=PDF" style="width:70px;height:70px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6;flex-shrink:0;">
                                    @endif
                                    <p class="text-success small mb-0">Read only</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: Basic Info + Security --}}
                <div class="col-lg-8">
                    {{-- Basic Info --}}
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                        <div class="px-4 py-3" style="background:linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);">
                            <h6 class="fw-bold mb-0 text-white"><i class="fas fa-user-circle me-2"></i>Basic Information</h6>
                            <small class="text-white-50">Your business and contact details</small>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Business Name</label>
                                    <input type="text" class="form-control disabled-field" value="{{ $user->business_name }}" disabled>
                                    <small class="text-muted">Contact admin to change</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Owner Name</label>
                                    <input type="text" class="form-control disabled-field" value="{{ $user->owner_name }}" disabled>
                                    <small class="text-muted">Contact admin to change</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Contact Person</label>
                                    <input type="text" class="form-control disabled-field" value="{{ $user->name }}" disabled>
                                    <small class="text-muted">Contact admin to change</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', $user->email) }}" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Contact Number</label>
                                    <input type="text" name="contact" class="form-control @error('contact') is-invalid @enderror"
                                        value="{{ old('contact', $user->contact) }}" placeholder="Enter contact number">
                                    @error('contact')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Address</label>
                                    <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                                        value="{{ old('address', $user->address) }}" placeholder="Your business address">
                                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Security --}}
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="px-4 py-3" style="background:linear-gradient(135deg, var(--warning) 0%, var(--warning-dark) 100%);">
                            <h6 class="fw-bold mb-0 text-white"><i class="fas fa-lock me-2"></i>Security Settings</h6>
                            <small class="text-white-50">Update your password (optional)</small>
                        </div>
                        <div class="card-body p-4">
                            <div class="p-3 rounded-3 mb-4 d-flex align-items-center gap-3" style="background:var(--warning-soft);border-left:3px solid var(--warning);">
                                <i class="fas fa-info-circle" style="color:var(--warning-dark);"></i>
                                <small style="color:var(--warning-dark);">Leave password fields blank if you don't want to change your password.</small>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small" style="color:var(--warning-dark);">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-key" style="color:var(--warning);"></i></span>
                                        <input type="password" name="password" id="passwordField" class="form-control" placeholder="Enter new password">
                                        <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                                            <i id="passwordIcon" class="fas fa-eye"></i>
                                        </span>
                                    </div>
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small" style="color:var(--warning-dark);">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-check-circle" style="color:var(--warning);"></i></span>
                                        <input type="password" name="password_confirmation" id="confirmPasswordField" class="form-control" placeholder="Confirm new password">
                                        <span class="input-group-text"><span id="passwordMatchIcon"></span></span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                                <a href="{{ route($__routePrefix . '.users.show', $user->slug) }}" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn px-4 text-white" style="background:linear-gradient(135deg, var(--success) 0%, #059669 100%);border:none;">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($__isAdmin)
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
        roleSelect.addEventListener('change', function() { toggleParentField(); toggleAgentFeatures(); });
        toggleParentField();
        toggleAgentFeatures();
    }
    @endif

    const passwordField = document.getElementById('passwordField');
    const confirmField = document.getElementById('confirmPasswordField');
    const matchIcon = document.getElementById('passwordMatchIcon');

    function checkMatch() {
        if (!confirmField || !matchIcon) return;
        if (!confirmField.value) { matchIcon.innerHTML = ''; return; }
        matchIcon.innerHTML = (passwordField.value === confirmField.value)
            ? '<i class="fas fa-check-circle text-success"></i>'
            : '<i class="fas fa-times-circle text-danger"></i>';
    }

    if (passwordField && confirmField) {
        passwordField.addEventListener('input', checkMatch);
        confirmField.addEventListener('input', checkMatch);
    }
});

@if(!$__isAdmin)
function togglePassword() {
    const field = document.getElementById('passwordField');
    const icon = document.getElementById('passwordIcon');
    if (!field || !icon) return;
    const isPassword = field.type === 'password';
    field.type = isPassword ? 'text' : 'password';
    icon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
}
@endif

function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!input.files || !input.files[0] || !preview) return;
    const file = input.files[0];
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; };
        reader.readAsDataURL(file);
    } else {
        preview.src = 'https://placehold.co/70x70?text=FILE';
    }
}

@if($__isAdmin)
function toggleParentField() {
    const roleVal = document.getElementById('role')?.value;
    const c = document.getElementById('parentFieldContainer');
    if (c) c.style.display = roleVal === 'staff' ? 'block' : 'none';
}

function toggleAgentFeatures() {
    const roleVal = document.getElementById('role')?.value;
    const el = document.getElementById('agentFeaturesSection');
    if (el) el.style.display = roleVal === 'agent' ? 'block' : 'none';
}

function deleteAgreement(url, name) {
    Swal.fire({
        title: 'Delete Agreement?',
        text: `Remove agreement for ${name}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Delete',
    }).then(result => {
        if (result.isConfirmed) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            $.ajax({ url, type: 'POST', data: { _method: 'DELETE', _token: csrf },
                success: () => { Swal.fire('Deleted!', 'Agreement removed.', 'success').then(() => location.reload()); },
                error: () => { Swal.fire('Error!', 'Something went wrong.', 'error'); }
            });
        }
    });
}
@endif
</script>
@endpush
