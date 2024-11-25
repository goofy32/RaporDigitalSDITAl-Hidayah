<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <title>@yield('title')</title>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
    <x-admin.topbar data-turbolinks-permanent></x-admin.topbar>
    <x-admin.sidebar></x-admin.sidebar>
    <x-session-timeout-alert></x-session-timeout-alert>


    <div class="p-4 sm:ml-64">
        @if(session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif

        @if(session('error'))
            <x-alert type="error" :message="session('error')" />
        @endif

        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>