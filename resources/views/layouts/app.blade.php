<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ Auth::id() }}">
    <meta name="user-name" content="{{ Auth::user()->name ?? '' }}">
    <meta name="user-avatar" content="{{ Auth::user()->business_logo ?? '' }}">
    @php $_siteName = \App\Models\Setting::getValue('site.name', 'Idea Consultancy'); $_siteLogo = \App\Models\Setting::getValue('site.logo', ''); $_favicon = \App\Models\Setting::getValue('site.favicon', ''); @endphp
    <title>@yield('title', $_siteName) - Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    @php
        $_themePrimary = \App\Models\Setting::getValue('theme_primary', '#1a0262');
        $_themeSecondary = \App\Models\Setting::getValue('theme_secondary', '#820b5c');
        $_themeSuccess = \App\Models\Setting::getValue('theme_success', '#10b981');
        $_themeWarning = \App\Models\Setting::getValue('theme_warning', '#f59e0b');
        $_themeInfo = \App\Models\Setting::getValue('theme_info', '#3b82f6');
        $_themeDanger = \App\Models\Setting::getValue('theme_danger', '#ef4444');
        $_themeBg = \App\Models\Setting::getValue('theme_bg', '#f8fafc');
        $_themeText = \App\Models\Setting::getValue('theme_text', '#1e293b');
        $_themeHeadingColor = \App\Models\Setting::getValue('theme_heading_color', '#0f172a');
        $_themeCardBg = \App\Models\Setting::getValue('theme_card_bg', '#ffffff');
        $_themeThBg = \App\Models\Setting::getValue('theme_table_header_bg', '#f0edfa');
        $_themeThColor = \App\Models\Setting::getValue('theme_table_header_color', '#1e293b');
        $_themeBtnGradFrom = \App\Models\Setting::getValue('theme_btn_gradient_from', '#1a0262');
        $_themeBtnGradTo = \App\Models\Setting::getValue('theme_btn_gradient_to', '#2c0e8a');

        $_ds_radius = \App\Models\Setting::getValue('design_border_radius', '12px');
        $_ds_btn_radius = \App\Models\Setting::getValue('design_btn_radius', '8px');
        $_ds_input_radius = \App\Models\Setting::getValue('design_input_radius', '8px');
        $_ds_card_radius = \App\Models\Setting::getValue('design_card_radius', '16px');
        $_ds_header_font = \App\Models\Setting::getValue('design_header_font', "'Inter', sans-serif");
        $_ds_body_font = \App\Models\Setting::getValue('design_body_font', "'Inter', sans-serif");
        $_ds_header_weight = \App\Models\Setting::getValue('design_header_weight', '700');
        $_ds_body_size = \App\Models\Setting::getValue('design_body_size', '0.875rem');
        $_ds_line_height = \App\Models\Setting::getValue('design_line_height', '1.6');
        $_ds_card_shadow = \App\Models\Setting::getValue('design_card_shadow', '0 1px 3px rgba(0,0,0,0.08)');
        $_ds_transition = \App\Models\Setting::getValue('design_transition', '0.2s ease');
        $_ds_container_width = \App\Models\Setting::getValue('design_container_width', '1320px');
    @endphp
    <style>
        :root {
            --primary: {{ $_themePrimary }};
            --secondary: {{ $_themeSecondary }};
            --success: {{ $_themeSuccess }};
            --warning: {{ $_themeWarning }};
            --info: {{ $_themeInfo }};
            --danger: {{ $_themeDanger }};
            --bg: {{ $_themeBg }};
            --text: {{ $_themeText }};
            --heading-color: {{ $_themeHeadingColor }};
            --card-bg: {{ $_themeCardBg }};
            --th-bg: {{ $_themeThBg }};
            --th-color: {{ $_themeThColor }};
            --btn-grad-from: {{ $_themeBtnGradFrom }};
            --btn-grad-to: {{ $_themeBtnGradTo }};

            --ds-radius: {{ $_ds_radius }};
            --ds-btn-radius: {{ $_ds_btn_radius }};
            --ds-input-radius: {{ $_ds_input_radius }};
            --ds-card-radius: {{ $_ds_card_radius }};
            --ds-header-font: {{ $_ds_header_font }};
            --ds-body-font: {{ $_ds_body_font }};
            --ds-header-weight: {{ $_ds_header_weight }};
            --ds-body-size: {{ $_ds_body_size }};
            --ds-line-height: {{ $_ds_line_height }};
            --ds-card-shadow: {{ $_ds_card_shadow }};
            --ds-transition: {{ $_ds_transition }};
            --ds-container-width: {{ $_ds_container_width }};
        }

        /* ── Global body colors ── */
        body { background: var(--bg); color: var(--text); font-family: var(--ds-body-font); font-size: var(--ds-body-size); line-height: var(--ds-line-height); }
        h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 { color: var(--heading-color); font-family: var(--ds-header-font); font-weight: var(--ds-header-weight); }

        /* ── Bootstrap variable overrides ── */
        .btn-primary, .btn-soft-primary { --bs-btn-bg: var(--btn-grad-from); --bs-btn-border-color: var(--btn-grad-from); background: linear-gradient(135deg, var(--btn-grad-from), var(--btn-grad-to)); --bs-btn-hover-bg: var(--primary); --bs-btn-hover-border-color: var(--primary); --bs-btn-active-bg: var(--primary); border: none; color: #fff; }
        .btn-primary:hover, .btn-soft-primary:hover { background: linear-gradient(135deg, var(--btn-grad-to), var(--btn-grad-from)); }
        .btn-secondary { --bs-btn-bg: var(--secondary); --bs-btn-border-color: var(--secondary); --bs-btn-hover-bg: color-mix(in srgb, var(--secondary) 80%, #000); --bs-btn-hover-border-color: color-mix(in srgb, var(--secondary) 80%, #000); --bs-btn-active-bg: color-mix(in srgb, var(--secondary) 60%, #000); }
        a, .link-primary { color: var(--primary); }
        a:hover { color: var(--secondary); }
        .text-primary { color: var(--primary) !important; }
        .text-secondary { color: var(--secondary) !important; }
        .text-success { color: var(--success) !important; }
        .text-warning { color: var(--warning) !important; }
        .text-info { color: var(--info) !important; }
        .text-danger { color: var(--danger) !important; }
        .bg-primary { background-color: var(--primary) !important; color: #fff !important; }
        .bg-primary .text-primary { color: #fff !important; }
        .bg-primary.text-primary { color: #fff !important; }
        .bg-secondary { background-color: var(--secondary) !important; color: #fff !important; }
        .bg-secondary .text-secondary { color: #fff !important; }
        .bg-secondary.text-secondary { color: #fff !important; }
        .bg-success { background-color: var(--success) !important; color: #fff !important; }
        .bg-success .text-success { color: #fff !important; }
        .bg-success.text-success { color: #fff !important; }
        .bg-warning { background-color: var(--warning) !important; color: #fff !important; }
        .bg-warning .text-warning { color: #fff !important; }
        .bg-warning.text-warning { color: #fff !important; }
        .bg-info { background-color: var(--info) !important; color: #fff !important; }
        .bg-info .text-info { color: #fff !important; }
        .bg-info.text-info { color: #fff !important; }
        .bg-danger { background-color: var(--danger) !important; color: #fff !important; }
        .bg-danger .text-danger { color: #fff !important; }
        .bg-danger.text-danger { color: #fff !important; }
        .border-primary { border-color: var(--primary) !important; }
        .border-secondary { border-color: var(--secondary) !important; }
        .card { background: var(--card-bg); }

        /* ── Typography ── */
        body { font-family: var(--ds-body-font); font-size: var(--ds-body-size); line-height: var(--ds-line-height); }
        h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 { font-family: var(--ds-header-font); font-weight: var(--ds-header-weight); }

        /* ── Border radius ── */
        .card, .card-custom { border-radius: var(--ds-card-radius) !important; }
        .btn, button:not(.btn-close):not(.navbar-toggler) { border-radius: var(--ds-btn-radius) !important; }
        .form-control, .form-select, .input-group-text { border-radius: var(--ds-input-radius) !important; }
        .rounded-custom { border-radius: var(--ds-radius) !important; }

        /* ── Table ── */
        .table th { padding: var(--ds-table-pad); background: var(--th-bg) !important; color: var(--th-color) !important; border-bottom: 2px solid var(--primary); }
        .table td { padding: var(--ds-table-pad); }

        /* ── Cards ── */
        .card { box-shadow: var(--ds-card-shadow); transition: box-shadow var(--ds-transition); }
        .card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

        /* ── Container ── */
        .container { max-width: var(--ds-container-width); }

        /* ── Form elements ── */
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 0.2rem rgba(from var(--primary) r g b / 0.25); }

        /* ── Primary/secondary replacement ── */
        .nav-pills .nav-link.active, .nav-tabs .nav-link.active { background: var(--primary); border-color: var(--primary); color: #fff; }
        .page-item.active .page-link { background: var(--primary); border-color: var(--primary); }
        .progress-bar { background: linear-gradient(90deg, var(--btn-grad-from), var(--btn-grad-to)); }
        .badge-primary { background: var(--primary); color: #fff; }
        .badge-secondary { background: var(--secondary); color: #fff; }
        .badge-success { background: var(--success); color: #fff; }
        .badge-warning { background: var(--warning); color: #fff; }
        .badge-info { background: var(--info); color: #fff; }
        .badge-danger { background: var(--danger); color: #fff; }
        .list-group-item.active { background: var(--primary); border-color: var(--primary); }
        .dropdown-item:active { background: var(--primary); }
        .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }
        .btn-outline-primary { color: var(--primary); border-color: var(--primary); }
        .btn-outline-primary:hover { background: linear-gradient(135deg, var(--btn-grad-from), var(--btn-grad-to)); border-color: transparent; color: #fff; }
        .btn-link { color: var(--primary); }
        .btn-link:hover { color: var(--secondary); }
        .alert-primary { background: color-mix(in srgb, var(--primary) 10%, white); border-color: var(--primary); color: var(--primary); }
        .alert-success { background: color-mix(in srgb, var(--success) 10%, white); border-color: var(--success); color: var(--success); }
        .alert-warning { background: color-mix(in srgb, var(--warning) 10%, white); border-color: var(--warning); color: var(--warning); }
        .alert-info { background: color-mix(in srgb, var(--info) 10%, white); border-color: var(--info); color: var(--info); }
        .alert-danger { background: color-mix(in srgb, var(--danger) 10%, white); border-color: var(--danger); color: var(--danger); }
    </style>
    <link rel="icon" type="image/x-icon" href="{{ \App\Models\Setting::resolveImageUrl($_favicon) ?? asset('favicon.ico') }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
    @stack('styles')
</head>
<body>
    <div class="app-wrapper">
        <!-- ========== SIDEBAR ========== -->
        <aside class="app-sidebar" id="appSidebar">
            <div class="sidebar-brand">
                <div class="brand-logo">@if($_siteLogo)<img src="{{ \App\Models\Setting::resolveImageUrl($_siteLogo) }}" alt="" style="height:32px;width:32px;object-fit:contain;border-radius:6px;">@else{{ substr($_siteName,0,2) }}@endif</div>
                <div class="brand-text">
                    {{ $_siteName }}
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
                    @php $_au = Auth::user(); @endphp
                    <div class="user-avatar" style="position:relative;overflow:hidden;">
                        @if ($_au->business_logo && Storage::disk('public')->exists($_au->business_logo))
                            <img src="{{ Storage::url($_au->business_logo) }}" alt=""
                                style="width:100%;height:100%;object-fit:cover;position:absolute;top:0;left:0;">
                        @else
                            {{ strtoupper(substr($_au->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="text-white small fw-semibold truncate">{{ $_au->name }}</div>
                        <div style="font-size:0.7rem;color:rgba(255,255,255,0.4);">{{ $_au->email }}</div>
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
                        <span class="chat-unread-badge" id="chatUnreadBadge" style="display:none;position:absolute;top:-4px;right:-6px;background:var(--danger,#ef4444);color:#fff;font-size:0.6rem;font-weight:700;min-width:16px;height:16px;border-radius:8px;align-items:center;justify-content:center;padding:0 4px;box-shadow:0 2px 4px rgba(0,0,0,0.2);">0</span>
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
                            @php $_u = Auth::user(); @endphp
                            @if (!$_u->is_staff || $_u->is_admin_staff)
                            <li><a class="dropdown-item" href="{{ route('profile.show', $_u->slug) }}"><i class="fas fa-user me-2"></i>My Profile</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('profile.edit', $_u->slug) }}"><i class="fas fa-edit me-2"></i>Edit Profile</a></li>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @include('partials.popup-display')
    @stack('scripts')
</body>
</html>
