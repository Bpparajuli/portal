@extends('layouts.app')

@section('title', 'Admin Notifications Center')

@section('content')

    <div class="container py-4">
        {{-- Header Section --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-0">
                    <i class="fa fa-bell me-2 text-primary"></i>Admin Notifications Center
                </h2>
                <p class="text-muted mb-0 mt-1">Manage all system updates and application messages in one place</p>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary btn-sm btn-mark-all">
                    <i class="fa fa-check-double me-1"></i>Mark All Read
                </button>
            </div>
        </div>

        <div class="row g-4">
            {{-- LEFT COLUMN: REGULAR NOTIFICATIONS --}}
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <div>
                            <i class="fa fa-bell text-primary me-2"></i>
                            <strong>Regular Notifications</strong>
                            <span class="badge bg-secondary ms-2">{{ $notifications->total() }}</span>
                        </div>
                        <div>
                            @if ($notifications->count() > 0)
                                <button class="btn btn-sm btn-link text-danger btn-delete-bulk"
                                    data-url="{{ route('admin.notifications.deleteAll') }}" data-type="notifications"
                                    data-name="all regular notifications">
                                    <i class="fa fa-trash me-1"></i>Clear All
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="card-body p-0">
                        @if ($notifications->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach ($notifications as $notification)
                                    @php
                                        $data = $notification->data ?? [];
                                        $isUnread = is_null($notification->read_at);
                                        $message = $data['message'] ?? ($data['title'] ?? 'New notification');
                                        $created = $notification->created_at?->format('M d, Y h:i A');
                                        $timeAgo = $notification->created_at?->diffForHumans();
                                        $typeName = ucfirst(str_replace('_', ' ', $data['type'] ?? 'other'));

                                        $icon = 'fa-bell';
                                        $iconColor = 'text-secondary';
                                        if (str_contains($data['type'] ?? '', 'application')) {
                                            $icon = 'fa-file-text';
                                            $iconColor = 'text-primary';
                                        } elseif (str_contains($data['type'] ?? '', 'student')) {
                                            $icon = 'fa-user-graduate';
                                            $iconColor = 'text-success';
                                        } elseif (str_contains($data['type'] ?? '', 'payment')) {
                                            $icon = 'fa-credit-card';
                                            $iconColor = 'text-warning';
                                        } elseif (str_contains($data['type'] ?? '', 'agent')) {
                                            $icon = 'fa-user-tie';
                                            $iconColor = 'text-info';
                                        }

                                        $url = null;
                                        if (!empty($data['application']['id'])) {
                                            $url = route('admin.applications.show', $data['application']['id']);
                                        } elseif (!empty($data['student']['id'])) {
                                            $url = route('admin.students.show', $data['student']['id']);
                                        } elseif (!empty($data['agent']['id'])) {
                                            $url = route('admin.agents.show', $data['agent']['id']);
                                        }
                                    @endphp

                                    <div
                                        class="list-group-item list-group-item-action {{ $isUnread ? 'bg-secondary bg-opacity-10' : '' }}">
                                        <div class="d-flex">
                                            <div class="me-3">
                                                <i class="fa {{ $icon }} {{ $iconColor }} fa-lg"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                        <span class="badge bg-light text-dark">{{ $typeName }}</span>
                                                        @if ($isUnread)
                                                            <span class="badge bg-warning text-dark">
                                                                <i class="fa fa-circle me-1"
                                                                    style="font-size: 8px;"></i>Unread
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <small class="text-muted" title="{{ $created }}">
                                                        <i class="fa fa-clock-o me-1"></i>{{ $timeAgo }}
                                                    </small>
                                                </div>
                                                {{-- Clickable link for the content --}}
                                                @if ($isUnread)
                                                    <a href="{{ $url ?? '#' }}" class="text-decoration-none text-white">
                                                        <div class="mb-2">
                                                            {!! $message !!}
                                                        </div>
                                                    </a>
                                                @else
                                                    <a href="{{ $url ?? '#' }}" class="text-decoration-none text-dark">
                                                        <div class="mb-2">
                                                            {!! $message !!}
                                                        </div>
                                                    </a>
                                                @endif

                                                <div class="d-flex gap-2 mt-2">
                                                    @if ($isUnread)
                                                        <form method="POST"
                                                            action="{{ route('admin.notifications.markRead', $notification->id) }}"
                                                            class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-success"
                                                                title="Mark as read">
                                                                <i class="fa fa-check me-1"></i>Mark Read
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form method="POST"
                                                            action="{{ route('admin.notifications.markUnread', $notification->id) }}"
                                                            class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary"
                                                                title="Mark as unread">
                                                                <i class="fa fa-undo me-1"></i>Mark Unread
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                                        data-url="{{ route('admin.notifications.delete', $notification->id) }}"
                                                        data-name="this notification">
                                                        <i class="fa fa-trash me-1"></i>Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="card-footer bg-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        Showing {{ $notifications->firstItem() ?? 0 }} to
                                        {{ $notifications->lastItem() ?? 0 }} of {{ $notifications->total() }}
                                        notifications
                                    </small>
                                    {{ $notifications->appends(request()->query())->links() }}
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fa fa-bell-slash fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No notifications to display</h5>
                                <p class="text-muted small">You're all caught up!</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: MESSAGES SECTION (Same as agent version) --}}
            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <div>
                            <i class="fa fa-envelope text-primary me-2"></i>
                            <strong>Messages</strong>
                            <span class="badge bg-secondary ms-2">{{ $messages->total() }}</span>
                        </div>
                        @if ($messages->count() > 0)
                            <button class="btn btn-sm btn-link text-danger btn-delete-bulk"
                                data-url="{{ route('admin.notifications.deleteAll') }}" data-type="messages"
                                data-name="all messages">
                                <i class="fa fa-trash me-1"></i>Clear All
                            </button>
                        @endif
                    </div>

                    <div class="card-body p-0">
                        @if ($messages->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach ($messages as $message)
                                    @php
                                        $data = $message->data ?? [];
                                        $isUnread = is_null($message->read_at);
                                        $sender = $data['added_by']['name'] ?? ($data['sender_name'] ?? 'System User');
                                        $messageContent = $data['message'] ?? 'New message received';
                                        $created = $message->created_at?->format('M d, Y h:i A');
                                        $timeAgo = $message->created_at?->diffForHumans();
                                        $applicationId = $data['application']['id'] ?? null;
                                        $applicationRef =
                                            $data['application']['reference_no'] ??
                                            ($data['application']['id'] ?? null);
                                        $agentId = $data['agent']['id'] ?? null;

                                        $url = $applicationId ? route('admin.applications.show', $applicationId) : '#';
                                    @endphp

                                    <div
                                        class="list-group-item list-group-item-action {{ $isUnread ? 'bg-primary bg-opacity-10' : '' }}">
                                        <div class="d-flex">
                                            <div class="me-3">
                                                <div class="avatar-circle bg-primary bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 45px; height: 45px;">
                                                    <i class="fa fa-user text-primary fa-lg"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <div>
                                                        <strong class="text-primary">{{ $sender }}</strong>
                                                        @if ($isUnread)
                                                            <span class="badge bg-warning text-dark ms-2">
                                                                <i class="fa fa-envelope me-1"></i>New
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <small class="text-muted" title="{{ $created }}">
                                                        <i class="fa fa-clock-o me-1"></i>{{ $timeAgo }}
                                                    </small>
                                                </div>
                                                @if ($isUnread)
                                                    <a href="{{ $url }}" class="text-decoration-none text-white">
                                                        <div class="mb-2">
                                                            {!! Str::limit($messageContent, 100) !!}
                                                        </div>
                                                    </a>
                                                @else
                                                    <a href="{{ $url }}" class="text-decoration-none text-dark">
                                                        <div class="mb-2">
                                                            {!! Str::limit($messageContent, 100) !!}
                                                        </div>
                                                    </a>
                                                @endif
                                                @if ($applicationRef)
                                                    <div class="mb-2">
                                                        <span class="badge bg-light text-dark">
                                                            <i class="fa fa-file-text me-1"></i>App #{{ $applicationRef }}
                                                        </span>
                                                    </div>
                                                @endif

                                                <div class="d-flex gap-2 mt-2">
                                                    @if ($isUnread)
                                                        <form method="POST"
                                                            action="{{ route('admin.notifications.markRead', $message->id) }}"
                                                            class="flex-grow-1">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success w-100">
                                                                <i class="fa fa-check me-1"></i>Mark Read
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form method="POST"
                                                            action="{{ route('admin.notifications.markUnread', $message->id) }}"
                                                            class="flex-grow-1">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-secondary w-100">
                                                                <i class="fa fa-undo me-1"></i>Mark Unread
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger btn-delete"
                                                        data-url="{{ route('admin.notifications.delete', $message->id) }}"
                                                        data-name="this message from {{ $sender }}">
                                                        <i class="fa fa-trash me-1"></i>Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="card-footer bg-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        Showing {{ $messages->firstItem() ?? 0 }} to {{ $messages->lastItem() ?? 0 }} of
                                        {{ $messages->total() }} messages
                                    </small>
                                    {{ $messages->appends(request()->query())->links() }}
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fa fa-inbox fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No messages to display</h5>
                                <p class="text-muted small">Application messages will appear here</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Handle bulk delete buttons
            document.querySelectorAll('.btn-delete-bulk').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const deleteUrl = this.getAttribute('data-url');
                    const deleteType = this.getAttribute('data-type');
                    const itemName = this.getAttribute('data-name');

                    Swal.fire({
                        title: "Are you sure?",
                        html: `You are about to delete <strong>${itemName}</strong>.<br><span style="color:red;">This action cannot be undone!</span>`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Yes, delete it!",
                        cancelButtonText: "No, cancel",
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.createElement("form");
                            form.method = "POST";
                            form.action = deleteUrl;

                            let html =
                                `<input type="hidden" name="_token" value="{{ csrf_token() }}">`;
                            html += `<input type="hidden" name="_method" value="DELETE">`;
                            html +=
                                `<input type="hidden" name="type" value="${deleteType}">`;
                            form.innerHTML = html;

                            document.body.appendChild(form);
                            form.submit();
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            Swal.fire({
                                title: "Cancelled",
                                text: "Your data is safe 🙂",
                                icon: "info",
                                timer: 1200,
                                showConfirmButton: false
                            });
                        }
                    });
                });
            });

            // Handle mark all button
            document.querySelector('.btn-mark-all')?.addEventListener('click', function(e) {
                const url = "{{ route('admin.notifications.markAll') }}";

                Swal.fire({
                    title: "Mark all as read?",
                    text: "This will mark all your notifications as read.",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, mark all!",
                    cancelButtonText: "Cancel"
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement("form");
                        form.method = "POST";
                        form.action = url;
                        form.innerHTML =
                            `<input type="hidden" name="_token" value="{{ csrf_token() }}">`;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    </script>

    <style>
        .avatar-circle {
            transition: transform 0.2s;
        }

        .list-group-item:hover .avatar-circle {
            transform: scale(1.05);
        }

        .list-group-item {
            transition: all 0.2s ease;
        }

        .list-group-item:hover {
            transform: translateX(5px);
        }

        .badge {
            font-weight: 500;
        }

        .list-group-item {
            word-wrap: break-word;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .list-group-item {
            animation: slideIn 0.3s ease-out;
        }

        a.text-decoration-none:hover {
            text-decoration: none;
        }
    </style>
@endpush
