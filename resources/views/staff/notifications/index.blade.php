@extends('layouts.staff')

@section('staff-content')
<div class="container-fluid p-4">
    <x-page-header title="Notifications" subtitle="Stay updated with your activities">
        <x-slot:actions>
            <form action="{{ route('staff.notifications.markAll') }}" method="POST" class="d-inline" id="markAllForm">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary" onclick="return confirm('Mark all as read?')">
                    <i class="fas fa-check-double me-1"></i>Mark All Read
                </button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    @forelse($notifications as $notification)
                    <div class="d-flex align-items-start p-3 border-bottom notification-item {{ $notification->read_at ? '' : 'bg-light' }}">
                        <div class="me-3">
                            <i class="fas fa-bell fa-lg {{ $notification->read_at ? 'text-muted' : 'text-primary' }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="mb-1 {{ $notification->read_at ? '' : 'fw-semibold' }}">
                                        @if(isset($notification->data['link']))
                                            <a href="{{ route('staff.notifications.readAndRedirect', $notification->id) }}"
                                               class="{{ $notification->read_at ? 'text-reset' : 'text-dark' }}">
                                                {{ $notification->data['message'] ?? 'Notification' }}
                                            </a>
                                        @else
                                            {{ $notification->data['message'] ?? 'Notification' }}
                                        @endif
                                    </p>
                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                </div>
                                <div class="d-flex gap-1">
                                    @if(!$notification->read_at)
                                    <form action="{{ route('staff.notifications.markRead', $notification->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-link text-success p-0" title="Mark as read">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    @endif
                                    <form action="{{ route('staff.notifications.delete', $notification->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this notification?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Delete">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <x-empty-state icon="fa-bell-slash" title="No notifications" description="You're all caught up!" />
                    @endforelse
                </div>
            </div>

            @if($notifications->hasPages())
            <div class="mt-3">
                {{ $notifications->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
