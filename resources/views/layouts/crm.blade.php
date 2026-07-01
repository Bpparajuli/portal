<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $_crmSiteLogo = \App\Models\Setting::getValue('site.logo', '');
        $_crmSiteName = \App\Models\Setting::getValue('site.name', 'CRM');
    @endphp
    <title>@yield('title', $_crmSiteName . ' — CRM')</title>

    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #f4f2f8;
        }

        a,
        a:hover,
        a:focus,
        a:active,
        .dropdown-item,
        .btn-link,
        .task-stats-link,
        .nav-link {
            text-decoration: none !important;
        }

        .crm-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Top Navigation Bar */
        .crm-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            z-index: 1030;
            height: 56px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .crm-navbar .container-fluid {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 0 16px;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .nav-center {
            flex: 1;
            display: flex;
            justify-content: center;
            min-width: 0;
        }

        /* Content wrapper */
        .crm-content-wrapper {
            margin-top: 56px;
            min-height: calc(100vh - 56px);
            padding: 16px 20px;
        }

        /* Header Search */
        .header-search-wrapper {
            position: relative;
            max-width: 320px;
            width: 100%;
        }

        .header-search-wrapper i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 13px;
            pointer-events: none;
        }

        .header-search-wrapper input {
            padding-left: 32px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            font-size: 13px;
            width: 100%;
            height: 34px;
            transition: all 0.2s;
            background: #f8fafc;
        }

        .header-search-wrapper input:focus {
            border-color: #820b5c;
            box-shadow: 0 0 0 2px rgba(130, 11, 92, 0.08);
            outline: none;
            background: #fff;
        }

        .search-btn {
            border-radius: 8px;
            height: 34px;
            padding: 0 14px;
            font-size: 13px;
            background: #1a0262;
            border-color: #1a0262;
            color: #fff;
            white-space: nowrap;
        }

        .search-btn:hover {
            background: #2a0272;
            border-color: #2a0272;
        }

        /* Notification Dropdown */
        .notification-badge {
            position: absolute;
            top: -4px;
            right: -6px;
            background: #ef4444;
            color: white;
            font-size: 9px;
            border-radius: 50%;
            padding: 1px 4px;
            min-width: 16px;
            text-align: center;
            line-height: 1.4;
        }

        .notification-dropdown {
            width: 340px;
            max-height: 450px;
            overflow-y: auto;
        }

        .notification-item {
            transition: background 0.15s;
            white-space: normal !important;
            padding: 10px 14px !important;
            text-decoration: none;
            display: block;
            border-bottom: 1px solid #e5e7eb;
        }

        .notification-item:hover {
            background: #f9fafb;
        }

        .notification-item.unread {
            background: #ede5f8;
            border-left: 3px solid #820b5c;
        }

        /* User Menu */
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1a0262, #820b5c);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
            flex-shrink: 0;
        }

        .nav-icon-btn {
            background: none;
            border: none;
            color: #4b5563;
            padding: 4px;
            cursor: pointer;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            border-radius: 8px;
            transition: background .15s;
        }

        .nav-icon-btn:hover {
            background: #f3f4f6;
        }

        .user-name {
            font-size: 13px;
            font-weight: 500;
            color: #1f2937;
        }

        .nav-portal-btn {
            height: 30px;
            padding: 0 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 5px;
            background: #820b5c;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background .15s;
        }

        .nav-portal-btn:hover {
            background: #9a0d6c;
        }

        /* Task Statistics Styles */
        .task-stats-link {
            transition: all 0.2s ease;
        }

        .task-stats-link:hover {
            background: #ede5f8 !important;
            transform: translateX(2px);
        }

        .task-stats-dropdown {
            width: 320px;
            max-height: 400px;
            overflow-y: auto;
        }

        /* Dropdown Styles */
        .dropdown-menu {
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .toast-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        }

        @media (max-width: 992px) {
            .nav-desktop-search {
                display: none;
            }

            .crm-navbar .container-fluid {
                padding: 0 12px;
                gap: 8px;
            }
        }

        @media (min-width: 993px) {
            .nav-mobile-search-btn {
                display: none !important;
            }
        }
    </style>

    @stack('head')
    @stack('styles')
