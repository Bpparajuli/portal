<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- ← ADD THIS LINE --}}
    <title>@yield('title')</title>
    <!-- Bootstrap CSS (only one) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Optional: FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    {{-- In your layouts/crm.blade.php <head> section --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- At the bottom, before </body> --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            padding: 0 !important;
            margin: 0 !important;
        }

        .crm-wrapper {
            width: 100%;
            height: 100vh;
            margin: 0;
            padding: 0;
        }
    </style>

    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <script src="{{ asset('js/app.js') }}"></script>

    {{-- Fav icon  --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    @stack('head')
    @stack('scripts')
    @stack('styles')
</head>

<body>

    {{-- No normal navbar/header here --}}

    <div class="crm-wrapper">

        @yield('content')
    </div>

    @stack('scripts')

</body>

</html>
