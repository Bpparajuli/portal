{{-- resources/views/admin/users/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'User Details - ' . ($user->business_name ?? $user->name))
@section('content')
    <div>
        {{-- Header Section with Gradient Background --}}
        <div class="row mb-2">
            <div class="col-12">
                <div class="card bg-gradient-primary">
                    <div class="card-body p-2">
                        <div class="row align-items-start">
                            <div class="col-auto">
                                @if ($user->business_logo)
                                    <img src="{{ Storage::url($user->business_logo) }}" alt="{{ $user->business_name }}"
                                        class="rounded border border-3 border-white shadow" width="100" height="100"
                                        style="object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center shadow"
                                        style="width: 100px; height: 100px;">
                                        <i class="fas fa-building fa-3x text-white"></i>
                                    </div>
                                @endif
                            </div>
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
                                    @if ($user->parent)
                                        <span class="badge bg-info text-dark px-3 py-2">
                                            <i class="fas fa-building me-1"></i> Parent: {{ $user->parent->business_name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            {{-- Statistics Cards Row --}}
                            <div class="col d-flex justify-content-center gap-3 align-items-center flex-wrap">
                                <div class="card bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-secondary mb-1">Total Students</h6>
                                            <h2 class="text-secondary mb-0">{{ $students->count() }}</h2>
                                        </div>
                                        <div>
                                            <i class="fas fa-users fa-2x text-secondary"></i>
                                        </div>
                                    </div>
                                    @if ($user->role === 'agent')
                                        <a href="{{ route('admin.users.students', $user) }}"
                                            class="text-secondary small text-decoration-none mt-2 d-block">
                                            View All Students <i class="fas fa-arrow-right"></i>
                                        </a>
                                    @endif
                                </div>
                                <div class="card bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-primary mb-1">Total Applications</h6>
                                            <h2 class="text-primary mb-0">{{ $applications->count() }}</h2>
                                        </div>
                                        <div>
                                            <i class="fas fa-file-alt fa-2x text-primary"></i>
                                        </div>
                                    </div>
                                    @if ($user->role === 'agent')
                                        <a href="{{ route('admin.users.applications', $user) }}"
                                            class="text-primary small text-decoration-none mt-2 d-block">
                                            View All Applications <i class="fas fa-arrow-right"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="dropdown">
                                    <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-cog"></i> Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.users.edit', $user->slug) }}">
                                                <i class="fas fa-edit text-primary"></i> Edit User
                                            </a>
                                        </li>
                                        @if (!$user->active)
                                            <li>
                                                <form action="{{ route('admin.users.approve', $user) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-check-circle text-success"></i> Approve User
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                        @if ($user->agreement_status === 'uploaded')
                                            <li>
                                                <form action="{{ route('admin.users.verifyAgreement', $user) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PUT') {{-- Add this if the route expects PUT --}}
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-check-double text-success"></i> Verify Agreement
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteUserModal">
                                                <i class="fas fa-trash-alt"></i> Delete User
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
                <div class="card mb-2">
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
                <div class="card mb-4">
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
                                            <a href="{{ Storage::url($user->$docKey) }}"
                                                class="btn btn-sm btn-outline-primary me-1 previewable">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ Storage::url($user->$docKey) }}" download
                                                class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            @if ($docKey === 'agreement_file')
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                                    data-url="{{ route('admin.users.agreement.delete', $user->slug) }}"
                                                    data-name="Agreement Document for {{ $user->business_name }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
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

                {{-- Login Status Card --}}
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-0"><i class="fas fa-sign-in-alt text-primary me-2"></i>Login Status</h5>

                        @php $status = $user->online_status; @endphp
                        <div class="text-start p-2">
                            <p>Joined In: {{ $user->created_at->format('M d, Y') }}</p>
                            <p>Last Logged In: {{ $user->online_status['last_login'] ?? 'Never' }}</p>
                            <p>Last Login IP: {{ $user->online_status['last_login_ip'] ?? 'N/A' }}</p>
                            </p>
                            @if ($status['is_online'])
                                <div ">
                                                                                <span class=" badge bg-success p-1">
                                                                                <i class="fas fa-circle me-1"></i> Online
                                                                                </span>
                                                                                <small class="text-muted">Active now</small>
                                                                            </div>
@else
    <div>
                                                                                <span class="badge bg-secondary p-1">
                                                                                    <i class="fas fa-circle me-1"></i> Offline
                                                                                </span>
                                                                            </div>
                                                                            <small class="text-muted">Last seen: {{ $status['last_seen_human'] }}</small>
                                                                                         @if ($status['last_seen_full'])
                                    <br><small class="text-muted">{{ $status['last_seen_full'] }}</small>
                            @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column - Statistics and Activities --}}
            <div class="col-xl-8 col-lg-7">
                {{-- Students List --}}
                <div class="card mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-graduation-cap text-primary me-2"></i>Recent Students</h5>
                        @if ($students->count() > 5)
                            <a href="{{ route('admin.users.students', $user) }}" class="btn btn-sm btn-link">View All</a>
                        @endif
                    </div>
                    <div class="card-body">
                        @if ($students->count() > 0)
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
                                        @foreach ($students->take(5) as $student)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('admin.students.show', $student) }}"
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
                                                <td class="text-center ">
                                                    <span
                                                        class=" badge bg-info">{{ $student->applications_count ?? 0 }}</span>
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
                                <p class="text-muted mb-0">No students found for this user.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Recent Applications --}}
                <div class="card mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-file-signature text-primary me-2"></i>Recent Applications</h5>
                        @if ($applications->count() > 5)
                            <a href="{{ route('admin.users.applications', $user) }}" class="btn btn-sm btn-link">View
                                All</a>
                        @endif
                    </div>
                    <div class="card-body">
                        @if ($applications->count() > 0)
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
                                        @foreach ($applications->take(5) as $application)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('admin.students.show', $application->student) }}"
                                                        class="text-decoration-none">
                                                        {{ $application->student->first_name }}
                                                        {{ $application->student->last_name }}
                                                    </a>
                                                </td>
                                                <td>{{ $application->course->title ?? 'N/A' }}</td>
                                                <td>{{ $application->course->university->name ?? 'N/A' }}</td>
                                                <td>
                                                    @php
                                                        $latestApplication = $student->applications
                                                            ->sortByDesc('created_at')
                                                            ->first();
                                                    @endphp
                                                    @if ($latestApplication)
                                                        <a
                                                            href="{{ route('admin.applications.show', $latestApplication->id) }}">
                                                            <span
                                                                class="badge {{ $latestApplication->status_class }}">{{ $latestApplication->application_status }}</span>
                                                        </a>
                                                    @else
                                                        <span class="badge bg-light text-muted">No Application</span>
                                                    @endif
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
                                <p class="text-muted mb-0">No applications found for this user.</p>
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
                                <a href="{{ route('admin.students.show', $act->notifiable_id ?? '#') }}"
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
                                <a href="{{ route('admin.documents.index', $act->notifiable_id ?? '#') }}"
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
                                <a href="{{ route('admin.applications.show', $act->notifiable_id ?? '#') }}"
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

    {{-- Delete User Modal --}}
    <div class="modal fade" id="deleteUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-exclamation-triangle text-warning fa-4x"></i>
                    </div>
                    <p class="text-center mb-0">
                        Are you sure you want to delete <strong>{{ $user->business_name ?? $user->name }}</strong>?<br>
                        This action cannot be undone and will delete all associated students and applications.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
