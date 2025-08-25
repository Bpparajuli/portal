@extends('layout.app')
@section('title', 'Dashboard')
@section('content')
<div class="text-center mt-5 mb-4">
    <h1 class="mb-3">Welcome to Idea Consultancy Agent Portal</h1>
    <p class="lead">Register or login to manage agents, students, and applications.</p>
    <div class="mt-4 row justify-content-center">
        <div class="col-lg-2 col-md-4 col-sm-4 m-2">
            <a href="{{ route('login') }}" class="btn btn-success">Login</a>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-4 m-2">
            <a href="{{ route('register') }}" class="btn btn-secondary ">Register</a>
        </div>
    </div>
</div>
<div class="it-cta-area it-cta-height black-bg p-relative">
    <div class="it-cta-bg d-none d-xl-block">
        <img src="assets/img/cta-1.png" alt>
    </div>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-xl-9 col-lg-7 col-md-7">
                <div class="it-cta-content">
                    <h4 class="it-cta-title">Grow your business 10x with MAKE IT IN ABROAD</h4>
                    <p class="it-cta-para">Create applications within 30 mins</p>
                </div>
            </div>

            <div class="col-xl-3 col-lg-5 col-md-5">
                <div class="it-cta-button text-md-end">
                    <a href="partner-register.php" class="theme-btn"><i class="fa-regular fa-user-tie"></i>
                        Become a partner </a>
                </div>
            </div>
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
@endsection