</head>

<body>
    @include('partials.alerts')

    <div class="crm-wrapper">
        {{-- TOP NAVIGATION BAR --}}
        <nav class="crm-navbar">
            <div class="container-fluid">
                {{-- Left: Logo + Search --}}
                <div class="nav-left">
                    @if ($_crmSiteLogo)
                        <img src="{{ \App\Models\Setting::resolveImageUrl($_crmSiteLogo) }}" alt=""
                            style="height:28px;width:28px;object-fit:contain;border-radius:6px;">
                        <a href="{{ route('crm.dashboard') }}" class="fw-semibold"
                            style="font-size:15px;color:#1f2937;">{{ $_crmSiteName }}</a>
                    @else
                        <i class="fas fa-chalkboard-user" style="color:#1a0262;font-size:20px;"></i>
                        <a href="{{ route('crm.dashboard') }}" class="fw-semibold"
                            style="font-size:16px;color:#1f2937;">CRM</a>
                    @endif
                </div>

                {{-- Center: Desktop Search --}}
                <div class="nav-center nav-desktop-search">
                    <div class="d-flex gap-2" style="max-width:360px;width:100%;">
                        <div class="header-search-wrapper">
                            <input type="text" id="quickSearch" class="form-control"
                                placeholder="Search students...">
                        </div>
                        <button id="quickSearchBtn" class="search-btn" type="button"><i class="fas fa-search"></i>
                            Search</button>
                    </div>
                </div>

                {{-- Right: Actions --}}
                <div class="nav-right">
                    {{-- Mobile Search --}}
                    <button class="nav-icon-btn nav-mobile-search-btn" id="mobileSearchBtn" type="button">
                        <i class="fas fa-search"></i>
                    </button>

                    {{-- Admin Links --}}
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('crm.configure.index') }}" class="btn btn-sm"
                            style="color:#4b5563;border:1px solid #e5e7eb;border-radius:6px;font-size:12px;padding:3px 10px;">
                            <i class="fas fa-cog"></i> Configure
                        </a>
                        <a href="{{ route('admin.exports.index') }}" class="btn btn-sm"
                            style="color:#4b5563;border:1px solid #e5e7eb;border-radius:6px;font-size:12px;padding:3px 10px;">
                            <i class="fas fa-download"></i> Export
                        </a>
                    @endif

                    {{-- Portal Button --}}
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="nav-portal-btn">
                            <i class="fas fa-upload"></i> Portal
                        </a>
                    @elseif (auth()->user()->is_staff)
                        <a href="{{ route('staff.dashboard') }}" class="nav-portal-btn">
                            <i class="fas fa-upload"></i> Portal
                        </a>
                    @endif

                    {{-- Notification Bell --}}
                    <div class="dropdown">
                        <button class="nav-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="far fa-bell"></i>
                            <span class="notification-badge" id="crmNotificationCount" style="display:none;">0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-0 notification-dropdown">
                            <div class="p-2 border-bottom fw-semibold bg-light d-flex justify-content-between align-items-center"
                                style="font-size:13px;">
                                <span><i class="far fa-bell me-2"></i>Notifications</span>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('crm.notifications.settings') }}" class="small text-muted"><i
                                            class="fas fa-cog"></i></a>
                                    <a href="{{ route('crm.notifications.all') }}" class="small text-muted">View
                                        All</a>
                                </div>
                            </div>
                            <div id="crmNotificationList" class="notification-list">
                                <div class="text-center py-4 text-muted small"><i
                                        class="fas fa-spinner fa-spin me-2"></i>Loading...</div>
                            </div>
                            <div class="p-2 border-top d-flex justify-content-between">
                                <button class="btn btn-link btn-sm text-primary p-1" id="markAllCrmReadBtn"
                                    style="font-size:12px;">
                                    <i class="fas fa-check-double me-1"></i>Mark all read
                                </button>
                                <button class="btn btn-link btn-sm text-danger p-1" id="clearReadCrmBtn"
                                    style="font-size:12px;">
                                    <i class="fas fa-trash-alt me-1"></i>Clear read
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Task Stats --}}
                    <div class="dropdown">
                        <button class="nav-icon-btn" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-tasks"></i>
                            <span class="badge bg-danger rounded-pill" id="taskBadge"
                                style="position:absolute;top:-4px;right:-6px;font-size:9px;padding:1px 4px;min-width:16px;line-height:1.4;">0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-0 task-stats-dropdown">
                            <div class="p-2 border-bottom fw-semibold bg-light d-flex justify-content-between align-items-center"
                                style="font-size:13px;">
                                <span><i class="fas fa-tasks me-2"></i>Task Overview</span>
                                <a href="{{ route('crm.dashboard') }}?view=list&activity_filter={{ auth()->user()->is_admin ? 'all_task' : 'my_task' }}"
                                    class="btn btn-sm btn-primary" style="font-size:11px;padding:2px 8px;">View
                                    All</a>
                            </div>
                            <div class="p-2">
                                <a href="{{ route('crm.dashboard') }}?view=list&activity_filter={{ auth()->user()->is_admin ? 'overdue' : 'my_overdue' }}"
                                    class="task-stats-link d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded text-decoration-none"
                                    style="font-size:12px;">
                                    <span><i class="fas fa-exclamation-triangle text-danger me-2"></i><span
                                            class="fw-semibold">Late</span> <span
                                            class="small text-muted">(Overdue)</span></span>
                                    <span class="badge bg-danger" id="dropdownLateCount"
                                        style="font-size:10px;">0</span>
                                </a>
                                <a href="{{ route('crm.dashboard') }}?view=list&activity_filter={{ auth()->user()->is_admin ? 'today' : 'my_today' }}"
                                    class="task-stats-link d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded text-decoration-none"
                                    style="font-size:12px;">
                                    <span><i class="fas fa-calendar-day text-warning me-2"></i><span
                                            class="fw-semibold">Today</span> <span class="small text-muted">(Due
                                            Today)</span></span>
                                    <span class="badge bg-warning text-dark" id="dropdownTodayCount"
                                        style="font-size:10px;">0</span>
                                </a>
                                <a href="{{ route('crm.dashboard') }}?view=list&activity_filter={{ auth()->user()->is_admin ? 'upcoming' : 'my_upcoming' }}"
                                    class="task-stats-link d-flex justify-content-between align-items-center p-2 bg-light rounded text-decoration-none"
                                    style="font-size:12px;">
                                    <span><i class="fas fa-calendar-alt text-success me-2"></i><span
                                            class="fw-semibold">Future</span> <span
                                            class="small text-muted">(Upcoming)</span></span>
                                    <span class="badge bg-success" id="dropdownFutureCount"
                                        style="font-size:10px;">0</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- User Menu --}}
                    @php $_crmUser = auth()->user(); @endphp
                    <div class="dropdown">
                        <button class="btn btn-link text-dark p-0 d-flex align-items-center gap-2" type="button"
                            data-bs-toggle="dropdown" style="text-decoration:none;">
                            @if ($_crmUser->business_logo && Storage::disk('public')->exists($_crmUser->business_logo))
                                <img src="{{ Storage::url($_crmUser->business_logo) }}" alt=""
                                    style="width:28px;height:28px;border-radius:50%;object-fit:cover;border:2px solid #ede5f8;">
                            @else
                                <div class="user-avatar">{{ strtoupper(substr($_crmUser->name, 0, 1)) }}</div>
                            @endif
                            <span class="user-name d-none d-md-inline">{{ $_crmUser->name }}</span>
                            <i class="fas fa-chevron-down text-muted" style="font-size:10px;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" style="font-size:13px;">
                            @if (!$_crmUser->is_staff || $_crmUser->is_admin_staff)
                                <li><a class="dropdown-item" href="{{ route('profile.show', $_crmUser->slug) }}"><i
                                            class="fas fa-user me-2"></i> Profile</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('profile.edit', $_crmUser->slug) }}"><i
                                        class="fas fa-edit me-2"></i> Edit Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('crm.notifications.settings') }}"><i
                                        class="fas fa-bell me-2"></i> Notification Settings</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i
                                            class="fas fa-sign-out-alt me-2"></i> Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        {{-- Mobile Search Modal --}}
        <div class="modal fade" id="mobileSearchModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body p-3">
                        <div class="d-flex gap-2">
                            <div class="header-search-wrapper w-100">
                                <input type="text" id="mobileQuickSearch" class="form-control"
                                    placeholder="Search students...">
                            </div>
                            <button id="mobileQuickSearchBtn" class="btn btn-primary btn-sm search-btn"
                                type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Toast Container for Real-time Notifications --}}
        <div class="toast-notification" id="toastContainer"></div>

        {{-- MAIN CONTENT --}}
        <div class="crm-content-wrapper">
            @yield('content')
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    <script>
        // CSRF Token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Global view switching function
        window.setView = function(view) {
            const form = document.getElementById('crmFilterForm');
            if (form) {
                const viewInput = form.querySelector('[name="view"]');
                if (viewInput) viewInput.value = view;
                form.submit();
            } else {
                const params = new URLSearchParams(window.location.search);
                params.set('view', view);
                window.location.href = window.location.pathname + '?' + params.toString();
            }
        };

        // Auto hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                let bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            });
        }, 5000);

        // ==================== QUICK SEARCH - Navigates to CRM dashboard with search results ====================
        function setupHeaderSearch(inputElement, searchButtonElement) {
            if (!inputElement) return;

            function performSearch() {
                const search = inputElement.value.trim();
                if (search.length === 0) return;
                window.location.href = '{{ route('crm.dashboard') }}?view=list&search=' + encodeURIComponent(search) +
                    '&search_type=name';
            }

            // Search on Enter key
            inputElement.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performSearch();
                }
            });

            // Search on button click
            if (searchButtonElement) {
                searchButtonElement.addEventListener('click', function(e) {
                    e.preventDefault();
                    performSearch();
                });
            }
        }

        // ==================== CRM NOTIFICATIONS ====================
        // ==================== CRM NOTIFICATIONS ====================
        let lastNotificationCount = 0;

        function escapeHtmlCustom(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function formatNotificationDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
            if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
            if (diffDays === 1) return 'Yesterday';
            if (diffDays < 7) return `${diffDays} days ago`;
            return date.toLocaleDateString();
        }

        function updateNotificationUI(data) {
            const container = document.getElementById('crmNotificationList');
            const countBadge = document.getElementById('crmNotificationCount');
            if (!container) return;

            const unreadCount = data.unread_count || 0;
            if (countBadge) {
                if (unreadCount > 0) {
                    countBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                    countBadge.style.display = 'inline-block';
                } else {
                    countBadge.style.display = 'none';
                }
            }

            const notifications = data.notifications || [];
            if (notifications.length === 0) {
                container.innerHTML = '<div class="text-center py-4 text-muted small">No CRM notifications</div>';
                return;
            }

            container.innerHTML = notifications.map(notif => {
                const isUnread = !notif.read_at;
                // Fix: Safely get message from data
                const message = notif.data?.message || notif.data?.task_title || 'Task notification';
                // Fix: Use correct redirect URL
                const link = `/crm/notifications/${notif.id}/redirect`;

                let icon = '📌';
                const subtype = notif.data?.subtype;
                if (subtype === 'assigned') icon = '📋';
                else if (subtype === 'due_today') icon = '⚠️';
                else if (subtype === 'upcoming') icon = '🔔';
                else if (subtype === 'overdue') icon = '❌';

                return `
            <div class="notification-item ${isUnread ? 'unread' : ''}" style="white-space: normal; position: relative;">
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1">
                        <a href="${link}" class="text-decoration-none">
                            <p class="small mb-1 text-dark">${icon} ${escapeHtmlCustom(message)}</p>
                            <p class="small text-muted mb-0">${formatNotificationDate(notif.created_at)}</p>
                        </a>
                    </div>
                    <button class="btn btn-sm btn-link text-danger delete-notification-btn" 
                            data-id="${notif.id}" 
                            onclick="event.preventDefault(); deleteNotification('${notif.id}')"
                            style="font-size: 12px; padding: 0;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
            }).join('');
        }

        async function fetchNotifications() {
            try {
                const response = await fetch('/crm/notifications/fetch', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) throw new Error('Failed to fetch');
                const data = await response.json();

                if (data.unread_count > lastNotificationCount && lastNotificationCount > 0) {
                    showNotificationToast(data.unread_count - lastNotificationCount);
                }
                lastNotificationCount = data.unread_count;

                updateNotificationUI(data);
            } catch (error) {
                console.error('Notification error:', error);
                const container = document.getElementById('crmNotificationList');
                if (container) {
                    container.innerHTML =
                        '<div class="text-center py-4 text-muted small">Failed to load notifications</div>';
                }
            }
        }

        async function deleteNotification(notificationId) {
            if (!confirm('Delete this notification?')) return;

            try {
                const response = await fetch(`/crm/notifications/${notificationId}/delete-ajax`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showToastMessage('Notification deleted', 'success');
                    fetchNotifications(); // Refresh the list
                } else {
                    showToastMessage(data.error || 'Failed to delete notification', 'error');
                }
            } catch (error) {
                console.error('Error deleting notification:', error);
                showToastMessage('Failed to delete notification', 'error');
            }
        }

        async function markAllCrmRead() {
            try {
                const response = await fetch('/crm/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    fetchNotifications();
                    showToastMessage(data.message || 'All notifications marked as read', 'success');
                } else {
                    showToastMessage(data.error || 'Failed to mark notifications as read', 'error');
                }
            } catch (error) {
                console.error('Error marking all read:', error);
                showToastMessage('Failed to mark notifications as read', 'error');
            }
        }

        async function clearReadNotifications() {
            if (!confirm('Clear all read notifications?')) return;

            try {
                const response = await fetch('/crm/notifications/read/all', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    showToastMessage(data.message || 'Read notifications cleared', 'success');
                    fetchNotifications(); // Refresh the notification list
                } else {
                    showToastMessage(data.error || 'Failed to clear notifications', 'error');
                }
            } catch (error) {
                console.error('Error clearing read:', error);
                showToastMessage('Failed to clear notifications', 'error');
            }
        }

        function showToastMessage(message, type = 'info') {
            const toastContainer = document.getElementById('toastContainer');
            if (!toastContainer) return;

            const bgClass = type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-danger' : 'bg-info');
            const icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' :
                'fa-info-circle');

            const toastHtml = `
                <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000">
                    <div class="toast-header ${bgClass} text-white">
                        <i class="fas ${icon} me-2"></i>
                        <strong class="me-auto">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;

            toastContainer.innerHTML = toastHtml;
            setTimeout(() => {
                const toast = toastContainer.querySelector('.toast');
                if (toast) toast.remove();
            }, 3000);
        }

        // ==================== TASK STATISTICS ====================
        async function fetchTaskStats() {
            try {
                const response = await fetch('/crm/task-stats', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) throw new Error('Failed to fetch');
                const data = await response.json();
                updateTaskStatsUI(data);
                return data;
            } catch (error) {
                console.error('Task stats error:', error);
                updateTaskStatsUI({
                    success: false,
                    stats: {
                        late: 0,
                        today: 0,
                        future: 0
                    }
                });
            }
        }

        function updateTaskStatsUI(data) {
            if (!data.success) {
                console.warn('Task stats not successful:', data);
                return;
            }

            const {
                stats
            } = data;

            const taskBadge = document.getElementById('taskBadge');
            if (taskBadge) {
                const total = (stats.late || 0) + (stats.today || 0) + (stats.future || 0);
                taskBadge.textContent = total;
                taskBadge.className = `badge rounded-pill ${total > 0 ? 'bg-danger' : 'bg-secondary'}`;
            }

            const lateCountElem = document.getElementById('dropdownLateCount');
            const todayCountElem = document.getElementById('dropdownTodayCount');
            const futureCountElem = document.getElementById('dropdownFutureCount');

            if (lateCountElem) {
                lateCountElem.textContent = stats.late || 0;
                lateCountElem.className = `badge ${(stats.late || 0) > 0 ? 'bg-danger' : 'bg-secondary'}`;
            }
            if (todayCountElem) {
                todayCountElem.textContent = stats.today || 0;
                todayCountElem.className = `badge ${(stats.today || 0) > 0 ? 'bg-warning text-dark' : 'bg-secondary'}`;
            }
            if (futureCountElem) {
                futureCountElem.textContent = stats.future || 0;
                futureCountElem.className = `badge ${(stats.future || 0) > 0 ? 'bg-success' : 'bg-secondary'}`;
            }
        }

        // ==================== AUTO-TRIGGER TASK CHECKS ====================
        let lastCheckDate = localStorage.getItem('lastTaskCheckDate') || '';
        const todayStr = new Date().toISOString().slice(0, 10);

        async function autoCheckTasks() {
            if (lastCheckDate === todayStr) return;

            try {
                const response = await fetch('/crm/tasks/check-due', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    localStorage.setItem('lastTaskCheckDate', todayStr);
                    await fetchTaskStats();
                    await fetchNotifications();
                }
            } catch (error) {
                console.error('Auto-check error:', error);
            }
        }

        // ==================== INITIALIZE ====================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing...');

            // Setup Desktop Search (with button and Enter key)
            const desktopSearchInput = document.getElementById('quickSearch');
            const desktopSearchBtn = document.getElementById('quickSearchBtn');
            if (desktopSearchInput) {
                setupHeaderSearch(desktopSearchInput, desktopSearchBtn);
            }

            // Setup Mobile Search (with button and Enter key)
            const mobileSearchInput = document.getElementById('mobileQuickSearch');
            const mobileSearchBtn = document.getElementById('mobileQuickSearchBtn');
            if (mobileSearchInput) {
                setupHeaderSearch(mobileSearchInput, mobileSearchBtn);
            }

            // Mobile search modal trigger
            const mobileSearchBtnTrigger = document.getElementById('mobileSearchBtn');
            if (mobileSearchBtnTrigger) {
                mobileSearchBtnTrigger.addEventListener('click', function() {
                    const modal = new bootstrap.Modal(document.getElementById('mobileSearchModal'));
                    modal.show();
                    setTimeout(() => document.getElementById('mobileQuickSearch')?.focus(), 500);
                });
            }

            // Notifications
            fetchNotifications();
            setInterval(fetchNotifications, 30000);

            // Notification buttons
            const markAllBtn = document.getElementById('markAllCrmReadBtn');
            if (markAllBtn) markAllBtn.addEventListener('click', markAllCrmRead);

            const clearReadBtn = document.getElementById('clearReadCrmBtn');
            if (clearReadBtn) clearReadBtn.addEventListener('click', clearReadNotifications);

            // Task stats
            fetchTaskStats();
            setInterval(fetchTaskStats, 60000);

            // Auto-check tasks
            autoCheckTasks();
            setInterval(autoCheckTasks, 3600000);
        });
    </script>
    @include('components.file-preview-modal')
    @stack('scripts')
</body>

</html>
