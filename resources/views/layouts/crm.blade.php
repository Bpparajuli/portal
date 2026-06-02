<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CRM')</title>

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
            background-color: #f8fafc;
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

        /* CRM Wrapper */
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
            height: 60px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* Content wrapper */
        .crm-content-wrapper {
            margin-top: 60px;
            min-height: calc(100vh - 60px);
            padding: 20px;
        }

        /* Header Search */
        .header-search-wrapper {
            position: relative;
        }

        .header-search-wrapper i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 14px;
        }

        .header-search-wrapper input {
            padding-left: 36px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            font-size: 14px;
            width: 280px;
            transition: all 0.2s;
        }

        .header-search-wrapper input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
        }

        /* Notification Dropdown */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -8px;
            background: #ef4444;
            color: white;
            font-size: 10px;
            border-radius: 50%;
            padding: 2px 5px;
            min-width: 18px;
            text-align: center;
        }

        .notification-dropdown {
            width: 340px;
            max-height: 450px;
            overflow-y: auto;
        }

        .notification-item {
            transition: background 0.15s;
            white-space: normal !important;
            padding: 12px 16px !important;
            text-decoration: none;
            display: block;
            border-bottom: 1px solid #e5e7eb;
        }

        .notification-item:hover {
            background: #f9fafb;
        }

        .notification-item.unread {
            background: #eff6ff;
            border-left: 3px solid #4f46e5;
        }

        /* User Menu */
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
        }

        /* Task Statistics Styles */
        .task-stats-link {
            transition: all 0.2s ease;
        }

        .task-stats-link:hover {
            background: #e9ecef !important;
            transform: translateX(2px);
        }

        /* Stats Dropdown for Detailed View */
        .task-stats-dropdown {
            width: 320px;
            max-height: 400px;
            overflow-y: auto;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-search-wrapper input {
                width: 180px;
            }

            .crm-navbar .container-fluid {
                padding: 0 12px;
            }

            .crm-content-wrapper {
                padding: 12px;
            }
        }

        /* Dropdown Styles */
        .dropdown-menu {
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Toast notification */
        .toast-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        }
    </style>

    @stack('head')
    @stack('styles')
</head>

