<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ Auth::id() }}">
    <meta name="user-name" content="{{ Auth::user()->name ?? '' }}">
    <meta name="user-avatar" content="{{ Auth::user()->business_logo ?? '' }}">
    <title>@yield('title', 'Idea Consultancy') - Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @stack('styles')
</head>
<body>
    <div class="app-wrapper">
        <!-- ========== SIDEBAR ========== -->
        <aside class="app-sidebar" id="appSidebar">
            <div class="sidebar-brand">
                <div class="brand-logo">IC</div>
                <div class="brand-text">
                    Idea Consultancy
                    <small>@auth {{ ucfirst(Auth::user()->role) }} Panel @endauth</small>
                </div>
            </div>
            <nav class="sidebar-nav">
                @auth
                    @if(Auth::user()->is_admin)
                        @include('partials.sidebar-admin')
                    @elseif(Auth::user()->is_agent)
                        @include('partials.sidebar-agent')
                    @elseif(Auth::user()->is_staff)
                        @include('partials.sidebar-staff')
                    @endif
                @endauth
            </nav>
            @auth
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="text-white small fw-semibold truncate">{{ Auth::user()->name }}</div>
                        <div style="font-size:0.7rem;color:rgba(255,255,255,0.4);">{{ Auth::user()->email }}</div>
                    </div>
                </div>
            </div>
            @endauth
        </aside>

        <!-- ========== OVERLAY ========== -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <!-- ========== MAIN ========== -->
        <main class="app-main" id="appMain">
            <header class="app-header">
                <div class="header-left">
                    <button class="toggle-sidebar" onclick="toggleSidebar()" title="Toggle sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="page-title">@yield('page-title', 'Dashboard')</span>
                </div>
                <div class="header-right">
                    @auth
                    <button class="btn-icon-header position-relative" onclick="window.location.href='{{ Auth::user()->is_admin ? route('admin.chat') : (Auth::user()->is_agent ? route('agent.chat') : (Auth::user()->is_staff ? route('staff.chat.index') : '#')) }}'" title="Chat">
                        <i class="fas fa-comment-dots"></i>
                        <span class="chat-unread-badge" id="chatUnreadBadge" style="display:none;position:absolute;top:-4px;right:-6px;background:#ef4444;color:#fff;font-size:0.6rem;font-weight:700;min-width:16px;height:16px;border-radius:8px;align-items:center;justify-content:center;padding:0 4px;box-shadow:0 2px 4px rgba(0,0,0,0.2);">0</span>
                    </button>
                    <div class="dropdown" id="notifDropdown">
                        <button class="btn-icon-header" id="notifToggle" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                            <i class="fas fa-bell"></i>
                            <span class="notif-dot" id="notifDot" style="display:none;"></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow-sm notif-dropdown" aria-labelledby="notifToggle">
                            <div class="notif-header">
                                <strong>Notifications</strong>
                                <small id="notifCount">0 unread</small>
                            </div>
                            <div class="notif-body" id="notifBody">
                                <div class="notif-loading text-center py-3"><small class="text-muted">Loading...</small></div>
                            </div>
                            <div class="notif-footer">
                                <a href="{{ Auth::user()->is_admin ? route('admin.notifications.index') : (Auth::user()->is_agent ? route('agent.notifications.index') : (Auth::user()->is_staff ? route('staff.notifications.index') : '#')) }}" class="notif-view-all">
                                    <i class="fas fa-list me-1"></i>View All Notifications
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn-icon-header dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            @if(Auth::user()->is_agent)
                                <li><a class="dropdown-item" href="{{ route('agent.users.show', Auth::user()->slug) }}"><i class="fas fa-user me-2"></i>My Profile</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ Auth::user()->is_admin ? route('admin.settings.index') : '#' }}"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    @endauth
                </div>
            </header>

            <div class="app-content">
                @include('partials.alerts')
                @yield('content')
            </div>
        </main>
    </div>

    @include('components.file-preview-modal')
    @include('shared.delete-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
