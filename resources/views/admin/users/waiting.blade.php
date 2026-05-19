@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        {{-- Header Section --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1">User Management</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">User Approvals</li>
                    </ol>
                </nav>
            </div>
            <div class="text-muted">
                <i class="fa fa-calendar"></i> {{ now()->format('F j, Y') }}
            </div>
        </div>

        @if (auth()->check() && auth()->user()->is_admin)
            {{-- Stats Cards --}}
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-gradient-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">Pending Approvals</h6>
                                    <h2 class="text-white mb-0">{{ $waitingUsers->count() }}</h2>
                                </div>
                                <div class="rounded-circle bg-white-20 p-3">
                                    <i class="fa fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-gradient-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">Awaiting Verification</h6>
                                    <h2 class="text-white mb-0">{{ $agreementUsers->count() }}</h2>
                                </div>
                                <div class="rounded-circle bg-white-20 p-3">
                                    <i class="fa fa-file-text fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-gradient-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">Total Users</h6>
                                    <h2 class="text-white mb-0">{{ \App\Models\User::count() }}</h2>
                                </div>
                                <div class="rounded-circle bg-white-20 p-3">
                                    <i class="fa fa-id-badge fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Users Waiting Approval Section --}}
            <div class="card border-0 shadow-sm mb-5">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h3 class="h5 mb-0">
                        <i class="fa fa-clock text-warning me-2"></i>
                        Users Waiting for Approval
                    </h3>
                    <span class="badge bg-warning text-dark">{{ $waitingUsers->count() }} Pending</span>
                </div>
                <div class="card-body p-0">
                    @if ($waitingUsers->isEmpty())
                        <div class="alert alert-success m-3 mb-0">
                            <i class="fa fa-check-circle me-2"></i>
                            All users have been approved. Great job!
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">ID</th>
                                        <th>Business</th>
                                        <th>Owner</th>
                                        <th>Contact</th>
                                        <th>Registered</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($waitingUsers as $user)
                                        <tr>
                                            <td class="ps-3"><span class="fw-semibold">#{{ $user->id }}</span></td>
                                            <td>
                                                <a href="{{ route('admin.users.show', $user->slug) }}"
                                                    class="text-decoration-none fw-semibold">
                                                    {{ $user->business_name }}
                                                </a>
                                                <div class="small text-muted">{{ $user->email }}</div>
                                            </td>
                                            <td>{{ $user->owner_name ?? 'N/A' }}</td>
                                            <td>{{ $user->contact ?? 'N/A' }}</td>
                                            <td>{{ $user->created_at->diffForHumans() }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-success btn-sm px-3 approve-btn"
                                                    data-url="{{ route('admin.users.approve', $user->slug) }}"
                                                    data-name="{{ $user->business_name }}">
                                                    <i class="fa fa-check me-1"></i> Approve
                                                </button>
                                                <a href="{{ route('admin.users.show', $user->slug) }}"
                                                    class="btn btn-outline-secondary btn-sm">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Agreement Verification Section --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h3 class="h5 mb-0">
                        <i class="fa fa-shield text-secondary me-2"></i>
                        Agreement Verification Required
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if ($agreementUsers->isEmpty())
                        <div class="alert alert-success m-3 mb-0">
                            <i class="fa fa-check-circle me-2"></i>
                            No pending agreement verifications. Excellent work!
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="agreementTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="40" class="text-center">
                                            <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                                        </th>
                                        <th>ID</th>
                                        <th>Business Information</th>
                                        <th width="120" class="text-center">Agreement</th>
                                        <th width="140" class="text-center">Status</th>
                                        <th width="120" class="text-center">Submitted</th>
                                        <th width="200" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($agreementUsers as $user)
                                        <tr data-user-id="{{ $user->id }}"
                                            data-user-name="{{ $user->business_name }}">
                                            <td class="text-center">
                                                <input type="checkbox" name="selected_users[]" value="{{ $user->id }}"
                                                    class="user-checkbox form-check-input"
                                                    data-name="{{ $user->business_name }}">
                                            </td>
                                            <td><span class="fw-semibold">#{{ $user->id }}</span></td>
                                            <td>
                                                <div class="fw-semibold">
                                                    <a href="{{ route('admin.users.show', $user->slug) }}"
                                                        class="text-decoration-none">
                                                        {{ $user->business_name }}
                                                    </a>
                                                </div>
                                                <div class="small text-muted">{{ $user->owner_name }} •
                                                    {{ $user->email }}</div>
                                            </td>
                                            <td class="text-center">
                                                @if ($user->agreement_file)
                                                    <a href="{{ asset('storage/' . $user->agreement_file) }}"
                                                        target="_blank" class="btn btn-link btn-sm p-0">
                                                        <i class="fa fa-file-pdf-o text-danger fa-lg"></i>
                                                        <span class="small ms-1">View</span>
                                                    </a>
                                                @else
                                                    <span class="text-muted small">
                                                        <i class="fa fa-file-o"></i> No file
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($user->agreement_status === 'not_uploaded')
                                                    <span class="badge bg-secondary px-3 py-2">
                                                        <i class="fa fa-cloud-upload me-1"></i> Not Uploaded
                                                    </span>
                                                @elseif ($user->agreement_status === 'uploaded')
                                                    <span class="badge bg-warning text-dark px-3 py-2">
                                                        <i class="fa fa-hourglass-half me-1"></i> Pending Review
                                                    </span>
                                                @elseif ($user->agreement_status === 'verified')
                                                    <span class="badge bg-success px-3 py-2">
                                                        <i class="fa fa-check-circle me-1"></i> Verified
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center small text-muted">
                                                {{ $user->updated_at->diffForHumans() }}
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-primary verify-single"
                                                        data-url="{{ route('admin.users.verifyAgreement', $user->slug) }}"
                                                        data-name="{{ $user->business_name }}">
                                                        <i class="fa fa-check-circle me-1"></i> Verify
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-outline-secondary reminder-single"
                                                        data-id="{{ $user->id }}"
                                                        data-name="{{ $user->business_name }}">
                                                        <i class="fa fa-envelope me-1"></i> Reminder
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Bulk Action Bar --}}
                        <div class="bg-light p-3 border-top d-flex justify-content-between align-items-center"
                            id="bulkActionBar" style="display: none;">
                            <div>
                                <strong id="selectedCount">0</strong> user(s) selected
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-success" id="bulkVerifyBtn">
                                    <i class="fa fa-check-square-o me-1"></i> Verify Selected
                                </button>
                                <button class="btn btn-primary" id="bulkReminderBtn">
                                    <i class="fa fa-envelope me-1"></i> Send Reminder
                                </button>
                                <button class="btn btn-secondary" id="clearSelectionBtn">
                                    <i class="fa fa-times-circle me-1"></i> Clear
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="alert alert-danger text-center">
                <i class="fa fa-exclamation-triangle me-2"></i>
                You are not authorized to view this page.
            </div>
        @endif
    </div>

    {{-- Email Preview Modal --}}
    <div class="modal fade" id="emailPreviewModal" tabindex="-1" aria-labelledby="emailPreviewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title" id="emailPreviewModalLabel">
                        <i class="fa fa-envelope-open me-2"></i>
                        Email Preview
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="email-preview-container p-4" style="max-height: 70vh; overflow-y: auto;">
                        <div id="emailPreviewContent">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3 text-muted">Loading email preview...</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa fa-times-circle me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-success" id="confirmSendEmailBtn">
                        <i class="fa fa-paper-plane me-1"></i> Send Email
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden Forms for Actions --}}
    <form id="verifyForm" method="POST" style="display: none;">
        @csrf
        @method('PUT')
    </form>

    <form id="reminderForm" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="send_all" id="sendAllFlag" value="0">
        <!-- user_ids will be added dynamically as array -->
    </form>

    <form id="approveForm" method="POST" style="display: none;">
        @csrf
        @method('PUT')
    </form>

    <style>
        .bg-white-20 {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transition: all 0.2s ease;
        }

        .btn-group-sm .btn {
            border-radius: 4px;
        }

        .badge {
            font-weight: 500;
            border-radius: 6px;
        }

        .form-check-input {
            cursor: pointer;
            width: 1.2em;
            height: 1.2em;
        }

        .email-preview-container {
            background: #f8f9fa;
        }

        .email-preview-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .email-header {
            background: var(--active);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .email-body {
            padding: 30px;
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .email-footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
        }

        .email-info-bar {
            background: #e7f3ff;
            padding: 10px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        /* Font Awesome sizing */
        .fa-2x {
            font-size: 2em;
        }

        .fa-lg {
            font-size: 1.3333em;
            line-height: 0.75em;
            vertical-align: -0.0667em;
        }
    </style>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // ==================== SELECT ALL FUNCTIONALITY ====================
                const selectAllCheckbox = document.getElementById('selectAllCheckbox');
                const userCheckboxes = document.querySelectorAll('.user-checkbox');
                const bulkActionBar = document.getElementById('bulkActionBar');
                const selectedCountSpan = document.getElementById('selectedCount');

                // Store current email data for preview
                let currentEmailData = {
                    user_ids: [],
                    user_names: [],
                    is_bulk: false
                };

                // Initialize modal
                let emailPreviewModal = null;
                if (typeof bootstrap !== 'undefined') {
                    emailPreviewModal = new bootstrap.Modal(document.getElementById('emailPreviewModal'));
                }

                function updateBulkActionBar() {
                    const selected = Array.from(userCheckboxes).filter(cb => cb.checked);
                    const count = selected.length;

                    if (count > 0) {
                        bulkActionBar.style.display = 'flex';
                        selectedCountSpan.textContent = count;
                    } else {
                        bulkActionBar.style.display = 'none';
                        if (selectAllCheckbox) selectAllCheckbox.checked = false;
                    }
                }

                function updateSelectAllCheckbox() {
                    if (!selectAllCheckbox) return;
                    const allChecked = userCheckboxes.length > 0 && Array.from(userCheckboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                }

                if (selectAllCheckbox) {
                    selectAllCheckbox.addEventListener('change', function() {
                        userCheckboxes.forEach(cb => {
                            cb.checked = selectAllCheckbox.checked;
                        });
                        updateBulkActionBar();
                    });
                }

                userCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        updateBulkActionBar();
                        updateSelectAllCheckbox();
                    });
                });

                // Clear selection button
                const clearSelectionBtn = document.getElementById('clearSelectionBtn');
                if (clearSelectionBtn) {
                    clearSelectionBtn.addEventListener('click', function() {
                        userCheckboxes.forEach(cb => cb.checked = false);
                        if (selectAllCheckbox) selectAllCheckbox.checked = false;
                        updateBulkActionBar();
                    });
                }

                // ==================== APPROVE SINGLE USER ====================
                const approveForm = document.getElementById('approveForm');

                document.querySelectorAll('.approve-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const userName = this.dataset.name;
                        const url = this.dataset.url;

                        Swal.fire({
                            title: "Approve User?",
                            html: `You are about to approve <strong>${userName}</strong>.<br>This will activate their account and send them a notification.`,
                            icon: "question",
                            showCancelButton: true,
                            confirmButtonColor: "#28a745",
                            cancelButtonColor: "#6c757d",
                            confirmButtonText: "Yes, approve user!",
                            cancelButtonText: "No, cancel",
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                approveForm.action = url;
                                approveForm.submit();

                                Swal.fire({
                                    title: "Approved!",
                                    text: `${userName} has been approved successfully.`,
                                    icon: "success",
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            } else if (result.dismiss === Swal.DismissReason.cancel) {
                                Swal.fire({
                                    title: "Cancelled",
                                    text: "No changes were made.",
                                    icon: "info",
                                    timer: 1200,
                                    showConfirmButton: false
                                });
                            }
                        });
                    });
                });

                // ==================== VERIFY SINGLE USER ====================
                const verifyForm = document.getElementById('verifyForm');

                document.querySelectorAll('.verify-single').forEach(button => {
                    button.addEventListener('click', function() {
                        const userName = this.dataset.name;
                        const url = this.dataset.url;

                        Swal.fire({
                            title: "Verify Agreement?",
                            html: `You are about to verify <strong>${userName}</strong>'s agreement document.<br><span style="color:orange;">This confirms their agreement is valid and approved.</span>`,
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#007bff",
                            cancelButtonColor: "#6c757d",
                            confirmButtonText: "Yes, verify agreement!",
                            cancelButtonText: "No, cancel",
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                verifyForm.action = url;
                                verifyForm.submit();

                                Swal.fire({
                                    title: "Verified!",
                                    text: `${userName}'s agreement has been verified.`,
                                    icon: "success",
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            } else if (result.dismiss === Swal.DismissReason.cancel) {
                                Swal.fire({
                                    title: "Cancelled",
                                    text: "Verification was cancelled.",
                                    icon: "info",
                                    timer: 1200,
                                    showConfirmButton: false
                                });
                            }
                        });
                    });
                });

                // ==================== EMAIL PREVIEW FUNCTIONS ====================
                async function fetchEmailPreview(userIds, isBulk = false) {
                    try {
                        const emailPreviewContent = document.getElementById('emailPreviewContent');

                        // Show loading
                        emailPreviewContent.innerHTML = `
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3 text-muted">Loading email preview...</p>
                            </div>
                        `;

                        // Make AJAX request to get email preview
                        const response = await fetch('{{ route('admin.reminder.preview') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                user_ids: userIds,
                                is_bulk: isBulk
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            emailPreviewContent.innerHTML = data.html;
                        } else {
                            emailPreviewContent.innerHTML = `
                                <div class="alert alert-danger m-3">
                                    <i class="fa fa-exclamation-triangle me-2"></i>
                                    ${data.message || 'Failed to load email preview'}
                                </div>
                            `;
                        }
                    } catch (error) {
                        console.error('Error fetching preview:', error);
                        const emailPreviewContent = document.getElementById('emailPreviewContent');
                        emailPreviewContent.innerHTML = `
                            <div class="alert alert-danger m-3">
                                <i class="fa fa-exclamation-triangle me-2"></i>
                                Failed to load email preview. Please try again.
                            </div>
                        `;
                    }
                }

                function showEmailPreview(userIds, userNames, isBulk = false) {
                    // Store current email data
                    currentEmailData = {
                        user_ids: Array.isArray(userIds) ? userIds : [userIds],
                        user_names: userNames,
                        is_bulk: isBulk
                    };

                    // Show modal
                    if (emailPreviewModal) {
                        emailPreviewModal.show();
                    }

                    // Fetch and display preview
                    fetchEmailPreview(currentEmailData.user_ids, isBulk);
                }

                // ==================== REMINDER FUNCTIONS WITH PREVIEW ====================
                const reminderForm = document.getElementById('reminderForm');

                function submitReminder(userIds, isSendAll = false) {
                    // Clear existing user_ids inputs
                    const existingInputs = reminderForm.querySelectorAll('input[name="user_ids[]"]');
                    existingInputs.forEach(input => input.remove());

                    // Set the send_all flag
                    reminderForm.querySelector('#sendAllFlag').value = isSendAll ? '1' : '0';

                    // Add each user ID as a separate array element
                    if (Array.isArray(userIds) && userIds.length > 0) {
                        userIds.forEach(id => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'user_ids[]';
                            input.value = id;
                            reminderForm.appendChild(input);
                        });
                    } else if (!isSendAll && userIds) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'user_ids[]';
                        input.value = userIds;
                        reminderForm.appendChild(input);
                    }

                    reminderForm.action = '{{ route('admin.reminder.send') }}';
                    reminderForm.submit();
                }

                // Single reminder button with preview
                document.querySelectorAll('.reminder-single').forEach(button => {
                    button.addEventListener('click', function() {
                        const userId = this.dataset.id;
                        const userName = this.dataset.name;

                        Swal.fire({
                            title: "Preview Email",
                            html: `Would you like to preview the email before sending to <strong>${userName}</strong>?`,
                            icon: "question",
                            showCancelButton: true,
                            confirmButtonColor: "#007bff",
                            cancelButtonColor: "#6c757d",
                            confirmButtonText: "Yes, preview email",
                            cancelButtonText: "Send without preview",
                            showDenyButton: true,
                            denyButtonText: "Cancel",
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Show preview
                                showEmailPreview(userId, userName, false);
                            } else if (result.dismiss === Swal.DismissReason.cancel) {
                                // Send without preview
                                Swal.fire({
                                    title: "Send Reminder?",
                                    html: `Send reminder email to <strong>${userName}</strong>?`,
                                    icon: "info",
                                    showCancelButton: true,
                                    confirmButtonColor: "#28a745",
                                    cancelButtonColor: "#6c757d",
                                    confirmButtonText: "Yes, send!",
                                    cancelButtonText: "No, cancel"
                                }).then((sendResult) => {
                                    if (sendResult.isConfirmed) {
                                        submitReminder(userId, false);

                                        Swal.fire({
                                            title: "Sending...",
                                            text: `Sending reminder to ${userName}.`,
                                            icon: "info",
                                            timer: 1500,
                                            showConfirmButton: false
                                        });
                                    }
                                });
                            }
                        });
                    });
                });

                // Confirm send button in modal
                document.getElementById('confirmSendEmailBtn')?.addEventListener('click', function() {
                    if (emailPreviewModal) {
                        emailPreviewModal.hide();
                    }

                    const userCount = currentEmailData.user_ids.length;
                    const userNamesList = currentEmailData.user_names;

                    Swal.fire({
                        title: "Confirm Send",
                        html: `Send reminder email${currentEmailData.is_bulk ? 's' : ''} to <strong>${currentEmailData.is_bulk ? userCount + ' user(s)' : userNamesList}</strong>?`,
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonColor: "#28a745",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Yes, send!",
                        cancelButtonText: "No, cancel"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitReminder(currentEmailData.user_ids, false);

                            Swal.fire({
                                title: "Sending...",
                                text: `Sending reminder${currentEmailData.is_bulk ? 's' : ''} to ${currentEmailData.is_bulk ? userCount + ' user(s)' : userNamesList}.`,
                                icon: "info",
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    });
                });

                // ==================== BULK VERIFY ====================
                const bulkVerifyBtn = document.getElementById('bulkVerifyBtn');

                async function bulkVerify() {
                    const selected = Array.from(userCheckboxes).filter(cb => cb.checked);
                    const selectedNames = selected.map(cb => cb.dataset.name);

                    if (selected.length === 0) {
                        Swal.fire({
                            title: "No Users Selected",
                            text: "Please select at least one user to verify.",
                            icon: "warning",
                            timer: 2000,
                            showConfirmButton: false
                        });
                        return;
                    }

                    Swal.fire({
                        title: "Bulk Verification",
                        html: `You are about to verify agreements for <strong>${selected.length} user(s)</strong>:<br><br>${selectedNames.join('<br>')}<br><br><span style="color:orange;">This action cannot be undone!</span>`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#28a745",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, verify all!",
                        cancelButtonText: "No, cancel",
                        reverseButtons: true
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            // Show loading
                            Swal.fire({
                                title: "Processing...",
                                text: "Please wait while we verify the agreements.",
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            // Process each verification sequentially
                            let successCount = 0;
                            let failCount = 0;

                            for (const checkbox of selected) {
                                const row = checkbox.closest('tr');
                                const verifyBtn = row.querySelector('.verify-single');
                                const url = verifyBtn?.dataset.url;

                                if (url) {
                                    try {
                                        const response = await fetch(url, {
                                            method: 'PUT',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Content-Type': 'application/json'
                                            }
                                        });

                                        if (response.ok) {
                                            successCount++;
                                        } else {
                                            failCount++;
                                        }
                                    } catch (error) {
                                        failCount++;
                                    }
                                } else {
                                    failCount++;
                                }
                            }

                            // Show result
                            Swal.fire({
                                title: "Bulk Verification Complete",
                                html: `<strong>${successCount}</strong> user(s) verified successfully.<br>${failCount > 0 ? `<strong style="color:red;">${failCount}</strong> user(s) failed.` : ''}`,
                                icon: successCount > 0 ? "success" : "error",
                                timer: 3000,
                                showConfirmButton: true
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    });
                }

                if (bulkVerifyBtn) bulkVerifyBtn.addEventListener('click', bulkVerify);

                // ==================== BULK REMINDER WITH PREVIEW ====================
                const bulkReminderBtn = document.getElementById('bulkReminderBtn');

                function bulkReminder() {
                    const selected = Array.from(userCheckboxes).filter(cb => cb.checked);
                    const selectedIds = selected.map(cb => cb.value);
                    const selectedNames = selected.map(cb => cb.dataset.name);

                    if (selectedIds.length === 0) {
                        Swal.fire({
                            title: "No Users Selected",
                            text: "Please select at least one user to send reminders.",
                            icon: "warning",
                            timer: 2000,
                            showConfirmButton: false
                        });
                        return;
                    }

                    Swal.fire({
                        title: "Bulk Reminder",
                        html: `You are about to send reminder emails to <strong>${selectedIds.length} user(s)</strong>.<br><br>Would you like to preview the email first?`,
                        icon: "info",
                        showCancelButton: true,
                        confirmButtonColor: "#007bff",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Yes, preview emails",
                        cancelButtonText: "Send without preview",
                        showDenyButton: true,
                        denyButtonText: "Cancel"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show preview with all selected users
                            showEmailPreview(selectedIds, selectedNames, true);
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            // Send without preview
                            Swal.fire({
                                title: "Send Reminders?",
                                html: `Send reminder emails to <strong>${selectedIds.length} user(s)</strong>:<br><br>${selectedNames.join('<br>')}`,
                                icon: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#28a745",
                                cancelButtonColor: "#6c757d",
                                confirmButtonText: "Yes, send all!",
                                cancelButtonText: "No, cancel"
                            }).then((sendResult) => {
                                if (sendResult.isConfirmed) {
                                    submitReminder(selectedIds, false);

                                    Swal.fire({
                                        title: "Sending Reminders...",
                                        text: `Sending reminders to ${selectedIds.length} user(s).`,
                                        icon: "info",
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                }
                            });
                        }
                    });
                }

                if (bulkReminderBtn) bulkReminderBtn.addEventListener('click', bulkReminder);

                // Initialize bulk action bar state
                updateBulkActionBar();
            });
        </script>
    @endpush
@endsection
