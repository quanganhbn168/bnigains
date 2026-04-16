<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'BNI 1-2-1 Digital Profile')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/bnilogo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/bnilogo.jpg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body style="@yield('body_style', '')">

    <div class="mobile-container pb-10" x-data="{ drawerOpen: false }">
        <!-- STICKY HEADER & DRAWER -->
        @include('profile.partials.header')

        <!-- MAIN CONTENT -->
        @yield('content')

        <!-- FOOTER -->
        @include('profile.partials.footer')
    </div>

</body>

</html>