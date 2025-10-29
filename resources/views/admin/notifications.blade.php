@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .notifications-section {
        margin-bottom: 2rem;
    }

    .notifications-section h4 {
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .notifications-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    @media (max-width: 768px) {
        .notifications-grid {
            grid-template-columns: 1fr;
        }
    }

    .notification-card {
        border-radius: 10px;
        padding: 1rem;
        background: #fff;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        transition: transform .08s ease;
    }

    .notification-card.unread {
        border-left: 6px solid #0d6efd;
        background: linear-gradient(90deg, rgba(13, 110, 253, 0.03), #fff);
    }

    .notification-card:hover {
        transform: translateY(-3px);
    }

    .notification-icon {
        min-width: 48px;
        min-height: 48px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        background: rgba(0, 0, 0, 0.04);
    }

    .notification-body {
        flex: 1;
    }

    .notification-title {
        margin: 0;
        font-weight: 600;
        font-size: 0.98rem;
    }

    .notification-text {
        margin: 0.25rem 0 0.55rem 0;
        color: #333;
        font-size: 0.92rem;
    }

    .notification-meta {
        display: flex;
        gap: .5rem;
        align-items: center;
        font-size: 0.85rem;
        color: #666;
    }

    .notification-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .btn-sm {
        padding: .25rem .5rem;
        font-size: .82rem;
        border-radius: 6px;
    }

</style>

<div class="container p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Admin Notifications</h2>
        <div>
            <form action="{{ route('admin.notifications.markAll') }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-outline-primary btn-sm" type="submit">
                    <i class="fa fa-check-double"></i> Mark all as read
                </button>
            </form>
        </div>
    </div>

    @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($notifications->count())
    @php
    // Group notifications by type
    $grouped = $notifications->groupBy(function($notification) {
    $data = (array) ($notification->data ?? []);
    return $data['type'] ?? 'other';
    });

    // Mapping type to readable title
    $typeTitles = [
    'student_added' => 'Student Added',
    'student_deleted' => 'Student Deleted',
    'student_status' => 'Student Status Updated',
    'application_submitted' => 'Application Submitted',
    'application_status' => 'Application Status Updated',
    'application_withdrawn' => 'Application Withdrawn',
    'application_message' => 'Application Message Added',
    'document_uploaded' => 'Document Uploaded',
    'document_deleted' => 'Document Deleted',
    'user_registered' => 'User Registered',
    'other' => 'Other Notifications',
    ];
    @endphp

    @foreach($grouped as $type => $notificationsOfType)
    <div class="notifications-section">
        <h4>{{ $typeTitles[$type] ?? ucfirst($type) }}</h4>
        <div class="notifications-grid">
            @foreach($notificationsOfType as $notification)
            @php
            $isUnread = is_null($notification->read_at);
            $data = (array) ($notification->data ?? []);
            $message = $data['message'] ?? $data['title'] ?? $data['body'] ?? 'Notification';
            $created = $notification->created_at ? $notification->created_at->diffForHumans() : '';
            $url = $data['url'] ?? null;

            if (!$url) {
            if (!empty($data['application_id'])) {
            $url = route('admin.applications.show', $data['application_id']);
            } elseif (!empty($data['student_id'])) {
            $url = route('admin.students.show', $data['student_id']);
            } elseif (!empty($data['comment_id']) && !empty($data['application_id'])) {
            $url = route('admin.applications.show', $data['application_id']) . '#comment-' . $data['comment_id'];
            }
            }

            $icon = 'fa-bell';
            switch(true) {
            case str_contains($type, 'student_status'):
            $icon = 'fa-user-check'; break;
            case str_contains($type, 'application_status'):
            $icon = 'fa-file-circle-check'; break;
            case str_contains($type, 'student_deleted'):
            $icon = 'fa-user-slash'; break;
            case str_contains($type, 'application_message'):
            case str_contains($type, 'application_withdrawn'):
            $icon = 'fa-comment-dots'; break;
            }
            @endphp

            <div class="notification-card {{ $isUnread ? 'unread' : '' }}">
                <div class="notification-icon">
                    <i class="fa {{ $icon }}"></i>
                </div>

                <div class="notification-body">
                    <p class="notification-title">{!! $message !!}</p>
                    @if(!empty($data['short']))
                    <p class="notification-text">{{ Str::limit($data['short'], 120) }}</p>
                    @elseif(!empty($data['details']))
                    <p class="notification-text">{{ Str::limit($data['details'], 120) }}</p>
                    @endif

                    <div class="notification-meta">
                        <small>{{ $created }}</small>
                        @if($isUnread)
                        <span class="badge bg-primary">New</span>
                        @endif
                    </div>
                </div>

                <div class="notification-actions text-end">
                    @if($url)
                    <a href="{{ $url }}" class="btn btn-outline-secondary btn-sm" title="Open">
                        <i class="fa fa-arrow-right"></i> Open
                    </a>
                    @endif
                    @if($isUnread)
                    <form action="{{ route('admin.notifications.markRead', $notification->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-primary btn-sm" type="submit" title="Mark as read">
                            <i class="fa fa-check"></i>
                        </button>
                    </form>
                    @else
                    <form action="{{ route('admin.notifications.markUnread', $notification->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-outline-secondary btn-sm" type="submit" title="Mark unread">
                            <i class="fa fa-undo"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    <div class="mt-4 d-flex justify-content-center">
        {{ $notifications->links() }}
    </div>

    @else
    <div class="text-center py-6">
        <i class="fa fa-bell-slash fa-2x mb-3" style="color:#999;"></i>
        <p class="mb-0">No notifications yet.</p>
    </div>
    @endif
</div>
@endsection
