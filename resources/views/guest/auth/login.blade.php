@extends('layouts.guest')
@section('title', 'Login - Idea Consultancy')

@section('content')
    <style>
        /* ============================================================
                                   LOGIN PAGE – ONLY UNIQUE/SPECIFIC STYLES
                                   Everything else (btn, card, input, variables, shadows) comes from styles.css
                                ============================================================ */

        /* === 1. Login Page Container === */
        .login-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f0828 0%, #1a0262 45%, #820b5c 100%);
            padding: 2rem;
        }

        .login-container {
            max-width: 1400px;
            margin: 0 auto;
            background: transparent;
            backdrop-filter: blur(10px);
        }

        /* === 2. Login Form Panel (Glass Morphism) === */
        .login-form-panel {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 24px 0 0 24px;
            padding: 2.5rem;
        }

        @media (max-width: 992px) {
            .login-form-panel {
                border-radius: 24px 24px 0 0;
            }
        }

        .login-form-panel h2 {
            font-family: 'Sora', sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            margin-bottom: 1.75rem;
        }

        /* === 3. Form Input Overrides (Glass Theme) === */
        .login-form-panel .form-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .login-form-panel .form-control {
            background: rgba(255, 255, 255, 0.08) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.15) !important;
            color: #fff !important;
            padding: 0.85rem 1rem;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .login-form-panel .form-control:focus {
            border-color: rgba(255, 255, 255, 0.4) !important;
            background: rgba(255, 255, 255, 0.12) !important;
            box-shadow: none !important;
        }

        .login-form-panel .form-control::placeholder {
            color: rgba(255, 255, 255, 0.3) !important;
        }

        .login-form-panel .form-control.is-invalid {
            border-color: #f87171 !important;
            background: rgba(239, 68, 68, 0.1) !important;
        }

        .invalid-feedback {
            color: #fca5a5;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        /* Password wrapper for toggle */
        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 0.2s;
            padding: 0;
        }

        .password-toggle:hover {
            color: rgba(255, 255, 255, 0.9);
        }

        /* === 4. Login Button === */
        .login-btn {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, #820b5c, #be185d);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(130, 11, 92, 0.45);
        }

        /* === 5. Login Links === */
        .login-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            margin-top: 1.25rem;
        }

        .login-links a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            transition: color 0.2s;
        }

        .login-links a:hover {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: underline;
        }

        /* === 6. Countries Panel === */
        .countries-panel {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0 24px 24px 0;
            padding: 2rem;
        }

        @media (max-width: 992px) {
            .countries-panel {
                border-radius: 0 0 24px 24px;
            }
        }

        .countries-panel h3 {
            font-family: 'Sora', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        /* Countries Grid */
        .countries-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .country-card {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            cursor: pointer;
            text-decoration: none;
            display: block;
            aspect-ratio: 4/3;
            transition: transform 0.3s ease;
        }

        .country-card:hover {
            transform: translateY(-5px);
        }

        .country-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .country-card:hover img {
            transform: scale(1.1);
        }

        .country-card .overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
            padding: 1rem;
            text-align: center;
        }

        .country-card .overlay h3 {
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        /* Carousel for mobile */
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: rgba(130, 11, 92, 0.7);
            border-radius: 50%;
            padding: 1.5rem;
        }


        /* === 8. Mobile Responsive === */
        @media (max-width: 576px) {
            .login-page {
                padding: 1rem;
            }

            .login-form-panel {
                padding: 1.5rem;
            }

            .login-form-panel h2 {
                font-size: 1.5rem;
            }

            .countries-panel {
                padding: 1.5rem;
            }

            .countries-panel h3 {
                font-size: 1.25rem;
            }

            .login-links {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
        }
    </style>

    <div class="login-page d-flex align-items-center justify-content-center">
        <div class="row w-100 g-0 overflow-hidden login-container rounded-4">

            {{-- Left: Login Form (Glass Morphism) --}}
            <div class="col-lg-4 login-form-panel d-flex flex-column justify-content-center">
                <h2 class="fw-bold text-center">Welcome Back</h2>
                <p class="text-center login-subtitle">Login to continue to your dashboard</p>

                <form action="{{ route('auth.login.post') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email"
                            class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                            placeholder="you@example.com" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password"
                                class="form-control @error('password') is-invalid @enderror" placeholder="••••••••"
                                required>
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="login-btn">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </button>

                    <div class="login-links">
                        <small><a href="{{ route('guest.enquiries.create') }}"><i class="fas fa-question-circle me-1"></i> Forgot
                                Password?</a></small>
                        <small>Don't have an account? <a href="{{ route('register') }}">Register</a></small>
                    </div>
                </form>
            </div>

            {{-- Right: Countries Showcase --}}
            <div class="col-lg-8 countries-panel d-flex flex-column justify-content-center">
                <h3><i class="fas fa-globe-americas me-2"></i> Countries We Work For</h3>

                {{-- Desktop Grid (3 columns) --}}
                <div class="countries-grid d-none d-lg-grid">
                    @foreach ([['Germany', 'https://images.pexels.com/photos/109629/pexels-photo-109629.jpeg?auto=compress&cs=tinysrgb&w=600'], ['USA', 'https://images.pexels.com/photos/356844/pexels-photo-356844.jpeg?auto=compress&cs=tinysrgb&w=600'], ['UK', 'https://images.pexels.com/photos/51363/london-tower-bridge-bridge-monument-51363.jpeg?auto=compress&cs=tinysrgb&w=900'], ['Australia', 'https://images.pexels.com/photos/1878293/pexels-photo-1878293.jpeg?auto=compress&cs=tinysrgb&w=600'], ['Dubai', 'https://images.pexels.com/photos/325193/pexels-photo-325193.jpeg?auto=compress&cs=tinysrgb&w=600'], ['Malta', 'https://images.pexels.com/photos/548077/pexels-photo-548077.jpeg?auto=compress&cs=tinysrgb&w=600']] as [$country, $image])
                        <a href="#" class="country-card">
                            <img src="{{ $image }}" alt="{{ $country }}" loading="lazy">
                            <div class="overlay">
                                <h3>{{ $country }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- Mobile Carousel (swipeable) --}}
                <div id="countriesCarousel" class="carousel slide d-lg-none" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach ([['Germany', 'https://images.pexels.com/photos/109629/pexels-photo-109629.jpeg?auto=compress&cs=tinysrgb&w=600'], ['USA', 'https://images.pexels.com/photos/356844/pexels-photo-356844.jpeg?auto=compress&cs=tinysrgb&w=600'], ['UK', 'https://images.pexels.com/photos/51363/london-tower-bridge-bridge-monument-51363.jpeg?auto=compress&cs=tinysrgb&w=900'], ['Australia', 'https://images.pexels.com/photos/1878293/pexels-photo-1878293.jpeg?auto=compress&cs=tinysrgb&w=600'], ['Dubai', 'https://images.pexels.com/photos/325193/pexels-photo-325193.jpeg?auto=compress&cs=tinysrgb&w=600'], ['Malta', 'https://images.pexels.com/photos/548077/pexels-photo-548077.jpeg?auto=compress&cs=tinysrgb&w=600']] as $index => [$country, $image])
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <div class="country-card w-100" style="aspect-ratio: 16/9;">
                                    <img src="{{ $image }}" class="d-block w-100 h-100" alt="{{ $country }}"
                                        style="object-fit: cover;">
                                    <div class="overlay">
                                        <h3>{{ $country }}</h3>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button class="carousel-control-prev" type="button" data-bs-target="#countriesCarousel"
                        data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#countriesCarousel"
                        data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
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
                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });
            }
        });
    </script>
@endsection
