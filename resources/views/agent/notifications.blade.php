@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="notifications-container">
    <!-- Header -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">
            All Notifications
        </h1>
        <form action="{{ route('agent.notifications.markAll') }}" method="POST" class="d-inline">
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
    'application_status' => 'Application Status Updated',
    'student_status' => 'Student Status Updated',
    'document_deleted' => 'Document Deleted',
    'other' => 'Other Notifications',
    ];

    $grouped = collect($otherNotifications)->groupBy(fn($n) => ($n->data['type'] ?? 'other'));
    @endphp

    <div class="notifications-grid d-flex gap-4">
        <!-- Main Notifications -->
        <div class="flex-grow-1">
            @forelse($grouped as $type => $notificationsOfType)
            <div class="mb-5">

                <h4 class="section-title d-flex align-items-center mb-3">
                    <i class="fa {{
                        match(true) {
                            str_contains($type, 'application_status') => 'fa-file-circle-check',
                            str_contains($type, 'student_status') => 'fa-user-check',
                            default => 'fa-bell'
                        }
                    }} me-2"></i>
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

                $url = $data['link'] ?? $data['url'] ?? null;
                if (!$url && !empty($data['application_id'])) {
                $url = route('agent.applications.show', $data['application_id']);
                if (!empty($data['comment_id'])) $url .= '#comment-' . $data['comment_id'];
                } elseif (!$url && !empty($data['student_id'])) {
                $url = route('agent.students.show', $data['student_id']);
                }

                $icon = match(true) {
                str_contains($type, 'student_status') => 'fa-user-check',
                str_contains($type, 'application_status') => 'fa-file-circle-check',
                default => 'fa-bell'
                };
                @endphp

                @if($url)<a href="{{ $url }}" class="notification-link text-decoration-none">@endif
                    <div class="notification-card mb-2 p-3 d-flex align-items-start border rounded {{ $isUnread ? 'unread' : '' }}">
                        <div class="notification-icon me-3">
                            <i class="fa {{ $icon }} fa-2x"></i>
                        </div>
                        <div class="notification-body flex-grow-1">
                            <p class="notification-title mb-1">{!! $message !!}</p>
                            @if($short)
                            <p class="notification-text">{{ Str::limit($short, 130) }}</p>
                            @endif
                            <small class="text-muted">
                                <i class="fa fa-clock me-1"></i>{{ $created }}
                                @if($isUnread)<span class="badge bg-primary ms-2">New</span>@endif
                            </small>
                        </div>
                        <div class="notification-actions ms-3" onclick="event.stopPropagation();">
                            @if($isUnread)
                            <form action="{{ route('agent.notifications.markRead', $notification->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success" title="Mark as read"><i class="fa fa-check"></i></button>
                            </form>
                            @else
                            <form action="{{ route('agent.notifications.markUnread', $notification->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Mark as unread"><i class="fa fa-undo"></i></button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @if($url)</a>@endif
                @endforeach
            </div>
            @empty
            <div class="empty-state text-center py-4">
                <i class="fa fa-bell-slash fa-2x mb-2"></i>
                <p>No notifications in this category.</p>
            </div>
            @endforelse
        </div>

        <!-- Messages Sidebar -->
        <div style="width: 300px;">
            <h4 class="mb-3"><i class="fa fa-envelope me-1"></i> Messages
                @if(count($messages))<span class="text-muted ms-auto">({{ count($messages) }})</span>@endif
            </h4>

            @if(count($messages))
            @foreach($messages as $notification)
            @php
            $isUnread = is_null($notification->read_at);
            $data = (array) $notification->data;
            $sender = $data['added_by']['name'] ?? 'Unknown';
            $message = $data['message'] ?? 'No message';
            $created = $notification->created_at?->diffForHumans();
            $url = $data['link'] ?? null;
            @endphp

            @if($url)<a href="{{ $url }}" class="notification-link text-decoration-none">@endif
                <div class="notification-card mb-2 p-2 border rounded {{ $isUnread ? 'unread' : '' }}">
                    <p class="mb-1">{!! $message !!}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="text-muted">{{ $created }} | By: {{ $sender }}</p>
                        <div>
                            @if($isUnread)
                            <form action="{{ route('agent.notifications.markRead', $notification->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success"><i class="fa fa-check"></i></button>
                            </form>
                            @else
                            <form action="{{ route('agent.notifications.markUnread', $notification->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fa fa-undo"></i></button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @if($url)</a>@endif
            @endforeach
            @else
            <div class="empty-state text-center py-4">
                <i class="fa fa-envelope-open fa-2x mb-2"></i>
                <p>No messages yet.</p>
            </div>
            @endif
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
