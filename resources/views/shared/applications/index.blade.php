@php
    $__user = auth()->user();
    $__isAgent = $__user->is_agent;
    $__isStaff = $__user->is_staff && !$__user->is_admin_staff;
    $__layout = $__isAgent ? 'layouts.agent' : ($__isStaff ? 'layouts.staff' : 'layouts.admin');
    $__section = $__isAgent ? 'agent-content' : ($__isStaff ? 'staff-content' : 'admin-content');
    $role = $__user->role;
@endphp

@extends($__layout)

@section('title', 'Applications')
@section('page-title', 'Applications')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
@endpush

@section($__section)
    <div class="container-fluid px-3 py-3">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h4 class="fw-bold mb-1" style="color:#1f2937;"><i class="fas fa-file-alt text-primary me-2"></i>Applications
                </h4>
                <p class="text-muted mb-0 small">Manage all student applications</p>
            </div>
            <div class="d-flex gap-2">
                @can('create', App\Models\Application::class)
                    <a href="{{ route($role . '.applications.create') }}" class="btn btn-primary"
                        style="font-size:0.82rem;padding:8px 20px;border-radius:10px;">
                        <i class="fas fa-plus me-1"></i>New Application
                    </a>
                @endcan
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="card border-0 h-100 shadow-sm" style="border-radius:12px;">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <div class="rounded-3 p-3" style="background:rgba(79,70,229,0.1);">
                            <i class="fas fa-file-alt fa-lg" style="color:#4f46e5;"></i>
                        </div>
                        <div>
                            <div class="small text-muted text-uppercase" style="font-size:0.68rem;letter-spacing:0.03em;">
                                Total</div>
                            <div class="fs-4 fw-bold" style="color:#1e293b;">{{ $applications->total() }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 h-100 shadow-sm" style="border-radius:12px;">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <div class="rounded-3 p-3" style="background:rgba(16,185,129,0.1);">
                            <i class="fas fa-check-circle fa-lg" style="color:#10b981;"></i>
                        </div>
                        <div>
                            <div class="small text-muted text-uppercase" style="font-size:0.68rem;letter-spacing:0.03em;">
                                Accepted</div>
                            <div class="fs-4 fw-bold" style="color:#1e293b;">{{ $acceptedCount ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 h-100 shadow-sm" style="border-radius:12px;">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <div class="rounded-3 p-3" style="background:rgba(239,68,68,0.1);">
                            <i class="fas fa-times-circle fa-lg" style="color:#ef4444;"></i>
                        </div>
                        <div>
                            <div class="small text-muted text-uppercase" style="font-size:0.68rem;letter-spacing:0.03em;">
                                Rejected</div>
                            <div class="fs-4 fw-bold" style="color:#1e293b;">{{ $rejectedCount ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 h-100 shadow-sm" style="border-radius:12px;">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <div class="rounded-3 p-3" style="background:rgba(148,163,184,0.15);">
                            <i class="fas fa-ban fa-lg" style="color:#64748b;"></i>
                        </div>
                        <div>
                            <div class="small text-muted text-uppercase" style="font-size:0.68rem;letter-spacing:0.03em;">
                                Lost</div>
                            <div class="fs-4 fw-bold" style="color:#1e293b;">{{ $lostCount ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        @include('shared.applications._filters')

        {{-- Table --}}
        <div class="app-table-wrap shadow-sm">
            <table class="app-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        @unless ($__isAgent)
                            <th>Agent</th>
                        @endunless
                        <th>University</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                        <tr>
                            <td style="color:#9ca3af;font-size:0.75rem;">{{ $app->id ?? $app->application_number }}</td>
                            <td>
                                <a href="{{ route($role . '.students.show', $app->student_id) }}"
                                    class="text-decoration-none fw-semibold" style="color:var(--primary);">
                                    {{ $app->student?->full_name ?? '—' }}
                                </a>
                            </td>
                            @unless ($__isAgent)
                                <td style="font-size:0.78rem;">{{ $app->agent?->business_name ?? '—' }}</td>
                            @endunless
                            <td style="font-size:0.78rem;">
                                {{ $app->university?->short_name ?? ($app->university?->name ?? '—') }}</td>
                            <td style="font-size:0.75rem;color:#6b7280;">{{ $app->course?->title ?? '—' }}</td>
                            <td>
                                <span class="app-status-badge"
                                    style="background:{{ $app->status?->bg_color ?? '#6c757d' }}20;color:{{ $app->status?->bg_color ?? '#6c757d' }};">
                                    <span
                                        style="width:6px;height:6px;border-radius:50%;background:{{ $app->status?->bg_color ?? '#6c757d' }};display:inline-block;"></span>
                                    {{ $app->status?->name ?? '—' }}
                                </span>
                            </td>
                            <td style="font-size:0.75rem;color:#9ca3af;">{{ $app->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                <div class="dropdown" style="display:inline-block;">
                                    <button data-bs-toggle="dropdown"
                                        style="width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #e5e7eb;background:#fff;color:#6b7280;font-size:0.75rem;cursor:pointer;transition:all 0.15s;padding:0;"
                                        onmouseover="this.style.background='#eef2ff';this.style.borderColor='var(--primary)'"
                                        onmouseout="this.style.background='#fff';this.style.borderColor='#e5e7eb'"><i
                                            class="fas fa-ellipsis-v"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0"
                                        style="border-radius:10px;font-size:0.82rem;padding:0.4rem;">
                                        <li><a class="dropdown-item"
                                                href="{{ route($role . '.applications.show', $app->id) }}"
                                                style="padding:0.4rem 0.8rem;border-radius:6px;font-size:0.78rem;"><i
                                                    class="fas fa-eye me-2" style="color:#3b82f6;"></i>View</a></li>
                                        @unless ($__isAgent)
                                            @can('update', $app)
                                                <li><a class="dropdown-item"
                                                        href="{{ route($role . '.applications.edit', $app->id) }}"
                                                        style="padding:0.4rem 0.8rem;border-radius:6px;font-size:0.78rem;"><i
                                                            class="fas fa-edit me-2" style="color:#f59e0b;"></i>Edit</a></li>
                                            @endcan
                                            @can('updateStatus', $app)
                                                <li><button type="button" class="dropdown-item"
                                                        style="padding:0.4rem 0.8rem;border-radius:6px;font-size:0.78rem;border:none;background:transparent;width:100%;text-align:left;"
                                                        onclick="updateStatusApp({{ $app->id }}, {{ $app->application_status_id }})"><i
                                                            class="fas fa-exchange-alt me-2" style="color:#8b5cf6;"></i>Update
                                                        Status</button></li>
                                            @endcan
                                        @endunless
                                        @can('withdraw', $app)
                                            <li><button type="button" class="dropdown-item text-danger"
                                                    style="padding:0.4rem 0.8rem;border-radius:6px;font-size:0.78rem;border:none;background:transparent;width:100%;text-align:left;"
                                                    onclick="withdrawApp({{ $app->id }})"><i
                                                        class="fas fa-ban me-2"></i>Withdraw</button></li>
                                        @endcan
                                        @unless ($__isAgent)
                                            @can('delete', $app)
                                                <li>
                                                    <hr class="dropdown-divider" style="margin:0.2rem 0;">
                                                </li>
                                                <li>
                                                    <x-confirm-delete action="{{ $role }}.applications.destroy"
                                                        :id="$app->id" label="Delete"
                                                        title="Delete Application of: {{ $app->student?->full_name ?? '—' }} for {{ $app->course?->title ?? '—' }}"
                                                        message="This will permanently delete this Application."
                                                        class="btn btn-sm btn-outline-danger" />
                                                </li>
                                            @endcan
                                        @endunless
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $__isAgent ? 7 : 8 }}" class="text-center py-5" style="color:#9ca3af;">
                                <i class="fas fa-inbox fa-2x d-block mb-2"></i>No applications found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $applications->appends(request()->query())->links() }}</div>
    </div>

    @push('scripts')
        <script>
            const statusOptions = {!! json_encode($statuses->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()) !!};

            async function withdrawApp(id) {
                const {
                    value: reason
                } = await Swal.fire({
                    title: 'Withdraw Application',
                    text: 'Are you sure you want to withdraw this application?',
                    icon: 'warning',
                    input: 'textarea',
                    inputLabel: 'Reason for withdrawal',
                    inputPlaceholder: 'Enter the reason...',
                    inputAttributes: {
                        required: true
                    },
                    inputValidator: (value) => {
                        if (!value || !value.trim()) return 'Reason is required';
                    },
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, Withdraw',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                });
                if (reason) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route($role . '.applications.withdraw', '__ID__') }}'.replace('__ID__', id);
                    form.innerHTML =
                        '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="PATCH"><input type="hidden" name="reason" value="' +
                        encodeURIComponent(reason) + '">';
                    document.body.appendChild(form);
                    form.submit();
                }
            }

            async function updateStatusApp(id, currentStatusId) {
                const opts = statusOptions.map(s =>
                    `<option value="${s.id}"${s.id == currentStatusId ? ' selected' : ''}>${s.name}</option>`).join('');
                const {
                    value: statusId
                } = await Swal.fire({
                    title: '<h5 class="fw-bold mb-0"><i class="fas fa-exchange-alt text-warning me-2"></i>Update Status</h5>',
                    html: `<div style="text-align:left;padding:0.5rem 0;"><label class="fw-semibold mb-2" style="font-size:0.85rem;">Current status: <span class="badge bg-secondary ms-1" style="font-size:0.75rem;">${statusOptions.find(s => s.id == currentStatusId)?.name || 'Unknown'}</span></label><select id="swal-status-select" class="form-select" style="font-size:0.85rem;padding:2px 14px;border-radius:8px;border-color:#d1d5db;">${opts}</select></div>`,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonColor: '#4f46e5',
                    confirmButtonText: 'Update',
                    cancelButtonText: 'Cancel',
                    buttonsStyling: true,
                    reverseButtons: true,
                    customClass: {
                        popup: 'app-modal-content',
                        header: 'app-modal-header',
                        footer: 'app-modal-footer'
                    },
                    preConfirm: () => {
                        const val = document.getElementById('swal-status-select').value;
                        if (!val) {
                            Swal.showValidationMessage('Please select a status');
                            return false;
                        }
                        return val;
                    }
                });
                if (statusId) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route($role . '.applications.updateStatus', '__ID__') }}'.replace('__ID__', id);
                    form.innerHTML =
                        '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="PATCH"><input type="hidden" name="application_status_id" value="' +
                        statusId + '">';
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        </script>
    @endpush
@endsection
