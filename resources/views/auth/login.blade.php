@extends('layouts.app')
@section('title', 'Login')

@section('content')
<div class="login-page d-flex align-items-center justify-content-center py-2">

    <div class="row w-100 g-0 shadow-lg rounded-4 overflow-hidden login-container">
        {{-- Left: Login Form --}}
        <div class="col-lg-5 bg-white p-5 d-flex flex-column justify-content-center">
            <h2 class="fw-bold mb-4 text-center">Welcome Back</h2>
            <p class="text-muted text-center mb-4">Login to continue to your dashboard</p>

            <form action="{{ route('auth.login.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 mt-2">
                    <i class="fas fa-sign-in-alt me-2"></i> Login
                </button>

                <div class="d-flex justify-content-between mt-3">
                    <small><a href="{{ route('auth.contact') }}">Forgot Password?</a></small>
                    <small>Don't have an account? <a href="{{ route('register') }}">Register</a></small>
                </div>
            </form>
        </div>

        {{-- Right: Countries Showcase --}}
        <div class="col-lg-7 bg-light p-4 d-flex flex-column justify-content-center">
            <h3 class="fw-bold text-center mb-4">Countries We Work For</h3>

            {{-- Desktop Grid --}}
            <div class="countries-grid d-none d-lg-grid">
                @foreach ([
                ['Germany', 'https://images.pexels.com/photos/109629/pexels-photo-109629.jpeg?auto=compress&cs=tinysrgb&w=600'],
                ['USA', 'https://images.pexels.com/photos/356844/pexels-photo-356844.jpeg?auto=compress&cs=tinysrgb&w=600'],
                ['UK', 'https://images.pexels.com/photos/51363/london-tower-bridge-bridge-monument-51363.jpeg?auto=compress&cs=tinysrgb&w=900'],
                ['Australia', 'https://images.pexels.com/photos/1878293/pexels-photo-1878293.jpeg'],
                ['Dubai', 'https://images.pexels.com/photos/325193/pexels-photo-325193.jpeg?auto=compress&cs=tinysrgb&w=600'],
                ['Malta', 'https://images.pexels.com/photos/548077/pexels-photo-548077.jpeg?auto=compress&cs=tinysrgb&w=600'],
                ] as [$country, $image])
                <a href="#" class="country-card">
                    <img src="{{ $image }}" alt="{{ $country }}" />
                    <div class="overlay">
                        <h3>{{ $country }}</h3>
                    </div>
                </a>
                @endforeach
            </div>

            {{-- Mobile Carousel --}}
            <div id="countriesCarousel" class="carousel slide d-lg-none" data-bs-ride="carousel">
                <div class="carousel-inner">
                    @foreach ([
                    ['Germany', 'https://images.pexels.com/photos/109629/pexels-photo-109629.jpeg?auto=compress&cs=tinysrgb&w=600'],
                    ['USA', 'https://images.pexels.com/photos/356844/pexels-photo-356844.jpeg?auto=compress&cs=tinysrgb&w=600'],
                    ['UK', 'https://images.pexels.com/photos/51363/london-tower-bridge-bridge-monument-51363.jpeg?auto=compress&cs=tinysrgb&w=900'],
                    ['Australia', 'https://images.pexels.com/photos/1878293/pexels-photo-1878293.jpeg'],
                    ['Dubai', 'https://images.pexels.com/photos/325193/pexels-photo-325193.jpeg?auto=compress&cs=tinysrgb&w=600'],
                    ['Malta', 'https://images.pexels.com/photos/548077/pexels-photo-548077.jpeg?auto=compress&cs=tinysrgb&w=600'],
                    ] as $index => [$country, $image])
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <div class="country-card w-100" style="height:250px">
                            <img src="{{ $image }}" class="d-block w-100" alt="{{ $country }}" />
                            <div class="overlay">
                                <h3>{{ $country }}</h3>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <button class="carousel-control-prev" type="button" data-bs-target="#countriesCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#countriesCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
