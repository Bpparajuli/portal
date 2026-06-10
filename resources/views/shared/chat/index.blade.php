@php
    $role = auth()->user()->role;
    $layout = match($role) {
        'admin' => 'layouts.admin',
        'agent' => 'layouts.agent',
        default => 'layouts.staff',
    };
    $section = match($role) {
        'admin' => 'admin-content',
        'agent' => 'agent-content',
        'staff' => 'staff-content',
    };
    $routePrefix = match($role) {
        'admin' => 'admin.chat',
        'agent' => 'agent.chat',
        'staff' => 'staff.chat',
    };
    $canDeleteAny = $role === 'admin';
@endphp

@extends($layout)
@section('title', 'Chat')
@section('page-title', 'Chat')
@section($section)

@push('styles')
<link href="{{ asset('css/chat.css') }}" rel="stylesheet">
@endpush

<div class="chat-wrapper" id="chatWrapper">
    <div class="sidebar-overlay" id="chatSidebarOverlay"></div>
    @include('shared.chat.partials.sidebar')
    <div class="chat-main" id="chatMain">
        @include('shared.chat.partials.placeholder')
        <div id="chatActive" class="d-none" style="display:none;flex-direction:column;height:100%;min-height:0;">
            @include('shared.chat.partials.chat-header')
            @include('shared.chat.partials.message-box')
            @include('shared.chat.partials.chat-footer')
        </div>
    </div>
</div>

@push('scripts')
<script>
window.CHAT_CONFIG = {
    role: '{{ $role }}',
    authId: {{ Auth::id() }},
    csrfToken: '{{ csrf_token() }}',
    usersRoute: '{{ route($routePrefix . '.users') }}',
    canDeleteAny: {{ $canDeleteAny ? 'true' : 'false' }},
    pusherKey: '{{ env("PUSHER_APP_KEY") }}',
    pusherCluster: '{{ env("PUSHER_APP_CLUSTER") }}',
    broadcastDefault: '{{ config("broadcasting.default") }}',
};
</script>
@if(config('broadcasting.default') === 'pusher')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
@endif
<script src="{{ asset('js/chat.js') }}"></script>
@endpush

@endsection