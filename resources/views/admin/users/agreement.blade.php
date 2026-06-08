@extends('layouts.admin')

@section('admin-content')
    <x-page-header title="Agreement Management" subtitle="Review and verify user agreement documents">
        <x-slot:actions>
            <form method="POST" action="{{ route('admin.reminder.send') }}" id="bulkReminderForm" style="display:inline">
                @csrf
                <input type="hidden" name="send_all" value="1">
                <button type="submit" class="btn btn-outline-primary" id="sendAllRemindersBtn">
                    <i class="fas fa-bell me-2"></i>Send Reminder to All
                </button>
            </form>
        </x-slot:actions>
    </x-page-header>

    @if($users->count())
        <div class="card">
            <div class="card-body p-0">
                <form id="bulkActionForm" method="POST">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small">
                                <tr>
                                    <th width="40" class="text-center">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th width="140" class="text-center">Status</th>
                                    <th width="120" class="text-center">Document</th>
                                    <th width="200" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="form-check-input user-checkbox">
                                    </td>
                                    <td>
                                        <div class="fw-semibold">
                                            <a href="{{ route('admin.users.show', $user->slug) }}" class="text-decoration-none">
                                                {{ $user->business_name ?: $user->name }}
                                            </a>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td class="text-center">
                                        @if($user->agreement_status === 'not_uploaded')
                                            <span class="badge bg-secondary rounded-pill px-3 py-2">
                                                <i class="fas fa-cloud-upload-alt me-1"></i>Not Uploaded
                                            </span>
                                        @elseif($user->agreement_status === 'uploaded')
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                                <i class="fas fa-hourglass-half me-1"></i>Pending Review
                                            </span>
                                        @elseif($user->agreement_status === 'verified')
                                            <span class="badge bg-success rounded-pill px-3 py-2">
                                                <i class="fas fa-check-circle me-1"></i>Verified
                                            </span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill px-3 py-2">
                                                {{ $user->agreement_status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($user->agreement_file)
                                            <a href="{{ asset('storage/' . $user->agreement_file) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-file-pdf text-danger me-1"></i>View
                                            </a>
                                        @else
                                            <span class="text-muted small">
                                                <i class="fas fa-file me-1"></i>No file
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            @if($user->agreement_status !== 'verified')
                                                <form method="POST" action="{{ route('admin.users.verifyAgreement', $user->slug) }}" class="d-inline verify-form">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-success verify-btn" data-name="{{ $user->business_name ?: $user->name }}">
                                                        <i class="fas fa-check-circle me-1"></i>Verify
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('admin.reminder.send') }}" class="d-inline reminder-form">
                                                @csrf
                                                <input type="hidden" name="user_ids[]" value="{{ $user->id }}">
                                                <button type="submit" class="btn btn-sm btn-outline-primary reminder-btn" data-name="{{ $user->business_name ?: $user->name }}">
                                                    <i class="fas fa-envelope me-1"></i>Reminder
                                                </button>
                                            </form>
                                            @if($user->agreement_file)
                                                <form method="POST" action="{{ route('admin.users.agreement.delete', $user->slug) }}" class="d-inline delete-agreement-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger delete-agreement-btn" data-name="{{ $user->business_name ?: $user->name }}">
                                                        <i class="fas fa-trash-alt me-1"></i>Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>

            <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center" id="bulkActionBar" style="display:none">
                <div>
                    <strong id="selectedCount">0</strong> user(s) selected
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success" id="bulkVerifyBtn">
                        <i class="fas fa-check-double me-1"></i>Verify Selected
                    </button>
                    <button type="button" class="btn btn-primary" id="bulkReminderBtn">
                        <i class="fas fa-envelope me-1"></i>Send Reminder
                    </button>
                    <button type="button" class="btn btn-outline-danger" id="bulkDeleteBtn">
                        <i class="fas fa-trash-alt me-1"></i>Delete Selected
                    </button>
                    <button type="button" class="btn btn-secondary" id="clearSelectionBtn">
                        <i class="fas fa-times me-1"></i>Clear
                    </button>
                </div>
            </div>

            <div class="card-footer bg-white border-top-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users
                    </div>
                    {{ $users->withQueryString()->links() }}
                </div>
            </div>
        </div>
    @else
        <x-empty-state
            icon="fa-file-contract"
            title="No pending agreement verifications"
            description="All users have their agreements verified or no agreements are pending review."
        />
    @endif

