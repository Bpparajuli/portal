@extends('layout.app')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-6 p-5 border border-primary rounded">
        <h1 class="bg-secondary bold text-center text-white p-3">Fill the form and submit to become an IDEA AGENT</h1><br>
        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
            @csrf
            <label>Business Name</label>
            <input type="text" name="business_name" placeholder="Business Name" class="form-control mb-2" value="{{ old('business_name') }}">

            <label>Owner Name</label>
            <input type="text" name="owner_name" placeholder="Owner Name" class="form-control mb-2" value="{{ old('owner_name') }}">

            <label>Name</label>
            <input type="text" name="name" placeholder="Name" class="form-control mb-2" value="{{ old('name') }}" required>

            <label>Contact</label>
            <input type="text" name="contact" placeholder="Contact" class="form-control mb-2" value="{{ old('contact') }}">

            <label>Address</label>
            <input type="text" name="address" placeholder="Address" class="form-control mb-2" value="{{ old('address') }}">

            <label>Email</label>
            <input type="email" name="email" placeholder="Email" class="form-control mb-2" value="{{ old('email') }}" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Password" class="form-control mb-2" required>

            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" placeholder="Confirm Password" class="form-control mb-2" required>

            <label>Business Logo</label>
            <input type="file" name="business_logo" class="form-control mb-3" accept="image/*">

            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                <label class="form-check-label" for="terms">
                    I agree to the <a href="{{ route('terms') }}">Terms and Conditions</a>
                </label>
            </div>

            <button type="submit" class="btn bg-primary text-white w-100">Register</button>

            <div class="text-center mt-2">
                <a href="{{ route('login') }}" class="text-primary">Already have an account? Login</a>
            </div>
        </form>
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

    </div>
</div>
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const password = document.querySelector('input[name="password"]');
        const confirmPassword = document.querySelector('input[name="password_confirmation"]');

        const matchIcon = document.createElement('span');
        matchIcon.style.marginLeft = '10px';

        confirmPassword.parentNode.insertBefore(matchIcon, confirmPassword.nextSibling);

        confirmPassword.addEventListener('input', () => {
            if (confirmPassword.value === "") {
                matchIcon.innerHTML = "";
            } else if (confirmPassword.value === password.value) {
                matchIcon.innerHTML = "✅";
                matchIcon.style.color = "green";
            } else {
                matchIcon.innerHTML = "❌";
                matchIcon.style.color = "red";
            }
        });
    });

</script>
