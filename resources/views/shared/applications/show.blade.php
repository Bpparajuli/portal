@php
    $__user = auth()->user();
    $__isAgent = $__user->is_agent;
    $__isStaff = $__user->is_staff && !$__user->is_admin_staff;
    $__layout = $__isAgent ? 'layouts.agent' : ($__isStaff ? 'layouts.staff' : 'layouts.admin');
    $__section = $__isAgent ? 'agent-content' : ($__isStaff ? 'staff-content' : 'admin-content');
    $role = $__user->is_admin_staff ? 'admin' : $__user->role;
@endphp

@extends($__layout)

@section('title', 'Application #' . $application->application_number)
@section('page-title', 'Application #' . $application->application_number)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
@endpush

@section($__section)
<div class="container-fluid px-3 py-3">

    {{-- Gradient Header --}}
    <div class="app-show-gradient">
        <div class="inner">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h1><i class="fas fa-file-alt me-2"></i>Application #{{ $application->application_number }}</h1>
                    <div class="meta">
                        <span class="meta-item"><i class="far fa-calendar-alt me-1"></i>{{ optional($application->created_at)->format('F j, Y') }}</span>
                        @if($application->course?->fee)
                            <span class="meta-item"><i class="fas fa-money-bill-wave me-1"></i>{{ number_format((float)$application->course->fee, 2) }}</span>
                        @endif
                        <span class="badge px-3 py-1 rounded-pill" style="font-size:0.72rem;background:{{ $application->status?->bg_color ?? '#6c757d' }};color:{{ $application->status?->text_color ?? '#fff' }};">
                            {{ $application->status?->name ?? 'No Status' }}
                        </span>
                    </div>
                </div>
                <a href="{{ route($role . '.applications.index') }}" class="btn" style="font-size:0.75rem;padding:6px 18px;border-radius:8px;border:1px solid rgba(255,255,255,0.2);color:#fff;background:rgba(255,255,255,0.08);text-decoration:none;">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">

            {{-- Student Info --}}
            <div class="app-show-card">
                <div class="app-show-card-header"><i class="fas fa-user-graduate" style="color:#6366f1;"></i>Student Information</div>
                <div class="app-show-card-body">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-3 text-center">
                            @if ($application->student->students_photo && Storage::disk('public')->exists($application->student->students_photo))
                                <img src="{{ Storage::url($application->student->students_photo) }}" class="rounded border shadow-sm" style="width:120px;height:120px;object-fit:cover;">
                            @else
                                <div class="rounded d-flex align-items-center justify-content-center mx-auto" style="width:120px;height:120px;background:#eef2ff;">
                                    <i class="fas fa-user fa-3x" style="color:var(--primary);"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <div class="app-show-info-grid">
                                <div class="app-show-info-item"><small>Full Name</small><div class="val">{{ $application->student->first_name }} {{ $application->student->last_name }}</div></div>
                                <div class="app-show-info-item"><small>Email</small><div class="val">{{ $application->student->email }}</div></div>
                                <div class="app-show-info-item"><small>Phone</small><div class="val">{{ $application->student->phone_number ?? 'N/A' }}</div></div>
                                <div class="app-show-info-item"><small>Agent</small><div class="val">{{ $application->student->agent?->business_name ?? 'N/A' }}</div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Messages --}}
            <div class="app-show-card">
                <div class="app-show-card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-comments" style="color:#3b82f6;"></i> Messages</span>
                </div>
                <div class="app-show-card-body">
                    <div class="mb-4" style="max-height:400px;overflow-y:auto;">
                        @forelse($application->messages as $msg)
                            <div class="d-flex mb-3 {{ $msg->type === 'admin' ? 'justify-content-start' : 'justify-content-end' }}">
                                <div class="app-show-msg-bubble {{ $msg->type === 'admin' ? 'admin' : 'agent' }}">
                                    <small class="d-block {{ $msg->type === 'admin' ? 'text-muted' : '' }}" style="font-size:0.65rem;">{{ $msg->type === 'admin' ? 'Admin' : 'Agent' }} · {{ $msg->created_at->diffForHumans() }}</small>
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
                            <p class="text-muted text-center py-3" style="font-size:0.82rem;">No messages yet.</p>
                        @endforelse
                    </div>
                    <form action="{{ route($role . '.applications.addMessage', $application) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <textarea name="message" class="app-form-control form-control" rows="2" placeholder="Type a message..."></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <input type="file" name="attachment" class="app-form-control form-control" style="font-size:0.78rem;padding:6px 10px;">
                            <button class="btn btn-primary" style="font-size:0.78rem;padding:7px 20px;border-radius:8px;"><i class="fas fa-paper-plane me-1"></i>Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- University & Course --}}
            <div class="app-show-card">
                <div class="app-show-card-header"><i class="fas fa-university" style="color:#8b5cf6;"></i>University &amp; Course</div>
                <div class="app-show-card-body">
                    <div class="p-3 rounded-3" style="background:#f8faff;">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-building fa-2x me-3" style="color:var(--primary);"></i>
                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size:0.85rem;">{{ $application->university?->name ?? 'N/A' }}</h6>
                                <small class="text-muted">{{ $application->university?->city ?? '' }}, {{ $application->university?->country ?? '' }}</small>
                            </div>
                        </div>
                        <hr style="border-color:#e5e7eb;margin:0.5rem 0;">
                        <div class="mt-2" style="font-size:0.82rem;"><i class="fas fa-graduation-cap me-2" style="color:var(--primary);"></i><span class="fw-semibold">Course:</span> {{ $application->course?->title ?? 'N/A' }}</div>
                        <hr style="border-color:#e5e7eb;margin:0.5rem 0;">
                        <div class="mt-2" style="font-size:0.82rem;"><i class="fas fa-money-bill-wave me-2 text-success"></i><span class="fw-semibold">Fee:</span> {{ $application->course?->fee ? number_format((float)$application->course->fee, 2) : 'N/A' }}</div>
                    </div>
                </div>
            </div>

            {{-- SOP --}}
            <div class="app-show-card">
                <div class="app-show-card-header"><i class="fas fa-file-alt" style="color:#f59e0b;"></i>Statement of Purpose (SOP)</div>
                <div class="app-show-card-body text-center">
                    @if ($application->sop_file)
                        <i class="fas fa-file-pdf fa-4x mb-3" style="color:#dc2626;"></i>
                        <p style="font-size:0.85rem;">Click below to view the SOP document</p>
                        <a href="{{ Storage::url($application->sop_file) }}" target="_blank" class="btn btn-primary previewable" style="font-size:0.82rem;padding:8px 24px;border-radius:10px;"
                           data-url="{{ Storage::url($application->sop_file) }}"
                           data-filename="SOP_{{ $application->application_number }}"
                           data-preview-type="application">
                            <i class="fas fa-eye me-1"></i>View SOP
                        </a>
                    @else
                        <i class="fas fa-file-alt fa-4x mb-3" style="color:#d1d5db;"></i>
                        <p class="text-muted">No SOP document uploaded.</p>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="app-show-card">
                <div class="app-show-card-header"><i class="fas fa-bolt" style="color:#f59e0b;"></i>Actions</div>
                <div class="app-show-card-body">
                    <div class="d-grid gap-2">
                        @unless($__isAgent)
                        @can('update', $application)
                            <a href="{{ route($role . '.applications.edit', $application) }}" class="btn btn-outline-primary" style="font-size:0.82rem;padding:9px 16px;border-radius:10px;"><i class="fas fa-edit me-1"></i>Edit</a>
                        @endcan
                        @endunless
                        @can('withdraw', $application)
                            @if($__isAgent)
                                <button class="btn btn-outline-danger" style="font-size:0.82rem;padding:9px 16px;border-radius:10px;" onclick="withdrawApplication({{ $application->id }})"><i class="fas fa-ban me-1"></i>Withdraw</button>
                            @else
                                <button class="btn btn-outline-danger" style="font-size:0.82rem;padding:9px 16px;border-radius:10px;" data-bs-toggle="modal" data-bs-target="#withdrawModal"><i class="fas fa-ban me-1"></i>Withdraw</button>
                            @endif
                        @endcan
                        @unless($__isAgent)
                        @can('updateStatus', $application)
                            <button class="btn btn-outline-warning" style="font-size:0.82rem;padding:9px 16px;border-radius:10px;" data-bs-toggle="modal" data-bs-target="#statusModal"><i class="fas fa-exchange-alt me-1"></i>Update Status</button>
                        @endcan
                        @can('delete', $application)
                            <x-confirm-delete
                                action="{{ $role }}.applications.destroy"
                                :id="$application->id"
                                label="Delete Application"
                                title="Delete Application #{{ $application->application_number }}?"
                                message="This will permanently delete this application and all associated data."
                                class="btn btn-outline-danger" style="font-size:0.82rem;padding:9px 16px;border-radius:10px;"
                            />
                        @endcan
                        @endunless
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Withdraw Modal --}}
@can('withdraw', $application)
<div class="modal fade" id="withdrawModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content app-modal-content">
            <div class="app-modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-ban text-danger me-2"></i>Withdraw Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route($role . '.applications.withdraw', $application) }}" method="POST">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <p style="font-size:0.85rem;">Are you sure you want to withdraw this application?</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.8rem;">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="Enter reason for withdrawal..." required style="font-size:0.82rem;border-radius:8px;"></textarea>
                    </div>
                </div>
                <div class="app-modal-footer d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light" style="font-size:0.82rem;padding:7px 20px;border-radius:8px;" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" style="font-size:0.82rem;padding:7px 20px;border-radius:8px;">Yes, Withdraw</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

