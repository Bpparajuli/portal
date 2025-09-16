@extends('layouts.app')
@section('title', 'Guest Dashboard')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

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
                <a href="{{ route('guest.universities.index') }}" class=" dash-btn dash-btn-login"> <i class="fa fa-university"></i>
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

    {{-- Filter Section --}}
    <section class="uni-filter">
        @include('partials.uni_filter')
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
            <h3>Status, Progress & Updates</h3>
            <p>Monitor performance and gain insights into operations.</p>
        </div>
    </section>

    <!-- Countries Section -->
    <section class="countries-section">
        <h3 class="section-title">Countries We Are Working For</h3>

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
        <h2 class="section-title">Upcoming Trainings & Webinars</h2>
        <div class="dash-programs-grid">
            <div class="dash-program-card">
                <h3>Student Management Training</h3>
                <p>Date: 2025-09-15</p>
                <a href="#" class="dash-btn-blue">Register</a>
            </div>
            <div class="dash-program-card">
                <h3>University Applications Webinar</h3>
                <p>Date: 2025-09-20</p>
                <a href="#" class="dash-btn-blue">Register</a>
            </div>
            <div class="dash-program-card">
                <h3>Visa Process Guidance For Germany</h3>
                <p>Date: 2025-09-25</p>
                <a href="#" class="dash-btn-blue">Register</a>
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
                    <p>"The way documents are handled in IDEA’s portal is very smooth and hassle-free. Everything is organized, making the application process easy."</p>
                    <h4>- Ramesh Kumar Thapa, Kathmandu, Nepal</h4>
                </div>
                <div class="dash-testimonial-card">
                    <p>"Finding courses and program details is really simple. The portal makes it easy to compare universities and options without any confusion."</p>
                    <h4>- Anisha Gurung, Pokhara, Nepal</h4>
                </div>
                <div class="dash-testimonial-card">
                    <p>"The IDEA team members are very supportive. They guide us at every step and are always available to answer questions."</p>
                    <h4>- Bipin Shrestha, Lalitpur, Nepal</h4>
                </div>
                <div class="dash-testimonial-card">
                    <p>"The training sessions organized by IDEA were extremely helpful. They gave practical insights and prepared us well for the application and visa process."</p>
                    <h4>- Sabina KC, Bhaktapur, Nepal</h4>
                </div>
                <div class="dash-testimonial-card">
                    <p>"Germany is such a great study destination, and IDEA has promoted it effectively. We are receiving complete support for our Germany applications."</p>
                    <h4>- Manoj Adhikari, Dharan, Nepal</h4>
                </div>
                <div class="dash-testimonial-card">
                    <p>"This inbuilt portal by IDEA is fantastic. It is user-friendly, intuitive, and makes navigating courses and universities very easy."</p>
                    <h4>- Priya Thapa Magar, Chitwan, Nepal</h4>
                </div>
                <div class="dash-testimonial-card">
                    <p>"The portal is simple to use, yet it offers many advanced functions. From checking eligibility to uploading documents, everything is seamless."</p>
                    <h4>- Suman Lama, Ilam, Nepal</h4>
                </div>
                <div class="dash-testimonial-card">
                    <p>"We are grateful that courses, university details, and other important info are visible on the homepage without even logging in. This is excellent service!"</p>
                    <h4>- Ritu Shrestha, Kathmandu, Nepal</h4>
                </div>
                <div class="dash-testimonial-card">
                    <p>"IDEA’s training and support system is top-notch. Every session adds value and helps in understanding the application process better."</p>
                    <h4>- Dev Raj Bhatt, Kailali, Nepal</h4>
                </div>
                <div class="dash-testimonial-card">
                    <p>"The IDEA team communicates clearly and promptly. They are helpful, professional, and make the whole study abroad process smooth."</p>
                    <h4>- Sunita Karki, Pokhara, Nepal</h4>
                </div>
                <div class="dash-testimonial-card">
                    <p>"The portal allows us to track our application progress easily. Its organized layout and simple navigation make it very convenient."</p>
                    <h4>- Pramod Kunwar, Lalitpur, Nepal</h4>
                </div>
                <div class="dash-testimonial-card">
                    <p>"We love that IDEA’s portal combines everything in one place—course info, university details, and support resources—making it very efficient and reliable."</p>
                    <h4>- Bishal Pokharel, Kathmandu, Nepal</h4>
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
        <h2 class="section-title">Our Partner Universities</h2>
        <div class="dash-university-slider" id="uni-slider">
            @foreach (File::files(public_path('images/uni_logo')) as $file)
            @php
            $fileName = basename($file);
            $alt = pathinfo($fileName, PATHINFO_FILENAME);
            @endphp
            <div class="dash-university-logo">
                <img src="{{ asset('images/uni_logo/' . $fileName) }}" alt="{{ ucfirst($alt) }}">
            </div>
            @endforeach
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
