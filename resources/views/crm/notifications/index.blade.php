@extends('layouts.crm')

@section('title', 'All Notifications')

@push('styles')
    <style>
        .notifications-container { max-width: 900px; margin: 0 auto; padding: 1.5rem; }
        .notifications-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
        .notifications-header h1 { font-size: 1.5rem; font-weight: 600; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
        .filter-tabs { display: flex; gap: 0.5rem; background: white; padding: 0.25rem; border-radius: 12px; border: 1px solid #e5e7eb; flex-wrap: wrap; }
        .filter-tab { padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.875rem; font-weight: 500; cursor: pointer; transition: all 0.2s; border: none; background: none; }
        .filter-tab.active { background: #3b82f6; color: white; }
        .notification-list { background: white; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; }
        .notification-item { padding: 1rem; border-bottom: 1px solid #e5e7eb; transition: background 0.2s; text-decoration: none; display: block; }
        .notification-item:last-child { border-bottom: none; }
        .notification-item:hover { background: #f9fafb; }
        .notification-item.unread { background: #eff6ff; border-left: 3px solid #3b82f6; }
        .notification-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .notification-content { flex: 1; }
        .notification-title { font-weight: 600; color: #1f2937; margin-bottom: 0.25rem; }
        .notification-message { font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem; }
        .notification-time { font-size: 0.75rem; color: #9ca3af; }
        .pagination { margin-top: 1.5rem; display: flex; justify-content: center; }
        .cleanup-badge { font-size: .7rem; padding: 2px 8px; border-radius: 20px; background: #fef3c7; color: #92400e; display: inline-block; margin-left: 8px; }
    </style>
@endpush

@section('content')
    <div class="notifications-container">
        <div class="notifications-header">
            <h1>
                <i class="fas fa-bell"></i>
                Notifications
            </h1>
            <div class="d-flex gap-2 flex-wrap">
                <button id="cleanupBtn" class="btn btn-sm btn-outline-warning" title="Remove duplicate task notifications">
                    <i class="fas fa-compress"></i> Deduplicate
                </button>
                <button id="clearOldBtn" class="btn btn-sm btn-outline-secondary" title="Delete read notifications older than 7 days">
                    <i class="fas fa-broom"></i> Clean old read
                </button>
                <button id="markAllReadBtn" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-check-double"></i> Mark all read
                </button>
                <button id="clearReadBtn" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-trash"></i> Clear read
                </button>
            </div>
        </div>

        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">All</button>
            <button class="filter-tab" data-filter="crm_task">📋 Task</button>
            <button class="filter-tab" data-filter="student_added">👤 Student</button>
            <button class="filter-tab" data-filter="application">📝 Application</button>
            <button class="filter-tab" data-filter="user_registered">🆕 User</button>
        </div>

        <div id="cleanupInfo" class="mt-2" style="display:none"></div>

        <div class="notification-list mt-2" id="notificationsList">
            <div class="text-center py-5 text-muted">
                <i class="fas fa-spinner fa-spin"></i> Loading...
            </div>
        </div>

        <div class="pagination" id="pagination"></div>
    </div>

    <script>
        let currentPage = 1;
        let currentFilter = 'all';

        async function loadNotifications(page = 1, filter = 'all') {
            currentPage = page;
            currentFilter = filter;

            try {
                const response = await fetch(`/crm/notifications/all?page=${page}&filter=${filter}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                renderNotifications(data);
            } catch (error) {
                console.error('Error loading notifications:', error);
                document.getElementById('notificationsList').innerHTML =
                    '<div class="text-center py-5 text-danger">Failed to load notifications</div>';
            }
        }

        function renderNotifications(data) {
            const container = document.getElementById('notificationsList');
            const pagination = document.getElementById('pagination');

            if (!data.data || data.data.length === 0) {
                container.innerHTML = '<div class="text-center py-5 text-muted">No notifications found</div>';
                pagination.innerHTML = '';
                return;
            }

            container.innerHTML = data.data.map(notif => {
                const isUnread = !notif.read_at;
                const icon = getNotificationIcon(notif.type, notif.data);
                const title = getNotificationTitle(notif);
                const message = getNotificationMessage(notif);
                const time = formatDate(notif.created_at);

                return `
            <a href="/crm/notifications/${notif.id}/redirect" class="notification-item ${isUnread ? 'unread' : ''}">
                <div class="d-flex gap-3">
                    <div class="notification-icon" style="background: ${icon.bg}20; color: ${icon.color}">
                        <i class="${icon.icon}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${title}</div>
                        <div class="notification-message">${escapeHtml(message)}</div>
                        <div class="notification-time">
                            <i class="far fa-clock me-1"></i>${time}
                        </div>
                    </div>
                    ${isUnread ? '<span class="badge bg-primary ms-2">New</span>' : ''}
                </div>
            </a>
        `;
            }).join('');

            if (data.last_page > 1) {
                let h = '<nav><ul class="pagination">';
                if (data.prev_page_url) {
                    h += `<li class="page-item"><button class="page-link" onclick="loadNotifications(${data.current_page - 1}, '${currentFilter}')">Previous</button></li>`;
                } else {
                    h += `<li class="page-item disabled"><span class="page-link">Previous</span></li>`;
                }
                for (let i = 1; i <= data.last_page; i++) {
                    if (i === data.current_page) {
                        h += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                    } else {
                        h += `<li class="page-item"><button class="page-link" onclick="loadNotifications(${i}, '${currentFilter}')">${i}</button></li>`;
                    }
                }
                if (data.next_page_url) {
                    h += `<li class="page-item"><button class="page-link" onclick="loadNotifications(${data.current_page + 1}, '${currentFilter}')">Next</button></li>`;
                } else {
                    h += `<li class="page-item disabled"><span class="page-link">Next</span></li>`;
                }
                h += '</ul></nav>';
                pagination.innerHTML = h;
            } else {
                pagination.innerHTML = '';
            }
        }

        function getNotificationIcon(type, data) {
            const icons = {
                'crm_task': { icon: 'fas fa-tasks', bg: '#3b82f6', color: '#3b82f6' },
                'student_added': { icon: 'fas fa-user-plus', bg: '#820b5c', color: '#820b5c' },
                'student_deleted': { icon: 'fas fa-user-minus', bg: '#ef4444', color: '#ef4444' },
                'application_submitted': { icon: 'fas fa-file-alt', bg: '#f59e0b', color: '#f59e0b' },
                'application_status_updated': { icon: 'fas fa-exchange-alt', bg: '#8b5cf6', color: '#8b5cf6' },
                'application_message_added': { icon: 'fas fa-comment', bg: '#06b6d4', color: '#06b6d4' },
                'document_uploaded': { icon: 'fas fa-upload', bg: '#6366f1', color: '#6366f1' },
                'user_registered': { icon: 'fas fa-user-check', bg: '#14b8a6', color: '#14b8a6' },
            };
            if (type === 'App\\Notifications\\CrmTaskNotification') return icons.crm_task;
            if (data && data.type) return icons[data.type] || { icon: 'fas fa-bell', bg: '#6b7280', color: '#6b7280' };
            return { icon: 'fas fa-bell', bg: '#6b7280', color: '#6b7280' };
        }

        function getNotificationTitle(notif) {
            if (notif.type === 'App\\Notifications\\CrmTaskNotification') {
                const t = { 'assigned': 'New Task Assigned', 'due_today': 'Task Due Today', 'upcoming': 'Upcoming Task', 'overdue': 'Task Overdue' };
                return t[notif.data?.subtype] || 'Task Notification';
            }
            if (notif.data?.type === 'student_added') return 'New Student Added';
            if (notif.data?.type === 'student_deleted') return 'Student Deleted';
            if (notif.data?.type === 'application_submitted') return 'Application Submitted';
            if (notif.data?.type === 'application_status_updated') return 'Status Updated';
            if (notif.data?.type === 'application_message_added') return 'New Message';
            if (notif.data?.type === 'document_uploaded') return 'Document Uploaded';
            if (notif.data?.type === 'user_registered') return 'New User Registered';
            return 'Notification';
        }

        function getNotificationMessage(notif) { return notif.data?.message || 'You have a new notification'; }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMins = Math.floor((now - date) / 60000);
            const diffHours = Math.floor((now - date) / 3600000);
            const diffDays = Math.floor((now - date) / 86400000);
            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
            if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
            if (diffDays === 1) return 'Yesterday';
            if (diffDays < 7) return `${diffDays} days ago`;
            return date.toLocaleDateString();
        }

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function showCleanupInfo(msg, type) {
            const el = document.getElementById('cleanupInfo');
            el.style.display = 'block';
            el.className = `alert alert-${type} alert-dismissible fade show py-2 mb-0`;
            el.innerHTML = `${msg} <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" style="font-size:.7rem"></button>`;
            setTimeout(() => { el.style.display = 'none'; }, 5000);
        }

        // Filter tabs
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                loadNotifications(1, tab.dataset.filter);
            });
        });

        // Mark all read
        document.getElementById('markAllReadBtn')?.addEventListener('click', async () => {
            await fetch('/crm/notifications/mark-all-read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            loadNotifications(currentPage, currentFilter);
        });

        // Clear read
        document.getElementById('clearReadBtn')?.addEventListener('click', async () => {
            if (!confirm('Clear all read notifications?')) return;
            await fetch('/crm/notifications/read/all', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            loadNotifications(currentPage, currentFilter);
        });

        // Deduplicate
        document.getElementById('cleanupBtn')?.addEventListener('click', async () => {
            const btn = document.getElementById('cleanupBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cleaning...';
            try {
                const r = await fetch('/crm/notifications/duplicates', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                const data = await r.json();
                if (data.success) {
                    showCleanupInfo(data.message || `Removed ${data.removed} duplicates`, data.removed > 0 ? 'warning' : 'success');
                    loadNotifications(currentPage, currentFilter);
                }
            } catch (e) {
                showCleanupInfo('Failed to deduplicate', 'danger');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-compress"></i> Deduplicate';
            }
        });

        // Clean old read
        document.getElementById('clearOldBtn')?.addEventListener('click', async () => {
            if (!confirm('Delete read notifications older than 7 days?')) return;
            const btn = document.getElementById('clearOldBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cleaning...';
            try {
                const r = await fetch('/crm/notifications/old-read', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                const data = await r.json();
                if (data.success) {
                    showCleanupInfo(data.message || `Cleaned ${data.removed} old notifications`, 'info');
                    loadNotifications(currentPage, currentFilter);
                }
            } catch (e) {
                showCleanupInfo('Failed to clean old notifications', 'danger');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-broom"></i> Clean old read';
            }
        });

        // Auto-deduplicate on first load (quietly)
        (async function autoDedup() {
            try {
                await fetch('/crm/notifications/duplicates', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
            } catch (_) {}
        })();

        // Initial load
        loadNotifications();
    </script>
@endsection