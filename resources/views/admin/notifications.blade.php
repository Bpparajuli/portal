@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">All Notifications</h3>

    <!-- Bulk actions -->
    <div class="mb-3 d-flex gap-2">
        <form method="POST" action="{{ route('notifications.markAll') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-success">Mark All as Read</button>
        </form>
        <form method="POST" action="{{ route('notifications.deleteAll') }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger">Delete All</button>
        </form>
    </div>

    @php
    // Group notifications by type from data['type']
    $notifications = auth()->user()->notifications;
    $grouped = $notifications->groupBy(fn($n) => $n->data['type'] ?? 'others');

    // Human-friendly display labels for each type
    $groups = [
    'user_registered' => 'User Registered',
    'student_added' => 'Student Added',
    'university_applied' => 'University Applied',
    'student_selected_message' => 'Message to Selected Student',
    'status_updated' => 'Status Updated',
    'others' => 'Other Notifications'
    ];
    @endphp

    @foreach ($grouped as $type => $typeNotifications)
    <h5 class="mt-4">{{ $groups[$type] ?? ucwords(str_replace('_', ' ', $type)) }}</h5>
    <ul class="list-group">
        @foreach ($typeNotifications as $notification)
        <li class="list-group-item d-flex justify-content-between align-items-center {{ is_null($notification->read_at) ? 'bg-light' : '' }}">
            <div>
                <div>{{ $notification->data['message'] ?? 'No message provided' }}</div>
                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
            </div>
            <div class="d-flex gap-2">
                @if(is_null($notification->read_at))
                <form method="POST" action="{{ route('notifications.mark', $notification->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-success">Mark as Read</button>
                </form>
                @endif
            </div>
        </li>
        @endforeach
    </ul>
    @endforeach

</div>
@endsection
