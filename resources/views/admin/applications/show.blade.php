@extends('layouts.admin')

@section('admin-content')
<style>
    .app-detail-page { max-width: 1200px; margin: 0 auto; }

    .info-label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        margin-bottom: 0.25rem;
    }

    .info-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-color);
    }

    .timeline {
        position: relative;
        padding-left: 2rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--border);
    }

    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }

    .timeline-item:last-child { padding-bottom: 0; }

    .timeline-dot {
        position: absolute;
        left: -2rem;
        top: 4px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 3px solid var(--primary);
        background: var(--bg-card);
        z-index: 1;
    }

    .timeline-dot.completed { background: var(--success); border-color: var(--success); }
    .timeline-dot.active { background: var(--warning); border-color: var(--warning); animation: pulse 2s infinite; }

    .timeline-content h6 { font-weight: 600; font-size: 0.9rem; }
    .timeline-content p { font-size: 0.82rem; color: var(--text-muted); margin: 0; }

    .status-badge-lg {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1.25rem;
        border-radius: var(--radius-full);
        font-size: 0.9rem;
        font-weight: 700;
    }

    .detail-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        transition: all var(--transition-fast);
    }

    .detail-card:hover { box-shadow: var(--shadow-md); }

    .detail-card .card-head {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg-main);
    }

    .detail-card .card-body { padding: 1.5rem; }

    .quick-action-btn {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.875rem 1.25rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        transition: all var(--transition-fast);
        cursor: pointer;
        text-decoration: none;
        color: var(--text-color);
    }

    .quick-action-btn:hover {
        border-color: var(--primary);
        background: var(--primary-soft);
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
    }

    .student-photo {
        width: 120px;
        height: 120px;
        border-radius: var(--radius-lg);
        object-fit: cover;
        border: 3px solid var(--border);
        box-shadow: var(--shadow-sm);
    }

    .student-photo-placeholder {
        width: 120px;
        height: 120px;
        border-radius: var(--radius-lg);
        background: var(--gradient-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 2.5rem;
    }

    @media (max-width: 768px) {
        .student-photo, .student-photo-placeholder {
            width: 80px;
            height: 80px;
            font-size: 1.75rem;
        }
    }
</style>

<div class="app-detail-page">
    <!-- Breadcrumb & Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.applications.index') }}">Applications</a></li>
                    <li class="breadcrumb-item active">#{{ $application->application_number }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0">
                <i class="fas fa-file-alt text-primary me-2"></i>Application Details
            </h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.applications.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
            <a href="{{ route('admin.applications.edit', $application->id) }}" class="btn btn-outline">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
        </div>
    </div>

    <!-- Status Banner -->
    <div class="detail-card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="status-badge-lg" style="background:{{ $application->status?->bg_color ?? 'var(--primary)' }}20;color:{{ $application->status?->bg_color ?? 'var(--primary)' }};border:2px solid {{ $application->status?->bg_color ?? 'var(--primary)' }};">
                    <i class="fas fa-circle me-1" style="font-size:8px;"></i>
                    {{ $application->status?->name ?? 'No Status' }}
                </span>
            </div>
            <div class="d-flex align-items-center gap-3 text-muted small">
                <span><i class="far fa-calendar-alt me-1"></i>Created: {{ optional($application->created_at)->format('M d, Y') }}</span>
                <span><i class="far fa-clock me-1"></i>{{ optional($application->created_at)->diffInDays(now()) ?? 0 }} days in process</span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Column -->
        <div class="col-lg-8">
            <!-- Student Info -->
            <div class="detail-card mb-4">
                <div class="card-head">
                    <h5 class="fw-bold mb-0"><i class="fas fa-user-graduate text-primary me-2"></i>Student Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-3 text-center">
                            @if ($application->student->students_photo && Storage::disk('public')->exists($application->student->students_photo))
                                <img src="{{ Storage::url($application->student->students_photo) }}" class="student-photo" alt="">
                            @else
                                <div class="student-photo-placeholder mx-auto">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="info-label">Full Name</div>
                                    <div class="info-value">{{ $application->student->first_name }} {{ $application->student->last_name }}</div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-label">Email</div>
                                    <div class="info-value">{{ $application->student->email }}</div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-label">Phone</div>
                                    <div class="info-value">{{ $application->student->phone_number ?? 'N/A' }}</div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-label">Agent</div>
                                    <div class="info-value">{{ $application->student->agent?->business_name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-12">
                                    <div class="info-label">Address</div>
                                    <div class="info-value">{{ $application->student->permanent_address ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- University & Course -->
            <div class="detail-card mb-4">
                <div class="card-head">
                    <h5 class="fw-bold mb-0"><i class="fas fa-university text-primary me-2"></i>University & Course</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3" style="background:var(--bg-main);border-radius:var(--radius-md);">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div style="width:48px;height:48px;border-radius:var(--radius-md);background:var(--primary-soft);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="fas fa-building text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0">{{ $application->university->name ?? 'N/A' }}</h6>
                                        <small class="text-muted">{{ $application->university->city ?? '' }}{{ $application->university->city && $application->university->country ? ', ' : '' }}{{ $application->university->country ?? '' }}</small>
                                    </div>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <i class="fas fa-graduation-cap text-primary"></i>
                                    <span class="fw-semibold">Course:</span>
                                    <span>{{ $application->course->title ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3" style="background:var(--bg-main);border-radius:var(--radius-md);">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div style="width:48px;height:48px;border-radius:var(--radius-md);background:var(--success-soft);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="fas fa-clock text-success"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0">Timeline</h6>
                                        <small class="text-muted">Application Progress</small>
                                    </div>
                                </div>
                                <hr class="my-2">
                                <div class="mt-2">
                                    <i class="fas fa-calendar-check text-success me-2"></i>
                                    <span class="fw-semibold">Days Active:</span>
                                    {{ number_format(optional($application->created_at)->diffInDays(now()) ?? 0) }} days
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="detail-card mb-4">
                <div class="card-head">
                    <h5 class="fw-bold mb-0"><i class="fas fa-history text-primary me-2"></i>Status Timeline</h5>
                </div>
                <div class="card-body">
                    @php
                        $history = $application->statusHistories ?? collect();
                    @endphp
                    @if($history->count() > 0)
                        <div class="timeline">
                            @foreach($history as $h)
                                <div class="timeline-item">
                                    <div class="timeline-dot {{ $loop->first ? 'active' : 'completed' }}"></div>
                                    <div class="timeline-content">
                                        <h6>{{ $h->status_name ?? 'Status Changed' }}</h6>
                                        <p>{{ $h->created_at?->format('M d, Y h:i A') ?? '' }}
                                            @if($h->changed_by_name)
                                                &middot; by {{ $h->changed_by_name }}
                                            @endif
                                        </p>
                                        @if($h->reason)
                                            <p class="mt-1" style="background:var(--bg-main);padding:0.5rem;border-radius:var(--radius-xs);">
                                                <i class="fas fa-quote-left text-muted me-1" style="font-size:0.65rem;"></i>
                                                {{ $h->reason }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-history fa-2x mb-2" style="opacity:0.3;"></i>
                            <p class="mb-0">No status history available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- SOP -->
            <div class="detail-card mb-4">
                <div class="card-head">
                    <h5 class="fw-bold mb-0"><i class="fas fa-file-alt text-primary me-2"></i>Statement of Purpose</h5>
                </div>
                <div class="card-body text-center">
                    @if ($application->sop_file)
                        <i class="fas fa-file-pdf fa-4x text-danger mb-3"></i>
                        <p class="mb-3">SOP document is attached to this application</p>
                        <a href="{{ Storage::url($application->sop_file) }}" target="_blank" class="btn btn-primary">
                            <i class="fas fa-eye me-1"></i>View SOP
                        </a>
                        <a href="{{ Storage::url($application->sop_file) }}" download class="btn btn-outline ms-2">
                            <i class="fas fa-download me-1"></i>Download
                        </a>
                    @else
                        <i class="fas fa-file-alt fa-4x mb-3" style="opacity:0.2;color:var(--text-muted);"></i>
                        <p class="text-muted">No SOP document uploaded</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Update Status -->
            <div class="detail-card mb-4">
                <div class="card-head">
                    <h5 class="fw-bold mb-0"><i class="fas fa-sync-alt text-primary me-2"></i>Update Status</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.applications.update', $application->id) }}" method="POST">
                        @csrf @method('PUT')
                        <input type="hidden" name="university_id" value="{{ $application->university_id }}">
                        <input type="hidden" name="course_id" value="{{ $application->course_id }}">
                        <div class="mb-3">
                            <label class="form-label">Application Status</label>
                            <select name="application_status_id" class="form-select" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}" {{ $application->application_status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Update Status</button>
                    </form>
                    <hr>
                    <a href="{{ route('admin.applications.edit', $application->id) }}" class="btn btn-outline-secondary w-100"><i class="fas fa-edit me-1"></i>Edit Full Application</a>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="detail-card">
                <div class="card-head">
                    <h5 class="fw-bold mb-0"><i class="fas fa-bolt text-primary me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    <a href="{{ route('admin.applications.edit', $application->id) }}" class="quick-action-btn">
                        <i class="fas fa-edit text-warning"></i>
                        <span>Edit Application</span>
                    </a>

                    @if (!$application->withdrawn_at)
                        <button class="quick-action-btn text-danger w-100" data-bs-toggle="modal" data-bs-target="#withdrawModal" style="border-color:var(--danger-soft);">
                            <i class="fas fa-ban text-danger"></i>
                            <span>Withdraw Application</span>
                        </button>
                    @endif

                    <a href="{{ route('admin.students.show', $application->student->id) }}" class="quick-action-btn">
                        <i class="fas fa-user-graduate text-info"></i>
                        <span>View Student Profile</span>
                    </a>

                    <a href="mailto:{{ $application->student->email }}" class="quick-action-btn">
                        <i class="fas fa-envelope text-primary"></i>
                        <span>Send Email</span>
                    </a>

                    <button class="quick-action-btn" onclick="window.print()">
                        <i class="fas fa-print text-muted"></i>
                        <span>Print Details</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Withdraw Modal -->
@if (!$application->withdrawn_at)
<div class="modal fade" id="withdrawModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-ban text-danger me-2"></i>Withdraw Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to withdraw this application?</p>
                <p class="text-muted small mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.applications.withdraw', $application->id) }}" method="POST" class="d-inline">
                    @csrf @method('PUT')
                    <button type="submit" class="btn btn-danger">Yes, Withdraw</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
