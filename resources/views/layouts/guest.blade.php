<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Idea Consultancy') - Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" />
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @php
        $siteName = App\Models\Setting::getValue('site.name', 'Idea Consultancy');
        $siteLogo = App\Models\Setting::getValue('site.logo', '');
        $notice = App\Models\Setting::getValue('site.notice', '');

        $_gPrimary = App\Models\Setting::getValue('theme_primary', '#1a0262');
        $_gSecondary = App\Models\Setting::getValue('theme_secondary', '#820b5c');
        $_gSuccess = App\Models\Setting::getValue('theme_success', '#10b981');
        $_gWarning = App\Models\Setting::getValue('theme_warning', '#f59e0b');
        $_gInfo = App\Models\Setting::getValue('theme_info', '#3b82f6');
        $_gDanger = App\Models\Setting::getValue('theme_danger', '#ef4444');
        $_gText = App\Models\Setting::getValue('theme_text', '#1e293b');
        $_gHeadingColor = App\Models\Setting::getValue('theme_heading_color', '#0f172a');
        $_gBg = App\Models\Setting::getValue('theme_bg', '#f8fafc');
        $_gBtnGradFrom = App\Models\Setting::getValue('theme_btn_gradient_from', '#1a0262');
        $_gBtnGradTo = App\Models\Setting::getValue('theme_btn_gradient_to', '#2c0e8a');
    @endphp
    <style>
        :root {
            --primary: {{ $_gPrimary }};
            --secondary: {{ $_gSecondary }};
            --success: {{ $_gSuccess }};
            --warning: {{ $_gWarning }};
            --info: {{ $_gInfo }};
            --danger: {{ $_gDanger }};
            --accent: {{ $_gSecondary }};
            --text: {{ $_gText }};
            --heading-color: {{ $_gHeadingColor }};
            --bg: {{ $_gBg }};
            --btn-grad-from: {{ $_gBtnGradFrom }};
            --btn-grad-to: {{ $_gBtnGradTo }};
            --font-family: 'Inter', sans-serif;
        }

        * {
            font-family: var(--font-family);
        }

        body {
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }
        h1, h2, h3, h4, h5, h6 { color: var(--heading-color); }

        /* ───── Top Navbar ───── */
        .guest-header {
            background: linear-gradient(135deg, var(--btn-grad-from) 0%, var(--btn-grad-to) 100%);
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 1040;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
        }

        .guest-header .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 0.6rem;
            padding-bottom: 0.6rem;
        }

        .guest-header .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #fff;
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: -0.02em;
        }

        .guest-header .navbar-brand img {
            height: 40px;
            width: auto;
            border-radius: 6px;
            object-fit: contain;
        }

        .guest-header .navbar-brand .brand-text {
            line-height: 1.2;
        }

        .guest-header .navbar-brand .brand-sub {
            font-size: 0.6rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.5);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            display: block;
        }

        .guest-header .nav-links {
            display: flex;
            gap: 0.25rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .guest-header .nav-links .nav-item {
            margin: 0;
        }

        .guest-header .nav-links .nav-link {
            color: rgba(255, 255, 255, 0.75);
            font-weight: 500;
            font-size: 0.85rem;
            padding: 0.45rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .guest-header .nav-links .nav-link:hover,
        .guest-header .nav-links .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }

        .guest-header .auth-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .guest-header .btn-auth {
            padding: 0.45rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .guest-header .btn-login {
            color: #fff;
            border: 1.5px solid rgba(255, 255, 255, 0.3);
            background: transparent;
        }

        .guest-header .btn-login:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .guest-header .btn-register {
            background: linear-gradient(135deg, var(--secondary), #a30e74);
            color: #fff;
            border: 1.5px solid transparent;
            box-shadow: 0 4px 14px rgba(from var(--secondary) r g b / 0.35);
        }

        .guest-header .btn-register:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(from var(--secondary) r g b / 0.45);
        }

        .guest-header .navbar-toggler {
            border: none;
            padding: 0.25rem 0.5rem;
            color: #fff;
        }

        .guest-header .navbar-toggler-icon {
            filter: invert(1);
        }

        /* ───── Notice Bar ───── */
        .notice-bar {
            background: linear-gradient(90deg, var(--secondary), #a30e74);
            font-size: 0.85rem;
            color: #fff;
            overflow: hidden;
        }
        .notice-bar .notice-inner {
            display: flex;
            align-items: center;
            white-space: nowrap;
        }
        .notice-bar .notice-icon {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.15);
            padding: 0.4rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            flex-shrink: 0;
            z-index: 1;
        }
        .notice-bar .notice-track {
            display: inline-block;
            padding-left: 2rem;
            animation: notice-scroll 35s linear infinite;
        }
        @keyframes notice-scroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .notice-bar .notice-track:hover {
            animation-play-state: paused;
        }

        /* ───── Main Content ───── */
        .guest-content {
            min-height: calc(100vh - 200px);
        }

        /* ───── Mobile Navbar ───── */
        @media (max-width: 991.98px) {
            .guest-header .container {
                flex-wrap: wrap;
            }
            .guest-header .navbar-collapse {
                background: rgba(13,1,58,0.98);
                border-radius: 0 0 16px 16px;
                padding: 0.75rem;
                margin-top: 0.5rem;
            }
            .guest-header .nav-links {
                flex-direction: column;
                width: 100%;
                padding: 0.5rem 0;
                gap: 0;
            }
            .guest-header .nav-links .nav-link {
                padding: 0.7rem 1rem;
                border-radius: 8px;
            }
            .guest-header .auth-buttons {
                width: 100%;
                padding: 0.75rem 0 0.25rem;
                justify-content: center;
                flex-wrap: wrap;
                border-top: 1px solid rgba(255,255,255,0.08);
                margin-top: 0.5rem;
            }
            .guest-header .auth-buttons .btn-auth {
                flex:1;
                justify-content:center;
                text-align:center;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    {{-- ════════════ HEADER ════════════ --}}
    <header class="guest-header">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                @if($siteLogo)
                    @php $logoSrc = str_starts_with($siteLogo, 'settings/') ? \Illuminate\Support\Facades\Storage::url($siteLogo) : (str_starts_with($siteLogo, 'images/') ? asset($siteLogo) : asset('storage/uni_logo/' . $siteLogo)); @endphp
                    <img src="{{ $logoSrc }}" alt="{{ $siteName }} logo">
                @else
                    <span class="d-inline-flex align-items-center justify-content-center"
                          style="width:40px;height:40px;border-radius:8px;background:rgba(255,255,255,0.1);">
                        <i class="fas fa-graduation-cap" style="color:#fff;font-size:1.2rem;"></i>
                    </span>
                @endif
                <span class="brand-text">
                    {{ $siteName }}
                    <span class="brand-sub">Study Abroad Portal</span>
                </span>
            </a>

            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#guestNav"
                    aria-controls="guestNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse d-lg-flex align-items-center" id="guestNav">
                <ul class="nav-links mx-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                           href="{{ route('home') }}"><i class="fas fa-home me-1"></i>Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('guest.universities.*') ? 'active' : '' }}"
                           href="{{ route('guest.universities.index') }}"><i class="fas fa-university me-1"></i>Universities</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('guest.courses.*') ? 'active' : '' }}"
                           href="{{ route('guest.courses.index') }}"><i class="fas fa-book-open me-1"></i>Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('guest.enquiries.create') ? 'active' : '' }}"
                           href="{{ route('guest.enquiries.create') }}"><i class="fas fa-envelope me-1"></i>Contact</a>
                    </li>
                </ul>

                <div class="auth-buttons">
                    @guest
                        <a href="{{ route('auth.login') }}" class="btn-auth btn-login">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="{{ route('auth.register') }}" class="btn-auth btn-register">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    @else
                        @php
                            $user = auth()->user();
                            $dashRoute = match(true) {
                                $user->is_admin => route('admin.dashboard'),
                                $user->is_agent => route('agent.dashboard'),
                                $user->is_staff && $user->paid_crm => route('crm.dashboard'),
                                $user->is_staff => route('staff.dashboard'),
                                default => route('home'),
                            };
                        @endphp
                        <a href="{{ $dashRoute }}" class="btn-auth btn-register">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </header>

    {{-- ════════════ NOTICE BAR ════════════ --}}
    @if($notice)
    <div class="notice-bar">
        <div class="container notice-inner">
            <span class="notice-icon"><i class="fas fa-bullhorn"></i> Notice</span>
            <div class="notice-track">{{ $notice }} &nbsp;&bull;&nbsp; {{ $notice }} &nbsp;&bull;&nbsp; {{ $notice }}</div>
        </div>
    </div>
    @endif

    {{-- ════════════ MAIN CONTENT ════════════ --}}
    <main class="guest-content">
        @include('partials.alerts')
        @yield('content')
    </main>

    @include('partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>AOS.init({ duration: 800, once: true, offset: 60 });</script>

    {{-- Tawk.to Live Chat --}}
    <script>
        var Tawk_API = Tawk_API || {},
            Tawk_LoadStart = new Date();
        (function() {
            var s1 = document.createElement("script"),
                s0 = document.getElementsByTagName("script")[0];
            s1.async = true;
            s1.src = 'https://embed.tawk.to/YOUR_TAWKTO_PROPERTY_ID/YOUR_TAWKTO_WIDGET_ID';
            s1.charset = 'UTF-8';
            s1.setAttribute('crossorigin', '*');
            s0.parentNode.insertBefore(s1, s0);
        })();
    </script>

    @stack('scripts')
</body>
</html>
