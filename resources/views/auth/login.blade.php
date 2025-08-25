@extends('layouts.app')
@section('title', 'Login')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-5 bg-secondary p-5 border border-primary rounded">
        @foreach (['success', 'error', 'warning', 'info'] as $msg)
        @if(session($msg))
        <div class="alert alert-{{ $msg }} mt-3 text-center">
            {{ session($msg) }}
        </div>
        @endif
        @endforeach

        <form action="{{ route('auth.login.post') }}" method="POST">
            @csrf
            <h2 class="bold text-white text-center">Please Sign-in to enter</h2>
            {{-- Error messages --}}
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <div class="input-box">
                <input type="email" id="email" name="email" value="{{ old('email') }}" required />
                <label class="text-white" for="email">Email address</label>
            </div>
            <div class="input-box">
                <input type="password" id="password" name="password" required />
                <label class="text-white" for="password">Password</label>
            </div>
            <div class="text-white">
                <a href="{{ route('auth.contact') }}">Forgot Password?</a>
            </div>
            <button data-mdb-ripple-init type="submit" class="btn button bg-primary btn-outline-light btn-block">Sign in</button>
        </form>
    </div>
</div>

<h3 class="bg-gray-300 text-dark bold p-3 text-center m-2">Countries We Are Working With</h3>
<div class="activity-container p-5">
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
