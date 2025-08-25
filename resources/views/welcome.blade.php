@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="text-center mt-5 mb-4">
    <h1 class="mb-3">Welcome to Idea Consultancy Agent Portal</h1>
    <p class="lead">Register or login to manage agents, students, and applications.</p>
    <div class="mt-4 row justify-content-center">
        <div class="col-lg-2 col-md-4 col-sm-4 m-2">
            <a href="{{ route('login') }}" class="btn bg-success">Login</a>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-4 m-2">
            <a href="{{ route('register') }}" class="btn bg-gray-500 ">Register</a>
        </div>
    </div>
</div>
<div class="main-div d-flex bg-secondary justify-content-between align-items-center">
    <div class="col-md-5 left-section">
        <img src="{{ asset('images/banner-1.png') }}" alt="banner-img">
    </div>
    <div class="right-section col-md-7 d-flex p-2 align-items-center gap-3">
        <div class="col-xl-8 col-lg-7 col-md-7 text-white">
            <h4>Grow your business 10x with MAKE IT IN ABROAD</h4>
            <p>Create applications within 30 mins</p>
        </div>
        <div class="col-xl-4 col-lg-5 col-md-5">
            <button class="bg-white p-2 rounded border-0">
                <a href="#" class="text-dark text-decoration-none">
                    <i class="fa fa-user-tie"></i> Become a partner
                </a>
            </button>
        </div>
    </div>
</div>

<h3 class="bg-gray-300 text-dark bold p-3 text-center m-2">Countries We Are Working With</h3>
<div class="activity-container">
    <div class="image-container img-one">
        <img src="https://images.pexels.com/photos/356844/pexels-photo-356844.jpeg?auto=compress&cs=tinysrgb&w=600" alt="tennis" />
        <div class="overlay">
            <h3>USA</h3>
        </div>
    </div>

    <div class="image-container img-two">
        <img src="https://images.pexels.com/photos/51363/london-tower-bridge-bridge-monument-51363.jpeg?auto=compress&cs=tinysrgb&w=600" alt="hiking" />
        <div class="overlay">
            <h3>UK</h3>
        </div>
    </div>

    <div class="image-container img-three">
        <img src="https://images.pexels.com/photos/109629/pexels-photo-109629.jpeg?auto=compress&cs=tinysrgb&w=600" alt="hiking" />
        <div class="overlay">
            <h3>Germany</h3>
        </div>
    </div>

    <div class="image-container img-four">
        <img src="https://images.pexels.com/photos/548077/pexels-photo-548077.jpeg?auto=compress&cs=tinysrgb&w=600" alt="cycling" />
        <div class="overlay">
            <h3>France</h3>
        </div>
    </div>

    <div class="image-container img-five">
        <img src="https://images.pexels.com/photos/3254729/pexels-photo-3254729.jpeg?auto=compress&cs=tinysrgb&w=600" alt="yoga" />
        <div class="overlay">
            <h3>Spain</h3>
        </div>
    </div>

    <div class="image-container img-six">
        <img src="https://images.pexels.com/photos/325193/pexels-photo-325193.jpeg?auto=compress&cs=tinysrgb&w=600" alt="swimming" />
        <div class="overlay">
            <h3>Dubai</h3>
        </div>
    </div>
</div>
<div class="main-div d-flex bg-secondary justify-content-between align-items-center">
    <div class="right-section col-md-7 d-flex p-2 align-items-center gap-3">
        <div class="col-xl-8 col-lg-7 col-md-7 text-white">
            <h4>Upto 90% commissions on all successful admissions</h4>
            <p>Trusted by Referral Partners around India</p>
        </div>
        <div class="col-xl-4 col-lg-5 col-md-5">
            <button class="bg-white p-2 rounded border-0">
                <a href="#" class="text-dark text-decoration-none">
                    <i class="fa fa-user-tie"></i> Become a partner
                </a>
            </button>
        </div>
    </div>
    <div class="left-section col-md-4">
        <img src="{{ asset('images/banner-2.png') }}" alt="banner-img">
    </div>
</div>
@endsection
