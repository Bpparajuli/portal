@extends('layouts.admin')

@section('content')

    <div class="container-fluid p-1">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 20px;">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            @if ($user->business_logo)
                                <img src="{{ Storage::url($user->business_logo) }}" alt="Logo" width="150"
                                    height="150" class="rounded-3 border shadow-sm object-fit-cover mb-3">
                            @else
                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                                    style="width: 150px; height: 150px;">
                                    <i class="fas fa-user-edit fa-2x text-primary"></i>
                                </div>
                            @endif
                            <h5 class="fw-bold mb-1">Edit User</h5>
                            <p class="text-muted small mb-0">{{ old('business_name', $user->business_name) }}</p>
                        </div>

                        <hr class="my-3">

                        <div class="nav flex-column nav-pills" id="userFormTabs" role="tablist">
                            <button class="nav-link active d-flex align-items-center gap-3 rounded-3 mb-2"
                                id="basic-info-tab" data-bs-toggle="pill" data-bs-target="#basic-info" type="button"
                                role="tab">
                                <i class="fas fa-user-circle fa-fw"></i>
                                <span>Basic Information</span>
                            </button>
                            <button class="nav-link d-flex align-items-center gap-3 rounded-3 mb-2" id="documents-tab"
                                data-bs-toggle="pill" data-bs-target="#documents" type="button" role="tab">
                                <i class="fas fa-file-alt fa-fw"></i>
                                <span>Documents & Files</span>
                            </button>
                            <button class="nav-link d-flex align-items-center gap-3 rounded-3 mb-2" id="security-tab"
                                data-bs-toggle="pill" data-bs-target="#security" type="button" role="tab">
                                <i class="fas fa-lock fa-fw"></i>
                                <span>Security Settings</span>
                            </button>
                        </div>

                        <hr class="my-3">

                        <div class="mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted">Status</span>
                                <span class="badge {{ $user->active ? 'bg-success' : 'bg-secondary' }} rounded-pill">
                                    {{ $user->active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted">Role</span>
                                <span class="badge bg-info rounded-pill">{{ ucfirst($user->role) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted">Agreement</span>
                                <span
                                    class="badge bg-warning rounded-pill">{{ str_replace('_', ' ', $user->agreement_status) }}</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center my-1">
                                <span class="small text-muted">
                                    Staff of:
                                </span>
                                <span>
                                    {{ $user->parent ? $user->parent->business_name : 'N/A' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Form Content -->
            <div class="col-lg-9">
                <form action="{{ route('admin.users.update', $user->slug) }}" method="POST" enctype="multipart/form-data"
                    id="userForm">
                    @csrf
                    @method('PUT')

                    <div class="tab-content">
                        <!-- Basic Information Tab -->
                        <div class="tab-pane fade show active" id="basic-info" role="tabpanel">
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-0">
                                    <div class="card-header bg-white border-0 pt-4 px-4">
                                        <h5 class="fw-bold mb-0">Basic Information</h5>
                                        <p class="text-muted small mb-0">Update user personal and business details</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold d-block">Status</label>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="status" value="0">
                                                <input class="form-check-input" type="checkbox" name="status"
                                                    id="statusToggle" value="1" {{ $user->active ? 'checked' : '' }}
                                                    style="width: 3rem; height: 1.5rem; cursor: pointer;">
                                                <label class="form-check-label fw-medium ms-2" for="statusToggle"
                                                    id="statusLabel">
                                                    <span
                                                        class="fw-semibold {{ $user->active ? 'text-success' : 'text-danger' }}">
                                                        <i class="fas fa-circle me-1" style="font-size: 0.75rem;"></i>
                                                        {{ $user->active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Business Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fas fa-building"></i></span>
                                                <input type="text" class="form-control border-start-0 ps-0"
                                                    name="business_name"
                                                    value="{{ old('business_name', $user->business_name) }}"
                                                    placeholder="Enter business name">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Owner Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fas fa-user-tie"></i></span>
                                                <input type="text" class="form-control border-start-0 ps-0"
                                                    name="owner_name" value="{{ old('owner_name', $user->owner_name) }}"
                                                    placeholder="Enter owner name">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">User Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fas fa-user"></i></span>
                                                <input type="text" class="form-control border-start-0 ps-0"
                                                    name="name" value="{{ old('name', $user->name) }}"
                                                    placeholder="Enter username">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Email <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fas fa-envelope"></i></span>
                                                <input type="email" class="form-control border-start-0 ps-0"
                                                    name="email" value="{{ old('email', $user->email) }}" required
                                                    placeholder="Enter email address">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Contact Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fas fa-phone"></i></span>
                                                <input type="text" class="form-control border-start-0 ps-0"
                                                    name="contact" value="{{ old('contact', $user->contact) }}"
                                                    placeholder="Enter contact number">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fas fa-map-marker-alt"></i></span>
                                                <input type="text" class="form-control border-start-0 ps-0"
                                                    name="address" value="{{ old('address', $user->address) }}"
                                                    placeholder="Enter address">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Role <span
                                                    class="text-danger">*</span></label>
                                            <select name="role" id="role" class="form-select" required>
                                                <option value="">Select user type</option>
                                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>
                                                    Admin</option>
                                                <option value="agent" {{ $user->role === 'agent' ? 'selected' : '' }}>
                                                    Agent</option>
                                                <option value="staff" {{ $user->role === 'staff' ? 'selected' : '' }}>
                                                    Staff</option>
                                                <option value="university"
                                                    {{ $user->role === 'university' ? 'selected' : '' }}>University
                                                </option>
                                                <option value="student" {{ $user->role === 'student' ? 'selected' : '' }}>
                                                    Student</option>
                                            </select>
                                        </div>

                                        <!-- Parent Field Container -->
                                        <div id="parentFieldContainer" class="col-md-4"
                                            style="display: {{ $user->role === 'staff' ? 'block' : 'none' }};">
                                            <label class="form-label fw-semibold">Parent <span
                                                    class="text-danger">*</span></label>
                                            <select name="parent_id" id="parent_id" class="form-select">
                                                <option value="">Select parent (Admin/Agent)</option>
                                                @if (isset($parents) && $parents->count())
                                                    @foreach ($parents as $parent)
                                                        <option value="{{ $parent->id }}"
                                                            {{ old('parent_id', $user->parent_id) == $parent->id ? 'selected' : '' }}>
                                                            {{ $parent->business_name ?? $parent->name }}
                                                            ({{ ucfirst($parent->role) }})
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <small class="text-muted">Select the parent (Admin/Agent)</small>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Agreement Status</label>
                                            <select name="agreement_status" class="form-select">
                                                <option value="not_uploaded"
                                                    {{ $user->agreement_status === 'not_uploaded' ? 'selected' : '' }}>Not
                                                    Uploaded</option>
                                                <option value="uploaded"
                                                    {{ $user->agreement_status === 'uploaded' ? 'selected' : '' }}>Uploaded
                                                </option>
                                                <option value="verified"
                                                    {{ $user->agreement_status === 'verified' ? 'selected' : '' }}>Verified
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-0 pb-4 px-4">
                                    <div class="col-md-12 d-flex justify-content-end align-items-center gap-3">
                                        <button type="button" class="btn btn-primary next-tab" data-next="documents">
                                            Next <i class="fa-solid fa-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Documents Tab -->
                        <div class="tab-pane fade" id="documents" role="tabpanel">
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 pt-4 px-4">
                                    <h5 class="fw-bold mb-0">Documents & Files</h5>
                                    <p class="text-muted small mb-0">Manage business documents and registrations</p>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-4">
                                        <!-- Business Logo -->
                                        <div class="col-md-6">
                                            <div class="border rounded-4 p-3 h-100">
                                                <label class="form-label fw-semibold mb-3">Business Logo</label>
                                                <div class="d-flex align-items-start gap-3">
                                                    <div class="flex-shrink-0">
                                                        <img id="logoPreview"
                                                            src="{{ $user->business_logo ? Storage::url($user->business_logo) : 'https://placehold.co/100?text=Logo' }}"
                                                            class="rounded-3 border shadow-sm object-fit-cover"
                                                            width="80" height="80" style="object-fit: cover;">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <input type="file" name="business_logo" class="form-control"
                                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.bmp,.tiff,.webp,image/*"
                                                            onchange="previewImage(this, 'logoPreview')">
                                                        <small class="text-muted">Allowed: Any file type — Max 10MB</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Registration File -->
                                        <div class="col-md-6">
                                            <div class="border rounded-4 p-3 h-100">
                                                <label class="form-label fw-semibold mb-3">Registration File</label>
                                                <div class="d-flex align-items-start gap-3">
                                                    <div class="flex-shrink-0">
                                                        <a href="{{ Storage::url($user->registration) }}"
                                                            target="_blank">
                                                            <img id="regPreview"
                                                                src="{{ $user->registration ? Storage::url($user->registration) : 'https://placehold.co/100?text=File' }}"
                                                                class="rounded-3 border shadow-sm" width="80"
                                                                height="80" style="object-fit: cover;">
                                                        </a>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <input type="file" name="registration" class="form-control"
                                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.bmp,.tiff,.webp,image/*"
                                                            onchange="previewImage(this, 'regPreview')">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- PAN File -->
                                        <div class="col-md-6">
                                            <div class="border rounded-4 p-3 h-100">
                                                <label class="form-label fw-semibold mb-3">PAN Registration</label>
                                                <div class="d-flex align-items-start gap-3">
                                                    <div class="flex-shrink-0">
                                                        <a href="{{ Storage::url($user->pan) }}" target="_blank">
                                                            <img id="panPreview"
                                                                src="{{ $user->pan ? Storage::url($user->pan) : 'https://placehold.co/100?text=No File' }}"
                                                                class="rounded-3 border shadow-sm" width="80"
                                                                height="80" style="object-fit: cover;">
                                                        </a>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <input type="file" name="pan" class="form-control"
                                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.bmp,.tiff,.webp,image/*"
                                                            onchange="previewImage(this, 'panPreview')">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Agreement File -->
                                        <div class="col-md-6">
                                            <div class="border rounded-4 p-3 h-100">
                                                <label class="form-label fw-semibold mb-3">Agreement File</label>
                                                <div class="d-flex align-items-start gap-3">
                                                    <div class="flex-shrink-0">
                                                        <a href="{{ Storage::url($user->agreement_file) }}"
                                                            target="_blank">
                                                            <img id="agreementPreview"
                                                                src="{{ $user->agreement_file ? Storage::url($user->agreement_file) : 'https://placehold.co/100?text=No+File' }}"
                                                                class="rounded-3 border shadow-sm" width="80"
                                                                height="80" style="object-fit: cover;">
                                                        </a>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex gap-2">
                                                            <input type="file" name="agreement_file"
                                                                class="form-control"
                                                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.bmp,.tiff,.webp,image/*"
                                                                onchange="previewImage(this, 'agreementPreview')">
                                                            @if ($user->agreement_file)
                                                                <button type="button"
                                                                    class="btn btn-outline-danger btn-sm"
                                                                    onclick="deleteAgreement('{{ route('admin.users.agreement.delete', $user->slug) }}', '{{ $user->business_name }}')">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-0 pb-4 px-4">
                                    <div class="col-md-12 d-flex justify-content-between align-items-center gap-3">
                                        <button type="button" class="btn btn-secondary prev-tab" data-prev="basic-info">
                                            <i class="fa-solid fa-arrow-left"></i> Previous
                                        </button>
                                        <button type="button" class="btn btn-primary next-tab" data-next="security">
                                            Next <i class="fa-solid fa-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Security Tab -->
                        <div class="tab-pane fade" id="security" role="tabpanel">
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 pt-4 px-4">
                                    <h5 class="fw-bold mb-0">Security Settings</h5>
                                    <p class="text-muted small mb-0">Update password and security preferences</p>
                                </div>
                                <div class="card-body p-4">
                                    <div class="alert alert-info border-0 rounded-3 mb-4">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Leave password fields blank if you don't want to change the password.
                                    </div>
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">New Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="fas fa-key"></i></span>
                                                <input type="password" class="form-control" name="password"
                                                    id="passwordField" placeholder="Enter new password">
                                                <span class="input-group-text" onclick="togglePassword()"
                                                    style="cursor:pointer;">
                                                    <i id="passwordIcon" class="fas fa-eye"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Confirm Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i
                                                        class="fas fa-check-circle"></i></span>
                                                <input type="password" class="form-control" name="password_confirmation"
                                                    id="confirmPassword" placeholder="Confirm new password">
                                                <span class="input-group-text"><span id="passwordMatchIcon"></span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-0 pb-4 px-4">
                                    <div class="col-md-12 d-flex justify-content-between align-items-center gap-3">
                                        <button type="button" class="btn btn-secondary prev-tab" data-prev="documents">
                                            <i class="fa-solid fa-arrow-left"></i> Previous
                                        </button>
                                        <button type="submit" class="btn btn-success" id="updateUserBtn">
                                            <i class="fas fa-save me-2"></i>Update User
                                        </button>
                                    </div>
                                </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Status toggle functionality
            const toggleSwitch = document.getElementById('statusToggle');
            const statusLabel = document.getElementById('statusLabel');

            if (toggleSwitch && statusLabel) {
                toggleSwitch.addEventListener('change', function() {
                    const span = statusLabel.querySelector('span');
                    if (this.checked) {
                        span.className = 'fw-semibold text-success';
                        span.innerHTML =
                            '<i class="fas fa-circle me-1" style="font-size: 0.75rem;"></i> Active';
                    } else {
                        span.className = 'fw-semibold text-danger';
                        span.innerHTML =
                            '<i class="fas fa-circle me-1" style="font-size: 0.75rem;"></i> Inactive';
                    }
                });
            }

            // Role change handling
            const roleSelect = document.getElementById('role');
            if (roleSelect) {
                roleSelect.addEventListener('change', function() {
                    toggleParentField();
                });
                toggleParentField(); // Initial call
            }

            // Password match checking
            const passwordField = document.getElementById("passwordField");
            const confirmPassword = document.getElementById("confirmPassword");
            if (passwordField && confirmPassword) {
                passwordField.addEventListener("input", checkPasswordMatch);
                confirmPassword.addEventListener("input", checkPasswordMatch);
            }

            // Tab Navigation - Simple and clean
            const nextButtons = document.querySelectorAll('.next-tab');
            nextButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const nextTabId = this.getAttribute('data-next');
                    if (nextTabId) {
                        const tabButton = document.querySelector(
                            `[data-bs-target="#${nextTabId}"]`);
                        if (tabButton) {
                            const tab = new bootstrap.Tab(tabButton);
                            tab.show();
                        }
                    }
                });
            });

            const prevButtons = document.querySelectorAll('.prev-tab');
            prevButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const prevTabId = this.getAttribute('data-prev');
                    if (prevTabId) {
                        const tabButton = document.querySelector(
                            `[data-bs-target="#${prevTabId}"]`);
                        if (tabButton) {
                            const tab = new bootstrap.Tab(tabButton);
                            tab.show();
                        }
                    }
                });
            });

            // Form submission - ensure it works
            const form = document.getElementById('userForm');
            const updateBtn = document.getElementById('updateUserBtn');

            if (form && updateBtn) {
                updateBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Basic validation for required fields
                    const requiredFields = form.querySelectorAll('[required]');
                    let hasError = false;

                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            hasError = true;
                            field.classList.add('is-invalid');
                            // Show error message
                            if (!field.nextElementSibling || !field.nextElementSibling.classList
                                .contains('error-message')) {
                                const errorMsg = document.createElement('div');
                                errorMsg.className = 'error-message text-danger small mt-1';
                                errorMsg.textContent = 'This field is required';
                                field.parentNode.insertBefore(errorMsg, field.nextSibling);
                            }
                        } else {
                            field.classList.remove('is-invalid');
                            // Remove error message if exists
                            const nextEl = field.nextElementSibling;
                            if (nextEl && nextEl.classList && nextEl.classList.contains(
                                    'error-message')) {
                                nextEl.remove();
                            }
                        }
                    });

                    if (!hasError) {
                        form.submit();
                    } else {
                        alert('Please fill all required fields');
                        // Switch to basic-info tab if there are errors
                        const basicInfoTab = document.querySelector('[data-bs-target="#basic-info"]');
                        if (basicInfoTab) {
                            const tab = new bootstrap.Tab(basicInfoTab);
                            tab.show();
                        }
                    }
                });
            }
        });

        // Function to fetch parents for staff
        function fetchParentsForStaff(currentParentId = null) {
            const parentSelect = document.getElementById('parent_id');
            if (!parentSelect) return;

            fetch('{{ route('admin.users.get-parents') }}', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.parents && Array.isArray(data.parents)) {
                        parentSelect.innerHTML = '<option value="">Select parent (Admin/Agent)</option>';

                        data.parents.forEach(parent => {
                            const option = document.createElement('option');
                            option.value = parent.id;
                            const displayName = parent.business_name || parent.name;
                            option.textContent = `${displayName} (${parent.role})`;

                            if (currentParentId && parseInt(parent.id) === parseInt(currentParentId)) {
                                option.selected = true;
                            }

                            parentSelect.appendChild(option);
                        });

                        parentSelect.disabled = false;
                    } else {
                        parentSelect.innerHTML = '<option value="">No parents available</option>';
                        parentSelect.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error fetching parents:', error);
                    parentSelect.innerHTML = '<option value="">Error loading parents</option>';
                    parentSelect.disabled = true;
                });
        }

        // Function to toggle parent field visibility
        function toggleParentField() {
            const roleSelect = document.getElementById('role');
            const parentContainer = document.getElementById('parentFieldContainer');
            const parentSelect = document.getElementById('parent_id');

            if (!roleSelect) return;

            const currentRole = roleSelect.value;
            const isStaff = currentRole === 'staff';

            if (parentContainer) {
                parentContainer.style.display = isStaff ? 'block' : 'none';
            }

            if (parentSelect) {
                if (isStaff) {
                    parentSelect.setAttribute('required', 'required');
                    // Fetch parents if needed
                    if (parentSelect.options.length <= 1) {
                        const currentParentId = '{{ $user->parent_id }}';
                        parentSelect.disabled = true;
                        parentSelect.innerHTML = '<option value="">Loading parents...</option>';
                        fetchParentsForStaff(currentParentId);
                    }
                } else {
                    parentSelect.removeAttribute('required');
                    parentSelect.value = '';
                }
            }
        }

        // Password match check
        function checkPasswordMatch() {
            const password = document.getElementById("passwordField");
            const confirmPassword = document.getElementById("confirmPassword");
            const icon = document.getElementById("passwordMatchIcon");

            if (!password || !confirmPassword || !icon) return;

            if (!password.value || !confirmPassword.value) {
                icon.textContent = "";
                return;
            }

            icon.textContent = password.value === confirmPassword.value ? "✅" : "❌";
        }

        // Toggle password visibility
        function togglePassword() {
            const field = document.getElementById('passwordField');
            const icon = document.getElementById('passwordIcon');

            if (!field || !icon) return;

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

        // File preview
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (!input.files || !input.files[0] || !preview) return;

            const file = input.files[0];

            if (file.type.startsWith("image/")) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = "https://placehold.co/100?text=FILE";
            }
        }

        // Delete agreement function
        function deleteAgreement(url, businessName) {
            if (confirm(`Are you sure you want to delete the agreement file for ${businessName}?`)) {
                fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error deleting agreement');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error deleting agreement');
                    });
            }
        }
    </script>
@endpush
