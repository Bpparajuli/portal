@php $user = auth()->user(); @endphp
<header class="app-header">
    <button type="button" class="sidebar-toggle d-lg-none" id="sidebarToggleMobile" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>
    <button type="button" class="sidebar-toggle d-none d-lg-flex" id="sidebarToggle" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <nav class="header-nav d-none d-lg-flex">
        @auth
            @if($user->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                </a>
                <a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->is('admin/students*') ? 'active' : '' }}">
                    <i class="fas fa-user-graduate me-1"></i>Students
                </a>
                <a href="{{ route('admin.universities.index') }}" class="nav-link {{ request()->is('admin/universities*') ? 'active' : '' }}">
                    <i class="fas fa-university me-1"></i>Universities
                </a>
                <a href="{{ route('admin.applications.index') }}" class="nav-link {{ request()->is('admin/applications*') ? 'active' : '' }}">
                    <i class="fas fa-file-alt me-1"></i>Applications
                </a>
            @elseif($user->is_agent)
                <a href="{{ route('agent.dashboard') }}" class="nav-link {{ request()->is('agent/dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                </a>
                <a href="{{ route('agent.students.index') }}" class="nav-link {{ request()->is('agent/students*') ? 'active' : '' }}">
                    <i class="fas fa-user-graduate me-1"></i>Students
                </a>
                <a href="{{ route('agent.universities.index') }}" class="nav-link {{ request()->is('agent/universities*') ? 'active' : '' }}">
                    <i class="fas fa-university me-1"></i>Universities
                </a>
            @elseif($user->is_staff)
                <a href="{{ route('crm.dashboard') }}" class="nav-link {{ request()->is('crm*') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                </a>
            @endif
        @else
            <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
                <i class="fas fa-home me-1"></i>Home
            </a>
            <a href="https://ideaconsultancyservices.com/" class="nav-link" target="_blank">
                <i class="fas fa-globe me-1"></i>Website
            </a>
            <a href="{{ route('auth.register') }}" class="nav-link {{ request()->is('auth/register') ? 'active' : '' }}">
                <i class="fas fa-user-plus me-1"></i>Register
            </a>
            <a href="{{ route('auth.login') }}" class="nav-link {{ request()->is('auth/login') ? 'active' : '' }}">
                <i class="fas fa-sign-in-alt me-1"></i>Login
            </a>
        @endauth
    </nav>

    <div class="header-actions">
        @auth
            @if($user->is_admin || $user->is_agent)
                <div class="dropdown">
                    <button class="icon-btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        @php $notifCount = $user->unreadNotifications->where('data.type', '!=', 'application_message_added')->count(); @endphp
                        @if($notifCount > 0)
                            <span class="badge-count">{{ $notifCount }}</span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-sm" style="width: 320px;">
                        <div class="dropdown-header d-flex justify-content-between align-items-center">
                            <strong>Notifications</strong>
                            <form action="{{ $user->is_admin ? route('admin.notifications.markAll') : route('agent.notifications.markAll') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-link btn-sm text-decoration-none p-0">Mark all read</button>
                            </form>
                        </div>
                        <div class="dropdown-divider m-0"></div>
                        @php $notifications = $user->notifications()->where('data->type', '!=', 'application_message_added')->take(5)->get(); @endphp
                        @forelse($notifications as $notification)
                            <a href="{{ $user->is_admin ? route('admin.notifications.readAndRedirect', $notification->id) : route('agent.notifications.readAndRedirect', $notification->id) }}"
                               class="dropdown-item {{ is_null($notification->read_at) ? 'bg-light' : '' }} py-3">
                                <div class="small">{!! $user->formatNotification($notification) !!}</div>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            </a>
                        @empty
                            <div class="text-center py-4 text-muted small">
                                <i class="far fa-bell-slash fa-2x d-block mb-2"></i>
                                No notifications
                            </div>
                        @endforelse
                        <div class="dropdown-divider m-0"></div>
                        <a href="{{ $user->is_admin ? route('admin.notifications') : route('agent.notifications') }}"
                           class="dropdown-item text-center small py-2">
                            View all notifications
                        </a>
                    </div>
                </div>
            @endif

            @if($user->is_admin)
                <a href="{{ route('admin.users.waiting') }}" class="icon-btn" title="Pending Users">
                    <i class="fas fa-user-clock"></i>
                    @php $pendingUsers = \App\Models\User::agents()->whereIn('agreement_status', ['not_uploaded', 'uploaded'])->count(); @endphp
                    @if($pendingUsers > 0)
                        <span class="badge-count">{{ $pendingUsers }}</span>
                    @endif
                </a>
            @endif
        @endauth

        <button class="icon-btn" id="darkModeToggle" title="Toggle dark mode">
            <i class="fas fa-moon"></i>
        </button>

        @auth
            @php
                $profileRoute = $user->is_admin
                    ? route('admin.users.show', $user)
                    : ($user->is_agent
                        ? route('agent.users.show', $user)
                        : '#');
            @endphp
            <div class="dropdown">
                <button class="dropdown-user dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    @if($user->business_logo)
                        <img src="{{ Storage::url($user->business_logo) }}" alt="{{ $user->business_name }}">
                    @else
                        <div class="avatar-placeholder">
                            {{ substr($user->name ?? ($user->business_name ?? 'U'), 0, 1) }}
                        </div>
                    @endif
                    <span class="d-none d-md-inline small fw-medium">{{ $user->name ?? explode(' ', $user->business_name ?? 'User')[0] }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li>
                        <div class="dropdown-item-text d-flex align-items-center gap-3 py-2">
                            @if($user->business_logo)
                                <img src="{{ Storage::url($user->business_logo) }}" alt="" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;">
                            @else
                                <div style="width: 40px; height: 40px; border-radius: 8px; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                    {{ substr($user->name ?? ($user->business_name ?? 'U'), 0, 1) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <div class="fw-semibold small">{{ $user->name ?? ($user->business_name ?? 'User') }}</div>
                                <div class="text-muted small text-truncate">{{ $user->email }}</div>
                            </div>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ $profileRoute }}"><i class="fas fa-user-circle me-2"></i>My Profile</a></li>
                    @if($user->is_admin)
                        <li><a class="dropdown-item" href="{{ route('admin.application-status.index') }}"><i class="fas fa-layer-group me-2"></i>Application Status</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.qr-code') }}"><i class="fas fa-qrcode me-2"></i>ADD Student QR</a></li>
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="#"
                           onclick="event.preventDefault(); document.getElementById('logout-form-header').submit();">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                        <form id="logout-form-header" method="POST" action="{{ route('logout') }}" class="d-none">@csrf</form>
                    </li>
                </ul>
            </div>
        @else
            <a href="{{ route('auth.login') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-sign-in-alt me-1"></i>Login
            </a>
        @endauth
    </div>
</header>
