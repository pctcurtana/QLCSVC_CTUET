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
<body class="antialiased bg-[#f4f7fc] min-h-screen relative overflow-x-hidden">
    <!-- Aurora Glow Orbs for Glassmorphism -->
    <div class="glow-orb orb-1"></div>
    <div class="glow-orb orb-2"></div>
    <div class="glow-orb orb-3"></div>

    <!-- Initial Page Loader Screen -->
    <div id="initial-loader" class="initial-loader-container">
        <div class="initial-loader-glass">
            <div class="loader-pulse-logo">
                <img src="/images/logoctuet.png" alt="CTUET" class="loader-logo" onerror="this.src='/favicon.png'">
            </div>
            <div class="loader-bar-container">
                <div class="loader-bar-fill"></div>
            </div>
        </div>
    </div>

    @inertia
</body>
</html>