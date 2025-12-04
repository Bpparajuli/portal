@php
$user = auth()->user();
@endphp

<header class="perfect-header">
    <div class="header-container">
        {{-- Top Section: Logo, User Info & Notice Bar --}}
        <div class="header-top-row d-flex justify-content-between align-items-center">
            {{-- Left Section: User Logo + Name --}}
            <div class="header-left d-flex align-items-center gap-2">
                @auth
                @php
                // Dynamic dashboard link
                $dashboardRoute = $user->is_admin
                ? route('admin.dashboard')
                : ($user->is_agent ? route('agent.dashboard') : '/');

                // Dynamic user show link
                $userShowRoute = $user->is_admin
                ? route('admin.users.show', $user)
                : ($user->is_agent ? route('agent.users.show', $user) : '#');
                @endphp
                <a href="{{ $dashboardRoute }}" class="d-flex align-items-center gap-2 text-decoration-none">
                    @if($user->business_logo)
                    <img src="{{ Storage::url($user->business_logo) }}" alt="Logo" width="90" height="90" class="rounded border shadow-sm">
                    @else
                    <div class="no-logo rounded border bg-secondary text-white d-flex justify-content-center align-items-center" style="width:50px; height:50px;">
                        N/A
                    </div>
                    @endif
                </a>
                <a href="{{ $userShowRoute }}" class="text-dark fw-bold">
                    {{ $user->business_name }}
                </a>
                @else
                {{-- Default Logo for Guests --}}
                <div class="header-logo">
                    <img src="{{ asset('images/main_logo.png') }}" alt="Idea Consultancy" />
                </div>
                @endauth
            </div>
            {{-- Center Section: Notice Bar --}}
            <div class="header-center flex-grow-1 mx-3">
                <div class="notice-bar">
                    <p class="notice-text mb-0">
                        <span class="marquee-text">
                            📢 Notice: Due to issues with the NOC of Dubai colleges in Nepal, we’ve partnered with 5+ universities—promote these for good commissions, and top performers can still earn FAM trips!
                        </span>
                    </p>
                </div>
            </div>

            {{-- Right Section: Notifications & Mobile Toggle --}}
            <div class="header-right d-flex align-items-center gap-2">

                @auth
                {{-- 🔔 Normal Notifications --}}
                @if ($user->is_admin || $user->is_agent)
                <div class="notification-dropdown">
                    <button class="notification-toggle" aria-expanded="false" aria-controls="notif-menu">
                        <i class="fa fa-bell"></i>
                        @php
                        $unreadCount = $user->unreadNotifications
                        ->where('data.type', '!=', 'application_message_added')
                        ->count();
                        @endphp
                        @if($unreadCount > 0)
                        <span class="notification-badge">{{ $unreadCount }}</span>
                        @endif
                    </button>

                    <div class="notification-menu" id="notif-menu">
                        @php
                        $unreadNotifications = $user->unreadNotifications
                        ->where('data.type', '!=', 'application_message_added');
                        @endphp
                        @forelse($unreadNotifications->take(5) as $notification)
                        <a href="{{ $user->is_admin 
                                        ? route('admin.notifications.readAndRedirect', $notification->id)
                                        : route('agent.notifications.readAndRedirect', $notification->id) }}" class="notification-item unread">
                            <div>{{ $user->formatNotification($notification) }}</div>
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                        </a>
                        @empty
                        <span class="notification-item-text">No new notifications</span>
                        @endforelse
                        <hr class="my-1">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                            <form action="{{ $user->is_admin 
                                        ? route('admin.notifications.markAll')
                                        : route('agent.notifications.markAll') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="text-decoration-none text-dark btn btn-link p-0">
                                    <i class="fa fa-check-double me-1"></i> Mark All
                                </button>
                            </form>
                            <a href="{{ $user->is_admin 
                                        ? route('admin.notifications')
                                        : route('agent.notifications') }}" class="text-decoration-none text-primary">
                                View All
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                {{-- ✉️ Application Messages --}}
                <div class="notification-dropdown">
                    <button class="notification-toggle" aria-expanded="false" aria-controls="message-menu">
                        <i class="fa fa-envelope"></i>
                        @if($user->unreadNotifications->where('data.type', 'application_message_added')->count() > 0)
                        <span class="notification-badge">
                            {{ $user->unreadNotifications->where('data.type', 'application_message_added')->count() }}
                        </span>
                        @endif
                    </button>

                    <div class="notification-menu" id="message-menu">
                        @php
                        $messageNotifications = $user->unreadNotifications
                        ->where('data.type', 'application_message_added');
                        @endphp

                        @forelse($messageNotifications->take(5) as $notification)
                        <a href="{{ $user->is_admin 
                                    ? route('admin.notifications.readAndRedirect', $notification->id)
                                    : route('agent.notifications.readAndRedirect', $notification->id) }}" class="notification-item unread bg-light border-start border-primary ps-3">
                            <div>{{ $user->formatNotification($notification) }}</div>
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                        </a>
                        @empty
                        <span class="notification-item-text">No new messages</span>
                        @endforelse

                        <a href="{{ $user->is_admin 
                                ? route('admin.notifications') 
                                : route('agent.notifications') }}" class="notification-view-all">View All Messages</a>
                    </div>
                </div>
                @if ($user->is_admin )
                <div class="notification-dropdown">
                    <a href="{{ route('admin.users.waiting') }}" class="notification-item">
                        <button class="notification-toggle">
                            <i class="fa fa-users"></i>
                            @php
                            $totalWaitingUsers = app\models\User::where('is_agent', 1)
                            ->whereIn('agreement_status', ['not_uploaded', 'uploaded'])
                            ->count();
                            @endphp
                            @if($totalWaitingUsers > 0)
                            <span class="notification-badge">{{ $totalWaitingUsers }}</span>
                            @endif
                        </button>
                    </a>
                </div>
                @endif

                @else
                {{-- Guest text for non-logged-in users --}}
                <span class="guest-text fw-bold text-primary">Idea Consultancy Portal</span>
                @endauth

                {{-- Mobile Toggle Button --}}
                <button class="menu-toggle" aria-label="Toggle navigation menu">
                    <i class="fa fa-bars"></i>
                </button>
            </div>
        </div>

        {{-- Main Navigation Menu --}}
        <nav class="header-nav">
            <ul class="nav-list">
                @if ($user?->is_admin)
                <li class="nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}"><i class="fa fa-house"></i> Dashboard</a>
                </li>
                <li class="nav-item {{ request()->is('admin/students*') ? 'active' : '' }}">
                    <a href="{{ route('admin.students.index') }}"><i class="fa fa-users"></i> Students</a>
                </li>
                <li class="nav-item {{ request()->is('admin/universities*') ? 'active' : '' }}">
                    <a href="{{ route('admin.universities.index') }}"><i class="fa fa-university"></i> Universities</a>
                </li>
                <li class="nav-item {{ request()->is('admin/users*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}"><i class="fa fa-user"></i> Users</a>
                </li>
                <li class="nav-item {{ request()->is('admin/applications*') ? 'active' : '' }}">
                    <a href="{{ route('admin.applications.index') }}"><i class="fa fa-file-text"></i> Applications</a>
                </li>
                @elseif ($user?->is_agent)
                <li class="nav-item {{ request()->is('agent/dashboard') ? 'active' : '' }}">
                    <a href="{{ route('agent.dashboard') }}"><i class="fa fa-house"></i> Dashboard</a>
                </li>
                <li class="nav-item {{ request()->is('agent/students*') ? 'active' : '' }}">
                    <a href="{{ route('agent.students.index') }}"><i class="fa fa-users"></i> Students</a>
                </li>
                <li class="nav-item {{ request()->is('agent/universities*') ? 'active' : '' }}">
                    <a href="{{ route('agent.universities.index') }}"><i class="fa fa-university"></i> Universities</a>
                </li>
                <li class="nav-item {{ request()->is('agent/applications*') ? 'active' : '' }}">
                    <a href="{{ route('agent.applications.index') }}"><i class="fa fa-book"></i> Applied List</a>
                </li>
                @else
                {{-- Guest Navigation --}}
                <li class="nav-item {{ request()->is('/') ? 'active' : '' }}">
                    <a href="{{ url('/') }}"><i class="fa fa-house"></i> Home</a>
                </li>
                <li class="nav-item">
                    <a href="https://ideaconsultancyservices.com/"><i class="fa fa-globe"></i> Webpage</a>
                </li>
                <li class="nav-item {{ request()->is('guest/universities*') ? 'active' : '' }}">
                    <a href="{{ route('guest.universities.index') }}"><i class="fa fa-calendar-check"></i> Universities</a>
                </li>
                <li class="nav-item {{ request()->is('auth/contact') ? 'active' : '' }}">
                    <a href="{{ route('auth.contact') }}"><i class="fa fa-envelope"></i> Contact Us</a>
                </li>
                <li class="nav-item {{ request()->is('auth/login') ? 'active' : '' }}">
                    <a href="{{ route('login') }}"><i class="fa fa-sign-in"></i> Login</a>
                </li>
                <li class="nav-item {{ request()->is('auth/register') ? 'active' : '' }}">
                    <a href="{{ route('register') }}"><i class="fa fa-user-plus"></i> Register</a>
                </li>
                @endif

                {{-- Logout --}}
                @auth
                <li class="nav-item logout-link">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="fa-solid fa-right-from-bracket"></i> Logout
                        </a>
                    </form>
                </li>
                @endauth
            </ul>
        </nav>
    </div>
</header>
