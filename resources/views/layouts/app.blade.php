<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>@yield('title','Idea Consultancy')</title>

    <!-- Bootstrap CSS (only one) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Optional: FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/agent-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/university.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contact.css')}}">
    <link rel="stylesheet" href="{{ asset('css/login.css')}}">
    <link rel="stylesheet" href="{{ asset('css/application.css')}}">
    <link rel="stylesheet" href="{{ asset('css/notification.css')}}">
    <link rel="stylesheet" href="{{ asset('css/user.css')}}">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">



    {{-- Fav icon  --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

</head>
<body class="font-roboto">

    @include('partials.header')
    @include('partials.alerts')
    @include('components.file_modal')

    <div class="main-content">
        @yield('content')
    </div>

    @include('partials.footer')

    <!-- Bootstrap JS Bundle (only once, includes Popper) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Your custom JS -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    <script src="{{ asset('js/login.js') }}"></script>
    <script src="{{ asset('js/header.js') }}"></script>
    <script src="{{ asset('js/filter.js') }}"></script>
    <script src="{{ asset('js/file_modal.js') }}"></script>
    @stack('scripts')
</body>
</html>
