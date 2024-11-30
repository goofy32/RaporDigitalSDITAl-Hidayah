<!-- resources/views/layouts/wali_kelas/app.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="turbo-cache-control" content="no-preview">
    
    <title>@yield('title')</title>
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
</head>
<body>
    <x-admin.topbar data-turbo-permanent id="topbar"></x-admin.topbar>
    <x-wali-kelas.sidebar data-turbo-permanent id="sidebar"></x-wali-kelas.sidebar>

    <x-session-timeout-alert data-turbo-permanent id="session-alert" />

    <div class="p-4 sm:ml-64">
        <div id="main" data-turbo-frame="main">
            @if(session('success'))
                <x-alert type="success" :message="session('success')" />
            @endif

            @if(session('error'))
                <x-alert type="error" :message="session('error')" />
            @endif

            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>