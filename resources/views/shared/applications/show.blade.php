@extends('layouts.app')
@php $role = auth()->user()->role; $user = auth()->user(); @endphp

@section('title', 'Application #' . $application->application_number)

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route($role . '.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route($role . '.applications.index') }}" class="text-decoration-none">Applications</a></li>
                    <li class="breadcrumb-item active">Application #{{ $application->application_number }}</li>
                </ol>
            </nav>
            <h1 class="display-6 fw-bold mb-0">Application Details</h1>
        </div>
        <a href="{{ route($role . '.applications.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <span class="badge px-4 py-2 rounded-pill fs-6" style="background:{{ $application->status?->bg_color ?? '#6c757d' }}; color:{{ $application->status?->text_color ?? '#fff' }};">
                                <i class="fas fa-circle me-1" style="font-size:8px;"></i>{{ $application->status?->name ?? 'No Status' }}
                            </span>
                        </div>
                        <div class="text-muted small"><i class="far fa-calendar-alt me-1"></i>Created: {{ optional($application->created_at)->format('F j, Y') }}</div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0"><i class="fas fa-user-graduate me-2 text-primary"></i>Student Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-4 text-center">
                            @if ($application->student->students_photo && Storage::disk('public')->exists($application->student->students_photo))
                                <img src="{{ Storage::url($application->student->students_photo) }}" class="rounded border shadow-sm" style="width:150px;height:150px;object-fit:cover;">
                            @else
                                <div class="rounded d-flex align-items-center justify-content-center mx-auto shadow-sm bg-primary bg-opacity-10" style="width:150px;height:150px;">
                                    <i class="fas fa-user fa-4x text-primary"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="text-muted small text-uppercase">Full Name</label>
                                    <p class="fw-semibold mb-0">{{ $application->student->first_name }} {{ $application->student->last_name }}</p>
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-muted small text-uppercase">Email</label>
                                    <p class="fw-semibold mb-0">{{ $application->student->email }}</p>
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-muted small text-uppercase">Phone</label>
                                    <p class="fw-semibold mb-0">{{ $application->student->phone_number ?? 'N/A' }}</p>
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-muted small text-uppercase">Agent</label>
                                    <p class="fw-semibold mb-0">{{ $application->student->agent?->business_name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0"><i class="fas fa-file-alt me-2 text-primary"></i>Statement of Purpose</h5>
                </div>
                <div class="card-body p-4 text-center">
                    @if ($application->sop_file)
                        <i class="fas fa-file-pdf fa-4x text-danger mb-3"></i>
                        <p class="mb-3">Click below to view the SOP document</p>
                        <a href="{{ Storage::url($application->sop_file) }}" target="_blank" class="btn btn-primary rounded-pill px-4 previewable" data-url="{{ Storage::url($application->sop_file) }}" data-filename="SOP_{{ $application->application_number }}" data-preview-type="application">
                            <i class="fas fa-eye me-2"></i>View SOP
                        </a>
                    @else
                        <i class="fas fa-file-alt fa-4x mb-3 text-muted opacity-50"></i>
                        <p class="text-muted">No SOP document uploaded.</p>
                    @endif
                </div>
            </div>

            {{-- Messages --}}
            <div class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="fas fa-comments me-2 text-primary"></i>Messages</h5>
                </div>
                <div class="card-body p-4">
                    <div class="messages-container mb-4" style="max-height:400px;overflow-y:auto;">
                        @forelse($application->messages as $msg)
                            <div class="d-flex mb-3 {{ $msg->type === 'admin' ? 'justify-content-start' : 'justify-content-end' }}">
                                <div class="rounded-3 p-3 {{ $msg->type === 'admin' ? 'bg-light' : 'bg-primary text-white' }}" style="max-width:80%;">
                                    <small class="d-block {{ $msg->type === 'admin' ? 'text-muted' : 'text-white-50' }}">{{ $msg->type === 'admin' ? 'Admin' : 'Agent' }} · {{ $msg->created_at->diffForHumans() }}</small>
                                    @if($msg->message)<p class="mb-1 mt-1">{{ $msg->message }}</p>@endif
                                    @if($msg->file_path)
                                        <a href="{{ Storage::url($msg->file_path) }}" target="_blank" class="small {{ $msg->type === 'admin' ? '' : 'text-white' }} previewable"
                                           data-url="{{ Storage::url($msg->file_path) }}"
                                           data-filename="{{ basename($msg->file_path) }}"
                                           data-preview-type="document"><i class="fas fa-paperclip me-1"></i>Attachment</a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center py-3">No messages yet.</p>
                        @endforelse
                    </div>
                    <form action="{{ route($role . '.applications.addMessage', $application) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <textarea name="message" class="form-control" rows="2" placeholder="Type a message..."></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <input type="file" name="attachment" class="form-control form-control-sm">
                            <button class="btn btn-primary btn-sm rounded-pill px-4"><i class="fas fa-paper-plane"></i> Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0"><i class="fas fa-university me-2 text-primary"></i>University & Course</h5>
                </div>
                <div class="card-body p-4">
                    <div class="p-3 bg-light rounded-3">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-building fa-2x text-primary me-3"></i>
                            <div>
                                <h6 class="mb-0 fw-bold">{{ $application->university?->name ?? 'N/A' }}</h6>
                                <small class="text-muted">{{ $application->university?->city ?? '' }}, {{ $application->university?->country ?? '' }}</small>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="mt-2"><i class="fas fa-graduation-cap me-2 text-primary"></i><span class="fw-semibold">Course:</span> {{ $application->course?->title ?? 'N/A' }}</div>
                        <hr class="my-2">
                        <div class="mt-2"><i class="fas fa-money-bill-wave me-2 text-success"></i><span class="fw-semibold">Fee:</span> {{ $application->course?->fee ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0"><i class="fas fa-bolt me-2 text-primary"></i>Actions</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-grid gap-3">
                        @can('update', $application)
                            <a href="{{ route($role . '.applications.edit', $application) }}" class="btn btn-outline-primary rounded-pill py-2"><i class="fas fa-edit me-2"></i>Edit</a>
                        @endcan
                        @can('withdraw', $application)
                            <button class="btn btn-outline-danger rounded-pill py-2" data-bs-toggle="modal" data-bs-target="#withdrawModal"><i class="fas fa-ban me-2"></i>Withdraw</button>
                        @endcan
                        @can('updateStatus', $application)
                            <button class="btn btn-outline-warning rounded-pill py-2" data-bs-toggle="modal" data-bs-target="#statusModal"><i class="fas fa-exchange-alt me-2"></i>Update Status</button>
                        @endcan
                        @can('delete', $application)
                            <x-confirm-delete
                                action="{{ $role }}.applications.destroy"
                                :id="$application->id"
                                label="Delete Application"
                                title="Delete Application #{{ $application->application_number }}?"
                                message="This will permanently delete this application and all associated data. This action cannot be undone."
                                class="btn btn-outline-danger rounded-pill py-2"
                            />
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@can('withdraw', $application)
    <div class="modal fade" id="withdrawModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Withdraw Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route($role . '.applications.withdraw', $application) }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="modal-body">
                        <p>Are you sure you want to withdraw this application?</p>
                        <div class="mb-3">
                            <label class="form-label">Reason (optional)</label>
                            <textarea name="reason" class="form-control" rows="2" placeholder="Enter reason for withdrawal..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4">Yes, Withdraw</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan

@can('updateStatus', $application)
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Update Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route($role . '.applications.updateStatus', $application) }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="modal-body">
                        <select name="application_status_id" class="form-select form-select-lg" required>
                            <option value="">-- Select Status --</option>
                            @foreach($statuses as $st)
                                <option value="{{ $st->id }}" {{ $application->application_status_id == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan
@endsection
