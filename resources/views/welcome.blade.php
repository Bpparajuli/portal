@extends('layouts.app')
@section('title', 'Guest Dashboard')

@section('content')
<div class="dash-dashboard">
    <!-- Hero Section -->
    <section class="dash-hero">
        <div class="dash-hero-text" data-aos="fade-right">
            <h1>Welcome to Idea Consultancy Agent Portal</h1>
            <p class="dash-lead">Manage students, agents, and applications easily. Login or register to get started.</p>
            <div class="dash-hero-buttons">
                <a href="{{ route('auth.register') }}" class="dash-btn dash-btn-register"> <i class="fa fa-user-plus"></i>
                    Register</a>
                <a href="#" class="dash-btn dash-btn-login"> <i class="fa fa-university"></i>
                    Find Universities</a>
            </div>
        </div>
        <div class="dash-login-form" data-aos="fade-left">
            <form action="{{ route('auth.login.post') }}" method="POST">
                @csrf
                <div class="dash-form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus>
                    @error('email')<span class="dash-error">{{ $message }}</span>@enderror
                </div>

                <div class="dash-form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                    @error('password')<span class="dash-error">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="dash-btn-login-submit">Login</button>

                <p class="dash-register-link">
                    Don't have an account? <a href="{{ route('register') }}">Register here</a>
                </p>
                <div><a href="{{ route('auth.contact') }}">Forgot Password?</a></div>
            </form>
        </div>
    </section>

    <!-- Features Section -->
    <section class="dash-features" data-aos="fade-up">
        <div class="dash-feature-card">
            <i class="fa fa-user-graduate dash-icon"></i>
            <h3>Students Management</h3>
            <p>Track and manage student applications efficiently.</p>
        </div>
        <div class="dash-feature-card">
            <i class="fa fa-university dash-icon"></i>
            <h3>University Applications</h3>
            <p>Connect with top universities and handle applications seamlessly.</p>
        </div>
        <div class="dash-feature-card">
            <i class="fa fa-chart-line dash-icon"></i>
            <h3>Status & Progress</h3>
            <p>Monitor performance and gain insights into operations.</p>
        </div>
    </section>

    <!-- Countries Section -->
    <section class="countries-section">
        <h3 class="section-title">Countries We Are Working With</h3>

        <div class="countries-custom-grid">
            <!-- Row 1 -->

            <a href="#" class="country-card double">
                <img src="https://images.pexels.com/photos/109629/pexels-photo-109629.jpeg?auto=compress&cs=tinysrgb&w=600" alt="Germany" />
                <div class="overlay">
                    <h3>Germany</h3>
                </div>
            </a>
            <a href="#" class="country-card single">
                <img src="https://images.pexels.com/photos/356844/pexels-photo-356844.jpeg?auto=compress&cs=tinysrgb&w=600" alt="USA" />
                <div class="overlay">
                    <h3>USA</h3>
                </div>
            </a>
            <a href="#" class="country-card single">
                <img src="https://images.pexels.com/photos/51363/london-tower-bridge-bridge-monument-51363.jpeg?auto=compress&cs=tinysrgb&w=900" alt="UK" />
                <div class="overlay">
                    <h3>UK</h3>
                </div>
            </a>
            <!-- Row 2 -->
            <a href="#" class="country-card single">
                <img src="https://images.pexels.com/photos/1878293/pexels-photo-1878293.jpeg" alt="Spain" />
                <div class="overlay">
                    <h3>Australia</h3>
                </div>
            </a>
            <a href="#" class="country-card double">
                <img src="https://images.pexels.com/photos/325193/pexels-photo-325193.jpeg?auto=compress&cs=tinysrgb&w=600" alt="Dubai" />
                <div class="overlay">
                    <h3>Dubai</h3>
                </div>
            </a>
            <a href="#" class="country-card single">
                <img src="https://images.pexels.com/photos/548077/pexels-photo-548077.jpeg?auto=compress&cs=tinysrgb&w=600" alt="France" />
                <div class="overlay">
                    <h3>Malta</h3>
                </div>
            </a>
        </div>
    </section>

    <!-- Programs Section -->
    <section class="dash-programs-section" data-aos="fade-up">
        <h2>Upcoming Trainings & Webinars</h2>
        <div class="dash-programs-grid">
            <div class="dash-program-card">
                <h3>Student Management Training</h3>
                <p>Date: 2025-09-15</p>
                <a href="#" class="btn btn-primary">Register</a>
            </div>
            <div class="dash-program-card">
                <h3>University Applications Webinar</h3>
                <p>Date: 2025-09-20</p>
                <a href="#" class="btn btn-primary">Register</a>
            </div>
            <div class="dash-program-card">
                <h3>Visa Process Guidance For Germany</h3>
                <p>Date: 2025-09-25</p>
                <a href="#" class="btn btn-primary">Register</a>
            </div>
        </div>
    </section>


    {{-- ⭐ Testimonials --}}
    <section class="dash-testimonials">
        <h2>What Our Clients Say</h2>
        <div class="testimonial-slider-container">
            <button class="slider-btn prev">&#10094;</button>
            <div class="dash-testimonial-slider" id="testimonial-slider">
                <div class="dash-testimonial-card">
                    <p>"Amazing support! My application process was super smooth."</p>
                    <h4>- John Doe</h4>
                </div>
                <div class="dash-testimonial-card">
                    <p>"The team guided me to the best universities."</p>
                    <h4>- Priya Sharma</h4>
                </div>
                <div class="dash-testimonial-card">
                    <p>"I loved the personalized approach and quick responses."</p>
                    <h4>- Ahmed Ali</h4>
                </div>
            </div>
            <button class="slider-btn next">&#10095;</button>
        </div>
    </section>
    <!-- Call To Action Section -->
    <section class="dash-cta">
        <div class="cta-container">
            <div class="cta-left-section">
                <img src="{{ asset('images/banner-1.png') }}" alt="banner-img" class="cta-image">
            </div>
            <div class="cta-right-section">
                <h2>Partner With Us Today!</h2>
                <p>Grow your business with our platform. Become an official agent and access all tools.</p>
                <a href="#" class="dash-btn-cta">Become a Partner</a>
            </div>
        </div>
    </section>

    {{-- 🎓 University Logo Slider --}}
    <section class="dash-universities">
        <h2>Our Partner Universities</h2>
        <div class="dash-university-slider" id="uni-slider">
            <div class="dash-university-logo"><img src="/images/uni_logo/anu.jpg" alt="ANU"></div>
            <div class="dash-university-logo"><img src="/images/uni_logo/cambridge_logo.png" alt="Cambridge"></div>
            <div class="dash-university-logo"><img src="/images/uni_logo/eth.png" alt="ETH"></div>
            <div class="dash-university-logo"><img src="/images/uni_logo/harvard.png" alt="Harvard"></div>
            <div class="dash-university-logo"><img src="/images/uni_logo/melbourne_logo.png" alt="Melbourne"></div>
            <div class="dash-university-logo"><img src="/images/uni_logo/mit.jpg" alt="MIT"></div>
            <div class="dash-university-logo"><img src="/images/uni_logo/nus_logo.png" alt="NUS"></div>
            <div class="dash-university-logo"><img src="/images/uni_logo/oxford_logo.png" alt="Oxford"></div>
            <div class="dash-university-logo"><img src="/images/uni_logo/toronto_logo.png" alt="Toronto"></div>
            <div class="dash-university-logo"><img src="/images/uni_logo/ubc_logo.png" alt="UBC"></div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<!-- AOS -->
<link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 1000
        , once: true
    });

</script>
@endsection