<body>
    {{-- ============================= --}}
    {{-- Floating CRM Alerts --}}
    {{-- ============================= --}}

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080; width: 400px; max-width: 95%;">

        {{-- Success --}}
        @if (session('success'))
            <div class="alert alert-success border-0 shadow rounded-4 alert-dismissible fade show mb-3" role="alert">
                <div class="d-flex align-items-start">
                    <div class="me-3 fs-4"><i class="fas fa-check-circle"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">Success</div>
                        <div class="small">{{ session('success') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        {{-- Error --}}
        @if (session('error'))
            <div class="alert alert-danger border-0 shadow rounded-4 alert-dismissible fade show mb-3" role="alert">
                <div class="d-flex align-items-start">
                    <div class="me-3 fs-4"><i class="fas fa-times-circle"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">Error</div>
                        <div class="small">{{ session('error') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        {{-- Warning --}}
        @if (session('warning'))
            <div class="alert alert-warning border-0 shadow rounded-4 alert-dismissible fade show mb-3" role="alert">
                <div class="d-flex align-items-start">
                    <div class="me-3 fs-4"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">Warning</div>
                        <div class="small">{{ session('warning') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        {{-- Info --}}
        @if (session('info'))
            <div class="alert alert-info border-0 shadow rounded-4 alert-dismissible fade show mb-3" role="alert">
                <div class="d-flex align-items-start">
                    <div class="me-3 fs-4"><i class="fas fa-info-circle"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">Info</div>
                        <div class="small">{{ session('info') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow rounded-4 alert-dismissible fade show mb-3" role="alert">
                <div class="d-flex align-items-start">
                    <div class="me-3 fs-4"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-bold mb-2">Please fix the following:</div>
                        <ul class="small ps-3 mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        {{-- Duplicate Student --}}
        @if (session('duplicate_student'))
            @php $duplicate = session('duplicate_student'); @endphp
            <div class="alert alert-warning border-0 shadow rounded-4 alert-dismissible fade show mb-3" role="alert">
                <div class="d-flex align-items-start">
                    <div class="me-3 fs-3"><i class="fas fa-user-clock"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-bold mb-2">Duplicate Student Found</div>
                        <div class="small">
                            <div><strong>Name:</strong> {{ $duplicate->full_name }}</div>
                            @if ($duplicate->phone_number)
                                <div><strong>Phone:</strong> {{ $duplicate->phone_number }}</div>
                            @endif
                            @if ($duplicate->email)
                                <div><strong>Email:</strong> {{ $duplicate->email }}</div>
                            @endif
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <a href="{{ route('crm.student.show', $duplicate) }}"
                                class="btn btn-sm btn-dark rounded-pill">View Profile</a>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill"
                                data-bs-dismiss="alert">Dismiss</button>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        {{-- Student Created --}}
        @if (session('student_created'))
            @php $newStudent = session('student_created'); @endphp
            <div class="alert alert-success border-0 shadow rounded-4 alert-dismissible fade show mb-3" role="alert">
                <div class="d-flex align-items-start">
                    <div class="me-3 fs-3"><i class="fas fa-user-check"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-bold mb-2">Student Added Successfully</div>
                        <div class="small">
                            <div><strong>Name:</strong> {{ $newStudent->full_name }}</div>
                            @if ($newStudent->phone_number)
                                <div><strong>Phone:</strong> {{ $newStudent->phone_number }}</div>
                            @endif
                            @if ($newStudent->email)
                                <div><strong>Email:</strong> {{ $newStudent->email }}</div>
                            @endif
                            <div><strong>Source:</strong> {{ $newStudent->source ?? 'manual' }}</div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('crm.student.show', $newStudent) }}"
                                class="btn btn-sm btn-success rounded-pill">Open Profile</a>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif
    </div>
    <div class="crm-wrapper">
        {{-- TOP NAVIGATION BAR --}}
        <nav class="crm-navbar">
            <div class="container-fluid h-100 px-3 px-md-4">
                <div class="row align-items-center h-100 w-100 g-0">
                    {{-- Left Section --}}
                    <div class="col-auto d-flex align-items-center gap-3">
                        <i class="fas fa-chalkboard-user text-primary fs-4"></i>
                        @if (auth()->user()->is_admin)
                            <a href="{{ route('crm.dashboard') }}" class="text-decoration-none">
                                <span class="fw-semibold text-dark fs-5 d-none d-sm-inline">CRM</span>
                            </a>
                        @else
                            <a href="{{ url('/crm?view=list') }}" class="text-decoration-none">
                                <span class="fw-semibold text-dark fs-5 d-sm-inline">CRM</span>
                            </a>
                        @endif

                        {{-- Desktop Search --}}
                        <div class="header-search-wrapper d-none d-lg-block">
                            <i class="fas fa-search"></i>
                            <input type="text" id="quickSearch" class="form-control"
                                placeholder="Search students...">
                        </div>
                    </div>

                    {{-- Right Section --}}
                    <div class="col-auto ms-auto d-flex align-items-center gap-3">
                        {{-- Mobile Search --}}
                        <div class="d-lg-none">
                            <button class="btn btn-link text-dark p-0" id="mobileSearchBtn" type="button">
                                <i class="fas fa-search fs-5"></i>
                            </button>
                        </div>

                        {{-- Admin Links --}}
                        @if (auth()->user()->is_admin)
                            <a href="{{ route('crm.configure.index') }}"
                                class="btn btn-sm btn-outline-secondary d-none d-md-inline-block">
                                <i class="fas fa-cog"></i> Configure
                            </a>
                            <a href="{{ route('crm.export') }}?{{ http_build_query(request()->query()) }}"
                                class="btn btn-sm btn-outline-secondary d-none d-md-inline-block">
                                <i class="fas fa-download"></i> Export
                            </a>
                            <a href="{{ route('admin.dashboard') }}"
                                class="btn btn-sm btn-primary d-none d-md-inline-block">
                                <i class="fas fa-upload"></i> Portal
                            </a>
                        @endif

                        {{-- Notification Bell --}}
                        <div class="dropdown">
                            <button class="btn btn-link text-dark p-0 position-relative" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="far fa-bell fs-5"></i>
                                <span class="notification-badge" id="crmNotificationCount"
                                    style="display: none;">0</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end p-0 notification-dropdown">
                                <div
                                    class="p-3 border-bottom fw-semibold bg-light d-flex justify-content-between align-items-center">
                                    <span><i class="far fa-bell me-2"></i>CRM Notifications</span>
                                    <a href="{{ route('crm.notifications.settings') }}"
                                        class="small text-muted text-decoration-none">
                                        <i class="fas fa-cog"></i>
                                    </a>
                                    <a href="{{ route('crm.notifications.all') }}"
                                        class="small text-muted text-decoration-none">
                                        <i class="fas fa-eye"></i> View All
                                    </a>
                                </div>
                                <div id="crmNotificationList" class="notification-list">
                                    <div class="text-center py-4 text-muted small">
                                        <i class="fas fa-spinner fa-spin me-2"></i>Loading...
                                    </div>
                                </div>
                                <div class="p-2 border-top text-center d-flex justify-content-between">
                                    <button class="btn btn-link btn-sm text-primary p-1 text-decoration-none"
                                        id="markAllCrmReadBtn">
                                        <i class="fas fa-check-double me-1"></i>Mark all as read
                                    </button>
                                    <button class="btn btn-link btn-sm text-danger p-1 text-decoration-none"
                                        id="clearReadCrmBtn">
                                        <i class="fas fa-trash-alt me-1"></i>Clear read
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Task Statistics Badges --}}
                        <div class="dropdown">
                            <button class="btn btn-link text-dark p-0 d-flex align-items-center gap-2" type="button"
                                data-bs-toggle="dropdown">
                                <i class="fas fa-tasks fs-5"></i>
                                <span class="badge bg-danger rounded-pill" id="taskBadge">0</span>
                            </button>

                            <div class="dropdown-menu dropdown-menu-end p-0 task-stats-dropdown">
                                <div class="p-3 border-bottom fw-semibold bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>
                                            <i class="fas fa-tasks me-2"></i>Task Overview
                                        </span>
                                        <a href="{{ route('crm.dashboard') }}?view=list&activity_filter={{ auth()->user()->is_admin ? 'all_task' : 'my_task' }}"
                                            class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>View All
                                        </a>
                                    </div>
                                </div>

                                <div class="p-2">
                                    {{-- Late Tasks --}}
                                    <a href="{{ route('crm.dashboard') }}?view=list&activity_filter={{ auth()->user()->is_admin ? 'overdue' : 'my_overdue' }}"
                                        class="task-stats-link d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded text-decoration-none">
                                        <span>
                                            <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                            <span class="fw-semibold">Late</span>
                                            <span class="small text-muted ms-1">(Overdue)</span>
                                        </span>
                                        <span class="badge bg-danger" id="dropdownLateCount">0</span>
                                    </a>

                                    {{-- Today's Tasks --}}
                                    <a href="{{ route('crm.dashboard') }}?view=list&activity_filter={{ auth()->user()->is_admin ? 'today' : 'my_today' }}"
                                        class="task-stats-link d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded text-decoration-none">
                                        <span>
                                            <i class="fas fa-calendar-day text-warning me-2"></i>
                                            <span class="fw-semibold">Today</span>
                                            <span class="small text-muted ms-1">(Due Today)</span>
                                        </span>
                                        <span class="badge bg-warning text-dark" id="dropdownTodayCount">0</span>
                                    </a>

                                    {{-- Future Tasks --}}
                                    <a href="{{ route('crm.dashboard') }}?view=list&activity_filter={{ auth()->user()->is_admin ? 'upcoming' : 'my_upcoming' }}"
                                        class="task-stats-link d-flex justify-content-between align-items-center p-2 bg-light rounded text-decoration-none">
                                        <span>
                                            <i class="fas fa-calendar-alt text-success me-2"></i>
                                            <span class="fw-semibold">Future</span>
                                            <span class="small text-muted ms-1">(Upcoming)</span>
                                        </span>
                                        <span class="badge bg-success" id="dropdownFutureCount">0</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- User Menu --}}
                        <div class="dropdown">
                            <button class="btn btn-link text-dark p-0 d-flex align-items-center gap-2" type="button"
                                data-bs-toggle="dropdown">
                                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                                <span class="d-none d-md-inline text-dark">{{ auth()->user()->name }}</span>
                                <i class="fas fa-chevron-down text-muted small"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>
                                        Profile</a>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('crm.notifications.settings') }}"><i
                                            class="fas fa-bell me-2"></i> Notification Settings</a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        {{-- Mobile Search Modal --}}
        <div class="modal fade" id="mobileSearchModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body p-3">
                        <div class="header-search-wrapper w-100">
                            <i class="fas fa-search"></i>
                            <input type="text" id="mobileQuickSearch" class="form-control"
                                placeholder="Search students...">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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

        // ==================== QUICK SEARCH ====================
        let searchTimeout;

        function setupHeaderSearch(inputElement) {
            if (!inputElement) return;
            inputElement.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                const search = e.target.value.trim();
                if (search.length < 2) return;
                searchTimeout = setTimeout(() => {
                    window.location.href = '/crm?search=' + encodeURIComponent(search) +
                        '&search_type=name';
                }, 500);
            });
        }

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
                const message = notif.data?.message || 'Task notification';
                // Use the redirect route instead of direct link
                const link = `/crm/notifications/${notif.id}/redirect`;

                let icon = '📌';
                const subtype = notif.data?.subtype;
                if (subtype === 'assigned') icon = '📋';
                else if (subtype === 'due_today') icon = '⚠️';
                else if (subtype === 'upcoming') icon = '🔔';
                else if (subtype === 'overdue') icon = '❌';

                return `
            <a href="${link}" class="dropdown-item notification-item ${isUnread ? 'unread' : ''}" style="white-space: normal;">
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1">
                        <p class="small mb-1 text-dark">${icon} ${escapeHtmlCustom(message)}</p>
                        <p class="small text-muted mb-0">${formatNotificationDate(notif.created_at)}</p>
                    </div>
                </div>
            </a>
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

        function showNotificationToast(count) {
            const toastContainer = document.getElementById('toastContainer');
            if (!toastContainer) return;

            const toastHtml = `
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                <div class="toast-header bg-primary text-white">
                    <i class="fas fa-bell me-2"></i>
                    <strong class="me-auto">New Notification${count > 1 ? 's' : ''}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    You have ${count} new task notification${count > 1 ? 's' : ''}
                </div>
            </div>
        `;

            toastContainer.innerHTML = toastHtml;
            setTimeout(() => {
                const toast = toastContainer.querySelector('.toast');
                if (toast) toast.remove();
            }, 5000);
        }

        async function markCrmNotificationRead(id) {
            try {
                const response = await fetch(`/crm/notifications/${id}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                if (response.ok) {
                    fetchNotifications();
                }
            } catch (error) {
                console.error('Error marking read:', error);
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
                if (response.ok) {
                    fetchNotifications();
                    showToastMessage('All notifications marked as read', 'success');
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
                if (response.ok) {
                    fetchNotifications();
                    showToastMessage('Read notifications cleared', 'success');
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
                // Set default values on error
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

            // Update main badge
            const taskBadge = document.getElementById('taskBadge');
            if (taskBadge) {
                const total = (stats.late || 0) + (stats.today || 0) + (stats.future || 0);
                taskBadge.textContent = total;
                taskBadge.className = `badge rounded-pill ${total > 0 ? 'bg-danger' : 'bg-secondary'}`;
            }

            // Update dropdown counts
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

            console.log('Task stats updated:', stats);
        }

        // ==================== AUTO-TRIGGER TASK CHECKS ====================
        let lastCheckTime = localStorage.getItem('lastTaskCheck') || 0;
        const CHECK_INTERVAL = 5 * 60 * 1000;

        async function autoCheckTasks() {
            const now = Date.now();
            if (now - lastCheckTime < CHECK_INTERVAL) return;

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
                    lastCheckTime = now;
                    localStorage.setItem('lastTaskCheck', lastCheckTime);
                    await fetchTaskStats();
                    await fetchNotifications();

                    const data = await response.json();
                    if (data.sent > 0) {
                        showToastMessage(`${data.sent} new task notification${data.sent > 1 ? 's' : ''} sent`, 'info');
                    }
                }
            } catch (error) {
                console.error('Auto-check error:', error);
            }
        }

        // ==================== INITIALIZE ====================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing...');

            // Search
            setupHeaderSearch(document.getElementById('quickSearch'));
            setupHeaderSearch(document.getElementById('mobileQuickSearch'));

            // Mobile search modal
            const mobileSearchBtn = document.getElementById('mobileSearchBtn');
            if (mobileSearchBtn) {
                mobileSearchBtn.addEventListener('click', function() {
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
            setInterval(autoCheckTasks, CHECK_INTERVAL);
        });
    </script>
    @stack('scripts')
</body>

</html>
