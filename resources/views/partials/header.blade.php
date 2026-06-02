@php
    $user = auth()->user();
@endphp
<style>
    /* Modern Professional Header */
    .app-header {
        background: var(--active);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        border-radius: 5px 5px 0px 0px;
        z-index: 1000;
        position: relative;
    }

    .app-header-inner {
        max-width: 1400px;
        margin: 0 auto;
        padding: 12px 16px;
    }

    /* Top Bar */
    .header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    /* Logo Section */
    .logo-section {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .logo-link {
        display: block;
        transition: transform 0.2s;
        line-height: 0;
    }

    .logo-link:hover {
        transform: scale(1.03);
    }

    .logo-img {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        object-fit: contain;
    }

    .logo-placeholder {
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: bold;
        color: white;
    }

    /* Notice Section */
    .notice-section {
        flex: 1;
        min-width: 0;
        display: none;
    }

    @media (min-width: 768px) {
        .notice-section {
            display: block;
        }
    }

    .notice-content {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 8px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
    }

    .notice-content i {
        color: #ff6200;
        font-size: 16px;
        flex-shrink: 0;
    }

    .notice-text {
        flex: 1;
        overflow: hidden;
        position: relative;
    }

    .notice-text span {
        display: inline-block;
        color: var(--light);
        font-size: 14px;
        font-weight: 700;
        white-space: nowrap;
        animation: marquee 25s linear infinite;
    }

    @keyframes marquee {
        0% {
            transform: translateX(100%);
        }

        100% {
            transform: translateX(-100%);
        }
    }

    /* Actions Section */
    .actions-section {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .action-btn {
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: white;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        cursor: pointer;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.2s;
        font-size: 16px;
    }

    .action-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }

    .header-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #e74c3c;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 10px;
        font-weight: bold;
        min-width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* User Menu Button */
    .user-menu-btn {
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: white;
        padding: 5px 10px;
        border-radius: 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        height: 38px;
    }

    .user-menu-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }

    .user-name {
        font-size: 13px;
        font-weight: 500;
        display: none;
    }

    @media (min-width: 480px) {
        .user-name {
            display: inline-block;
        }
    }

    .user-menu-btn i {
        font-size: 12px;
    }

    /* Dropdown Panels */
    .dropdown-container {
        position: relative;
        display: inline-block;
    }

    .dropdown-panel {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        width: 320px;
        max-width: 90vw;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.25s ease;
        z-index: 1001;
    }

    /* Desktop hover effect */
    @media (min-width: 769px) {
        .dropdown-container:hover .dropdown-panel {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
    }

    /* Mobile dropdown - positioned near the button */
    @media (max-width: 768px) {
        .dropdown-panel {
            position: fixed;
            top: auto;
            bottom: auto;
            left: 50%;
            transform: translateX(-50%) translateY(10px);
            right: auto;
            width: calc(100% - 32px);
            max-width: 400px;
            margin: 0 auto;
            border-radius: 16px;
            z-index: 1002;
        }

        .dropdown-container.active .dropdown-panel {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
        }

        /* Position the dropdown below the trigger button */
        .dropdown-container {
            position: relative;
        }
    }

    .dropdown-panel.dropdown-right {
        right: 0;
        left: auto;
    }

    .panel-header {
        padding: 15px 20px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 14px;
        font-weight: 600;
    }

    .mark-all {
        background: none;
        border: none;
        color: #3498db;
        font-size: 12px;
        cursor: pointer;
        transition: opacity 0.2s;
    }

    .mark-all:hover {
        opacity: 0.8;
    }

    .panel-body {
        max-height: 380px;
        overflow-y: auto;
    }

    .panel-item {
        display: flex !important;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 20px;
        text-decoration: none;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s;
        cursor: pointer;
        color: #2c3e50;
    }

    .panel-item:hover {
        background: #f8f9fa;
    }

    .panel-item.unread {
        background: #e8f4fd;
        border-left: 3px solid #3498db;
    }

    .panel-item i {
        margin-top: 2px;
        font-size: 14px;
        color: #7f8c8d;
    }

    .panel-item .item-content {
        flex: 1;
    }

    .item-text {
        color: #2c3e50;
        font-size: 13px;
        margin-bottom: 5px;
        line-height: 1.4;
    }

    .item-time {
        color: #95a5a6;
        font-size: 11px;
    }

    .empty-panel {
        padding: 50px 20px;
        text-align: center;
        color: #95a5a6;
    }

    .empty-panel i {
        font-size: 45px;
        margin-bottom: 10px;
        opacity: 0.5;
    }

    .empty-panel p {
        margin: 0;
        font-size: 13px;
    }

    .panel-footer {
        padding: 12px 20px;
        border-top: 1px solid #e9ecef;
        text-align: center;
    }

    .panel-footer a {
        color: #3498db;
        text-decoration: none;
        font-size: 13px;
        transition: opacity 0.2s;
        display: inline-block;
    }

    .panel-footer a:hover {
        opacity: 0.8;
    }

    .panel-divider {
        height: 1px;
        background: #e9ecef;
        margin: 5px 0;
    }

    .user-info-header {
        padding: 20px;
    }

    .user-details {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .user-avatar-lg {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        object-fit: cover;
    }

    .user-avatar-lg-placeholder {
        width: 50px;
        height: 50px;
        background: var(--primary-gradient);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: bold;
        color: white;
    }

    .user-fullname {
        font-weight: 600;
        color: #2c3e50;
        font-size: 14px;
        margin-bottom: 3px;
    }

    .user-email {
        font-size: 12px;
        color: #7f8c8d;
        word-break: break-all;
    }

    /* Guest Buttons */
    .btn-guest {
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-login {
        border: 2px solid white;
        color: white;
        background: transparent;
    }

    .btn-login:hover {
        background: white;
        color: #1a1a2e;
        transform: translateY(-2px);
    }

    /* Bottom Navigation */
    .header-bottom {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        padding: 16px;
        max-height: calc(100vh - 80px);
        overflow-y: auto;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        z-index: 1000;
    }

    .header-bottom.open {
        display: block;
    }

    .nav-menu ul {
        display: flex;
        flex-direction: column;
        gap: 8px;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .nav-menu li a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        color: var(--secondary);
        text-decoration: none;
        font-size: 15px;
        font-weight: 600;
        transition: all 0.2s;
        border-radius: 8px;
    }

    .nav-menu li a:hover,
    .nav-menu li.active a {
        background: var(--active);
        color: white;
    }

    /* Desktop Navigation */
    @media (min-width: 992px) {
        .mobile-toggle {
            display: none !important;
        }

        .header-bottom {
            display: block !important;
            position: static;
            background: transparent;
            padding: 0;
            box-shadow: none;
            margin-top: 12px;
        }

        .nav-menu ul {
            flex-direction: row;
            justify-content: space-between;
            background-color: #ffffff;
            border-radius: 8px;
            padding: 0 4px;
        }

        .nav-menu li a {
            padding: 10px 20px;
            font-size: 14px;
        }

        .nav-menu li a i {
            font-size: 14px;
        }
    }

    /* Mobile Toggle Button */
    .mobile-toggle {
        display: flex;
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: white;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 18px;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .mobile-toggle:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    /* Scrollbar Styling */
    .panel-body::-webkit-scrollbar {
        width: 6px;
    }

    .panel-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .panel-body::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 10px;
    }

    /* Responsive Adjustments */
    @media (max-width: 480px) {
        .app-header-inner {
            padding: 10px 12px;
        }

        .logo-img,
        .logo-placeholder {
            width: 40px;
            height: 40px;
        }

        .action-btn {
            width: 34px;
            height: 34px;
            font-size: 14px;
        }

        .user-menu-btn {
            padding: 4px 8px;
            height: 34px;
        }

        .btn-guest {
            padding: 6px 12px;
            font-size: 12px;
        }

        .header-badge {
            font-size: 9px;
            min-width: 16px;
            height: 16px;
            padding: 1px 4px;
            top: -4px;
            right: -4px;
        }

        .dropdown-panel {
            width: calc(100% - 24px);
            max-width: 380px;
        }
    }
</style>
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
                        <img src="{{ asset('images/logo.png') }}" alt="Idea Consultancy" class="logo-img">
                    </a>
                    <span class="fw-bold text-white">Idea Consultancy</span>
                @endauth
            </div>

            {{-- Notice Bar - Hidden for Admin Users --}}
            @auth
                @if (!$user->is_admin && ($user->is_agent || !$user->is_staff))
                    <div class="notice-section">
                        <div class="notice-content">
                            <i class="fas fa-bullhorn"></i>
                            <div class="notice-text">
                                <span>
                                    Due to issues with NOC of Dubai colleges in Nepal, we've partnered with 5+ universities
                                    — promote these for good commissions, and top performers can still earn FAM trips!
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <div class="notice-section">
                    <div class="notice-content">
                        <i class="fas fa-bullhorn"></i>
                        <div class="notice-text">
                            <span>
                                Applications are now open for Germany September/October 2026 intake. As this is the main
                                intake, agents are encouraged to advise students to apply early to avoid last-minute hassles
                                and ensure timely processing of admissions, documents, and visas.
                            </span>
                        </div>
                    </div>
                </div>
            @endauth
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
                                        href="{{ route('admin.users.index') }}"><i class="fas fa-users"></i> Users</a></li>
                                <li class="{{ request()->is('admin/applications*') ? 'active' : '' }}"><a
                                        href="{{ route('admin.applications.index') }}"><i class="fas fa-file-alt"></i>
                                        Applications</a></li>
                                <li class="{{ request()->is('crm') ? 'active' : '' }}">
                                    <a href="{{ route('crm.dashboard') }}">
                                        <i class="fas fa-chart-line"></i> CRM
                                    </a>

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
                            <i class="fas fa-comments"></i>
                            @if ($unreadChatCount > 0)
                                <span class="header-badge">{{ $unreadChatCount }}</span>
                            @endif
                        </a>
                    @endif

                    {{-- Notifications --}}
                    @if ($user->is_admin || $user->is_agent)
                        <div class="dropdown-container" data-dropdown="notifications">
                            <button class="action-btn dropdown-trigger">
                                <i class="fas fa-bell"></i>
                                @php $notifCount = $user->unreadNotifications->where('data.type', '!=', 'application_message_added')->count(); @endphp
                                @if ($notifCount > 0)
                                    <span class="header-badge">{{ $notifCount }}</span>
                                @endif
                            </button>
                            <div class="dropdown-panel">
                                <div class="panel-header">
                                    <strong>Notifications</strong>
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
                                            <div class="item-time">{{ $notification->created_at->diffForHumans() }}
                                            </div>
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
                    @if ($user->is_admin || $user->is_agent)
                        <div class="dropdown-container" data-dropdown="messages">
                            <button class="action-btn dropdown-trigger">
                                <i class="fas fa-envelope"></i>
                                @php $msgCount = $user->unreadNotifications->where('data.type', 'application_message_added')->count(); @endphp
                                @if ($msgCount > 0)
                                    <span class="header-badge">{{ $msgCount }}</span>
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
                                            <div class="item-time">{{ $notification->created_at->diffForHumans() }}
                                            </div>
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
                    @endif

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
                    <div class="dropdown-container" data-dropdown="user">
                        <button class="user-menu-btn dropdown-trigger">
                            <span
                                class="user-name">{{ $user->name ?? explode(' ', $user->business_name ?? 'User')[0] }}</span>
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
                                            {{ substr($user->name ?? ($user->business_name ?? 'U'), 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="user-fullname">
                                            {{ $user->name ?? ($user->business_name ?? 'User') }}
                                        </div>
                                        <div class="user-email">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-divider"></div>
                            <a href="{{ $profileRoute }}" class="panel-item">
                                <i class="fas fa-user-circle"></i> My Profile
                            </a>
                            @if ($user->is_admin)
                                <a href="{{ route('admin.application-status.index') }}" class="panel-item">
                                    <i class="fas fa-layer-group"></i> Application Status
                                </a>
                                <a href="{{ route('admin.qr-code') }}" class="panel-item">
                                    <i class="fas fa-qrcode"></i> ADD Student QR
                                </a>
                            @endif
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
                    <a href="https://wa.me/+977905799575" class="btn-guest btn-login" target="_blank">
                        <i class="fab fa-whatsapp"></i> WhatsApp
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
                @auth
                    @if ($user->is_admin)
                    @elseif($user->is_agent)
                        <ul>
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
                        </ul>
                    @elseif($user->is_staff)
                        <ul>
                            <li class="{{ request()->is('crm/dashboard') ? 'active' : '' }}"><a
                                    href="{{ route('crm.dashboard') }}"><i class="fas fa-tachometer-alt"></i>
                                    Dashboard</a></li>
                        </ul>
                    @endif
                @else
                    <ul>
                        <li class="{{ request()->is('/') ? 'active' : '' }}"><a href="{{ url('/') }}"><i
                                    class="fas fa-home"></i> Home</a></li>
                        <li><a href="https://ideaconsultancyservices.com/" target="_blank"><i class="fas fa-globe"></i>
                                Website</a></li>
                        @if (Route::has('guest.universities.index'))
                            <li class="{{ request()->is('guest/universities*') ? 'active' : '' }}"><a
                                    href="{{ route('guest.universities.index') }}"><i class="fas fa-university"></i>
                                    Universities</a></li>
                        @endif
                        @if (Route::has('auth.contact'))
                            <li class="{{ request()->is('auth/contact') ? 'active' : '' }}"><a
                                    href="{{ route('auth.contact') }}"><i class="fas fa-envelope"></i> Contact</a></li>
                        @endif
                        <li class="{{ request()->is('auth/register') ? 'active' : '' }}"><a
                                href="{{ route('auth.register') }}"><i class="fas fa-user-plus"></i> Register</a></li>
                        <li class="{{ request()->is('auth/login') ? 'active' : '' }}"><a
                                href="{{ route('auth.login') }}"><i class="fas fa-user"></i> Login</a></li>
                    </ul>
                @endauth
            </nav>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile menu toggle (unchanged)
        const mobileToggle = document.getElementById('mobileToggle');
        const navMenu = document.getElementById('navMenu');

        if (mobileToggle && navMenu) {
            mobileToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                navMenu.classList.toggle('open');
                const icon = this.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-bars', !navMenu.classList.contains('open'));
                    icon.classList.toggle('fa-times', navMenu.classList.contains('open'));
                }
            });
        }

        // Close mobile menu on link click
        document.querySelectorAll('.nav-menu li a').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 991 && navMenu && navMenu.classList.contains('open')) {
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

        // ---------- DROPDOWN HANDLING (no backdrop) ----------
        const dropdownTriggers = document.querySelectorAll('.dropdown-trigger');
        let activeDropdown = null;

        function closeAllDropdowns() {
            if (activeDropdown) {
                activeDropdown.classList.remove('active');
                activeDropdown = null;
            }
        }

        function isMobile() {
            return window.innerWidth <= 768;
        }

        // Handle click on trigger
        dropdownTriggers.forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const container = this.closest('.dropdown-container');

                // If already open -> close it
                if (activeDropdown === container) {
                    closeAllDropdowns();
                    return;
                }

                // Close any other open dropdown
                closeAllDropdowns();

                // Open the new one
                container.classList.add('active');
                activeDropdown = container;

                // On mobile, position panel just below the trigger
                if (isMobile()) {
                    const rect = this.getBoundingClientRect();
                    const panel = container.querySelector('.dropdown-panel');
                    if (panel) {
                        panel.style.top = (rect.bottom + 5) + 'px';
                        panel.style.left = '50%';
                        panel.style.transform = 'translateX(-50%)';
                    }
                }
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!activeDropdown) return;

            // If click is inside the active dropdown container, do nothing
            if (activeDropdown.contains(e.target)) return;

            // Otherwise close it
            closeAllDropdowns();
        });

        // Prevent clicks inside dropdown panel from bubbling to document (not strictly needed
        // because of the above check, but safe)
        document.querySelectorAll('.dropdown-panel').forEach(panel => {
            panel.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });

        // Handle window resize – close dropdowns and reset mobile menu
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                closeAllDropdowns();
                if (window.innerWidth > 768 && navMenu && navMenu.classList.contains('open')) {
                    navMenu.classList.remove('open');
                    if (mobileToggle) {
                        const icon = mobileToggle.querySelector('i');
                        if (icon) {
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                        }
                    }
                }
            }, 250);
        });
    });
</script>
