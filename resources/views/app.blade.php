<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>QLCSVC - CTUET</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <!-- Styles -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    
    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}" defer></script>
    @inertiaHead
</head>
<body class="antialiased">
    @inertia
</body>
</html>