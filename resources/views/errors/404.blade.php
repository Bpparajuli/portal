@extends('layouts.app')
<!-- or layouts.admin depending on your app -->

@section('content')
<div class="container text-center py-5">
    <h1 class="display-1">404</h1>
    <h3>Page Not Found</h3>
    <p>Sorry, the page you are looking for does not exist.</p>
    <a href="{{ route('home') }}" class="btn btn-primary mt-3">Go to Home</a>
</div>
@endsection
