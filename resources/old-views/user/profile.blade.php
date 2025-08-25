@extends('layout.app')
@section('title', $user->name . ' Profile')

@section('content')
<div>
    <h2>User Profile: {{ $user->name }}</h2>

    <div class="card mb-3">
        <div class="d-flex justify-content-between align-items-center p-1">
            <div class="card-body">
                <p><strong>Business Name:</strong> {{ $user->business_name }}</p>
                <p><strong>Owner:</strong> {{ $user->owner_name }}</p>
                <p><strong>Contact:</strong> {{ $user->contact }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Role:</strong>
                    @if($user->is_admin) Admin
                    @elseif($user->is_agent) Agent
                    @else User
                    @endif
                </p>
                <p><strong>Status:</strong>
                    {!! $user->active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Pending</span>' !!}
                </p>
            </div>
            <div class="business-logo">
                @if($user->business_logo)
                <img src="{{ asset('images/Agents_logo/' . $user->business_logo) }}" alt="Logo" class="uni-logo">
                @endif
            </div>
        </div>
    </div>

    <h4>Students Added by User</h4>
    {{-- @if($students->isEmpty())
    <p>No students yet.</p>
    @else
    <ul class="list-group mb-3">
        @foreach($students as $student)
        <li class="list-group-item">
            <strong>{{ $student->name }}</strong> ({{ $student->email ?? 'N/A' }})
    </li>
    @endforeach
    </ul>
    @endif --}}

    <h4>User Notifications</h4>
    @if($notifications->isEmpty())
    <p>No notifications yet.</p>
    @else
    <ul class="list-group mb-3">
        @foreach($notifications as $note)
        <li class="list-group-item {{ $note->read_at ? '' : 'bg-light' }}">
            <strong>{{ $note->data['title'] ?? 'Notification' }}</strong><br>
            {{ $note->data['message'] ?? '' }}
            <small class="d-block text-muted">{{ $note->created_at->diffForHumans() }}</small>
        </li>
        @endforeach
    </ul>
    @endif

    @if(auth()->user()->is_admin)
    <a href="{{ route('user.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit User</a>
    @endif
</div>
@endsection