<form id="bulkVerifyForm" method="POST" style="display:none">
    @csrf
    @method('PUT')
</form>

<form id="bulkDeleteForm" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.user-checkbox');
        const bulkActionBar = document.getElementById('bulkActionBar');
        const selectedCount = document.getElementById('selectedCount');
        const clearSelectionBtn = document.getElementById('clearSelectionBtn');

        function updateBulkBar() {
            const checked = Array.from(checkboxes).filter(cb => cb.checked);
            if (checked.length > 0) {
                bulkActionBar.style.display = 'flex';
                selectedCount.textContent = checked.length;
            } else {
                bulkActionBar.style.display = 'none';
                if (selectAll) selectAll.checked = false;
            }
            if (selectAll) {
                selectAll.checked = checkboxes.length > 0 && Array.from(checkboxes).every(cb => cb.checked);
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
                updateBulkBar();
            });
        }

        checkboxes.forEach(cb => cb.addEventListener('change', updateBulkBar));

        if (clearSelectionBtn) {
            clearSelectionBtn.addEventListener('click', function() {
                checkboxes.forEach(cb => cb.checked = false);
                updateBulkBar();
            });
        }

        document.querySelectorAll('.verify-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('.verify-form');
                const name = this.dataset.name;
                Swal.fire({
                    title: 'Verify Agreement?',
                    html: `Verify agreement for <strong>${name}</strong>?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Yes, verify',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });

        document.querySelectorAll('.reminder-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('.reminder-form');
                const name = this.dataset.name;
                Swal.fire({
                    title: 'Send Reminder?',
                    html: `Send agreement reminder to <strong>${name}</strong>?`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#007bff',
                    confirmButtonText: 'Yes, send',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });

        document.querySelectorAll('.delete-agreement-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('.delete-agreement-form');
                const name = this.dataset.name;
                Swal.fire({
                    title: 'Delete Agreement?',
                    html: `Delete agreement document for <strong>${name}</strong>? This cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });

        document.getElementById('sendAllRemindersBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Send Reminder to All?',
                text: 'Send agreement reminder to all users listed on this page?',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                confirmButtonText: 'Yes, send to all',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) document.getElementById('bulkReminderForm').submit();
            });
        });

        function getSelectedIds() {
            return Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
        }

        document.getElementById('bulkVerifyBtn')?.addEventListener('click', function() {
            const ids = getSelectedIds();
            if (ids.length === 0) {
                Swal.fire('No Selection', 'Please select at least one user.', 'warning');
                return;
            }
            Swal.fire({
                title: 'Bulk Verify?',
                text: `Verify agreements for ${ids.length} user(s)?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Yes, verify all',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('bulkVerifyForm');
                    ids.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'user_ids[]';
                        input.value = id;
                        form.appendChild(input);
                    });
                    form.action = '{{ route('admin.users.verifyAgreement', 'bulk') }}'.replace('bulk', 'bulk');
                    form.submit();
                }
            });
        });

        document.getElementById('bulkReminderBtn')?.addEventListener('click', function() {
            const ids = getSelectedIds();
            if (ids.length === 0) {
                Swal.fire('No Selection', 'Please select at least one user.', 'warning');
                return;
            }
            Swal.fire({
                title: 'Bulk Reminder?',
                text: `Send agreement reminders to ${ids.length} user(s)?`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                confirmButtonText: 'Yes, send',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('bulkReminderForm');
                    ids.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'user_ids[]';
                        input.value = id;
                        form.appendChild(input);
                    });
                    form.submit();
                }
            });
        });

        document.getElementById('bulkDeleteBtn')?.addEventListener('click', function() {
            const ids = getSelectedIds();
            if (ids.length === 0) {
                Swal.fire('No Selection', 'Please select at least one user.', 'warning');
                return;
            }
            Swal.fire({
                title: 'Bulk Delete Agreements?',
                text: `Delete agreement documents for ${ids.length} user(s)? This cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, delete all',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('bulkDeleteForm');
                    ids.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'user_ids[]';
                        input.value = id;
                        form.appendChild(input);
                    });
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
@endsection
