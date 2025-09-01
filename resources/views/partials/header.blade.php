@php
$user = auth()->user();
@endphp

<nav class="bp-navbar">
    <div class="bp-navbar-container">
        {{-- Logo --}}
        <div class="bp-navbar-logo">
            <img src="https://ideaconsultancyservices.com/wp-content/uploads/2023/10/Logos.png" alt="Idea Consultancy" />
        </div>

        {{-- Brand Text --}}
        <div class="bp-navbar-brand">
            <h1 class="text-secondary">
                @if ($user?->is_admin)
                Admin Portal<br> Idea Consultancy
                @elseif ($user?->is_agent)
                Agent Portal <br>Idea Consultancy
                @else
                Idea Consultancy
                @endif
            </h1>
        </div>

        {{-- Toggle Button for Mobile --}}
        <button class="bp-navbar-toggle" id="bp-navbar-toggle">
            <i class="fa fa-bars"></i>
        </button>

        {{-- Menu & Icons --}}
        <div class="bp-navbar-collapse" id="bp-navbar-collapse">
            {{-- Navigation Menu --}}
            <ul class="bp-navbar-menu">
                @if ($user?->is_admin)
                <li class="nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}"><i class="fa fa-house"></i>Dashboard</a>
                </li>
                <li class="nav-item {{ request()->is('student/index') ? 'active' : '' }}">
                    <a href="{{ route('admin.students.index') }}"><i class="fa fa-users"></i>Students</a>
                </li>
                <li class="nav-item {{ request()->is('universities/index') ? 'active' : '' }}">
                    <a href="{{ route('admin.universities.index') }}"><i class="fa fa-calendar-check"></i>Universities</a>
                </li>
                <li class="nav-item {{ request()->is('user/index') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}"><i class="fa fa-sliders"></i>Users</a>
                </li>
                @php
                $totalWaitingUsers = \App\Models\User::where('active', 0)->count();
                @endphp
                <li class="nav-item {{ request()->is('user/waiting') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.waiting') }}">
                        <i class="fa fa-question"></i>
                        Waiting Users
                        @if($totalWaitingUsers > 0)
                        <span class="bp-badge">{{ $totalWaitingUsers }}</span>
                        @endif
                    </a>
                </li>
                @elseif ($user?->is_agent)
                <li class="nav-item {{ request()->is('agent/dashboard') ? 'active' : '' }}">
                    <a href="{{ route('agent.dashboard') }}"><i class="fa fa-house"></i>Dashboard</a>
                </li>
                <li class="nav-item {{ request()->is('agent/students/index') ? 'active' : '' }}">
                    <a href="{{ route('agent.students.index') }}"><i class="fa fa-users"></i>Students</a>
                </li>
                <li class="nav-item {{ request()->is('agent/universities/index') ? 'active' : '' }}">
                    <a href="{{ route('agent.universities.index') }}"><i class="fa fa-calendar-check"></i>Universities</a>
                </li>
                <li class="nav-item {{ request()->is('agent/applied-list') ? 'active' : '' }}">
                    <a href="{{ route('agent.students.index') }}"><i class="fa fa-sliders"></i>Applied List</a>
                </li>
                @else
                {{-- Guest Links --}}
                <li class="nav-item {{ request()->is('/') ? 'active' : '' }}">
                    <a href="{{ url('/') }}"><i class="fa fa-house"></i>Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="https://ideaconsultancyservices.com/"><i class="fa fa-globe"></i>Webpage</a>
                </li>
                <li class="nav-item {{ request()->is('guest/universities') ? 'active' : '' }}">
                    <a href="{{ route('guest.universities.index') }}"><i class="fa fa-calendar-check"></i>Universities</a>
                </li>
                <li class="nav-item {{ request()->is('contact/show') ? 'active' : '' }}">
                    <a href="{{ route('auth.contact') }}"><i class="fa fa-envelope"></i>Contact Us</a>
                </li>
                <li class="nav-item {{ request()->is('login') ? 'active' : '' }}">
                    <a href="{{ route('login') }}"><i class="fa fa-sign-in"></i>Login</a>
                </li>
                <li class="nav-item {{ request()->is('register') ? 'active' : '' }}">
                    <a href="{{ route('register') }}"><i class="fa fa-user-plus"></i>Register</a>
                </li>
                @endif
            </ul>

            {{-- Icons / Notifications / Avatar --}}
            @if ($user)
            <div class="bp-navbar-icons">
                {{-- Notifications --}}
                @if ($user->is_admin || $user->is_agent)
                <div class="bp-dropdown">
                    <span class="bp-icon">
                        🔔
                        @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="bp-badge">{{ auth()->user()->unreadNotifications->count() }}</span>
                        @endif
                    </span>
                    <div class="bp-dropdown-menu">
                        @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                        <a href="{{ $notification->data['link'] ?? '#' }}">
                            {{ $notification->data['message'] ?? 'New Notification' }}
                            <small>{{ $notification->created_at->diffForHumans() }}</small>
                        </a>
                        @empty
                        <span class="notification-item-text">No new notifications</span>
                        @endforelse
                        <hr>
                        <a href="{{ $user->is_admin ? route('admin.notifications') : route('agent.notifications') }}">
                            View All Notifications
                        </a>
                    </div>
                </div>
                @endif

                {{-- Welcome + Avatar --}}
                <div class="welcome">Welcome, <strong>{{ $user->name }}</strong></div>
                <div class="user-avatar">
                    <img src="{{ asset('images/Agents_logo/' . $user->business_logo) }}" alt="user" />
                </div>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </a>
                </form>
            </div>
            @endif
        </div>
    </div>
</nav>
