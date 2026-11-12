<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" type="image/png" href="{{ url('images/favicon-real-school.png') }}">
        <link rel="apple-touch-icon" href="{{ url('images/favicon-real-school.png') }}">

        <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=montserrat:300,400,500,600,700,800|playfair+display:600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ url('css/auth.css') }}">
        <link rel="stylesheet" href="{{ url('css/marca.css') }}">
    </head>
    <body class="auth-body">
        <div class="auth-shell">
            {{ $slot }}
        </div>
    </body>
</html>
