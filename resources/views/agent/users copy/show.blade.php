@extends('layouts.agent')

@section('title', 'Profile - ' . ($user->business_name ?? $user->name))

@section('content')

    <style>
        .col-auto {
            position: static;
        }

        .dropdown-menu {
            z-index: 1050;
        }

        .bg-gradient-primary .card-body {
            overflow: visible;
        }

        .bg-gradient-primary {
            overflow: visible;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }

        .bg-gradient-info {
            background: linear-gradient(135deg, var(--info) 0%, var(--info-light) 100%);
        }

        .bg-gradient-warning {
            background: linear-gradient(135deg, var(--warning) 0%, var(--warning-dark) 100%);
        }

        .card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        /* Modern Activities Row */
        .activities-modern-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        /* Modern Activity Card */
        .modern-activity-card {
            background: var(--card);
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .modern-activity-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        /* Card Header */
        .card-header-modern {
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, var(--gray) 0%, var(--card) 100%);
            border-bottom: 2px solid var(--border);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .student-icon {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: var(--white);
        }

        .document-icon {
            background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
            color: var(--white);
        }

        .application-icon {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-light) 100%);
            color: var(--white);
        }

        .staff-icon {
            background: linear-gradient(135deg, var(--info) 0%, #1a0262 100%);
            color: var(--white);
        }

        .card-header-modern h5 {
            flex: 1;
            font-size: 1rem;
            font-weight: 600;
            color: var(--secondary);
        }

        .activity-count {
            background: var(--light-gray);
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--muted);
        }

        /* Card Body */
        .card-body-modern {
            padding: 0.5rem 0;
            max-height: 400px;
            overflow-y: auto;
        }

        /* Custom Scrollbar */
        .card-body-modern::-webkit-scrollbar {
            width: 4px;
        }

        .card-body-modern::-webkit-scrollbar-track {
            background: var(--light);
        }

        .card-body-modern::-webkit-scrollbar-thumb {
            background: var(--muted-2);
            border-radius: 10px;
        }

        /* Activity Item */
        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.875rem 1.25rem;
            border-bottom: 1px solid var(--border);
            transition: background 0.2s ease;
        }

        .activity-item:hover {
            background: var(--bg-light);
        }

        .activity-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-top: 6px;
            flex-shrink: 0;
        }

        .student-dot {
            background: var(--primary);
            box-shadow: 0 0 0 3px rgba(130, 11, 92, 0.2);
        }

        .document-dot {
            background: var(--success);
            box-shadow: 0 0 0 3px rgba(0, 128, 43, 0.2);
        }

        .application-dot {
            background: var(--secondary-light);
            box-shadow: 0 0 0 3px rgba(50, 11, 245, 0.2);
        }

        .staff-dot {
            background: var(--info);
            box-shadow: 0 0 0 3px rgba(13, 202, 240, 0.2);
        }

        /* Activity Content */
        .activity-content {
            flex: 1;
        }

        .activity-link {
            text-decoration: none;
            color: var(--text-color);
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 4px;
            transition: color 0.2s ease;
        }

        .activity-link:hover {
            color: var(--primary);
        }

        .activity-time {
            font-size: 0.7rem;
            color: var(--muted);
            display: flex;
            align-items: center;
        }

        /* Empty State */
        .empty-activities {
            text-align: center;
            padding: 2rem;
            color: var(--muted);
        }

        .empty-activities i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            opacity: 0.5;
        }

        .empty-activities p {
            font-size: 0.875rem;
            margin: 0;
        }

        /* Staff Table Styles */
        .staff-table {
            margin-bottom: 0;
        }

        .staff-table th {
            background: #f8f9fa;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #dee2e6;
        }

        .staff-table td {
            vertical-align: middle;
            font-size: 0.9rem;
        }

        .status-dot {
            display: inline-block;
            width: 15px;
            height: 10px;
            border-radius: 3px;
        }

        .status-dot.active {
            background-color: #28a745;
            /* green */
        }

        .status-dot.inactive {
            background-color: #dc3545;
            /* red */
        }

        .btn-outline-danger {
            background: transparent;
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .btn-outline-danger:hover {
            background: var(--danger);
            color: var(--white);
        }

        /* OUTLINE WARNING */
        .btn-outline-warning {
            background: transparent;
            color: var(--warning);
            border: 1px solid var(--warning);
        }

        .btn-outline-warning:hover {
            background: var(--warning);
            color: var(--white);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .activities-modern-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .card-header-modern {
                padding: 0.875rem 1rem;
            }

            .activity-item {
                padding: 0.75rem 1rem;
            }
        }

        /* Staff Card */
        .staff-card {
            border-left: 4px solid var(--info);
        }

        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    </style>

    <div>
        {{-- Header Section with Gradient Background --}}
        <div class="row mb-2">
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-gradient-primary">
                    <div class="card-body p-2">
                        <div class="row align-items-start">

                            <div class="col">
                                <h1 class="text-white mb-2">{{ $user->business_name ?? $user->name }}</h1>
                                <div class="d-flex flex-wrap gap-3">
                                    <span class="badge bg-light text-dark px-3 py-2">
                                        <i class="fas fa-tag me-1"></i> {{ ucfirst($user->role) }}
                                    </span>
                                    <span class="badge {{ $user->active ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                                        <i class="fas {{ $user->active ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                        {{ $user->active ? 'Active' : 'Inactive' }}
                                    </span>
                                    @if ($user->agreement_status)
                                        <span
                                            class="badge {{ $user->agreement_status === 'verified' ? 'bg-success' : ($user->agreement_status === 'uploaded' ? 'bg-warning' : 'bg-secondary') }} px-3 py-2">
                                            <i class="fas fa-file-contract me-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $user->agreement_status)) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            {{-- Statistics Cards Row --}}
                            <div class="col d-flex justify-content-center gap-2 align-items-center ">
                                <a href="{{ route('agent.students.index') }}">
                                    <div class="card bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-secondary mb-1">Total Students</h6>
                                                <h2 class="text-secondary mb-0">{{ $user->students->count() }}</h2>
                                            </div>
                                            <div>
                                                <i class="fas fa-users fa-2x text-secondary"></i>
                                            </div>
                                        </div>
                                </a>
                            </div>
                            <div class="card bg-light">
                                <a href="{{ route('agent.applications.index', $user->slug) }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-primary mb-1">Total Applications</h6>
                                            <h2 class="text-primary mb-0">{{ $user->applications->count() }}</h2>
                                        </div>
                                        <div>
                                            <i class="fas fa-file-alt fa-2x text-primary"></i>
                                        </div>
                                    </div>
                                </a>

                            </div>
                            <div class="card bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-success mb-1">Staff Members</h6>
                                        <h2 class="text-success mb-0">{{ $staffMembers->count() }} / 5</h2>
                                    </div>
                                    <div>
                                        <i class="fas fa-user-tie fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="dropdown">
                                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-cog"></i> Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('agent.users.edit', $user->slug) }}">
                                            <i class="fas fa-edit text-primary"></i> Edit Profile
                                        </a>
                                    </li>
                                    @if (in_array(auth()->id(), [11, 12]) && $staffMembers->count() < 5)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('agent.staff.create') }}">
                                                <i class="fas fa-user-plus text-success"></i> Add Staff Member
                                            </a>
                                        </li>
                                    @endif
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal"
                                            data-bs-target="#resetPasswordModal">
                                            <i class="fas fa-key"></i> Reset Password
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div class="mt-2">
                                @php $status = $user->online_status; @endphp
                                @if ($status['is_online'])
                                    <div class="mb-2">
                                        <span class="badge bg-success px-4 py-2">
                                            <i class="fas fa-circle me-1"></i> Online
                                        </span>
                                    </div>
                                    <small class="text-muted">Active now</small>
                                @else
                                    <div class="mb-2">
                                        <span class="badge bg-dark px-4 py-2">
                                            <i class="fas fa-circle me-1"></i> Offline
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Left Column - User Information --}}
        <div class="col-xl-4 col-lg-5 mb-4">
            {{-- Profile Card --}}
            <div class="card border-0 shadow-sm mb-2">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0"><i class="fas fa-user-circle text-primary me-2"></i>Profile Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Owner Name</label>
                        <p class="fw-bold mb-0">{{ $user->owner_name ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Contact Person</label>
                        <p class="fw-bold mb-0">{{ $user->name }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Email Address</label>
                        <p class="mb-0">
                            <a href="mailto:{{ $user->email }}" class="text-decoration-none">
                                <i class="fas fa-envelope me-1"></i> {{ $user->email }}
                            </a>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Contact Number</label>
                        <p class="mb-0">
                            @if ($user->contact)
                                <a href="tel:{{ $user->contact }}" class="text-decoration-none">
                                    <i class="fas fa-phone me-1"></i> {{ $user->contact }}
                                </a>
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Address</label>
                        <p class="mb-0">
                            @if ($user->address)
                                <i class="fas fa-map-marker-alt me-1"></i> {{ $user->address }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Account Created</label>
                        <p class="mb-0">
                            <i class="fas fa-calendar-alt me-1"></i>
                            {{ $user->created_at ? $user->created_at->format('F j, Y, g:i A') : 'N/A' }}
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Last Updated</label>
                        <p class="mb-0">
                            <i class="fas fa-clock me-1"></i>
                            {{ $user->updated_at ? $user->updated_at->diffForHumans() : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Documents Card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-0 pt-4 pb-0">
                    <h5 class="mb-0"><i class="fas fa-file-alt text-primary me-2"></i>Business Documents</h5>
                </div>
                <div class="card-body">
                    @php
                        $documents = [
                            'registration' => ['label' => 'Registration Certificate', 'icon' => 'fa-building'],
                            'pan' => ['label' => 'PAN Certificate', 'icon' => 'fa-file-invoice'],
                            'agreement_file' => ['label' => 'Agreement Document', 'icon' => 'fa-file-contract'],
                        ];
                    @endphp

                    @foreach ($documents as $docKey => $docInfo)
                        @if ($user->$docKey)
                            <div class="mb-3 pb-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas {{ $docInfo['icon'] }} text-primary me-2"></i>
                                        <strong>{{ $docInfo['label'] }}</strong>
                                    </div>
                                    <div>
                                        <a href="{{ Storage::url($user->$docKey) }}" target="_blank"
                                            class="btn btn-sm btn-outline-primary me-1">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ Storage::url($user->$docKey) }}" download
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    Uploaded: {{ $user->updated_at ? $user->updated_at->diffForHumans() : 'N/A' }}
                                </small>
                            </div>
                        @else
                            <div class="p-2 border-bottom text-muted">
                                <i class="fas {{ $docInfo['icon'] }}"></i>
                                {{ $docInfo['label'] }}: <em>Not uploaded</em>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Staff Members Section --}}

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user-tie text-info me-2"></i>Staff Members</h5>
                </div>
                <div class="card-body">
                    @if ($staffMembers->count() > 0)
                        @foreach ($staffMembers as $staff)
                            <div
                                class="d-flex justify-content-between align-items-center mb-3 p-2 border rounded staff-card">
                                <div>
                                    <h6 class="mb-0">{{ $staff->name }}
                                    </h6>
                                    <small class="text-muted">{{ $staff->email }}</small>
                                    <p class="text-muted mb-0">{{ $staff->contact ?? 'N/A' }}</p>
                                </div>

                                <div class="align-items-center">
                                    <span class="badge {{ $staff->active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $staff->active ? 'Onwork' : 'Inactive' }}
                                    </span><br>
                                    <a href="{{ route('agent.staff.edit', $staff->slug) }}"
                                        class="btn btn-sm btn-outline-warning" title="Edit Staff">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete Staff"
                                        onclick="confirmDeleteStaff('{{ $staff->slug }}', '{{ addslashes($staff->name) }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <form id="delete-staff-form-{{ $staff->slug }}"
                                        action="{{ route('agent.staff.destroy', $staff->slug) }}" method="POST"
                                        style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </div>
                        @endforeach

                        @if ($staffMembers->count() >= 5)
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="fas fa-exclamation-triangle"></i>
                                You have reached the maximum staff limit (5). Please contact admin if you need to add more
                                staff.
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-2">No staff members added yet.</p>
                            <p class="text-muted mb-0">Staff members can assist you in managing students and applications.
                            </p>
                            @if ($staffMembers->count() < 5)
                                <a href="{{ route('agent.chat') }}" class="btn btn-primary btn-sm mt-2">
                                    Contact the admin to add staff members.
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Login Status Card --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    @php $status = $user->online_status; @endphp
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5><i class="fas fa-sign-in-alt text-primary me-2"></i>Login Status</h5>
                        @if ($status['is_online'])
                            <div class="mt-2">
                                <span class="badge bg-success p-2">
                                    <i class="fas fa-circle me-1"></i> Online
                                </span>
                            </div>
                        @else
                            <div class="mt-2">
                                <span class="badge bg-secondary p-2">
                                    <i class="fas fa-circle me-1"></i> Offline
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="text-start">
                        <p class="mb-2"><strong>Last Logged In:</strong> {{ $status['last_login'] ?? 'Never' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column - Statistics and Activities --}}
        <div class="col-xl-8 col-lg-7">
            {{-- Students List --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-graduation-cap text-primary me-2"></i>Recent Students</h5>
                    @if ($user->students->count() > 5)
                        <a href="{{ route('agent.students.index', $user->slug) }}" class="btn btn-sm btn-link">View
                            All</a>
                    @endif
                </div>
                <div class="card-body">
                    @if ($user->students->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Applications</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($user->students->take(10) as $student)
                                        <tr>
                                            <td>
                                                <a href="{{ route('agent.students.show', $student->id) }}"
                                                    class="text-decoration-none fw-bold">
                                                    {{ $student->first_name }} {{ $student->last_name }}
                                                </a>
                                            </td>
                                            <td>{{ $student->email }}</td>
                                            <td>
                                                <span
                                                    class="badge {{ $student->student_status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ ucfirst($student->student_status ?? 'N/A') }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-info">{{ $student->applications->count() ?? 0 }}</span>
                                            </td>
                                            <td>{{ $student->created_at->format('F j, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No students found.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent Applications --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-signature text-primary me-2"></i>Recent Applications</h5>
                    @if ($user->applications->count() > 5)
                        <a href="{{ route('agent.applications.index', $user->slug) }}" class="btn btn-sm btn-link">View
                            All</a>
                    @endif
                </div>
                <div class="card-body">
                    @if ($user->applications->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Student</th>
                                        <th>Course</th>
                                        <th>University</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($user->applications->take(10) as $application)
                                        <tr>
                                            <td>
                                                <a href="{{ route('agent.students.show', $application->student_id) }}"
                                                    class="text-decoration-none">
                                                    {{ $application->student->first_name }}
                                                    {{ $application->student->last_name }}
                                                </a>
                                            </td>
                                            <td>{{ $application->course->title ?? 'N/A' }}</td>
                                            <td>{{ $application->course->university->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge {{ $application->status_class ?? 'bg-secondary' }}">
                                                    {{ $application->application_status ?? 'Pending' }}
                                                </span>
                                            </td>
                                            <td>{{ $application->created_at->format('F j, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No applications found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modern Activities Section --}}
    <div class="activities-modern-row">
        {{-- Student Activities --}}
        <div class="modern-activity-card">
            <div class="card-header-modern">
                <div class="header-icon student-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h5 class="mb-0">Student Activities</h5>
                <span class="activity-count">{{ $studentActivities->count() }}</span>
            </div>
            <div class="card-body-modern">
                @forelse($studentActivities as $act)
                    <div class="activity-item">
                        <div class="activity-dot student-dot"></div>
                        <div class="activity-content">
                            <a href="{{ route('agent.students.show', $act->notifiable_id ?? '#') }}"
                                class="activity-link">
                                {{ $act->description }}
                            </a>
                            <div class="activity-time">
                                <i class="far fa-clock me-1"></i>
                                {{ $act->created_at->format('F j, Y, g:i A') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-activities">
                        <i class="fas fa-inbox"></i>
                        <p>No student activities</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Document Activities --}}
        <div class="modern-activity-card">
            <div class="card-header-modern">
                <div class="header-icon document-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h5 class="mb-0">Document Activities</h5>
                <span class="activity-count">{{ $documentActivities->count() }}</span>
            </div>
            <div class="card-body-modern">
                @forelse($documentActivities as $act)
                    <div class="activity-item">
                        <div class="activity-dot document-dot"></div>
                        <div class="activity-content">
                            <a href="{{ route('agent.documents.index', $act->notifiable_id ?? '#') }}"
                                class="activity-link">
                                {{ $act->description }}
                            </a>
                            <div class="activity-time">
                                <i class="far fa-clock me-1"></i>
                                {{ $act->created_at->format('F j, Y, g:i A') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-activities">
                        <i class="fas fa-inbox"></i>
                        <p>No document activities</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Application Activities --}}
        <div class="modern-activity-card">
            <div class="card-header-modern">
                <div class="header-icon application-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h5 class="mb-0">Application Activities</h5>
                <span class="activity-count">{{ $applicationActivities->count() }}</span>
            </div>
            <div class="card-body-modern">
                @forelse($applicationActivities as $act)
                    <div class="activity-item">
                        <div class="activity-dot application-dot"></div>
                        <div class="activity-content">
                            <a href="{{ route('agent.applications.show', $act->notifiable_id ?? '#') }}"
                                class="activity-link">
                                {{ $act->description }}
                            </a>
                            <div class="activity-time">
                                <i class="far fa-clock me-1"></i>
                                {{ $act->created_at->format('F j, Y, g:i A') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-activities">
                        <i class="fas fa-inbox"></i>
                        <p>No application activities</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    </div>

    {{-- Reset Password Modal --}}
    <div class="modal fade" id="resetPasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-exclamation-triangle text-warning fa-4x"></i>
                    </div>
                    <p class="text-center mb-0">
                        Are you sure you want to reset your password?<br>
                        A new random password will be generated and displayed.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('agent.users.reset-password', $user->slug) }}" method="POST"
                        class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning">Reset Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDeleteStaff(slug, name) {
            if (confirm(`Are you sure you want to delete staff member "${name}"? This action cannot be undone.`)) {
                document.getElementById(`delete-staff-form-${slug}`).submit();
            }
        }
    </script>

@endsection
