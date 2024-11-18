<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <title>@yield('title')</title>
</head>
<body>
    <x-admin.topbar data-turbolinks-permanent></x-admin.topbar>
    <x-admin.sidebar data-turbolinks-permanent></x-admin.sidebar>

    <div class="p-4 sm:ml-64">
        @yield('content')
    </div>

    <!-- Inisialisasi JavaScript jika diperlukan -->
    @stack('scripts')
</body>
</html>