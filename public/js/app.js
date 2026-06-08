(function() {
    'use strict';

    // ========== SIDEBAR TOGGLE ==========
    window.toggleSidebar = function() {
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
    };

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
        const csrfToken = btn.data('csrf') || document.querySelector('meta[name="csrf-token"]')?.content || '';
        Swal.fire({
            title: btn.data('title') || 'Are you sure?',
            text: btn.data('message') || 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: btn.data('confirm-text') || 'Yes, delete it!',
            cancelButtonText: btn.data('cancel-text') || 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const formId = btn.data('form-id');
                if (formId) {
                    $('#' + formId).submit();
                    return;
                }
                const method = btn.data('method') || 'DELETE';
                const reload = btn.data('reload') !== '0';
                $.ajax({
                    url: btn.data('url'),
                    type: 'POST',
                    data: { _method: method, _token: csrfToken },
                    success: function() { if (reload) location.reload(); },
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
    $(document).on('shown.bs.dropdown', '#notifDropdown', function() { checkNotifications(); });

    // ========== CHAT UNREAD BADGE POLLING + GLOBAL SOUND ==========
    let chatAudioCtx = null;
    let prevUnreadCount = -1;
    window.ensureChatAudio = function() {
        if (chatAudioCtx) return chatAudioCtx;
        try {
            chatAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
        } catch(e) {}
        return chatAudioCtx;
    };
    ensureChatAudio();
    window.resumeChatAudio = function() {
        if (!chatAudioCtx || chatAudioCtx.state === 'closed') { ensureChatAudio(); }
        if (chatAudioCtx && chatAudioCtx.state === 'suspended') { chatAudioCtx.resume(); }
    };
    document.addEventListener('click', resumeChatAudio);
    document.addEventListener('touchstart', resumeChatAudio);
    window.playChatSound = function() {
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
    };
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
    window.showToast = function(message, type = 'success', title = '') {
        const icons = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-circle', info: 'fa-info-circle' };
        const colors = { success: '#10b981', error: '#ef4444', warning: '#f59e0b', info: '#3b82f6' };
        const toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:1100;background:#fff;border-radius:12px;padding:16px 20px;box-shadow:0 10px 40px rgba(0,0,0,0.15);display:flex;align-items:center;gap:12px;min-width:300px;max-width:420px;animation:slideUp 0.3s ease;border-left:4px solid ' + colors[type] + ';';
        toast.innerHTML = '<i class="fas ' + icons[type] + '" style="color:' + colors[type] + ';font-size:1.25rem;"></i><div><strong style="font-size:0.85rem;">' + (title || type.charAt(0).toUpperCase() + type.slice(1)) + '</strong><p style="margin:2px 0 0;font-size:0.8rem;color:#64748b;">' + message + '</p></div>';
        document.body.appendChild(toast);
        setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(() => toast.remove(), 300); }, 4000);
    };

})();
