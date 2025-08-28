@extends('layouts.app')

@section('content')
<div class="">
    <h3 class=" mb-4">All Notifications</h3>

    @php
    $user = auth()->user();
    $notifications = $user->notifications()->latest()->get();

    $grouped = $notifications->groupBy(fn($n) => $n->data['type'] ?? 'others');

    $groups = [
    'user_registered' => 'User Registered',
    'student_added' => 'Student Added',
    'university_applied' => 'University Applied',
    'student_selected_message' => 'Message to Selected Student',
    'status_updated' => 'Status Updated',
    'others' => 'Other Notifications'
    ];
    @endphp

    <!-- Bulk actions -->
    <div class="mb-3 d-flex gap-2">
        <form method="POST" action="{{ route('admin.notifications.readAll') }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-sm btn-success">Mark All as Read</button>
        </form>
    </div>

    @if($notifications->isEmpty())
    <p>No notifications available.</p>
    @else
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
                <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-outline-success">Mark as Read</button>
                </form>
                @endif
            </div>
        </li>
        @endforeach
    </ul>
    @endforeach
    @endif
</div>
@endsection
