{{-- resources/views/dashboard/guest-dashboard.blade.php --}}
@extends('layouts.guest')
@section('title', config('app.name') . ' — Study Abroad Portal')
@php
    $testimonials = App\Models\Testimonial::where('is_active', true)->latest()->take(6)->get();
    $heroTitle = App\Models\Setting::getValue('content.hero_title', 'Your Gateway to <span class="highlight">Global Education</span>');
    $heroSubtitle = App\Models\Setting::getValue('content.hero_subtitle', 'Manage students, track applications, and connect with top universities across Germany, UAE, UK and beyond.');
    $features = App\Models\Setting::getValue('content.features', []);
    $banners = App\Models\Setting::getValue('content.banners', []);
    $events = App\Models\Setting::getValue('content.events', []);
    $countriesTitle = App\Models\Setting::getValue('content.countries_title', 'Countries We Work With');
    $countriesData = App\Models\Setting::getValue('content.countries', []);
    $ctaTitle = App\Models\Setting::getValue('content.cta_title', 'Partner With Us Today!');
    $ctaDesc = App\Models\Setting::getValue('content.cta_description', 'Join our growing network of agents across Nepal and place your students in top universities worldwide.');
    $ctaBtnText = App\Models\Setting::getValue('content.cta_button_text', 'Become a Partner');
    $ctaBtnLink = App\Models\Setting::getValue('content.cta_button_link', '/register');
    $ctaImage = App\Models\Setting::resolveImageUrl(App\Models\Setting::getValue('content.cta_image', 'images/banner-2.png'));
    $uniCount = App\Models\Setting::getValue('content.hero_stat1_value', (string)App\Models\University::count());
    $countryCount = App\Models\Setting::getValue('content.hero_stat2_value', (string)App\Models\University::distinct('country')->count('country'));
    $courseCount = App\Models\Setting::getValue('content.hero_stat3_value', (string)App\Models\Course::count());
    $heroFormTitle = App\Models\Setting::getValue('content.hero_form_title', 'Welcome Back');
    $heroFormDescription = App\Models\Setting::getValue('content.hero_form_description', 'Sign in to access your agent dashboard');
    $heroBadge = App\Models\Setting::getValue('content.hero_badge', 'Idea Portal &mdash; Now Live');
    $heroBtn1Text = App\Models\Setting::getValue('content.hero_btn1_text', 'Register as Agent');
    $heroBtn1Link = App\Models\Setting::getValue('content.hero_btn1_link', '/register');
    $heroBtn2Text = App\Models\Setting::getValue('content.hero_btn2_text', 'Explore Universities');
    $heroBtn2Link = App\Models\Setting::getValue('content.hero_btn2_link', '/universities');
    $heroStat1Label = App\Models\Setting::getValue('content.hero_stat1_label', 'Universities');
    $heroStat2Label = App\Models\Setting::getValue('content.hero_stat2_label', 'Countries');
    $heroStat3Label = App\Models\Setting::getValue('content.hero_stat3_label', 'Courses');
    $sectionFilterTitle = App\Models\Setting::getValue('content.section_filter_title', 'Find Universities & Courses');
    $sectionFilterDesc = App\Models\Setting::getValue('content.section_filter_desc', 'Search by country, city, university or specific course &mdash; discover your best fit.');
    $sectionFeaturesTag = App\Models\Setting::getValue('content.section_features_tag', 'Why Choose Us');
    $sectionFeaturesTitle = App\Models\Setting::getValue('content.section_features_title', 'Everything You Need to <br>Place Students Globally');
    $sectionEventsTag = App\Models\Setting::getValue('content.section_events_tag', 'Events');
    $sectionEventsTitle = App\Models\Setting::getValue('content.section_events_title', 'Upcoming Trainings & Webinars');
    $sectionTestimonialsTag = App\Models\Setting::getValue('content.section_testimonials_tag', 'Testimonials');
    $sectionTestimonialsTitle = App\Models\Setting::getValue('content.section_testimonials_title', 'What Our Agents Say');
    $sectionLogosTag = App\Models\Setting::getValue('content.section_logos_tag', 'Our Network');
    $sectionLogosTitle = App\Models\Setting::getValue('content.section_logos_title', 'Partner Universities');
