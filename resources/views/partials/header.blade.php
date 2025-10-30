@php
$user = auth()->user();
@endphp

<header class="perfect-header">
    <div class="header-container">
        {{-- Top Section: Logo, Title, and User Info --}}
        <div class="header-top-row">

            {{-- Left Section: Logo --}}
            <div class="header-left">
                <div class="header-logo">
                    <img src="{{ asset('images/main_logo.png') }}" alt="Idea Consultancy" />
                </div>
            </div>

            {{-- Center Section: Notice Bar --}}
            <div class="header-center">
                <div class="notice-bar">
                    <p class="notice-text">
                        <span class="marquee-text">
                            Important Notice: Our office will be closed on December 25th and January 1st for the holidays.
                            Wishing you a joyful season!
                        </span>
                    </p>
                </div>
            </div>

            {{-- Right Section: Brand Title, User Info, and Mobile Toggle --}}
            <div class="header-right">
                @guest
                <div class="header-title header-logo d-flex g-2 ">
                    <h1 class="header-title-text">Idea Consultancy</h1>
                    <img src="{{ asset('images/logo2.png') }}" alt="Idea Consultancy" />
                </div>
                @endguest

                {{-- User Info and Notifications (visible on desktop) --}}
                @auth
                <div class="header-user-info">

                    {{-- 🔔 Normal Notifications Dropdown --}}
                    @if ($user->is_admin || $user->is_agent)
                    <div class="notification-dropdown">
                        <button class="notification-toggle" aria-expanded="false" aria-controls="notif-menu">
                            <i class="fa fa-bell"></i>
                            @if(auth()->user()->unreadNotifications->where('data.type', '!=', 'application_message')->count() > 0)
                            <span class="notification-badge">
                                {{ auth()->user()->unreadNotifications->where('data.type', '!=', 'application_message')->count() }}
                            </span>
                            @endif
                        </button>

                        <div class="notification-menu" id="notif-menu">
                            @php
                            $otherNotifications = auth()->user()->unreadNotifications
                            ->where('data.type', '!=', 'application_message');
                            @endphp

                            {{-- Regular Notifications --}}
                            @forelse($otherNotifications->take(5) as $notification)
                            <a href="{{ $notification->data['link'] ?? '#' }}" class="notification-item unread">
                                <div>{{ auth()->user()->formatNotification($notification) }}</div>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            </a>
                            @empty
                            <span class="notification-item-text">No new notifications</span>
                            @endforelse

                            {{-- Older notifications --}}
                            @if(auth()->user()->readNotifications->count() > 0)
                            <hr class="my-1">
                            <small class="text-muted ps-2">Earlier</small>
                            @endif

                            @foreach(auth()->user()->readNotifications->where('data.type', '!=', 'application_message')->take(3) as $notification)
                            <a href="{{ $notification->data['link'] ?? '#' }}" class="notification-item">
                                <div>{{ auth()->user()->formatNotification($notification) }}</div>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            </a>
                            @endforeach

                            <a href="{{ $user->is_admin ? route('admin.notifications') : route('agent.notifications') }}" class="notification-view-all">View All</a>
                        </div>
                    </div>
                    @endif

                    {{-- ✉️ Application Message Notifications Dropdown --}}
                    <div class="notification-dropdown">
                        <button class="notification-toggle" aria-expanded="false" aria-controls="message-menu">
                            <i class="fa fa-envelope"></i>
                            @if(auth()->user()->unreadNotifications->where('data.type', 'application_message')->count() > 0)
                            <span class="notification-badge">
                                {{ auth()->user()->unreadNotifications->where('data.type', 'application_message')->count() }}
                            </span>
                            @endif
                        </button>

                        <div class="notification-menu" id="message-menu">
                            @php
                            $messageNotifications = auth()->user()->unreadNotifications
                            ->where('data.type', 'application_message');
                            @endphp

                            @forelse($messageNotifications->take(5) as $notification)
                            <a href="{{ $notification->data['link'] ?? '#' }}" class="notification-item unread bg-light border-start border-primary ps-3">
                                <div>{{ auth()->user()->formatNotification($notification) }}</div>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            </a>
                            @empty
                            <span class="notification-item-text">No new messages</span>
                            @endforelse

                            <a href="{{ $user->is_admin ? route('admin.notifications') : route('agent.notifications') }}" class="notification-view-all">View All Messages</a>
                        </div>
                    </div>

                    {{-- Welcome Message & Avatar --}}
                    <div class="welcome-message">
                        <strong>{{ $user->name }}</strong>
                    </div>
                    <div class="user-avatar">
                        @if($user->business_logo)
                        <img src="{{ Storage::url($user->business_logo) }}" alt="Logo" width="120" height="120" class="rounded border shadow-sm">
                        @else
                        <div class="no-logo">No Logo</div>
                        @endif
                    </div>
                </div>
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
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="fa-solid fa-right-from-bracket"></i> Logout
                        </a>
                    </form>
                </li>
                @endauth
            </ul>
        </nav>
    </div>
</header>
