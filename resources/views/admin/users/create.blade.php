@extends('layouts.admin')

@section('content')
    <div class="container-fluid p-1">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 20px;">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <!-- Logo Preview - Only shows when logo is uploaded -->
                            <div class="d-flex justify-content-center mb-3">
                                <img id="sidebarLogoPreview" src="" alt="Logo"
                                    style="display: none; width: 150px; height: 150px;"
                                    class="rounded border shadow-sm object-fit-cover">
                            </div>
                            <h5 class="fw-bold mb-1">Create New User</h5>
                            <p class="text-muted small mb-0">Add a new user to the system</p>
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
                                <span class="badge bg-success rounded-pill">New User</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted">Role</span>
                                <span class="badge bg-info rounded-pill">To be selected</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted">Agreement</span>
                                <span class="badge bg-warning rounded-pill">Not Uploaded</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Form Content -->
            <div class="col-lg-9">
                <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="tab-content">
                        <!-- Basic Information Tab -->
                        <div class="tab-pane fade show active" id="basic-info" role="tabpanel">
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-0">
                                    <div class="card-header bg-white border-0 pt-4 px-4">
                                        <h5 class="fw-bold mb-0">Basic Information</h5>
                                        <p class="text-muted small mb-0">Enter user personal and business details</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold d-block">Status</label>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="status" value="0">
                                                <input class="form-check-input" type="checkbox" name="status"
                                                    id="statusToggle" value="1" checked
                                                    style="width: 3rem; height: 1.5rem; cursor: pointer;">
                                                <label class="form-check-label fw-medium ms-2" for="statusToggle"
                                                    id="statusLabel">
                                                    <span class="fw-semibold text-success">
                                                        <i class="fas fa-circle me-1" style="font-size: 0.75rem;"></i>
                                                        Active
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Business Name <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fas fa-building"></i></span>
                                                <input type="text"
                                                    class="form-control border-start-0 ps-0 @error('business_name') is-invalid @enderror"
                                                    name="business_name" id="business_name"
                                                    value="{{ old('business_name') }}" placeholder="Enter business name"
                                                    required>
                                                @error('business_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Owner Name <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fas fa-user-tie"></i></span>
                                                <input type="text"
                                                    class="form-control border-start-0 ps-0 @error('owner_name') is-invalid @enderror"
                                                    name="owner_name" id="owner_name" value="{{ old('owner_name') }}"
                                                    placeholder="Enter owner name" required>
                                                @error('owner_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">User Name <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fas fa-user"></i></span>
                                                <input type="text"
                                                    class="form-control border-start-0 ps-0 @error('name') is-invalid @enderror"
                                                    name="name" value="{{ old('name') }}"
                                                    placeholder="Enter username" required>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Email <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fas fa-envelope"></i></span>
                                                <input type="email"
                                                    class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror"
                                                    name="email" value="{{ old('email') }}" required
                                                    placeholder="Enter email address">
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Contact Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fas fa-phone"></i></span>
                                                <input type="text"
                                                    class="form-control border-start-0 ps-0 @error('contact') is-invalid @enderror"
                                                    name="contact" value="{{ old('contact') }}"
                                                    placeholder="Enter contact number">
                                                @error('contact')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fas fa-map-marker-alt"></i></span>
                                                <input type="text"
                                                    class="form-control border-start-0 ps-0 @error('address') is-invalid @enderror"
                                                    name="address" value="{{ old('address') }}"
                                                    placeholder="Enter address">
                                                @error('address')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Role <span
                                                    class="text-danger">*</span></label>
                                            <select name="role" id="role"
                                                class="form-select @error('role') is-invalid @enderror" required>
                                                <option value="">Select user type</option>
                                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>
                                                    Admin</option>
                                                <option value="agent" {{ old('role') === 'agent' ? 'selected' : '' }}>
                                                    Agent</option>
                                                <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>
                                                    Staff</option>
                                                <option value="university"
                                                    {{ old('role') === 'university' ? 'selected' : '' }}>University
                                                </option>
                                                <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>
                                                    Student</option>
                                            </select>
                                            @error('role')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Parent Field Container -->
                                        <div id="parentFieldContainer" class="col-md-4"
                                            style="display: {{ old('role') === 'staff' ? 'block' : 'none' }};">
                                            <label class="form-label fw-semibold">Parent <span
                                                    class="text-danger">*</span></label>
                                            <select name="parent_id" id="parent_id"
                                                class="form-select @error('parent_id') is-invalid @enderror">
                                                <option value="">Select parent (Admin/Agent)</option>
                                                @if (old('parent_id'))
                                                    <option value="{{ old('parent_id') }}" selected>
                                                        {{ old('parent_name', 'Selected Parent') }}</option>
                                                @endif
                                            </select>
                                            <small class="text-muted">Select the parent (Admin/Agent)</small>
                                            @error('parent_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Agreement Status <span
                                                    class="text-danger">*</span></label>
                                            <select name="agreement_status"
                                                class="form-select @error('agreement_status') is-invalid @enderror">
                                                <option value="not_uploaded"
                                                    {{ old('agreement_status') === 'not_uploaded' ? 'selected' : '' }}>Not
                                                    Uploaded</option>
                                                <option value="uploaded"
                                                    {{ old('agreement_status') === 'uploaded' ? 'selected' : '' }}>Uploaded
                                                </option>
                                                <option value="verified"
                                                    {{ old('agreement_status') === 'verified' ? 'selected' : '' }}>Verified
                                                </option>
                                            </select>
                                            @error('agreement_status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <!-- Next Button -->
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
                                    <p class="text-muted small mb-0">Upload business documents and registrations</p>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-4">
                                        <!-- Business Logo -->
                                        <div class="col-md-6">
                                            <div class="border rounded-4 p-3 h-100">
                                                <label class="form-label fw-semibold mb-3">Business Logo</label>
                                                <div class="d-flex align-items-start gap-3">
                                                    <div class="flex-shrink-0">
                                                        <img id="logoPreview" src="https://placehold.co/100?text=Logo"
                                                            class="rounded-3 border shadow-sm object-fit-cover"
                                                            width="80" height="80" style="object-fit: cover;">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <input type="file" name="business_logo" id="businessLogoInput"
                                                            class="form-control @error('business_logo') is-invalid @enderror"
                                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.bmp,.tiff,.webp,image/*"
                                                            onchange="previewLogo(this, 'logoPreview', 'sidebarLogoPreview')">
                                                        <small class="text-muted">Allowed: Any file type — Max 10MB</small>
                                                        @error('business_logo')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
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
                                                        <img id="regPreview" src="https://placehold.co/100?text=File"
                                                            class="rounded-3 border shadow-sm" width="80"
                                                            height="80" style="object-fit: cover;">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <input type="file" name="registration"
                                                            class="form-control @error('registration') is-invalid @enderror"
                                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.bmp,.tiff,.webp,image/*"
                                                            onchange="previewImage(this, 'regPreview')">
                                                        <small class="text-muted">Allowed: Any file type — Max 10MB</small>
                                                        @error('registration')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
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
                                                        <img id="panPreview" src="https://placehold.co/100?text=No+File"
                                                            class="rounded-3 border shadow-sm" width="80"
                                                            height="80" style="object-fit: cover;">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <input type="file" name="pan"
                                                            class="form-control @error('pan') is-invalid @enderror"
                                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.bmp,.tiff,.webp,image/*"
                                                            onchange="previewImage(this, 'panPreview')">
                                                        <small class="text-muted">Allowed: Any file type — Max 10MB</small>
                                                        @error('pan')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
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
                                                        <img id="agreementPreview"
                                                            src="https://placehold.co/100?text=No+File"
                                                            class="rounded-3 border shadow-sm" width="80"
                                                            height="80" style="object-fit: cover;">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <input type="file" name="agreement_file"
                                                            class="form-control @error('agreement_file') is-invalid @enderror"
                                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.bmp,.tiff,.webp,image/*"
                                                            onchange="previewImage(this, 'agreementPreview')">
                                                        <small class="text-muted">Allowed: Any file type — Max 10MB</small>
                                                        @error('agreement_file')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Navigation Buttons -->
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
                                    <p class="text-muted small mb-0">Set password and security preferences</p>
                                </div>
                                <div class="card-body p-4">
                                    <div class="alert alert-info border-0 rounded-3 mb-4">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Password is required for new users.
                                    </div>
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Password <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="fas fa-key"></i></span>
                                                <input type="password"
                                                    class="form-control @error('password') is-invalid @enderror"
                                                    name="password" id="passwordField" placeholder="Enter password"
                                                    required>
                                                <span class="input-group-text" onclick="togglePassword()"
                                                    style="cursor:pointer;">
                                                    <i id="passwordIcon" class="fas fa-eye"></i>
                                                </span>
                                                @error('password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Confirm Password <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i
                                                        class="fas fa-check-circle"></i></span>
                                                <input type="password"
                                                    class="form-control @error('password_confirmation') is-invalid @enderror"
                                                    name="password_confirmation" id="confirmPassword"
                                                    placeholder="Confirm password" required>
                                                <span class="input-group-text"><span id="passwordMatchIcon"></span></span>
                                                @error('password_confirmation')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Navigation Buttons -->
                                <div class="card-footer bg-white border-0 pb-4 px-4">
                                    <div class="col-md-12 d-flex justify-content-between align-items-center gap-3">
                                        <button type="button" class="btn btn-secondary prev-tab" data-prev="documents">
                                            <i class="fa-solid fa-arrow-left"></i> Previous
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save me-2"></i>Create User
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
        // Main initialization
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
                roleSelect.addEventListener('change', toggleParentField);
                toggleParentField(); // Initial call
            }

            // Password match checking
            const passwordField = document.getElementById("passwordField");
            const confirmPassword = document.getElementById("confirmPassword");
            if (passwordField && confirmPassword) {
                passwordField.addEventListener("input", checkPasswordMatch);
                confirmPassword.addEventListener("input", checkPasswordMatch);
            }

            // Tab Navigation
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

            // Validation before switching tabs
            function validateTab(tabId) {
                const currentTab = document.getElementById(tabId);
                const requiredFields = currentTab.querySelectorAll('[required]');
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('is-invalid');
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                return isValid;
            }

            const nextButtonsWithValidation = document.querySelectorAll('.next-tab');
            nextButtonsWithValidation.forEach(button => {
                button.addEventListener('click', function(e) {
                    const currentTabPane = this.closest('.tab-pane');
                    if (currentTabPane && !validateTab(currentTabPane.id)) {
                        e.preventDefault();
                        alert('Please fill all required fields before proceeding.');
                    }
                });
            });
        });

        // Function to fetch parents for staff with auto-fill data
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
                        // Clear existing options
                        parentSelect.innerHTML = '<option value="">Select parent (Admin/Agent)</option>';

                        // Add parent options with data attributes
                        data.parents.forEach(parent => {
                            const option = document.createElement('option');
                            option.value = parent.id;
                            const displayName = parent.business_name || parent.name;
                            option.textContent = `${displayName} (${parent.role})`;
                            option.setAttribute('data-business_name', parent.business_name);
                            option.setAttribute('data-owner_name', parent.owner_name);

                            if (currentParentId && parseInt(parent.id) === parseInt(currentParentId)) {
                                option.selected = true;
                            }

                            parentSelect.appendChild(option);
                        });

                        // Enable the select
                        parentSelect.disabled = false;

                        // Add change event listener for auto-fill
                        parentSelect.addEventListener('change', autoFillStaffFields);

                        // If there's a selected parent, trigger auto-fill
                        if (parentSelect.value) {
                            autoFillStaffFields();
                        }

                        // Make required if role is staff
                        const roleSelect = document.getElementById('role');
                        if (roleSelect && roleSelect.value === 'staff') {
                            parentSelect.setAttribute('required', 'required');
                        }
                    } else {
                        parentSelect.innerHTML = '<option value="">No parents available</option>';
                        parentSelect.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error fetching parents:', error);
                    if (parentSelect) {
                        parentSelect.innerHTML = '<option value="">Error loading parents</option>';
                        parentSelect.disabled = true;
                    }
                });
        }

        // Auto-fill staff fields when parent is selected
        function autoFillStaffFields() {
            const parentSelect = document.getElementById('parent_id');
            const selectedOption = parentSelect.options[parentSelect.selectedIndex];

            if (!selectedOption || !selectedOption.value) {
                // Clear auto-filled fields if no parent selected
                document.getElementById('business_name').value = '';
                document.getElementById('owner_name').value = '';
                return;
            }

            const businessName = selectedOption.getAttribute('data-business_name');
            const ownerName = selectedOption.getAttribute('data-owner_name');

            // Auto-fill business_name with parent's business name + " - Staff"
            if (businessName) {
                document.getElementById('business_name').value = businessName + ' - Staff';
            }

            // Auto-fill owner_name with parent's owner name
            if (ownerName) {
                document.getElementById('owner_name').value = ownerName;
            }
        }

        // Function to toggle parent field visibility and handle auto-fill
        function toggleParentField() {
            const roleSelect = document.getElementById('role');
            const parentContainer = document.getElementById('parentFieldContainer');
            const parentSelect = document.getElementById('parent_id');
            const businessNameField = document.getElementById('business_name');
            const ownerNameField = document.getElementById('owner_name');

            if (!roleSelect) return;

            const currentRole = roleSelect.value;
            const isStaff = currentRole === 'staff';

            if (parentContainer) {
                parentContainer.style.display = isStaff ? 'block' : 'none';
            }

            if (parentSelect) {
                if (isStaff) {
                    // Make parent field required for staff
                    parentSelect.setAttribute('required', 'required');

                    // Make business_name and owner_name readonly for staff
                    if (businessNameField) {
                        businessNameField.readOnly = true;
                        businessNameField.classList.add('bg-light');
                    }
                    if (ownerNameField) {
                        ownerNameField.readOnly = true;
                        ownerNameField.classList.add('bg-light');
                    }

                    // Fetch parents if the select is empty or only has the placeholder
                    if (parentSelect.options.length <= 1) {
                        parentSelect.disabled = true;
                        parentSelect.innerHTML = '<option value="">Loading parents...</option>';
                        fetchParentsForStaff();
                    } else {
                        // Trigger auto-fill if parent already selected
                        autoFillStaffFields();
                    }
                } else {
                    // Remove required for non-staff roles
                    parentSelect.removeAttribute('required');
                    // Clear the value when role is not staff
                    parentSelect.value = '';

                    // Make business_name and owner_name editable again
                    if (businessNameField) {
                        businessNameField.readOnly = false;
                        businessNameField.classList.remove('bg-light');
                    }
                    if (ownerNameField) {
                        ownerNameField.readOnly = false;
                        ownerNameField.classList.remove('bg-light');
                    }
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
                icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                field.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }

        // Logo preview with sidebar
        function previewLogo(input, previewId, sidebarPreviewId) {
            const preview = document.getElementById(previewId);
            const sidebarPreview = document.getElementById(sidebarPreviewId);

            if (!input.files || !input.files[0] || !preview) return;

            const file = input.files[0];
            const fileType = file.type;

            if (fileType.startsWith("image/")) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    if (sidebarPreview) {
                        sidebarPreview.src = e.target.result;
                        sidebarPreview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = "https://via.placeholder.com/100?text=FILE";
                if (sidebarPreview) {
                    sidebarPreview.style.display = 'none';
                }
            }
        }

        // Universal file preview
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (!input.files || !input.files[0] || !preview) return;

            const file = input.files[0];
            const fileType = file.type;

            if (fileType.startsWith("image/")) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = "https://via.placeholder.com/100?text=FILE";
            }
        }
    </script>
@endpush
