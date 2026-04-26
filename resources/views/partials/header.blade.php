@php
    $user = auth()->user();
@endphp

<header class="app-header">
    <div class="app-header-inner">
        {{-- Top Bar --}}
        <div class="header-top">
            {{-- Logo Section --}}
            <div class="logo-section">
                @auth
                    @php
                        $dashboardRoute = $user->is_admin
                            ? route('admin.dashboard')
                            : ($user->is_agent
                                ? route('agent.dashboard')
                                : ($user->is_staff
                                    ? route('crm.dashboard')
                                    : '/'));

                        $profileRoute = $user->is_admin
                            ? route('admin.users.show', $user)
                            : ($user->is_agent
                                ? route('agent.users.show', $user)
                                : ($user->is_staff
                                    ? route('crm.dashboard')
                                    : '#'));
                    @endphp
                    <a href="{{ $dashboardRoute }}" class="logo-link">
                        @if ($user->business_logo)
                            <img src="{{ Storage::url($user->business_logo) }}" alt="Logo" class="logo-img">
                        @else
                            <div class="logo-placeholder">
                                <span>{{ substr($user->business_name ?? 'IC', 0, 2) }}</span>
                            </div>
                        @endif
                    </a>
                @else
                    <a href="/" class="logo-link">
                        <img src="{{ asset('images/main_logo.png') }}" alt="Idea Consultancy" class="logo-img">
                    </a>
                    <span class="fw-bold text-white">Idea Consultancy</span>
                @endauth
            </div>

            {{-- Notice Bar --}}
            @if (!$user || $user->is_agent)
                <div class="notice-section">
                    <div class="notice-content">
                        <i class="fas fa-bullhorn"></i>
                        <div class="notice-text">
                            <span>
                                Due to issues with NOC of Dubai colleges in Nepal, we've partnered with 5+ universities
                                —
                                promote these for good commissions, and top performers can still earn FAM trips!
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            @auth
                @if ($user->is_admin)
                    <div class="header-bottom" id="navMenu">
                        <nav class="nav-menu">
                            <ul>
                                <li class="{{ request()->is('admin/dashboard') ? 'active' : '' }}"><a
                                        href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt"></i>
                                        Dashboard</a></li>
                                <li class="{{ request()->is('admin/students*') ? 'active' : '' }}"><a
                                        href="{{ route('admin.students.index') }}"><i class="fas fa-user-graduate"></i>
                                        Students</a></li>
                                <li class="{{ request()->is('admin/universities*') ? 'active' : '' }}"><a
                                        href="{{ route('admin.universities.index') }}"><i class="fas fa-university"></i>
                                        Universities</a></li>
                                <li class="{{ request()->is('admin/users*') ? 'active' : '' }}"><a
                                        href="{{ route('admin.users.index') }}"><i class="fas fa-users"></i> Users</a>
                                </li>
                                <li class="{{ request()->is('admin/applications*') ? 'active' : '' }}"><a
                                        href="{{ route('admin.applications.index') }}"><i class="fas fa-file-alt"></i>
                                        Applications</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                @endif
            @endauth
            {{-- Actions Section --}}
            <div class="actions-section">
                @auth
                    {{-- Chat --}}
                    @if (($user->is_admin || $user->is_agent) && ($user->is_admin ? Route::has('admin.chat') : Route::has('agent.chat')))
                        @php
                            $chatRoute = $user->is_admin ? route('admin.chat') : route('agent.chat');
                            $unreadChatCount =
                                \App\Models\ChatMessage::where('receiver_id', $user->id)
                                    ->where('status', '!=', 'read')
                                    ->count() ?? 0;
                        @endphp
                        <a href="{{ $chatRoute }}" class="action-btn">
                            <i class="fas fa-comments" title="Chat with Admin"></i>
                            @if ($unreadChatCount > 0)
                                <span class="header-badge">{{ $unreadChatCount }}</span>
                            @endif
                        </a>
                    @endif

                    {{-- Notifications --}}
                    @if ($user->is_admin || $user->is_agent)
                        <div class="dropdown-container">
                            <button class="action-btn notif-btn">
                                <i class="fas fa-bell"></i>
                                @php $notifCount = $user->unreadNotifications->where('data.type', '!=', 'application_message_added')->count(); @endphp
                                @if ($notifCount > 0)
                                    <span class="header-badge">{{ $notifCount }}</span>
                                @endif
                            </button>
                            <div class="dropdown-panel">
                                <div class="panel-header">
                                    <strong>AllNotifications</strong>
                                    <form
                                        action="{{ $user->is_admin ? route('admin.notifications.markAll') : route('agent.notifications.markAll') }}"
                                        method="POST">
                                        @csrf
                                        <button type="submit" class="mark-all">Mark all read</button>
                                    </form>
                                </div>
                                <div class="panel-body">
                                    @php $notifications = $user->notifications()->where('data->type', '!=', 'application_message_added')->take(5)->get(); @endphp
                                    @forelse($notifications as $notification)
                                        <a href="{{ $user->is_admin ? route('admin.notifications.readAndRedirect', $notification->id) : route('agent.notifications.readAndRedirect', $notification->id) }}"
                                            class="panel-item {{ is_null($notification->read_at) ? 'unread' : '' }}">
                                            <div class="item-text">{!! $user->formatNotification($notification) !!}</div>
                                            <div class="item-time">{{ $notification->created_at->diffForHumans() }}</div>
                                        </a>
                                    @empty
                                        <div class="empty-panel">
                                            <i class="far fa-bell-slash"></i>
                                            <p>No notifications</p>
                                        </div>
                                    @endforelse
                                </div>
                                <div class="panel-footer">
                                    <a
                                        href="{{ $user->is_admin ? route('admin.notifications') : route('agent.notifications') }}">View
                                        all notifications →</a>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Messages --}}
                    <div class="dropdown-container">
                        <button class="action-btn message-btn">
                            <i class="fas fa-envelope"></i>
                            @php $msgCount = $user->unreadNotifications->where('data.type', 'application_message_added')->count(); @endphp
                            @if ($msgCount > 0)
                                <span class="header-header-badge">{{ $msgCount }}</span>
                            @endif
                        </button>
                        <div class="dropdown-panel">
                            <div class="panel-header">
                                <strong>Application Messages</strong>
                            </div>
                            <div class="panel-body">
                                @php $messages = $user->notifications()->where('data->type', 'application_message_added')->take(5)->get(); @endphp
                                @forelse($messages as $notification)
                                    <a href="{{ $user->is_admin ? route('admin.notifications.readAndRedirect', $notification->id) : route('agent.notifications.readAndRedirect', $notification->id) }}"
                                        class="panel-item {{ is_null($notification->read_at) ? 'unread' : '' }}">
                                        <div class="item-text">{!! $user->formatNotification($notification) !!}</div>
                                        <div class="item-time">{{ $notification->created_at->diffForHumans() }}</div>
                                    </a>
                                @empty
                                    <div class="empty-panel">
                                        <i class="far fa-inbox"></i>
                                        <p>No messages</p>
                                    </div>
                                @endforelse
                            </div>
                            <div class="panel-footer">
                                <a
                                    href="{{ $user->is_admin ? route('admin.notifications') : route('agent.notifications') }}">View
                                    all messages →</a>
                            </div>
                        </div>
                    </div>
                    {{-- Pending Unapproved Users --}}

                    {{-- Pending Users (Admin) --}}
                    @if ($user->is_admin)
                        <a href="{{ route('admin.users.waiting') }}" class="action-btn">
                            <i class="fas fa-user-clock"></i>
                            @php
                                $pendingUsers = \App\Models\User::where('is_agent', 1)
                                    ->whereIn('agreement_status', ['not_uploaded', 'uploaded'])
                                    ->count();
                            @endphp
                            @if ($pendingUsers > 0)
                                <span class="header-badge">{{ $pendingUsers }}</span>
                            @endif
                        </a>
                    @endif

                    {{-- User Menu --}}
                    <div class="dropdown-container">
                        <button class="user-menu-btn">
                            <span>{{ $user->name ?? explode(' ', $user->business_name ?? 'User')[0] }}</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-panel dropdown-right">
                            <div class="panel-header user-info-header">
                                <div class="user-details">
                                    @if ($user->business_logo)
                                        <img src="{{ Storage::url($user->business_logo) }}"
                                            alt="{{ $user->business_name }}" class="user-avatar-lg">
                                    @else
                                        <div class="user-avatar-lg-placeholder">
                                            {{ substr($user->name ?? ($user->business_name ?? 'U'), 0, 1) }}</div>
                                    @endif
                                    <div>
                                        <div class="user-fullname">{{ $user->name ?? ($user->business_name ?? 'User') }}
                                        </div>
                                        <div class="user-email">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-divider"></div>
                            <a href="{{ $profileRoute }}" class="panel-item">
                                <i class="fas fa-user-circle"></i> My Profile
                            </a>
                            <a href="#" class="panel-item text-danger"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                            <form id="logout-form" method="POST" action="{{ route('logout') }}"
                                style="display: none;">
                                @csrf</form>
                        </div>
                    </div>
                @else
                    {{-- Guest Buttons --}}
                    <a href="https://wa.me/+977905799575" class="btn btn-success rounded-pill" target="_blank"
                        rel="noopener noreferrer">
                        <i class="fa fa-phone "> </i>Chat in whataspp
                    </a>
                @endauth

                {{-- Mobile Menu Toggle --}}
                <button class="mobile-toggle" id="mobileToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>

        {{-- Navigation Menu --}}
        <div class="header-bottom" id="navMenu">
            <nav class="nav-menu">
                <ul>
                    @if ($user && $user->is_admin)
                    @elseif($user && $user->is_agent)
                        <li class="{{ request()->is('agent/dashboard') ? 'active' : '' }}"><a
                                href="{{ route('agent.dashboard') }}"><i class="fas fa-tachometer-alt"></i>
                                Dashboard</a></li>
                        <li class="{{ request()->is('agent/students*') ? 'active' : '' }}"><a
                                href="{{ route('agent.students.index') }}"><i class="fas fa-user-graduate"></i>
                                Students</a></li>
                        <li class="{{ request()->is('agent/universities*') ? 'active' : '' }}"><a
                                href="{{ route('agent.universities.index') }}"><i class="fas fa-university"></i>
                                Universities</a></li>
                        <li class="{{ request()->is('agent/applications*') ? 'active' : '' }}"><a
                                href="{{ route('agent.applications.index') }}"><i class="fas fa-file-alt"></i>
                                Applications</a></li>
                    @elseif($user && $user->is_staff)
                    @else
                        <li class="{{ request()->is('/') ? 'active' : '' }}"><a href="{{ url('/') }}"><i
                                    class="fas fa-home"></i> Home</a></li>
                        <li><a href="https://ideaconsultancyservices.com/" target="_blank"><i
                                    class="fas fa-globe"></i> Website</a></li>
                        @if (Route::has('guest.universities.index'))
                            <li class="{{ request()->is('guest/universities*') ? 'active' : '' }}"><a
                                    href="{{ route('guest.universities.index') }}"><i class="fas fa-university"></i>
                                    Universities</a></li>
                        @endif
                        @if (Route::has('auth.contact'))
                            <li class="{{ request()->is('auth/contact') ? 'active' : '' }}"><a
                                    href="{{ route('auth.contact') }}"><i class="fas fa-envelope"></i> Contact</a>
                            </li>
                        @endif
                        <li class="{{ request()->is('auth/register') ? 'active' : '' }}">
                            <a href="{{ route('auth.register') }}"> <i class="fas fa-user-plus"></i>
                                Register</a>
                        </li>
                        <li class="{{ request()->is('auth/login') ? 'active' : '' }}">
                            <a href="{{ route('auth.login') }}"> <i class="fas fa-user"></i>
                                Login</a>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    </div>
</header>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile menu toggle
        const mobileToggle = document.getElementById('mobileToggle');
        const navMenu = document.getElementById('navMenu');

        if (mobileToggle && navMenu) {
            mobileToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                navMenu.classList.toggle('open');
                const icon = this.querySelector('i');
                if (icon) {
                    if (navMenu.classList.contains('open')) {
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-times');
                    } else {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                }
            });
        }

        // Close mobile menu on link click
        document.querySelectorAll('.nav-menu li a').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768 && navMenu && navMenu.classList.contains('open')) {
                    navMenu.classList.remove('open');
                    if (mobileToggle) {
                        const icon = mobileToggle.querySelector('i');
                        if (icon) {
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                        }
                    }
                }
            });
        });
    });
</script>
