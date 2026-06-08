@extends('layouts.admin')

@section('admin-content')
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1 fw-bold">User Management</h2>
                <p class="text-muted mb-0">Manage and monitor all users in the system</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" onclick="toggleAdvancedFilters()">
                    <i class="fas fa-sliders-h me-2"></i>Advanced Filters
                </button>
                <a href="{{ route('admin.exports.index') }}" class="btn btn-success">
                    <i class="fas fa-download me-2"></i>Export
                </a>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="fas fa-user-plus me-2"></i> Add User
                </a>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card" onclick="applyRoleFilter('all')">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Total Users</h6>
                                <h3 class="mb-0 fw-bold">{{ $totalUsers ?? 0 }}</h3>
                            </div>
                            <div class="rounded-circle bg-primary  p-3">
                                <i class="fas fa-users fa-2x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card" onclick="applyRoleFilter('admin')">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Admins</h6>
                                <h3 class="mb-0 fw-bold">
                                    {{ $admins instanceof \Illuminate\Pagination\LengthAwarePaginator ? $admins->total() : $admins->count() ?? 0 }}
                                </h3>
                            </div>
                            <div class="rounded-circle bg-danger  p-3">
                                <i class="fas fa-user-shield fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card" onclick="applyRoleFilter('agent')">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Agents</h6>
                                <h3 class="mb-0 fw-bold">
                                    {{ $agents instanceof \Illuminate\Pagination\LengthAwarePaginator ? $agents->total() : $agents->count() ?? 0 }}
                                </h3>
                            </div>
                            <div class="rounded-circle bg-success  p-3">
                                <i class="fas fa-handshake fa-2x "></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card" onclick="applyRoleFilter('staff')">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Staff</h6>
                                <h3 class="mb-0 fw-bold">
                                    {{ $staffs instanceof \Illuminate\Pagination\LengthAwarePaginator ? $staffs->total() : $staffs->count() ?? 0 }}
                                </h3>
                            </div>
                            <div class="rounded-circle bg-info  p-3">
                                <i class="fas fa-user-friends fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Filter Section --}}
        <div class="filter-section mb-3">
            <form method="GET" action="{{ route('admin.users.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                                placeholder="Search by name, email, business...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="role" class="form-select" onchange="this.form.submit()">
                            <option value="">All Roles</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admins</option>
                            <option value="agent" {{ request('role') === 'agent' ? 'selected' : '' }}>Agents</option>
                            <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="university" {{ request('role') === 'university' ? 'selected' : '' }}>
                                Universities</option>
                            <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Students</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="agreement" class="form-select" onchange="this.form.submit()">
                            <option value="">Agreement Status</option>
                            <option value="not_uploaded" {{ request('agreement') === 'not_uploaded' ? 'selected' : '' }}>
                                Not Uploaded</option>
                            <option value="uploaded" {{ request('agreement') === 'uploaded' ? 'selected' : '' }}>Uploaded
                            </option>
                            <option value="verified" {{ request('agreement') === 'verified' ? 'selected' : '' }}>Verified
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="sort" class="form-select" onchange="this.form.submit()">
                            <option value="created_at_desc" {{ request('sort') === 'created_at_desc' ? 'selected' : '' }}>
                                Newest First</option>
                            <option value="created_at_asc" {{ request('sort') === 'created_at_asc' ? 'selected' : '' }}>
                                Oldest First</option>
                            <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name (A-Z)
                            </option>
                            <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name (Z-A)
                            </option>
                            <option value="business_asc" {{ request('sort') === 'business_asc' ? 'selected' : '' }}>
                                Business (A-Z)</option>
                            <option value="business_desc" {{ request('sort') === 'business_desc' ? 'selected' : '' }}>
                                Business (Z-A)</option>
                        </select>
                    </div>
                </div>

                {{-- Advanced Filters (Hidden by default) --}}
                <div id="advancedFilters" style="display: none;" class="mt-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control"
                                value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Minimum Students</label>
                            <input type="number" name="min_students" class="form-control" placeholder="Min students"
                                value="{{ request('min_students') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Minimum Applications</label>
                            <input type="number" name="min_applications" class="form-control"
                                placeholder="Min applications" value="{{ request('min_applications') }}">
                        </div>
                    </div>
                    <div class="mt-3 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-2"></i>Apply Filters
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-undo me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </form>

            {{-- Active Filters Display --}}
            @if (isset($hasFilters) && $hasFilters)
                <div class="mt-3">
                    <small class="text-muted">Active Filters:</small>
                    <div class="d-flex flex-wrap mt-1">
                        @if (isset($activeFilters['search']))
                            <div class="filter-badge">
                                <i class="fas fa-search"></i> "{{ $activeFilters['search'] }}"
                                <span class="remove-filter" onclick="removeFilter('search')">&times;</span>
                            </div>
                        @endif
                        @if (isset($activeFilters['role']))
                            <div class="filter-badge">
                                <i class="fas fa-user-tag"></i> Role: {{ ucfirst($activeFilters['role']) }}
                                <span class="remove-filter" onclick="removeFilter('role')">&times;</span>
                            </div>
                        @endif
                        @if (isset($activeFilters['status']))
                            <div class="filter-badge">
                                <i class="fas fa-circle"></i> Status: {{ ucfirst($activeFilters['status']) }}
                                <span class="remove-filter" onclick="removeFilter('status')">&times;</span>
                            </div>
                        @endif
                        @if (isset($activeFilters['agreement']))
                            <div class="filter-badge">
                                <i class="fas fa-file-contract"></i> Agreement:
                                {{ str_replace('_', ' ', ucfirst($activeFilters['agreement'])) }}
                                <span class="remove-filter" onclick="removeFilter('agreement')">&times;</span>
                            </div>
                        @endif
                        @if (isset($activeFilters['min_students']))
                            <div class="filter-badge">
                                <i class="fas fa-graduation-cap"></i> Min Students: {{ $activeFilters['min_students'] }}
                                <span class="remove-filter" onclick="removeFilter('min_students')">&times;</span>
                            </div>
                        @endif
                        @if (isset($activeFilters['min_applications']))
                            <div class="filter-badge">
                                <i class="fas fa-file-alt"></i> Min Applications: {{ $activeFilters['min_applications'] }}
                                <span class="remove-filter" onclick="removeFilter('min_applications')">&times;</span>
                            </div>
                        @endif
                        @if (isset($activeFilters['date_from']) || isset($activeFilters['date_to']))
                            <div class="filter-badge">
                                <i class="fas fa-calendar"></i>
                                @if (isset($activeFilters['date_from']) && isset($activeFilters['date_to']))
                                    {{ $activeFilters['date_from'] }} to {{ $activeFilters['date_to'] }}
                                @elseif(isset($activeFilters['date_from']))
                                    From {{ $activeFilters['date_from'] }}
                                @else
                                    Until {{ $activeFilters['date_to'] }}
                                @endif
                                <span class="remove-filter" onclick="removeFilter('date')">&times;</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Admins Table --}}
        @if ($admins->count())
            <div class="card mb-4">
                <div class="card-header-custom rounded-top-4 p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-user-shield me-2"></i>Administrators</h5>
                        <span class="badge bg-light text-dark border fw-medium">{{ $admins->total() }} Total</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small">
                                <tr>
                                    <th width="70">ID</th>
                                    <th width="80">Logo</th>
                                    <th class="sortable" onclick="sortBy('business_name')">Business <i
                                            class="fas fa-sort sort-icon"></i></th>
                                    <th class="sortable" onclick="sortBy('name')">Name <i
                                            class="fas fa-sort sort-icon"></i></th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th class="sortable" onclick="sortBy('created_at')">Joined <i
                                            class="fas fa-sort sort-icon"></i></th>
                                    <th width="80" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($admins as $admin)
                                    <tr>
                                        <td class="align-middle">{{ $admin->id }}</td>
                                        <td class="align-middle">
                                            @if ($admin->business_logo)
                                                <img src="{{ Storage::url($admin->business_logo) }}" alt="Logo"
                                                    width="50" height="50"
                                                    class="rounded object-fit-cover shadow-sm border">
                                            @else
                                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 50px; height: 50px;">
                                                    <i class="fas fa-user texti-white"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <a href="{{ route('admin.users.show', $admin->slug) }}"
                                                class="text-decoration-none fw-semibold text-dark">
                                                {{ $admin->business_name }}
                                            </a>
                                        </td>
                                        <td class="align-middle">{{ $admin->name }}</td>
                                        <td class="align-middle">{{ $admin->email }}</td>
                                        <td class="align-middle">
                                            <span
                                                class="badge 
                                    {{ $admin->active ? 'bg-success' : 'bg-danger' }} 
                                    rounded-pill  p-2">
                                                <i class="fas fa-circle me-1" style="font-size: 10px;"></i>
                                                {{ $admin->active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="align-middle">{{ $admin->created_at->format('M d, Y') }}</td>
                                        <td class="align-middle text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-ghost" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                                    <li>
                                                        <a class="dropdown-item py-2"
                                                            href="{{ route('admin.users.show', $admin->slug) }}">
                                                            <i class="fas fa-eye me-2"></i> View
                                                        </a>
                                                    </li>
                                                    <li><a class="dropdown-item py-2"
                                                            href="{{ route('admin.users.edit', $admin->slug) }}"><i
                                                                class="fas fa-edit me-2"></i> Edit</a></li>
                                                    @if (in_array(auth()->id(), [1, 2]) && $admin->id !== auth()->id())
                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>
                                                        <li><button class="dropdown-item py-2 text-danger"
                                                                onclick="confirmDelete('{{ route('admin.users.destroy', $admin->slug) }}', '{{ $admin->business_name }}')"><i
                                                                    class="fas fa-trash me-2"></i> Delete</button></li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 pt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $admins->firstItem() }} to {{ $admins->lastItem() }} of {{ $admins->total() }}
                            admins
                        </div>
                        {{ $admins->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        @endif

        {{-- Staff Table --}}
        @if ($staffs->count())
            <div class="card mb-4">
                <div class="card-header-custom rounded-top-4 p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-user-friends me-2"></i>Staff Members</h5>
                        <span class="badge bg-light text-dark border fw-medium">{{ $staffs->total() }} Total</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small">
                                <tr>
                                    <th width="70">ID</th>
                                    <th width="80">Logo</th>
                                    <th class="sortable" onclick="sortBy('business_name')">Business <i
                                            class="fas fa-sort sort-icon"></i></th>
                                    <th class="sortable" onclick="sortBy('name')">Name <i
                                            class="fas fa-sort sort-icon"></i></th>
                                    <th>Email</th>
                                    <th>Parent Company</th>
                                    <th>Status</th>
                                    <th width="80" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($staffs as $staff)
                                    <tr>
                                        <td class="align-middle">{{ $staff->id }}</td>
                                        <td class="align-middle">
                                            @if ($staff->business_logo)
                                                <img src="{{ Storage::url($staff->business_logo) }}" alt="Logo"
                                                    width="50" height="50"
                                                    class="rounded object-fit-cover shadow-sm border">
                                            @else
                                                <div class="bg-secondary  rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 50px; height: 50px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <a href="{{ route('admin.users.show', $staff->slug) }}"
                                                class="text-decoration-none fw-semibold text-dark">
                                                {{ $staff->business_name }}
                                            </a>
                                        </td>
                                        <td class="align-middle">{{ $staff->name }}</td>
                                        <td class="align-middle">{{ $staff->email }}</td>
                                        <td class="align-middle">
                                            <span class="badge bg-primary rounded p-2">
                                                {{ $staff->parent ? $staff->parent->business_name : 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <span
                                                class="badge 
                                    {{ $staff->active ? 'bg-success' : 'bg-danger' }} 
                                    rounded-pill  p-2">
                                                <i class="fas fa-circle me-1" style="font-size: 10px;"></i>
                                                {{ $staff->active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-ghost" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                                    <li><a class="dropdown-item py-2"
                                                            href="{{ route('admin.users.show', $staff->slug) }}"><i
                                                                class="fas fa-eye me-2"></i> View</a></li>
                                                    <li><a class="dropdown-item py-2"
                                                            href="{{ route('admin.users.edit', $staff->slug) }}"><i
                                                                class="fas fa-edit me-2"></i> Edit</a></li>
                                                    @if (in_array(auth()->id(), [1, 2]) && $staff->id !== auth()->id())
                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>
                                                        <li><button class="dropdown-item py-2 text-danger"
                                                                onclick="confirmDelete('{{ route('admin.users.destroy', $staff->slug) }}', '{{ $staff->business_name }}')"><i
                                                                    class="fas fa-trash me-2"></i> Delete</button></li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 pt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $staffs->firstItem() }} to {{ $staffs->lastItem() }} of {{ $staffs->total() }}
                            staff
                        </div>
                        {{ $staffs->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        @endif

        {{-- Agents Table --}}
        @if ($agents->count())
            <div class="card mb-4">
                <div class="card-header-custom rounded-top-4 p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-handshake me-2"></i>Agents</h5>
                        <span class="badge bg-light text-dark border fw-medium">{{ $agents->total() }} Total</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small">
                                <tr>
                                    <th width="70">ID</th>
                                    <th>Business / Owner</th>
                                    <th width="250">Contact Details</th>
                                    <th width="100">Status</th>
                                    <th width="120">Agreement</th>
                                    <th class="text-center" width="80">Students</th>
                                    <th class="text-center" width="80">Apps</th>
                                    <th width="80" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($agents as $agent)
                                    <tr>
                                        <td class="align-middle">{{ $agent->id }}</td>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    @if ($agent->business_logo)
                                                        <img src="{{ Storage::url($agent->business_logo) }}"
                                                            alt="Logo" width="50" height="50"
                                                            class="rounded object-fit-cover shadow-sm border">
                                                    @else
                                                        <div class="bg-secondary rounded-3 d-flex align-items-center justify-content-center border"
                                                            style="width: 38px; height: 38px;">
                                                            <i class="fas fa-building texti-white small"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="lh-sm">
                                                    <a href="{{ route('admin.users.show', $agent->slug) }}"
                                                        class="text-decoration-none fw-bold text-dark d-block mb-0">
                                                        {{ $agent->business_name }} <br>
                                                    </a>
                                                    <small class="text-muted fw-bold">{{ $agent->owner_name }}</small><br>
                                                    <small class="fw-semibold text-muted">
                                                        <i class="fas fa-circle me-1 text-success"
                                                            style="font-size: 10px;">
                                                        </i>
                                                        @php $status = $agent->online_status; @endphp
                                                        {{ $agent->online_status['last_login'] ?? 'Never' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex flex-column small">
                                                <div class="text-truncate" style="max-width: 250px;"
                                                    title="{{ $agent->email }}">
                                                    <a href="mailto:{{ trim($agent->email) }}"
                                                        class="text-decoration-none text-reset">
                                                        <i class="fas fa-envelope text-muted me-2"
                                                            style="width: 14px;"></i>{{ $agent->email }}
                                                    </a>
                                                </div>
                                                <div class="text-nowrap mt-1">
                                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $agent->contact) }}"
                                                        class="text-decoration-none text-muted">
                                                        <i class="fas fa-phone text-muted me-2"
                                                            style="width: 14px;"></i>{{ $agent->contact ?? 'N/A' }}
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <span
                                                class="badge 
                    {{ $agent->active ? 'bg-success' : 'bg-danger' }} 
                    rounded-pill  p-2">
                                                <i class="fas fa-circle me-1" style="font-size: 10px;"></i>
                                                {{ $agent->active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            @php
                                                $badgeClass = match ($agent->agreement_status) {
                                                    'verified' => 'btn-outline-success',
                                                    'uploaded' => 'btn-outline-warning',
                                                    default => 'btn-outline-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }} rounded-1 px-3 py-2">
                                                {{ str_replace('_', ' ', ucfirst($agent->agreement_status)) }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <a href="{{ route('admin.users.students', $agent->slug) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i
                                                    class="fas fa-graduation-cap me-1"></i>{{ $agent->students_count ?? 0 }}
                                            </a>
                                        </td>
                                        <td class="align-middle text-center">
                                            <a href="{{ route('admin.users.applications', $agent->slug) }}"
                                                class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-file-alt me-1"></i>{{ $agent->applications_count ?? 0 }}
                                            </a>
                                        </td>
                                        <td class="align-middle text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-ghost" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                                    <li><a class="dropdown-item py-2"
                                                            href="{{ route('admin.users.show', $agent->slug) }}"><i
                                                                class="fas fa-eye me-2"></i> View</a></li>
                                                    <li><a class="dropdown-item py-2"
                                                            href="{{ route('admin.users.edit', $agent->slug) }}"><i
                                                                class="fas fa-edit me-2"></i> Edit</a></li>
                                                    @if (in_array(auth()->id(), [1, 2]) && !in_array($agent->id, [1, 2]))
                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>
                                                        <li><button class="dropdown-item py-2 text-danger"
                                                                onclick="confirmDelete('{{ route('admin.users.destroy', $agent->slug) }}', '{{ $agent->business_name }}')"><i
                                                                    class="fas fa-trash me-2"></i> Delete</button></li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white border-0 pt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $agents->firstItem() }} to {{ $agents->lastItem() }} of {{ $agents->total() }}
                            agents
                        </div>
                        {{ $agents->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        @endif

        @if (!$admins->count() && !$staffs->count() && !$agents->count())
            <div class="text-center py-5">
                <i class="fas fa-users fa-4x text-muted mb-3"></i>
                <h4>No users found</h4>
                <p class="text-muted">Try adjusting your filters or create a new user.</p>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="fas fa-user-plus me-2"></i> Add User
                </a>
            </div>
        @endif

        {{-- Delete Confirmation Modal --}}
        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete <strong id="deleteUserName"></strong>? This action cannot be
                        undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form id="deleteForm" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Toggle advanced filters
        function toggleAdvancedFilters() {
            const filters = document.getElementById('advancedFilters');
            if (filters.style.display === 'none') {
                filters.style.display = 'block';
            } else {
                filters.style.display = 'none';
            }
        }

        // Apply role filter from stats cards
        function applyRoleFilter(role) {
            const url = new URL(window.location.href);
            if (role === 'all') {
                url.searchParams.delete('role');
            } else {
                url.searchParams.set('role', role);
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        // Sort functionality
        function sortBy(field) {
            const url = new URL(window.location.href);
            const currentSort = url.searchParams.get('sort');
            let newSort = field + '_asc';

            if (currentSort === field + '_asc') {
                newSort = field + '_desc';
            }

            url.searchParams.set('sort', newSort);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        // Remove filter
        function removeFilter(filterName) {
            const url = new URL(window.location.href);
            if (filterName === 'date') {
                url.searchParams.delete('date_from');
                url.searchParams.delete('date_to');
            } else {
                url.searchParams.delete(filterName);
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        // Confirm delete with SweetAlert
        function confirmDelete(deleteUrl, userName) {
            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete "${userName}". This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = deleteUrl;
                    form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Export functionality
        function exportData(type) {
            const url = new URL(window.location.href);
            url.searchParams.set('export', type);
            window.location.href = url.toString();
        }
    </script>
@endpush
