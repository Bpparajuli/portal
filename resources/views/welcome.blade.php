@extends('layouts.app')
@section('title', 'Idea Consultancy — Agent Portal')

@section('content')
    <style>
        /* ============================================================
                                       WELCOME/GUEST DASHBOARD – ONLY UNIQUE/SPECIFIC STYLES
                                       Everything else (btn, card, input, bg, text, border, variables,
                                       shadows, radius, transitions, table, th, td, tr) comes from styles.css
                                    ============================================================ */

        /* === 1. Custom Font Override === */
        .guest-dash {
            font-family: 'Inter', sans-serif;
        }

        /* === 2. Hero Section (completely custom) === */
        .gd-hero {
            min-height: 92vh;
            background: linear-gradient(135deg, #0f0828 0%, #1a0262 45%, #820b5c 100%);
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 5rem 2rem;
        }

        .gd-hero-bg-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
        }

        .gd-hero-bg-orb.o1 {
            width: 500px;
            height: 500px;
            top: -150px;
            right: -100px;
            background: rgba(244, 63, 94, 0.18);
        }

        .gd-hero-bg-orb.o2 {
            width: 350px;
            height: 350px;
            bottom: -100px;
            left: 30%;
            background: rgba(6, 182, 212, 0.12);
        }

        .gd-hero-bg-orb.o3 {
            width: 200px;
            height: 200px;
            top: 50%;
            left: 5%;
            background: rgba(245, 158, 11, 0.08);
        }

        .gd-hero-grid {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 4rem;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        @media (max-width: 1024px) {
            .gd-hero-grid {
                grid-template-columns: 1fr;
                gap: 3rem;
            }
        }

        .gd-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            padding: 0.4rem 1rem;
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(10px);
        }

        .gd-hero-badge .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #4ade80;
            animation: pulse-anim 2s infinite;
        }

        @keyframes pulse-anim {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.5;
                transform: scale(1.4);
            }
        }

        .gd-hero-heading {
            font-family: 'Sora', sans-serif;
            font-size: clamp(2.2rem, 5vw, 3.5rem);
            font-weight: 800;
            color: #fff;
            line-height: 1.15;
            margin-bottom: 1.25rem;
        }

        .gd-hero-heading .highlight {
            background: linear-gradient(135deg, #f43f5e, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .gd-hero-desc {
            color: rgba(255, 255, 255, 0.65);
            font-size: 1.05rem;
            line-height: 1.7;
            margin-bottom: 2rem;
            max-width: 520px;
        }

        .gd-hero-ctas {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2.5rem;
        }

        .gd-hero-stats {
            display: flex;
            gap: 2.5rem;
            flex-wrap: wrap;
        }

        .gd-hero-stat {
            text-align: center;
        }

        .gd-hero-stat-val {
            font-family: 'Sora', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            display: block;
        }

        .gd-hero-stat-lbl {
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.5);
            font-weight: 500;
        }

        /* === 3. Login Card (custom glass morphism) === */
        .gd-login-card {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 24px;
            padding: 2.5rem;
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .gd-login-card h3 {
            font-family: 'Sora', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.5rem;
        }

        .gd-login-card p {
            color: rgba(255, 255, 255, 0.55);
            font-size: 0.85rem;
            margin-bottom: 1.75rem;
        }

        /* Password wrapper for view toggle */
        .gd-password-wrapper {
            position: relative;
        }

        .gd-password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            font-size: 1rem;
            transition: color 0.2s;
            padding: 0;
        }

        .gd-password-toggle:hover {
            color: rgba(255, 255, 255, 0.9);
        }

        .gd-login-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.82rem;
        }

        .gd-login-links a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            transition: color 0.2s;
        }

        .gd-login-links a:hover {
            color: rgba(255, 255, 255, 0.9);
        }

        /* === 4. Login Form Input Overrides (glass theme) === */
        .gd-login-card .form-control {
            background: rgba(255, 255, 255, 0.08) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.15) !important;
            color: #fff !important;
            padding: 0.85rem 1rem;
        }

        .gd-login-card .form-control:focus {
            border-color: rgba(255, 255, 255, 0.4) !important;
            background: rgba(255, 255, 255, 0.12) !important;
            box-shadow: none !important;
        }

        .gd-login-card .form-control::placeholder {
            color: rgba(255, 255, 255, 0.3) !important;
        }

        .gd-login-card label {
            color: rgba(255, 255, 255, 0.7) !important;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.4rem;
        }

        /* === 5. Section Wrappers === */
        .gd-section {
            padding: 5rem 2rem;
        }

        .gd-section-inner {
            max-width: 1200px;
            margin: 0 auto;
        }

        .gd-section-tag {
            display: inline-block;
            background: rgba(130, 11, 92, 0.1);
            color: var(--primary);
            border-radius: 50px;
            padding: 0.35rem 1rem;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 1rem;
        }

        .gd-section-title {
            font-family: 'Sora', sans-serif;
            font-size: clamp(1.75rem, 3vw, 2.5rem);
            font-weight: 800;
            color: var(--text-color);
            line-height: 1.25;
            margin-bottom: 1rem;
        }

        /* === 6. Filter Section (dark themed) === */
        .gd-filter-section {
            background: var(--secondary);
            padding: 3.5rem 2rem;
        }

        .gd-filter-inner {
            max-width: 1200px;
            margin: 0 auto;
        }

        .gd-filter-inner h2 {
            font-family: 'Sora', sans-serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.5rem;
        }

        .gd-filter-inner p {
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 1.75rem;
            font-size: 0.9rem;
        }

        /* === 7. Features Grid === */
        .gd-features-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-top: 3rem;
        }

        @media (max-width: 900px) {
            .gd-features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 540px) {
            .gd-features-grid {
                grid-template-columns: 1fr;
            }
        }

        .gd-feature-card {
            text-align: center;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }

        .gd-feature-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 0;
        }

        .gd-feature-card:hover {
            transform: translateY(-8px);
        }

        .gd-feature-card:hover::before {
            opacity: 1;
        }

        .gd-feature-card * {
            position: relative;
            z-index: 1;
        }

        .gd-feature-card:hover .gd-feature-icon,
        .gd-feature-card:hover h3,
        .gd-feature-card:hover p {
            color: #fff !important;
        }

        .gd-feature-icon-wrap {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            background: rgba(130, 11, 92, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            transition: background 0.3s;
        }

        .gd-feature-card:hover .gd-feature-icon-wrap {
            background: rgba(255, 255, 255, 0.15);
        }

        .gd-feature-icon {
            font-size: 1.6rem;
            color: var(--primary);
            transition: color 0.3s;
        }

        .gd-feature-card h3 {
            font-family: 'Sora', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.5rem;
            transition: color 0.3s;
        }

        .gd-feature-card p {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.6;
            transition: color 0.3s;
        }

        /* === 8. Programs Grid === */
        .gd-programs-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 3rem;
        }

        @media (max-width: 768px) {
            .gd-programs-grid {
                grid-template-columns: 1fr;
            }
        }

        .gd-program-card {
            position: relative;
            overflow: hidden;
        }

        .gd-program-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary), var(--primary));
        }

        .gd-program-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 1.25rem;
        }

        .gd-program-card h3 {
            font-family: 'Sora', sans-serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .gd-program-date {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 500;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* === 9. Testimonials Section (dark themed) === */
        .gd-testimonials-section {
            background: linear-gradient(135deg, #0f0828, #1a0262);
            padding: 5rem 2rem;
        }

        .gd-testimonials-section .gd-section-tag {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.8);
        }

        .gd-testimonials-section .gd-section-title {
            color: #fff;
        }

        .gd-testimonial-track {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 3rem;
        }

        @media (max-width: 900px) {
            .gd-testimonial-track {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .gd-testimonial-track {
                grid-template-columns: 1fr;
            }
        }

        .gd-testimonial-card {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            padding: 2rem;
            backdrop-filter: blur(10px);
        }

        .gd-testimonial-quote {
            font-size: 2.5rem;
            color: rgba(244, 63, 94, 0.5);
            font-family: Georgia, serif;
            line-height: 1;
            margin-bottom: 0.75rem;
        }

        .gd-testimonial-text {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.9rem;
            line-height: 1.7;
            margin-bottom: 1.25rem;
        }

        .gd-testimonial-author {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .gd-testimonial-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), #f43f5e);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .gd-testimonial-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #fff;
        }

        .gd-testimonial-loc {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.45);
        }

        /* === 10. CTA Section (custom gradient) === */
        .gd-cta-section {
            padding: 5rem 2rem;
        }

        .gd-cta-inner {
            max-width: 1200px;
            margin: 0 auto;
            background: linear-gradient(135deg, var(--secondary) 0%, #2d1270 50%, var(--primary) 100%);
            border-radius: 28px;
            padding: 4rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .gd-cta-inner::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 350px;
            height: 350px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
        }

        @media (max-width: 768px) {
            .gd-cta-inner {
                grid-template-columns: 1fr;
                padding: 2.5rem;
            }
        }

        .gd-cta-text {
            position: relative;
            z-index: 1;
        }

        .gd-cta-text h2 {
            font-family: 'Sora', sans-serif;
            font-size: 2.25rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 1rem;
        }

        .gd-cta-text p {
            color: rgba(255, 255, 255, 0.65);
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .gd-cta-image img {
            width: 100%;
            border-radius: 16px;
            position: relative;
            z-index: 1;
        }

        /* === 11. University Logos Section === */
        .gd-unis-section {
            padding: 4rem 2rem;
            background: var(--bg-card);
        }

        .gd-uni-logos-track {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 2.5rem;
        }

        .gd-uni-logo {
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            filter: grayscale(100%);
            opacity: 0.55;
            transition: all 0.3s;
        }

        .gd-uni-logo:hover {
            filter: grayscale(0%);
            opacity: 1;
            transform: scale(1.08);
        }

        .gd-uni-logo img {
            height: 100%;
            object-fit: contain;
            max-width: 160px;
        }

        /* === 12. Banner Section === */
        .gd-banner-section {
            padding: 2rem 0;
        }

        .gd-banner-section img {
            width: 100%;
            border-radius: 20px;
        }

        /* === 13. Mobile Responsive === */
        @media (max-width: 640px) {
            .gd-hero {
                padding: 4rem 1rem;
                min-height: auto;
            }

            .gd-section {
                padding: 3.5rem 1rem;
            }

            .gd-cta-inner {
                padding: 2rem 1.5rem;
            }
        }
    </style>

    <div class="guest-dash">

        {{-- ======== HERO ======== --}}
        <section class="gd-hero">
            <div class="gd-hero-bg-orb o1"></div>
            <div class="gd-hero-bg-orb o2"></div>
            <div class="gd-hero-bg-orb o3"></div>

            <div class="gd-hero-grid">
                <div class="gd-hero-left">
                    <div class="gd-hero-badge">
                        <div class="pulse-dot"></div>
                        Idea Portal — Now Live
                    </div>
                    <h1 class="gd-hero-heading">
                        Your Gateway to<br>
                        <span class="highlight">Global Education</span>
                    </h1>
                    <p class="gd-hero-desc">
                        Manage students, track applications, and connect with top universities across Germany, UAE, UK and
                        beyond — all from one powerful platform.
                    </p>
                    <div class="gd-hero-ctas">
                        <a href="{{ route('auth.register') }}" class="btn btn-light">
                            <i class="fa fa-user-plus"></i> Register as Agent
                        </a>
                        <a href="{{ route('guest.universities.index') }}" class="btn btn-outline-light">
                            <i class="fa fa-university"></i> Explore Universities
                        </a>
                    </div>
                    <div class="gd-hero-stats">
                        <div class="gd-hero-stat">
                            <span class="gd-hero-stat-val">180+</span>
                            <span class="gd-hero-stat-lbl">Universities</span>
                        </div>
                        <div class="gd-hero-stat">
                            <span class="gd-hero-stat-val">20+</span>
                            <span class="gd-hero-stat-lbl">Countries</span>
                        </div>
                        <div class="gd-hero-stat">
                            <span class="gd-hero-stat-val">500+</span>
                            <span class="gd-hero-stat-lbl">Students Placed</span>
                        </div>
                    </div>
                </div>

                {{-- LOGIN CARD WITH PASSWORD TOGGLE --}}
                <div class="gd-login-card">
                    <h3>Welcome Back</h3>
                    <p>Sign in to access your agent dashboard</p>
                    <form action="{{ route('auth.login.post') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control"
                                value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="gd-password-wrapper">
                                <input type="password" name="password" id="password" class="form-control"
                                    placeholder="••••••••" required>
                                <button type="button" class="gd-password-toggle" id="togglePassword">
                                    <i class="fa fa-eye-slash text-secondary"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-success w-100 mb-3">
                            <i class="fa fa-sign-in-alt me-2"></i>Sign In to Dashboard
                        </button>
                        <div class="gd-login-links">
                            <a href="{{ route('register') }}" class="border-bottom">Create account</a>
                            <a href="{{ route('auth.contact') }}" class="border-bottom">Forgot password?</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        {{-- ======== FILTER ======== --}}
        <section class="gd-filter-section">
            <div class="gd-filter-inner">
                <h2>Find Universities & Courses</h2>
                <p>Search by country, city, university or specific course — discover your best fit.</p>
                @include('partials.uni_filter')
            </div>
        </section>

        {{-- ======== FEATURES ======== --}}
        <section class="gd-section">
            <div class="gd-section-inner">
                <div style="text-align:center;max-width:600px;margin:0 auto;">
                    <div class="gd-section-tag">Why Choose Us</div>
                    <h2 class="gd-section-title">Everything You Need to <br>Place Students Globally</h2>
                </div>
                <div class="gd-features-grid">
                    <div class="gd-feature-card card">
                        <div class="gd-feature-icon-wrap"><i class="fa fa-user-graduate gd-feature-icon"></i></div>
                        <h3>Student Management</h3>
                        <p>Track and manage student profiles, documents, and applications all in one place.</p>
                    </div>
                    <div class="gd-feature-card card">
                        <div class="gd-feature-icon-wrap"><i class="fa fa-university gd-feature-icon"></i></div>
                        <h3>University Search</h3>
                        <p>Browse 180+ partner universities with detailed course listings and intake info.</p>
                    </div>
                    <div class="gd-feature-card card">
                        <div class="gd-feature-icon-wrap"><i class="fa fa-edit gd-feature-icon"></i></div>
                        <h3>Application Tracking</h3>
                        <p>Submit and monitor applications with real-time status updates throughout.</p>
                    </div>
                    <div class="gd-feature-card card">
                        <div class="gd-feature-icon-wrap"><i class="fa fa-chart-line gd-feature-icon"></i></div>
                        <h3>Analytics & Reports</h3>
                        <p>Gain insights into your performance with intuitive dashboards and reports.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ======== BANNER ======== --}}
        <div class="gd-banner-section">
            <div class="gd-section-inner">
                <img src="{{ asset('images/banner-3.png') }}" alt="Banner">
            </div>
        </div>

        {{-- ======== PROGRAMS ======== --}}
        <section class="gd-section" style="background:var(--bg-card);">
            <div class="gd-section-inner">
                <div style="text-align:center;max-width:600px;margin:0 auto;">
                    <div class="gd-section-tag">Events</div>
                    <h2 class="gd-section-title">Upcoming Trainings & Webinars</h2>
                </div>
                <div class="gd-programs-grid">
                    <div class="gd-program-card card">
                        <div class="gd-program-icon"><i class="fas fa-graduation-cap"></i></div>
                        <h3>Student Management Training</h3>
                        <div class="gd-program-date"><i class="fas fa-calendar-alt"></i> May 15, 2026</div>
                        <a href="#" class="btn btn-secondary btn-sm">Register Now</a>
                    </div>
                    <div class="gd-program-card card">
                        <div class="gd-program-icon"><i class="fas fa-file-alt"></i></div>
                        <h3>University Applications Webinar</h3>
                        <div class="gd-program-date"><i class="fas fa-calendar-alt"></i> May 25, 2026</div>
                        <a href="#" class="btn btn-secondary btn-sm">Register Now</a>
                    </div>
                    <div class="gd-program-card card">
                        <div class="gd-program-icon"><i class="fas fa-passport"></i></div>
                        <h3>Visa Process Guidance — Germany</h3>
                        <div class="gd-program-date"><i class="fas fa-calendar-alt"></i> June 2, 2026</div>
                        <a href="#" class="btn btn-secondary btn-sm">Register Now</a>
                    </div>
                </div>
            </div>
        </section>

        {{-- ======== TESTIMONIALS ======== --}}
        <section class="gd-testimonials-section">
            <div class="gd-section-inner" style="max-width:1200px;margin:0 auto;">
                <div style="text-align:center;margin-bottom:0.5rem;">
                    <div class="gd-section-tag">Testimonials</div>
                    <h2 class="gd-section-title">What Our Agents Say</h2>
                </div>
                <div class="gd-testimonial-track">
                    @php
                        $testimonials = [
                            [
                                'text' =>
                                    'The way documents are handled in IDEA\'s portal is very smooth and hassle-free. Everything is organized, making the application process easy.',
                                'name' => 'Ramesh Kumar Thapa',
                                'loc' => 'Kathmandu, Nepal',
                            ],
                            [
                                'text' =>
                                    'Finding courses and university details is really simple. The portal makes it easy to find universities and available courses without any confusion.',
                                'name' => 'Anisha Gurung',
                                'loc' => 'Pokhara, Nepal',
                            ],
                            [
                                'text' =>
                                    'The IDEA team members are very supportive. They guide us at every step and are always available to answer questions.',
                                'name' => 'Suman Shah',
                                'loc' => 'Biratnagar, Nepal',
                            ],
                            [
                                'text' =>
                                    'The training sessions organized by IDEA were extremely helpful. They gave practical insights and prepared us well for the application and visa process.',
                                'name' => 'Sabina KC',
                                'loc' => 'Bhaktapur, Nepal',
                            ],
                            [
                                'text' =>
                                    'Germany is such a great study destination, and IDEA has promoted it effectively. We are receiving complete support for our Germany applications.',
                                'name' => 'Manoj Adhikari',
                                'loc' => 'Dharan, Nepal',
                            ],
                            [
                                'text' =>
                                    'This inbuilt portal by IDEA is fantastic. It is user-friendly, intuitive, and makes navigating courses and universities very easy.',
                                'name' => 'Priya Thapa Magar',
                                'loc' => 'Chitwan, Nepal',
                            ],
                        ];
                    @endphp
                    @foreach ($testimonials as $t)
                        <div class="gd-testimonial-card">
                            <div class="gd-testimonial-quote">"</div>
                            <p class="gd-testimonial-text">{{ $t['text'] }}</p>
                            <div class="gd-testimonial-author">
                                <div class="gd-testimonial-avatar">{{ strtoupper(substr($t['name'], 0, 1)) }}</div>
                                <div>
                                    <div class="gd-testimonial-name">{{ $t['name'] }}</div>
                                    <div class="gd-testimonial-loc">{{ $t['loc'] }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ======== CTA ======== --}}
        <section class="gd-cta-section">
            <div class="gd-cta-inner">
                <div class="gd-cta-text">
                    <h2>Partner With Us Today!</h2>
                    <p>Join our growing network of agents across Nepal and place your students in top universities
                        worldwide. Access all tools — free.</p>
                    <a href="{{ route('register') }}" class="btn btn-success btn-lg">
                        <i class="fa fa-handshake me-2"></i> Become a Partner
                    </a>
                </div>
                <div class="gd-cta-image">
                    <img src="{{ asset('images/banner-2.png') }}" alt="Partner with Idea">
                </div>
            </div>
        </section>

        {{-- ======== UNIVERSITY LOGOS ======== --}}
        <section class="gd-unis-section">
            <div style="max-width:1200px;margin:0 auto;text-align:center;">
                <div class="gd-section-tag">Our Network</div>
                <h2 class="gd-section-title" style="font-size:1.75rem;">Partner Universities</h2>
                <div class="gd-uni-logos-track">
                    @foreach (File::files(storage_path('app/public/uni_logo')) as $file)
                        @php
                            $fileName = basename($file);
                            $alt = pathinfo($fileName, PATHINFO_FILENAME);
                        @endphp
                        <div class="gd-uni-logo">
                            <img src="{{ asset('storage/uni_logo/' . $fileName) }}" alt="{{ ucfirst($alt) }}">
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

    </div>

    {{-- Password Toggle Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.querySelector('i').classList.toggle('fa-eye');
                    this.querySelector('i').classList.toggle('fa-eye-slash');
                });
            }
        });
    </script>
@endsection

@section('scripts')
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 900,
            once: true,
            offset: 60
        });
    </script>
@endsection