{{-- Update Status Modal --}}
@can('updateStatus', $application)
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content app-modal-content">
            <div class="app-modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-exchange-alt text-warning me-2"></i>Update Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route($role . '.applications.updateStatus', $application) }}" method="POST">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <select name="application_status_id" class="form-select form-select-lg" required style="font-size:0.85rem;border-radius:8px;">
                        <option value="">-- Select Status --</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st->id }}" {{ $application->application_status_id == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="app-modal-footer d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light" style="font-size:0.82rem;padding:7px 20px;border-radius:8px;" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="font-size:0.82rem;padding:7px 20px;border-radius:8px;">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script>
async function withdrawApplication(id) {
    const { value: reason } = await Swal.fire({
        title: 'Withdraw Application',
        text: 'Are you sure you want to withdraw this application?',
        icon: 'warning',
        input: 'textarea',
        inputLabel: 'Reason for withdrawal',
        inputPlaceholder: 'Enter the reason...',
        inputAttributes: { required: true },
        inputValidator: (value) => { if (!value || !value.trim()) return 'Reason is required'; },
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, Withdraw',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    });
    if (reason) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route($role . ".applications.withdraw", $application) }}';
        form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="PATCH"><input type="hidden" name="reason" value="' + encodeURIComponent(reason) + '">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection