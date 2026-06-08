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
                                <a href="{{ Auth::user()->is_admin ? route('admin.notifications') : (Auth::user()->is_agent ? route('agent.notifications') : (Auth::user()->is_staff ? route('staff.notifications.index') : '#')) }}" class="notif-view-all">
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

    @include('components.file-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // ========== SIDEBAR TOGGLE ==========
        function toggleSidebar() {
            const sidebar = document.getElementById('appSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            } else {
                sidebar.classList.toggle('collapsed');
                const isCollapsed = sidebar.classList.contains('collapsed');
                sidebar.style.width = isCollapsed ? '60px' : '240px';
                document.getElementById('appMain').style.marginLeft = isCollapsed ? '60px' : '240px';
                try { localStorage.setItem('sidebarCollapsed', isCollapsed ? '1' : '0'); } catch(e) {}
            }
        }

        // Restore sidebar state
        try {
            if (localStorage.getItem('sidebarCollapsed') === '1' && window.innerWidth > 768) {
                const sidebar = document.getElementById('appSidebar');
                sidebar.classList.add('collapsed');
                sidebar.style.width = '60px';
                document.getElementById('appMain').style.marginLeft = '60px';
            }
        } catch(e) {}

        // ========== CONFIRM DELETE ==========
        $(document).on('click', '.btn-delete-confirm', function(e) {
            e.preventDefault();
            const btn = $(this);
            Swal.fire({
                title: 'Are you sure?',
                text: btn.data('message') || 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: btn.data('url'),
                        type: 'POST',
                        data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                        success: function() { location.reload(); },
                        error: function() { Swal.fire('Error!', 'Something went wrong.', 'error'); }
                    });
                }
            });
        });

        // ========== TABLE SEARCH ==========
        $(document).on('keyup', '[id$="-search"]', function() {
            const tableId = this.id.replace('-search', '');
            const searchText = this.value.toLowerCase();
            $(`#${tableId} tbody tr`).each(function() {
                let found = false;
                $(this).find('td').each(function() {
                    if ($(this).text().toLowerCase().includes(searchText)) found = true;
                });
                $(this).toggle(found);
            });
        });

        // ========== NOTIFICATION POLLING & DROPDOWN ==========
        function getNotifUrl() {
            const path = window.location.pathname;
            if (path.includes('/admin/')) return '/admin/notifications';
            if (path.includes('/agent/')) return '/agent/notifications';
            if (path.includes('/staff/')) return '/staff/notifications';
            if (path.includes('/crm/')) return '/crm/notifications/fetch';
            return null;
        }
        function renderNotifItem(n, icon, color) {
            const title = n.data?.message || n.data?.subject || n.data?.title || 'Notification';
            return '<a href="' + n.url + '" class="notif-item' + (n.read_at ? '' : ' unread') + '">' +
                '<div class="notif-icon" style="background:' + color + '20;color:' + color + ';">' +
                    '<i class="fas ' + icon + '"></i></div>' +
                '<div class="notif-content"><div class="notif-title">' + $('<span>').text(title).html() + '</div>' +
                '<div class="notif-time">' + (n.created_at || '') + '</div></div>' +
                (!n.read_at ? '<div class="notif-dot-indicator"></div>' : '') +
                '</a>';
        }
        function checkNotifications() {
            const userId = document.querySelector('meta[name="user-id"]')?.content;
            if (!userId) return;
            const url = getNotifUrl();
            if (!url) return;
            const dot = document.getElementById('notifDot');
            const body = document.getElementById('notifBody');
            const countEl = document.getElementById('notifCount');
            $.get(url + '?count=1', function(data) {
                if (typeof data !== 'object') return;
                const count = data.unread_count || 0;
                if (dot) dot.style.display = count > 0 ? '' : 'none';
                if (countEl) countEl.textContent = count + ' unread';
                if (body && data.recent) {
                    if (data.recent.length === 0) {
                        body.innerHTML = '<div class="notif-empty text-center py-3"><small class="text-muted">No notifications</small></div>';
                    } else {
                        let html = '';
                        data.recent.forEach(function(n) {
                            const type = (n.data && n.data.type) || '';
                            let icon = 'fa-bell', color = 'var(--primary)';
                            if (type.includes('message') || type.includes('chat')) { icon = 'fa-comment'; color = '#10b981'; }
                            else if (type.includes('document')) { icon = 'fa-file'; color = '#f59e0b'; }
                            else if (type.includes('application')) { icon = 'fa-file-alt'; color = '#3b82f6'; }
                            else if (type.includes('student')) { icon = 'fa-user-graduate'; color = '#8b5cf6'; }
                            else if (type.includes('agreement')) { icon = 'fa-file-signature'; color = '#ef4444'; }
                            html += renderNotifItem(n, icon, color);
                        });
                        body.innerHTML = html;
                    }
                }
            }).fail(function() { if (dot) dot.style.display = 'none'; });
        }
        setInterval(checkNotifications, 30000);
        // Initial load for dropdown content
        $(document).on('shown.bs.dropdown', '#notifDropdown', function() { checkNotifications(); });

        // ========== CHAT UNREAD BADGE POLLING + GLOBAL SOUND ==========
        let chatAudioCtx = null;
        let prevUnreadCount = -1;
        function ensureChatAudio() {
            if (chatAudioCtx) return chatAudioCtx;
            try {
                chatAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
            } catch(e) {}
            return chatAudioCtx;
        }
        ensureChatAudio();
        function resumeChatAudio() {
            if (!chatAudioCtx || chatAudioCtx.state === 'closed') { ensureChatAudio(); }
            if (chatAudioCtx && chatAudioCtx.state === 'suspended') { chatAudioCtx.resume(); }
        }
        document.addEventListener('click', resumeChatAudio);
        document.addEventListener('touchstart', resumeChatAudio);
        function playChatSound() {
            try {
                const ctx = chatAudioCtx;
                if (!ctx) return;
                const t = ctx.currentTime;
                const osc1 = ctx.createOscillator();
                const g1 = ctx.createGain();
                osc1.connect(g1);
                g1.connect(ctx.destination);
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(1200, t);
                g1.gain.setValueAtTime(0.28, t);
                g1.gain.exponentialRampToValueAtTime(0.001, t + 0.06);
                osc1.frequency.setValueAtTime(1800, t + 0.10);
                g1.gain.setValueAtTime(0.38, t + 0.10);
                g1.gain.setValueAtTime(0.32, t + 0.11);
                g1.gain.exponentialRampToValueAtTime(0.001, t + 0.30);
                osc1.start(t);
                osc1.stop(t + 0.31);
                const osc2 = ctx.createOscillator();
                const g2 = ctx.createGain();
                osc2.connect(g2);
                g2.connect(ctx.destination);
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(1800, t);
                g2.gain.setValueAtTime(0.14, t);
                g2.gain.exponentialRampToValueAtTime(0.001, t + 0.05);
                osc2.frequency.setValueAtTime(2800, t + 0.10);
                g2.gain.setValueAtTime(0.20, t + 0.10);
                g2.gain.exponentialRampToValueAtTime(0.001, t + 0.28);
                osc2.start(t);
                osc2.stop(t + 0.31);
            } catch(e) {}
        }
        function checkChatUnread() {
            const badge = document.getElementById('chatUnreadBadge');
            if (!badge) return;
            $.get('/chat/unread-count', function(data) {
                const count = data.count || 0;
                if (prevUnreadCount >= 0 && count > prevUnreadCount) {
                    playChatSound();
                    const diff = count - prevUnreadCount;
                    showToast(diff + ' new message' + (diff > 1 ? 's' : ''), 'info', 'New Chat Message');
                }
                prevUnreadCount = count;
                if (count > 0) {
                    badge.style.display = 'flex';
                    badge.textContent = count > 99 ? '99+' : count;
                } else {
                    badge.style.display = 'none';
                }
            }).fail(function() { badge.style.display = 'none'; });
        }
        checkChatUnread();
        setInterval(checkChatUnread, 15000);

        // ========== AUTO-CLOSE ALERTS ==========
        setTimeout(function() {
            document.querySelectorAll('.alert-dismissible').forEach(function(el) {
                el.style.transition = 'opacity 0.5s';
                el.style.opacity = '0';
                setTimeout(function() { el.remove(); }, 500);
            });
        }, 5000);

        // ========== TOAST NOTIFICATION SYSTEM ==========
        function showToast(message, type = 'success', title = '') {
            const icons = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-circle', info: 'fa-info-circle' };
            const colors = { success: '#10b981', error: '#ef4444', warning: '#f59e0b', info: '#3b82f6' };
            const toast = document.createElement('div');
            toast.style.cssText = `position:fixed;bottom:20px;right:20px;z-index:1100;background:#fff;border-radius:12px;padding:16px 20px;box-shadow:0 10px 40px rgba(0,0,0,0.15);display:flex;align-items:center;gap:12px;min-width:300px;max-width:420px;animation:slideUp 0.3s ease;border-left:4px solid ${colors[type]};`;
            toast.innerHTML = `<i class="fas ${icons[type]}" style="color:${colors[type]};font-size:1.25rem;"></i><div><strong style="font-size:0.85rem;">${title || type.charAt(0).toUpperCase() + type.slice(1)}</strong><p style="margin:2px 0 0;font-size:0.8rem;color:#64748b;">${message}</p></div>`;
            document.body.appendChild(toast);
            setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(() => toast.remove(), 300); }, 4000);
        }
    </script>
    @stack('scripts')
</body>
</html>