@endphp
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
<style>
.gd-hero {
    min-height: 92vh;
    background: linear-gradient(135deg, #0f0828 0%, var(--primary) 45%, var(--secondary) 100%);
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
    padding: 5rem 2rem;
}
.gd-hero-bg-orb {
    position: absolute; border-radius: 50%; filter: blur(80px); pointer-events: none;
}
.gd-hero-bg-orb.o1 { width:500px; height:500px; top:-150px; right:-100px; background:rgba(244,63,94,0.18); }
.gd-hero-bg-orb.o2 { width:350px; height:350px; bottom:-100px; left:30%; background:rgba(6,182,212,0.12); }
.gd-hero-bg-orb.o3 { width:200px; height:200px; top:50%; left:5%; background:rgba(245,158,11,0.08); }
.gd-hero-grid {
    max-width:1200px; margin:0 auto; width:100%;
    display:grid; grid-template-columns:1fr 420px; gap:4rem;
    align-items: center; position:relative; z-index:2;
}
@media (max-width:1024px) { .gd-hero-grid { grid-template-columns:1fr; gap:3rem; } }
.gd-hero-badge {
    display:inline-flex; align-items:center; gap:0.5rem;
    background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2);
    border-radius:50px; padding:0.4rem 1rem; color:rgba(255,255,255,0.85);
    font-size:0.8rem; font-weight:500; margin-bottom:1.5rem; backdrop-filter:blur(10px);
}
.gd-hero-badge .pulse-dot {
    width:8px; height:8px; border-radius:50%; background:#4ade80; animation:pulse-anim 2s infinite;
}
@keyframes pulse-anim { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:0.5; transform:scale(1.4); } }
.gd-hero-heading { font-size:clamp(2.2rem,5vw,3.5rem); font-weight:800; color:#fff; line-height:1.15; margin-bottom:1.25rem; }
.gd-hero-heading .highlight { background:linear-gradient(135deg,#f43f5e,#f59e0b); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
.gd-hero-desc { color:rgba(255,255,255,0.65); font-size:1.05rem; line-height:1.7; margin-bottom:2rem; max-width:520px; }
.gd-hero-ctas { display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:2.5rem; }
.gd-hero-stats { display:flex; gap:2.5rem; flex-wrap:wrap; }
.gd-hero-stat { text-align:center; }
.gd-hero-stat-val { font-size:2rem; font-weight:800; color:#fff; display:block; }
.gd-hero-stat-lbl { font-size:0.78rem; color:rgba(255,255,255,0.5); font-weight:500; }
.gd-login-card {
    background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15);
    border-radius:24px; padding:2.5rem; backdrop-filter:blur(20px); box-shadow:0 20px 60px rgba(0,0,0,0.3);
}
.gd-login-card h3 { font-size:1.4rem; font-weight:700; color:#fff; margin-bottom:0.5rem; }
.gd-login-card p { color:rgba(255,255,255,0.55); font-size:0.85rem; margin-bottom:1.75rem; }
.gd-password-wrapper { position:relative; }
.gd-password-toggle {
    position:absolute; right:12px; top:50%; transform:translateY(-50%);
    background:none; border:none; color:rgba(255,255,255,0.5); cursor:pointer; font-size:1rem; padding:0;
}
.gd-password-toggle:hover { color:rgba(255,255,255,0.9); }
.gd-login-links { display:flex; justify-content:space-between; align-items:center; font-size:0.82rem; }
.gd-login-links a { color:rgba(255,255,255,0.6); text-decoration:none; }
.gd-login-links a:hover { color:rgba(255,255,255,0.9); }
.gd-login-card .form-control {
    background:rgba(255,255,255,0.08) !important; border:1.5px solid rgba(255,255,255,0.15) !important;
    color:#fff !important; padding:0.85rem 1rem;
}
.gd-login-card .form-control:focus { border-color:rgba(255,255,255,0.4) !important; background:rgba(255,255,255,0.12) !important; box-shadow:none !important; }
.gd-login-card .form-control::placeholder { color:rgba(255,255,255,0.3) !important; }
.gd-login-card label { color:rgba(255,255,255,0.7) !important; font-size:0.8rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.4rem; }
.gd-section { padding:5rem 2rem; }
.gd-section-inner { max-width:1200px; margin:0 auto; }
.gd-section-tag {
    display:inline-block; background:rgba(130,11,92,0.1); color:var(--primary);
    border-radius:50px; padding:0.35rem 1rem; font-size:0.78rem; font-weight:700;
    text-transform:uppercase; letter-spacing:0.08em; margin-bottom:1rem;
}
.gd-section-title { font-size:clamp(1.75rem,3vw,2.5rem); font-weight:800; color:var(--text-color); line-height:1.25; margin-bottom:1rem; }
.gd-filter-section { background:var(--secondary); padding:3.5rem 2rem; }
.gd-filter-inner { max-width:1200px; margin:0 auto; }
.gd-filter-inner h2 { font-size:1.6rem; font-weight:700; color:#fff; margin-bottom:0.5rem; }
.gd-filter-inner p { color:rgba(255,255,255,0.6); margin-bottom:1.75rem; font-size:0.9rem; }
.gd-features-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:1.5rem; margin-top:3rem; }
@media (max-width:900px) { .gd-features-grid { grid-template-columns:repeat(2,1fr); } }
@media (max-width:540px) { .gd-features-grid { grid-template-columns:1fr; } }
.gd-feature-card { text-align:center; position:relative; overflow:hidden; padding:1.5rem; }
.gd-feature-card::before {
    content:''; position:absolute; inset:0;
    background:linear-gradient(135deg,var(--secondary),var(--primary));
    opacity:0; transition:opacity 0.3s; z-index:0;
}
.gd-feature-card:hover { transform:translateY(-8px); }
.gd-feature-card:hover::before { opacity:1; }
.gd-feature-card * { position:relative; z-index:1; }
.gd-feature-card:hover .gd-feature-icon, .gd-feature-card:hover h3, .gd-feature-card:hover p { color:#fff !important; }
.gd-feature-icon-wrap {
    width:64px; height:64px; border-radius:18px;
    background:rgba(130,11,92,0.08); display:flex; align-items:center;
    justify-content:center; margin:0 auto 1.25rem;
}
.gd-feature-card:hover .gd-feature-icon-wrap { background:rgba(255,255,255,0.15); }
.gd-feature-icon { font-size:1.6rem; color:var(--primary); }
.gd-feature-card h3 { font-size:1rem; font-weight:700; color:var(--text-color); margin-bottom:0.5rem; }
.gd-feature-card p { font-size:0.85rem; color:var(--text-muted); line-height:1.6; }
.gd-programs-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1.5rem; margin-top:3rem; }
@media (max-width:768px) { .gd-programs-grid { grid-template-columns:1fr; } }
.gd-program-card { position:relative; overflow:hidden; }
.gd-program-card::after {
    content:''; position:absolute; bottom:0; left:0; right:0; height:4px;
    background:linear-gradient(90deg,var(--secondary),var(--primary));
}
.gd-program-icon {
    width:48px; height:48px; border-radius:14px;
    background:linear-gradient(135deg,var(--secondary),var(--primary));
    color:#fff; display:flex; align-items:center; justify-content:center;
    font-size:1.2rem; margin-bottom:1.25rem;
}
.gd-program-card h3 { font-size:1.05rem; font-weight:700; color:var(--text-color); margin-bottom:0.5rem; }
.gd-program-date { font-size:0.8rem; color:var(--text-muted); font-weight:500; margin-bottom:1.25rem; display:flex; align-items:center; gap:0.4rem; }
.gd-testimonials-section { background:linear-gradient(135deg,#0f0828,var(--primary)); padding:5rem 2rem; }
.gd-testimonials-section .gd-section-tag { background:rgba(255,255,255,0.1); color:rgba(255,255,255,0.8); }
.gd-testimonials-section .gd-section-title { color:#fff; }
.gd-testimonial-track { display:grid; grid-template-columns:repeat(3,1fr); gap:1.5rem; margin-top:3rem; }
@media (max-width:900px) { .gd-testimonial-track { grid-template-columns:repeat(2,1fr); } }
@media (max-width:640px) { .gd-testimonial-track { grid-template-columns:1fr; } }
.gd-testimonial-card {
    background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12);
    border-radius:20px; padding:2rem; backdrop-filter:blur(10px);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow-wrap:break-word; word-break:break-word;
}
.gd-testimonial-card:hover { transform:translateY(-6px); box-shadow:0 12px 40px rgba(0,0,0,0.3); }
.gd-testimonial-quote { font-size:2.5rem; color:rgba(244,63,94,0.5); font-family:Georgia,serif; line-height:1; margin-bottom:0.75rem; }
.gd-testimonial-text { color:rgba(255,255,255,0.75); font-size:0.9rem; line-height:1.7; margin-bottom:1.25rem; overflow-wrap:break-word; }
.gd-testimonial-author { display:flex; align-items:center; gap:0.85rem; }
.gd-testimonial-avatar {
    width:40px; height:40px; border-radius:50%;
    background:linear-gradient(135deg,var(--primary),#f43f5e);
    color:#fff; display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:0.9rem; flex-shrink:0;
}
.gd-testimonial-stars { font-size:0.85rem; letter-spacing:2px; }
.gd-testimonial-stars .fa-star.text-muted { opacity:0.25; }
.gd-testimonial-avatar-img { width:40px; height:40px; border-radius:50%; object-fit:cover; flex-shrink:0; }
.gd-testimonial-name { font-size:0.85rem; font-weight:600; color:#fff; }
.gd-testimonial-loc { font-size:0.75rem; color:rgba(255,255,255,0.45); }
.guest-dash section { animation: fadeUp 0.7s ease both; }
@keyframes fadeUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
.guest-dash section:nth-child(2) { animation-delay:0.1s; }
.guest-dash section:nth-child(3) { animation-delay:0.2s; }
.guest-dash section:nth-child(4) { animation-delay:0.3s; }
.guest-dash section:nth-child(5) { animation-delay:0.4s; }
.guest-dash section:nth-child(6) { animation-delay:0.5s; }
.guest-dash section:nth-child(7) { animation-delay:0.6s; }
.guest-dash section:nth-child(8) { animation-delay:0.7s; }
.gd-hero { animation: fadeUp 0.8s ease both; }
.gd-cta-section { padding:5rem 2rem; }
.gd-cta-inner {
    max-width:1200px; margin:0 auto;
    background:linear-gradient(135deg,var(--secondary) 0%,#2d1270 50%,var(--primary) 100%);
    border-radius:28px; padding:4rem;
    display:grid; grid-template-columns:1fr 1fr; gap:3rem; align-items:center;
    position:relative; overflow:hidden;
}
.gd-cta-inner::before {
    content:''; position:absolute; top:-100px; right:-100px;
    width:350px; height:350px; border-radius:50%; background:rgba(255,255,255,0.04);
}
@media (max-width:768px) { .gd-cta-inner { grid-template-columns:1fr; padding:2.5rem; } }
.gd-cta-text { position:relative; z-index:1; }
.gd-cta-text h2 { font-size:2.25rem; font-weight:800; color:#fff; margin-bottom:1rem; }
.gd-cta-text p { color:rgba(255,255,255,0.65); font-size:1rem; line-height:1.7; margin-bottom:2rem; }
.gd-cta-image img { width:100%; border-radius:16px; position:relative; z-index:1; }
.gd-unis-section { padding:4rem 2rem; background:var(--bg-card); }
.gd-uni-logos-track { display:flex; gap:2rem; flex-wrap:wrap; justify-content:center; margin-top:2.5rem; }
.gd-uni-logo { height:60px; display:flex; align-items:center; justify-content:center; filter:grayscale(100%); opacity:0.55; transition:all 0.3s; }
.gd-uni-logo:hover { filter:grayscale(0%); opacity:1; transform:scale(1.08); }
.gd-uni-logo img { height:100%; object-fit:contain; max-width:160px; }
.gd-banner-section { padding:2rem 0; }
.gd-banner-section img { width:100%; border-radius:20px; }
.countries-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; }
@media (max-width:768px) { .countries-grid { grid-template-columns:repeat(2,1fr); } }
@media (max-width:480px) { .countries-grid { grid-template-columns:1fr; } }
.country-card { position:relative; border-radius:16px; overflow:hidden; cursor:pointer; text-decoration:none; display:block; aspect-ratio:4/3; transition:transform 0.3s ease; }
.country-card:hover { transform:translateY(-5px); }
.country-card img { width:100%; height:100%; object-fit:cover; transition:transform 0.5s ease; }
.country-card:hover img { transform:scale(1.1); }
.country-card .overlay { position:absolute; bottom:0; left:0; right:0; background:linear-gradient(to top,rgba(0,0,0,0.8),transparent); padding:1rem; text-align:center; }
.country-card .overlay h3 { color:#fff; font-size:1rem; font-weight:600; margin:0; }
.etype-badge { font-size:0.65rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; padding:0.2rem 0.65rem; border-radius:20px; margin-bottom:0.75rem; display:inline-block; }
.etype-training { background:#e0f2fe; color:#0369a1; }
.etype-webinar { background:#fce7f3; color:#9d174d; }
.etype-seminar { background:#ede9fe; color:#5b21b6; }
@media (max-width: 991.98px) {
    .guest-header .nav-links,
    .guest-header .auth-buttons {
        width: 100% !important;
        justify-content: center !important;
        padding: 0.25rem 0 !important;
    }
}
</style>
@endpush

@section('content')
<div class="guest-dash">

    {{-- ───── HERO ───── --}}
    <section class="gd-hero">
        <div class="gd-hero-bg-orb o1"></div>
        <div class="gd-hero-bg-orb o2"></div>
        <div class="gd-hero-bg-orb o3"></div>
        <div class="gd-hero-grid">
            <div class="gd-hero-left">
                <div class="gd-hero-badge">
                    <div class="pulse-dot"></div>
                    {!! $heroBadge !!}
                </div>
                <h1 class="gd-hero-heading">{!! $heroTitle !!}</h1>
                <p class="gd-hero-desc">{{ $heroSubtitle }}</p>
                <div class="gd-hero-ctas">
                    <a href="{{ $heroBtn1Link }}" class="btn btn-light">
                        <i class="fa fa-user-plus"></i> {{ $heroBtn1Text }}
                    </a>
                    <a href="{{ $heroBtn2Link }}" class="btn btn-outline-light">
                        <i class="fa fa-university"></i> {{ $heroBtn2Text }}
                    </a>
                </div>
                <div class="gd-hero-stats">
                    <div class="gd-hero-stat">
                        <span class="gd-hero-stat-val">{{ $uniCount }}+</span>
                        <span class="gd-hero-stat-lbl">{{ $heroStat1Label }}</span>
                    </div>
                    <div class="gd-hero-stat">
                        <span class="gd-hero-stat-val">{{ $countryCount }}+</span>
                        <span class="gd-hero-stat-lbl">{{ $heroStat2Label }}</span>
                    </div>
                    <div class="gd-hero-stat">
                        <span class="gd-hero-stat-val">{{ $courseCount }}+</span>
                        <span class="gd-hero-stat-lbl">{{ $heroStat3Label }}</span>
                    </div>
                </div>
            </div>

            {{-- LOGIN CARD --}}
            <div class="gd-login-card">
                <h3>{{ $heroFormTitle }}</h3>
                <p>{{ $heroFormDescription }}</p>
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
                        <a href="{{ route('guest.enquiries.create') }}" class="border-bottom">Forgot password?</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    {{-- ───── FILTER ───── --}}
    <section class="gd-filter-section">
        <div class="gd-filter-inner">
            <h2>{{ $sectionFilterTitle }}</h2>
            <p>{!! $sectionFilterDesc !!}</p>
            @include('partials.uni_filter')
        </div>
    </section>

    {{-- ───── FEATURES ───── --}}
    @if(count($features))
    <section class="gd-section">
        <div class="gd-section-inner">
            <div style="text-align:center;max-width:600px;margin:0 auto;">
                <div class="gd-section-tag">{{ $sectionFeaturesTag }}</div>
                <h2 class="gd-section-title">{!! $sectionFeaturesTitle !!}</h2>
            </div>
            <div class="gd-features-grid">
                @foreach($features as $f)
                <div class="gd-feature-card card border-0 shadow-sm">
                    <div class="gd-feature-icon-wrap"><i class="fa {{ $f['icon'] ?? 'fa-star' }} gd-feature-icon"></i></div>
                    <h3>{{ $f['title'] ?? '' }}</h3>
                    <p>{{ $f['text'] ?? '' }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ───── BANNERS ───── --}}
    @if(count($banners))
    <div class="gd-banner-section">
        <div class="gd-section-inner">
            @foreach($banners as $banner)
            <img src="{{ \App\Models\Setting::resolveImageUrl($banner['image'] ?? '') }}" alt="{{ $banner['title'] ?? 'Banner' }}" class="mb-3">
            @endforeach
        </div>
    </div>
    @endif

    {{-- ───── EVENTS ───── --}}
    @if(count($events))
    <section class="gd-section" style="background:var(--bg-card);">
        <div class="gd-section-inner">
            <div style="text-align:center;max-width:600px;margin:0 auto;">
                <div class="gd-section-tag">{{ $sectionEventsTag }}</div>
                <h2 class="gd-section-title">{!! $sectionEventsTitle !!}</h2>
            </div>
            <div class="gd-programs-grid">
                @foreach($events as $event)
                <div class="gd-program-card card border-0 shadow-sm">
                    @if(!empty($event['image']))
                    <img src="{{ \App\Models\Setting::resolveImageUrl($event['image']) }}" alt="{{ $event['title'] ?? '' }}"
                         style="width:100%;height:160px;object-fit:cover;border-radius:16px 16px 0 0;">
                    @else
                    <div class="gd-program-icon mx-3 mt-3"><i class="fas {{ $event['icon'] ?? 'fa-graduation-cap' }}"></i></div>
                    @endif
                    <div class="px-3 pt-3">
                        @if(!empty($event['event_type']))
                        <span class="etype-badge etype-{{ strtolower($event['event_type']) }}">{{ $event['event_type'] }}</span>
                        @endif
                        <h3>{{ $event['title'] ?? '' }}</h3>
                        @if(!empty($event['date']))
                        @php $eventDate = \Carbon\Carbon::parse($event['date'])->format('M d, Y'); @endphp
                        <div class="gd-program-date"><i class="fas fa-calendar-alt"></i> {{ $eventDate }}</div>
                        @endif
                        <p class="small text-muted">{{ $event['description'] ?? '' }}</p>
                    </div>
                    @if(!empty($event['link']))
                    <div class="px-3 pb-3">
                        <a href="{{ $event['link'] }}" class="btn btn-secondary btn-sm">Register Now</a>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ───── COUNTRIES ───── --}}
    @if(count($countriesData))
    <section class="gd-section">
        <div class="gd-section-inner">
            <div style="text-align:center;max-width:600px;margin:0 auto;">
                <div class="gd-section-tag">Global Reach</div>
                <h2 class="gd-section-title">{!! $countriesTitle !!}</h2>
            </div>
            <div class="countries-grid mt-4">
                @foreach($countriesData as $c)
                <a href="{{ route('guest.universities.index', ['country' => $c['name'] ?? '']) }}" class="country-card">
                    <img src="{{ \App\Models\Setting::resolveImageUrl($c['flag'] ?? ($c['image'] ?? '')) }}" alt="{{ $c['name'] ?? '' }}" loading="lazy">
                    <div class="overlay"><h3>{{ $c['name'] ?? '' }}</h3></div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ───── TESTIMONIALS ───── --}}
    <section class="gd-testimonials-section">
        <div class="gd-section-inner">
            <div style="text-align:center;margin-bottom:0.5rem;">
                <div class="gd-section-tag">{{ $sectionTestimonialsTag }}</div>
                <h2 class="gd-section-title">{!! $sectionTestimonialsTitle !!}</h2>
            </div>
            @if($testimonials->count())
            <div class="gd-testimonial-track">
                @foreach($testimonials as $t)
                <div class="gd-testimonial-card">
                    <div class="gd-testimonial-quote">"</div>
                    <div class="gd-testimonial-stars mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star{{ $i <= ($t->rating ?? 5) ? ' text-warning' : ' text-muted' }}"></i>
                        @endfor
                    </div>
                    <p class="gd-testimonial-text">{{ $t->content }}</p>
                    <div class="gd-testimonial-author">
                        @if($t->image)
                            <img src="{{ $t->image_url }}" alt="{{ $t->name }}" class="gd-testimonial-avatar-img">
                        @else
                            <div class="gd-testimonial-avatar">{{ strtoupper(substr($t->name, 0, 1)) }}</div>
                        @endif
                        <div>
                            <div class="gd-testimonial-name">{{ $t->name }}</div>
                            <div class="gd-testimonial-loc">{{ $t->location ?? '' }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-5">
                <p class="text-white-50">Testimonials coming soon.</p>
            </div>
            @endif
        </div>
    </section>

    {{-- ───── CTA ───── --}}
    <section class="gd-cta-section">
        <div class="gd-cta-inner">
            <div class="gd-cta-text">
                <h2>{{ $ctaTitle }}</h2>
                <p>{{ $ctaDesc }}</p>
                <a href="{{ $ctaBtnLink }}" class="btn btn-success btn-lg">
                    <i class="fa fa-handshake me-2"></i> {{ $ctaBtnText }}
                </a>
            </div>
            <div class="gd-cta-image">
                <img src="{{ $ctaImage }}" alt="Partner with Idea">
            </div>
        </div>
    </section>

    {{-- ───── UNIVERSITY LOGOS ───── --}}
    <section class="gd-unis-section">
        <div style="max-width:1200px;margin:0 auto;text-align:center;">
            <div class="gd-section-tag">{{ $sectionLogosTag }}</div>
            <h2 class="gd-section-title" style="font-size:1.75rem;">{!! $sectionLogosTitle !!}</h2>
            @if(File::exists(storage_path('app/public/uni_logo')) && count(File::files(storage_path('app/public/uni_logo'))))
            <div class="gd-uni-logos-track">
                @foreach(File::files(storage_path('app/public/uni_logo')) as $file)
                @php $fileName = basename($file); $alt = pathinfo($fileName, PATHINFO_FILENAME); @endphp
                <div class="gd-uni-logo">
                    <img src="{{ asset('storage/uni_logo/' . $fileName) }}" alt="{{ ucfirst($alt) }}">
                </div>
                @endforeach
            </div>
            @else
            <p class="text-muted">University logos will appear here once uploaded.</p>
            @endif
        </div>
    </section>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
AOS.init({ duration: 900, once: true, offset: 60 });
</script>
@endpush
