@push('styles')
    <style>
        /* ============================================ */
        /* GLOBAL CRM STYLES - Shared across all components */
        /* ============================================ */

        /* Toast Notifications */
        .crm-toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 280px;
            background: white;
            border-radius: 12px;
            padding: 12px 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: crmToastSlideIn 0.3s ease;
            border-left: 4px solid;
            cursor: default;
        }

        .crm-toast.success {
            border-left-color: #10b981;
        }

        .crm-toast.error {
            border-left-color: #ef4444;
        }

        .crm-toast.warning {
            border-left-color: #f59e0b;
        }

        .crm-toast.info {
            border-left-color: #3b82f6;
        }

        .crm-toast i:last-child {
            margin-left: auto;
            cursor: pointer;
            opacity: 0.7;
        }

        .crm-toast i:last-child:hover {
            opacity: 1;
        }

        @keyframes crmToastSlideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Loader */
        .crm-loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }

        .crm-loader-overlay.show {
            display: flex;
        }

        .crm-loader-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f4f6;
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: crmSpin 0.8s linear infinite;
            background: white;
            padding: 10px;
        }

        @keyframes crmSpin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Common Card Styles */
        .crm-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }

        .crm-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* Badge Styles */
        .badge-overdue {
            background: #fef2f2;
            color: #dc2626;
        }

        .badge-today {
            background: #fffbeb;
            color: #d97706;
        }

        .badge-upcoming {
            background: #f0fdf4;
            color: #10b981;
        }

        /* Star Rating */
        .crm-star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 2px;
        }

        .crm-star-rating input {
            display: none;
        }

        .crm-star-rating label {
            font-size: 14px;
            color: #d1d5db;
            cursor: pointer;
        }

        .crm-star-rating input:checked~label,
        .crm-star-rating label:hover,
        .crm-star-rating label:hover~label {
            color: #fbbf24;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // ============================================
        // CRM CORE MODULE - Singleton Pattern
        // ============================================
        window.CrmCore = (function() {
            'use strict';

            let instance = null;
            let loaderStack = 0;

            function getCsrfToken() {
                return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                    document.querySelector('meta[name="csrf-token"]')?.content ||
                    '{{ csrf_token() }}';
            }

            function escapeHtml(str) {
                if (!str) return '';
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            const Core = {
                config: {
                    isAdmin: {{ isset($isAdmin) && $isAdmin ? 'true' : 'false' }},
                    baseUrl: '{{ url('/') }}',
                    csrfToken: '{{ csrf_token() }}'
                },

                getCsrfToken: function() {
                    return this.config.csrfToken || getCsrfToken();
                },
                escapeHtml: function(str) {
                    return escapeHtml(str);
                },

                showToast: function(message, type = 'success') {
                    const existingToasts = document.querySelectorAll('.crm-toast');
                    if (existingToasts.length > 3) existingToasts[0]?.remove();

                    const toast = document.createElement('div');
                    toast.className = `crm-toast ${type}`;
                    const icons = {
                        success: 'fa-check-circle',
                        error: 'fa-exclamation-circle',
                        warning: 'fa-exclamation-triangle',
                        info: 'fa-info-circle'
                    };
                    const icon = icons[type] || icons.success;

                    toast.innerHTML =
                        `<i class="fas ${icon}"></i><span>${this.escapeHtml(message)}</span><i class="fas fa-times"></i>`;
                    const closeBtn = toast.querySelector('.fa-times');
                    closeBtn.onclick = () => toast.remove();
                    document.body.appendChild(toast);
                    setTimeout(() => {
                        if (toast.parentElement) toast.remove();
                    }, 4000);
                },

                showLoader: function() {
                    loaderStack++;
                    let loader = document.getElementById('crmLoader');
                    if (!loader) {
                        loader = document.createElement('div');
                        loader.id = 'crmLoader';
                        loader.className = 'crm-loader-overlay';
                        loader.innerHTML = '<div class="crm-loader-spinner"></div>';
                        document.body.appendChild(loader);
                    }
                    loader.classList.add('show');
                },

                hideLoader: function() {
                    loaderStack--;
                    if (loaderStack <= 0) {
                        loaderStack = 0;
                        const loader = document.getElementById('crmLoader');
                        if (loader) loader.classList.remove('show');
                    }
                },

                fetch: async function(url, options = {}) {
                    this.showLoader();
                    try {
                        const response = await fetch(url, {
                            ...options,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken(),
                                'Accept': 'application/json',
                                ...options.headers
                            }
                        });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.error || data.message ||
                            `HTTP ${response.status}`);
                        return data;
                    } catch (error) {
                        this.showToast(error.message, 'error');
                        throw error;
                    } finally {
                        this.hideLoader();
                    }
                },

                switchView: function(view) {
                    const form = document.getElementById('crmFilterForm');
                    if (form) {
                        const viewInput = form.querySelector('[name="view"]');
                        if (viewInput) viewInput.value = view;
                        window.dispatchEvent(new CustomEvent('crm:beforeViewChange', {
                            detail: {
                                view
                            }
                        }));
                        form.submit();
                    } else {
                        const params = new URLSearchParams(window.location.search);
                        params.set('view', view);
                        window.location.href = `${window.location.pathname}?${params.toString()}`;
                    }
                },

                getCurrentView: function() {
                    return document.querySelector('[name="view"]')?.value || 'kanban';
                },

                formatDate: function(date, format = 'short') {
                    const d = new Date(date);
                    if (isNaN(d.getTime())) return '';
                    if (format === 'short') return d.toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric'
                    });
                    if (format === 'long') return d.toLocaleDateString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                    return d.toISOString().split('T')[0];
                },

                getTimeSlotLabel: function(timeSlot) {
                    const slots = {
                        morning: '🌅 Morning (9AM-12PM)',
                        afternoon: '☀️ Afternoon (12PM-3PM)',
                        evening: '🌙 Evening (3PM-6PM)'
                    };
                    return slots[timeSlot] || '📅 No time slot';
                },

                getPriorityBadge: function(priority) {
                    const badges = {
                        high: '<span class="badge bg-danger">🔴 High Priority</span>',
                        medium: '<span class="badge bg-warning text-dark">🟡 Medium Priority</span>',
                        low: '<span class="badge bg-success">🟢 Low Priority</span>'
                    };
                    return badges[priority] || badges.medium;
                },

                getStatusBadge: function(status, isOverdue) {
                    if (status === 'completed') return '<span class="badge bg-success">✓ Completed</span>';
                    if (isOverdue) return '<span class="badge bg-danger">⚠️ Overdue</span>';
                    return '<span class="badge bg-secondary">⏳ Pending</span>';
                },

                updateStudentStage: async function(studentId, targetStageId) {
                    this.showLoader();
                    try {
                        const response = await this.fetch(`/crm/students/${studentId}/stage`, {
                            method: 'PUT',
                            body: JSON.stringify({
                                stage_id: parseInt(targetStageId)
                            })
                        });
                        if (response.success) {
                            this.showToast('Student moved successfully', 'success');
                            return true;
                        }
                        throw new Error(response.error || 'Failed to move student');
                    } catch (error) {
                        this.showToast(error.message, 'error');
                        return false;
                    } finally {
                        this.hideLoader();
                    }
                }
            };

            return {
                getInstance: function() {
                    if (!instance) instance = Core;
                    return instance;
                }
            };
        })();

        document.addEventListener('DOMContentLoaded', function() {
            const CRM = window.CrmCore.getInstance();
            if (!document.getElementById('crmLoader')) {
                const loaderDiv = document.createElement('div');
                loaderDiv.id = 'crmLoader';
                loaderDiv.className = 'crm-loader-overlay';
                loaderDiv.innerHTML = '<div class="crm-loader-spinner"></div>';
                document.body.appendChild(loaderDiv);
            }
        });

        window.crmShowToast = function(msg, type) {
            window.CrmCore.getInstance().showToast(msg, type);
        };
        window.crmEscapeHtml = function(str) {
            return window.CrmCore.getInstance().escapeHtml(str);
        };
        window.setView = function(view) {
            window.CrmCore.getInstance().switchView(view);
        };
    </script>
@endpush
