@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="notifications-container">
    <!-- Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fa fa-bell me-2"></i> Admin Notifications
        </h1>
        <form action="{{ route('admin.notifications.markAll') }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2">
                <i class="fa fa-check-double"></i> Mark All as Read
            </button>
        </form>
    </div>

    @if(session('status'))
    <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif

    @if($notifications->count())
    @php
    $messages = [];
    $otherNotifications = [];

    foreach($notifications as $notification) {
    $data = (array) ($notification->data ?? []);
    if(($data['type'] ?? '') === 'application_message_added') {
    $messages[] = $notification;
    } else {
    $otherNotifications[] = $notification;
    }
    }

    $typeTitles = [
    'student_added' => 'Student Added',
    'student_deleted' => 'Student Deleted',
    'student_status' => 'Student Status Updated',
    'application_submitted' => 'Application Submitted',
    'application_status' => 'Application Status Updated',
    'application_withdrawn' => 'Application Withdrawn',
    'document_uploaded' => 'Document Uploaded',
    'document_deleted' => 'Document Deleted',
    'user_registered' => 'User Registered',
    'other' => 'Other Notifications',
    ];

    $grouped = collect($otherNotifications)->groupBy(fn($n) => ($n->data['type'] ?? 'other'));
    @endphp

    <div class="notifications-grid">
        <!-- Main Notifications -->
        <div>
            <div class="section-card">
                @forelse($grouped as $type => $notificationsOfType)
                <div class="mb-5">
                    <h4 class="section-title">
                        <i class="fa {{ 
                                    match(true) {
                                        str_contains($type, 'student') => 'fa-user',
                                        str_contains($type, 'application') => 'fa-file-alt',
                                        str_contains($type, 'document') => 'fa-paperclip',
                                        default => 'fa-bell'
                                    }
                                }}"></i>
                        {{ $typeTitles[$type] ?? ucfirst(str_replace('_', ' ', $type)) }}
                        <span class="text-muted ms-auto">({{ $notificationsOfType->count() }})</span>
                    </h4>

                    @foreach($notificationsOfType as $notification)
                    @php
                    $isUnread = is_null($notification->read_at);
                    $data = (array) ($notification->data ?? []);
                    $message = $data['message'] ?? $data['title'] ?? $data['body'] ?? 'Notification';
                    $created = $notification->created_at?->diffForHumans();
                    $short = $data['short'] ?? $data['details'] ?? null;

                    // Use `link` from your notification, fallback to auto
                    $url = $data['link'] ?? $data['url'] ?? null;
                    if (!$url && !empty($data['application_id'])) {
                    $url = route('admin.applications.show', $data['application_id']);
                    if (!empty($data['comment_id'])) {
                    $url .= '#comment-' . $data['comment_id'];
                    }
                    } elseif (!$url && !empty($data['student_id'])) {
                    $url = route('admin.students.show', $data['student_id']);
                    }

                    $icon = match(true) {
                    str_contains($type, 'student_status') => 'fa-user-check',
                    str_contains($type, 'application_status') => 'fa-file-circle-check',
                    str_contains($type, 'student_deleted') => 'fa-user-slash',
                    str_contains($type, 'application_withdrawn') => 'fa-ban',
                    str_contains($type, 'document') => 'fa-paperclip',
                    default => 'fa-bell'
                    };
                    @endphp

                    <!-- CLICKABLE CARD -->
                    @if($url)
                    <a href="{{ $url }}" class="notification-link">
                        @endif

                        <div class="notification-card {{ $isUnread ? 'unread' : '' }}">
                            <div class="notification-icon">
                                <i class="fa {{ $icon }}"></i>
                            </div>

                            <div class="notification-body">
                                <p class="notification-title">{!! $message !!}</p>
                                @if($short)
                                <p class="notification-text">{{ Str::limit($short, 130) }}</p>
                                @endif
                                <div class="notification-meta">
                                    <small><i class="fa fa-clock me-1"></i> {{ $created }}</small>
                                    @if($isUnread)
                                    <span class="badge-new">New</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Actions stay clickable -->
                            <div class="notification-actions" onclick="event.stopPropagation();">
                                @if($isUnread)
                                <form action="{{ route('admin.notifications.markRead', $notification->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn-icon" title="Mark as read">
                                        <i class="fa fa-check"></i>
                                    </button>
                                </form>
                                @else
                                <form action="{{ route('admin.notifications.markUnread', $notification->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn-icon" title="Mark as unread">
                                        <i class="fa fa-undo"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>

                        @if($url)
                    </a>
                    @endif
                    @endforeach
                </div>
                @empty
                <div class="empty-state">
                    <i class="fa fa-bell-slash"></i>
                    <p>No notifications in this category.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Messages Sidebar -->
        <div>
            <div class="section-card">
                <h4 class="section-title">
                    <i class="fa fa-envelope"></i> Messages
                    @if(count($messages))
                    <span class="text-muted ms-auto">({{ count($messages) }})</span>
                    @endif
                </h4>

                @if(count($messages))
                <ul class="messages-list">
                    @foreach($messages as $notification)
                    @php
                    $isUnread = is_null($notification->read_at);
                    $data = (array) ($notification->data ?? []);
                    $senderName = $data['user_name'] ?? 'Unknown User';
                    $message = $data['message'] ?? 'No message';
                    $created = $notification->created_at?->diffForHumans();
                    $url = $data['link'] ?? null;
                    @endphp

                    <!-- CLICKABLE MESSAGE CARD -->
                    @if($url)
                    <a href="{{ $url }}" class="notification-link">
                        @endif

                        <li class="notification-card {{ $isUnread ? 'unread' : '' }} p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1 me-3">
                                    <p class="notification-title mb-1">{!! $message !!}</p>
                                    <small class="text-muted">
                                        <i class="fa fa-clock"></i> {{ $created }}
                                        @if($isUnread)
                                        <span class="badge-new ms-2">New</span>
                                        <span class="text-muted">By:</span>
                                        <span class="sender-name">{{ $senderName }}</span>

                                        @endif
                                    </small>
                                </div>

                                <div class="notification-actions" onclick="event.stopPropagation();">
                                    @if($isUnread)
                                    <form action="{{ route('admin.notifications.markRead', $notification->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn-icon" title="Mark as read">
                                            <i class="fa fa-check"></i>
                                        </button>
                                    </form>
                                    @else
                                    <form action="{{ route('admin.notifications.markUnread', $notification->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn-icon" title="Mark as unread">
                                            <i class="fa fa-undo"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </li>

                        @if($url)
                    </a>
                    @endif
                    @endforeach
                </ul>
                @else
                <div class="empty-state">
                    <i class="fa fa-envelope-open"></i>
                    <p>No messages yet.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-5 d-flex justify-content-center">
        {{ $notifications->links() }}
    </div>

    @else
    <div class="section-card text-center py-6">
        <div class="empty-state">
            <i class="fa fa-bell-slash"></i>
            <p class="mb-0 mt-3">No notifications yet. You're all caught up!</p>
        </div>
    </div>
    @endif
</div>
@endsection
