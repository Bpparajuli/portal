@extends('layouts.crm')

@section('title', 'All Notifications')

@push('styles')
    <style>
        .notifications-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .notifications-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            background: white;
            padding: 0.25rem;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }

        .filter-tab {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            background: none;
        }

        .filter-tab.active {
            background: #3b82f6;
            color: white;
        }

        .notification-list {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .notification-item {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            transition: background 0.2s;
            text-decoration: none;
            display: block;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item:hover {
            background: #f9fafb;
        }

        .notification-item.unread {
            background: #eff6ff;
            border-left: 3px solid #3b82f6;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .notification-message {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .notification-time {
            font-size: 0.75rem;
            color: #9ca3af;
        }

        .batch-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .batch-actions button {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
            border: 1px solid #e5e7eb;
            background: white;
            transition: all 0.2s;
        }

        .batch-actions button:hover {
            background: #f3f4f6;
        }

        .pagination {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
        }
    </style>
@endpush

@section('content')
    <div class="notifications-container">
        <div class="notifications-header">
            <h1>
                <i class="fas fa-bell"></i>
                Notifications
            </h1>
            <div class="batch-actions">
                <button id="markAllReadBtn" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-check-double"></i> Mark all as read
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

        <div class="notification-list" id="notificationsList">
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
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
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

            // Render pagination
            if (data.last_page > 1) {
                let paginationHtml = '<nav><ul class="pagination">';

                // Previous button
                if (data.prev_page_url) {
                    paginationHtml +=
                        `<li class="page-item"><button class="page-link" onclick="loadNotifications(${data.current_page - 1}, '${currentFilter}')">Previous</button></li>`;
                } else {
                    paginationHtml += `<li class="page-item disabled"><span class="page-link">Previous</span></li>`;
                }

                // Page numbers
                for (let i = 1; i <= data.last_page; i++) {
                    if (i === data.current_page) {
                        paginationHtml += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                    } else {
                        paginationHtml +=
                            `<li class="page-item"><button class="page-link" onclick="loadNotifications(${i}, '${currentFilter}')">${i}</button></li>`;
                    }
                }

                // Next button
                if (data.next_page_url) {
                    paginationHtml +=
                        `<li class="page-item"><button class="page-link" onclick="loadNotifications(${data.current_page + 1}, '${currentFilter}')">Next</button></li>`;
                } else {
                    paginationHtml += `<li class="page-item disabled"><span class="page-link">Next</span></li>`;
                }

                paginationHtml += '</ul></nav>';
                pagination.innerHTML = paginationHtml;
            } else {
                pagination.innerHTML = '';
            }
        }

        function getNotificationIcon(type, data) {
            const icons = {
                'crm_task': {
                    icon: 'fas fa-tasks',
                    bg: '#3b82f6',
                    color: '#3b82f6'
                },
                'student_added': {
                    icon: 'fas fa-user-plus',
                    bg: '#10b981',
                    color: '#10b981'
                },
                'student_deleted': {
                    icon: 'fas fa-user-minus',
                    bg: '#ef4444',
                    color: '#ef4444'
                },
                'application_submitted': {
                    icon: 'fas fa-file-alt',
                    bg: '#f59e0b',
                    color: '#f59e0b'
                },
                'application_status_updated': {
                    icon: 'fas fa-exchange-alt',
                    bg: '#8b5cf6',
                    color: '#8b5cf6'
                },
                'application_message_added': {
                    icon: 'fas fa-comment',
                    bg: '#06b6d4',
                    color: '#06b6d4'
                },
                'document_uploaded': {
                    icon: 'fas fa-upload',
                    bg: '#6366f1',
                    color: '#6366f1'
                },
                'user_registered': {
                    icon: 'fas fa-user-check',
                    bg: '#14b8a6',
                    color: '#14b8a6'
                },
            };

            if (type === 'App\\Notifications\\CrmTaskNotification') {
                return icons.crm_task;
            }

            if (data && data.type) {
                return icons[data.type] || {
                    icon: 'fas fa-bell',
                    bg: '#6b7280',
                    color: '#6b7280'
                };
            }

            return {
                icon: 'fas fa-bell',
                bg: '#6b7280',
                color: '#6b7280'
            };
        }

        function getNotificationTitle(notif) {
            if (notif.type === 'App\\Notifications\\CrmTaskNotification') {
                const subtype = notif.data?.subtype;
                const titles = {
                    'assigned': 'New Task Assigned',
                    'due_today': 'Task Due Today',
                    'upcoming': 'Upcoming Task',
                    'overdue': 'Task Overdue'
                };
                return titles[subtype] || 'Task Notification';
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

        function getNotificationMessage(notif) {
            return notif.data?.message || 'You have a new notification';
        }

        function formatDate(dateString) {
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

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // Event listeners
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                loadNotifications(1, tab.dataset.filter);
            });
        });

        document.getElementById('markAllReadBtn')?.addEventListener('click', async () => {
            await fetch('/crm/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            loadNotifications(currentPage, currentFilter);
        });

        document.getElementById('clearReadBtn')?.addEventListener('click', async () => {
            if (!confirm('Clear all read notifications?')) return;
            await fetch('/crm/notifications/read/all', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            loadNotifications(currentPage, currentFilter);
        });

        // Initial load
        loadNotifications();
    </script>
@endsection
