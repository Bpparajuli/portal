@push('styles')
    <style>
        :root {
            --primary: #1a0262;
            --primary-light: #ede5f8;
            --primary-dark: #0f0040;
            --accent: #820b5c;
            --accent-gradient: linear-gradient(135deg, #1a0262, #820b5c);
            --bg: #f4f2f8;
            --card-bg: #ffffff;
            --card-border: #e8e5ee;
            --card-shadow: 0 1px 2px rgba(26,2,98,.04), 0 4px 12px rgba(26,2,98,.06);
            --glass-bg: rgba(255,255,255,0.88);
            --glass-border: rgba(232,229,238,0.5);
            --glass-shadow: 0 8px 32px rgba(26,2,98,.06);
            --radius: 12px;
            --radius-sm: 8px;
            --font-xs: .6rem;
            --font-sm: .68rem;
            --font-md: .75rem;
            --font-lg: .82rem;
            --hd-xs: 2px;
            --hd-sm: 4px;
            --hd-md: 8px;
            --hd-lg: 12px;
            --hd-xl: 16px;
            --hd-radius: 8px;
            --hd-radius-sm: 4px;
            --hd-font-xs: .6rem;
            --hd-font-sm: .68rem;
            --hd-font-md: .75rem;
            --hd-font-lg: .82rem;
            --hd-shadow: 0 1px 3px rgba(0,0,0,.04);
            --hd-shadow-hover: 0 4px 12px rgba(0,0,0,.08);
            --hd-transition: .15s ease;
        }

        body { background: var(--bg); }

        .container-fluid { padding-left: 16px !important; padding-right: 16px !important; }
        .mb-3,.mb-4 { margin-bottom: 10px !important; }
        .btn-sm { font-size: var(--font-sm) !important; padding: 3px 10px !important; border-radius: var(--radius-sm) !important; }
        .badge { font-size: var(--font-xs) !important; padding: 2px 6px !important; border-radius: 4px !important; }
        .form-control,.form-select { font-size: var(--font-sm) !important; padding: 4px 8px !important; min-height: auto !important; height: auto !important; border-radius: var(--radius-sm) !important; }
        .table td,.table th { padding: 6px 8px !important; font-size: var(--font-sm) !important; }

        .hd-card {
            background: var(--card-bg);
            border-radius: var(--radius); padding: 16px 18px;
            box-shadow: var(--card-shadow); border: 1px solid var(--card-border);
            transition: box-shadow .2s, border-color .2s, transform .15s;
        }
        .hd-card:hover { box-shadow: 0 4px 16px rgba(26,2,98,.08); border-color: #c8c0d8; }
        .hd-card-title {
            font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px;
            display: flex; align-items: center; gap: 6px;
            padding-bottom: 8px; margin-bottom: 12px;
            border-bottom: 2px solid var(--primary-light); color: var(--primary-dark);
        }
        .hd-card-title i { color: var(--accent) !important; }

        .badge-overdue { background: #fef2f2; color: #dc2626; }
        .badge-today { background: #fffbeb; color: #d97706; }
        .badge-upcoming { background: #ede5f8; color: #1a0262; }
        .badge-notask { background: #f1f5f9; color: #64748b; }

        .hd-star {
            display: inline-flex; flex-direction: row-reverse; gap: 1px;
        }
        .hd-star input { display: none; }
        .hd-star label {
            font-size: 11px; color: #d1d5db; cursor: pointer;
            transition: color .1s; padding: 0 1px;
        }
        .hd-star input:checked ~ label,
        .hd-star label:hover,
        .hd-star label:hover ~ label { color: var(--primary) !important; }

        .hd-tags { display: flex; flex-wrap: wrap; gap: 3px; }
        .hd-tag {
            font-size: .52rem; background: var(--primary-light); color: var(--primary-dark);
            border-radius: 10px; padding: 2px 8px; display: inline-flex;
            align-items: center; gap: 3px; line-height: 1.4;
            border: 1px solid #d4c4ec;
        }
        .hd-tag-remove { cursor: pointer; font-weight: 700; opacity: .5; margin-left: 2px; }
        .hd-tag-remove:hover { opacity: 1; }
        .hd-add-tag {
            font-size: .52rem; background: transparent; border: 1px dashed #cbd5e1;
            border-radius: 10px; padding: 1px 8px; cursor: pointer; color: #94a3b8;
            transition: all .12s; line-height: 1.4;
        }
        .hd-add-tag:hover { background: var(--primary); color: #fff; border-color: var(--primary); }

        .crm-toast {
            position: fixed; bottom: 20px; right: 20px; z-index: 9999;
            min-width: 260px; background: rgba(255,255,255,0.95); backdrop-filter: blur(8px);
            border-radius: var(--radius); padding: 10px 14px;
            box-shadow: 0 8px 32px rgba(0,0,0,.12);
            display: flex; align-items: center; gap: 8px;
            animation: crmToastIn .25s ease; border-left: 3px solid; font-size: var(--font-sm);
        }
        .crm-toast.success { border-left-color: #10b981; }
        .crm-toast.error { border-left-color: #ef4444; }
        .crm-toast.warning { border-left-color: #f59e0b; }
        .crm-toast.info { border-left-color: #0ea5e9; }
        .crm-toast i:last-child { margin-left: auto; cursor: pointer; opacity: .6; }
        .crm-toast i:last-child:hover { opacity: 1; }
        @keyframes crmToastIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        .crm-loader-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,.3); backdrop-filter: blur(2px);
            display: none; align-items: center; justify-content: center; z-index: 10000;
        }
        .crm-loader-overlay.show { display: flex; }
        .crm-loader-spinner {
            width: 36px; height: 36px;
            border: 3px solid #e2e8f0; border-top-color: var(--primary);
            border-radius: 50%; animation: crmSpin .7s linear infinite;
        }
        @keyframes crmSpin { to { transform: rotate(360deg); } }

        .fab-add-student {
            position: fixed; bottom: 24px; right: 24px;
            width: 44px; height: 44px; border-radius: 50%;
            background: var(--accent-gradient); color: #fff; border: none;
            box-shadow: 0 4px 16px rgba(130,11,92,.35); z-index: 1000;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; transition: all .15s;
        }
        .fab-add-student:hover { transform: scale(1.08) translateY(-2px); box-shadow: 0 6px 24px rgba(130,11,92,.4); }

        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
@endpush

@push('scripts')
    <script>
        window.CrmCore = (function() {
            'use strict';
            var instance = null, loaderStack = 0;

            function getCsrfToken() {
                return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                    document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
            }

            function esc(s) {
                if (!s) return '';
                var d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            }

            var Core = {
                config: {
                    isAdmin: {{ isset($isAdmin) && $isAdmin ? 'true' : 'false' }},
                    baseUrl: '{{ url('/') }}',
                    csrfToken: '{{ csrf_token() }}'
                },
                getCsrfToken: function() { return this.config.csrfToken || getCsrfToken(); },
                escapeHtml: function(s) { return esc(s); },

                showToast: function(msg, type) {
                    var existing = document.querySelectorAll('.crm-toast');
                    if (existing.length > 3) existing[0].remove();
                    var t = document.createElement('div');
                    t.className = 'crm-toast ' + (type || 'info');
                    var icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
                    t.innerHTML = '<i class="fas ' + (icons[type] || icons.info) + '"></i><span>' + esc(msg) + '</span><i class="fas fa-times"></i>';
                    t.querySelector('.fa-times').onclick = function() { t.remove(); };
                    document.body.appendChild(t);
                    setTimeout(function() { if (t.parentElement) t.remove(); }, 4000);
                },

                showLoader: function() {
                    loaderStack++;
                    var el = document.getElementById('crmLoader');
                    if (!el) {
                        el = document.createElement('div');
                        el.id = 'crmLoader';
                        el.className = 'crm-loader-overlay';
                        el.innerHTML = '<div class="crm-loader-spinner"></div>';
                        document.body.appendChild(el);
                    }
                    el.classList.add('show');
                },

                hideLoader: function() {
                    loaderStack--;
                    if (loaderStack <= 0) { loaderStack = 0; var el = document.getElementById('crmLoader'); if (el) el.classList.remove('show'); }
                },

                fetch: async function(url, opts) {
                    this.showLoader();
                    try {
                        var r = await fetch(url, Object.assign({}, opts, {
                            headers: Object.assign({ 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.getCsrfToken(), 'Accept': 'application/json' }, (opts.headers || {}))
                        }));
                        var d = await r.json();
                        if (!r.ok) throw new Error(d.error || d.message || 'HTTP ' + r.status);
                        return d;
                    } catch (e) { this.showToast(e.message, 'error'); throw e; }
                    finally { this.hideLoader(); }
                },

                switchView: function(view) {
                    var form = document.getElementById('crmFilterForm');
                    if (form) {
                        var i = form.querySelector('[name="view"]');
                        if (i) i.value = view;
                        window.dispatchEvent(new CustomEvent('crm:beforeViewChange', { detail: { view: view } }));
                        form.submit();
                    } else {
                        var p = new URLSearchParams(window.location.search);
                        p.set('view', view);
                        window.location.href = window.location.pathname + '?' + p.toString();
                    }
                },

                getCurrentView: function() { return (document.querySelector('[name="view"]') || {}).value || 'kanban'; },
                formatDate: function(d, f) {
                    var dt = new Date(d);
                    if (isNaN(dt.getTime())) return '';
                    if (f === 'short') return dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    if (f === 'long') return dt.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                    return dt.toISOString().split('T')[0];
                },
                getPriorityBadge: function(p) {
                    var m = { high: '<span class="badge bg-danger">High</span>', medium: '<span class="badge bg-warning text-dark">Med</span>', low: '<span class="badge bg-success">Low</span>' };
                    return m[p] || m.medium;
                },
                getStatusBadge: function(s, od) {
                    if (s === 'completed') return '<span class="badge bg-success">Done</span>';
                    if (od) return '<span class="badge bg-danger">Overdue</span>';
                    return '<span class="badge bg-secondary">Pending</span>';
                },
                updateStudentStage: async function(id, targetId) {
                    try {
                        var r = await this.fetch('/crm/students/' + id + '/stage', { method: 'PUT', body: JSON.stringify({ stage_id: parseInt(targetId) }) });
                        if (r.success) { this.showToast('Moved', 'success'); return true; }
                        throw new Error(r.error || 'Failed');
                    } catch(e) { this.showToast(e.message, 'error'); return false; }
                }
            };
            return { getInstance: function() { if (!instance) instance = Core; return instance; } };
        })();

        document.addEventListener('DOMContentLoaded', function() {
            if (!document.getElementById('crmLoader')) {
                var d = document.createElement('div');
                d.id = 'crmLoader'; d.className = 'crm-loader-overlay';
                d.innerHTML = '<div class="crm-loader-spinner"></div>';
                document.body.appendChild(d);
            }
            window.CrmCore.getInstance();
        });
        window.crmShowToast = function(m, t) { window.CrmCore.getInstance().showToast(m, t); };
        window.crmEscapeHtml = function(s) { return window.CrmCore.getInstance().escapeHtml(s); };
        window.setView = function(v) { window.CrmCore.getInstance().switchView(v); };
    </script>
@endpush
