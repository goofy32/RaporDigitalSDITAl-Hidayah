<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="turbo-cache-control" content="no-preview">
    <meta name="turbo-visit-control" content="reload">


    <link rel="preload" 
        href="{{ asset('images/icons/dashboard-icon.png') }}" 
        as="image" 
        fetchpriority="high">

    <link rel="preload" 
        href="{{ asset('images/icons/score.png') }}" 
        as="image" 
        fetchpriority="high">


    <link rel="preload" 
        href="{{ asset('images/icons/subject-icon.png') }}" 
        as="image" 
        fetchpriority="high">


    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <title>@yield('title')</title>
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">

    <script>
        // Initialize global form change tracking
        window.formChanged = false;

        // Handle page unload
        window.addEventListener('beforeunload', (e) => {
            if (window.formChanged) {
                e.preventDefault();
                e.returnValue = 'Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
                return e.returnValue;
            }
        });

        // Handle Turbo navigation
        document.addEventListener('turbo:before-visit', (event) => {
            if (window.formChanged) {
                if (!confirm('Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?')) {
                    event.preventDefault();
                } else {
                    window.formChanged = false;
                }
            }
        });

        // Reset form changed state after successful form submission
        document.addEventListener('turbo:submit-end', (event) => {
            if (event.detail.success) {
                window.formChanged = false;
            }
        });

        // Preserve form state during page transitions
        document.addEventListener('turbo:before-cache', () => {
            // Save form state if needed
            sessionStorage.setItem('formChanged', window.formChanged);
        });

        document.addEventListener('turbo:load', () => {
            // Restore form state
            window.formChanged = sessionStorage.getItem('formChanged') === 'true';
            sessionStorage.removeItem('formChanged');
        });
    </script>
</head>
<body>
    <x-admin.topbar data-turbo-permanent id="topbar"></x-admin.topbar>
    <x-pengajar.sidebar data-turbo-permanent id="sidebar"></x-pengajar.sidebar>

    <x-session-timeout-alert data-turbo-permanent id="session-alert" />

    <div class="p-4 sm:ml-64">
        <div id="main" data-turbo-frame="main" class="w-full">
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

    @if(Session::has('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ Session::get('success') }}",
                showConfirmButton: false,
                timer: 1500
            });
        </script>
    @endif

    @if(Session::has('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ Session::get('error') }}",
                confirmButtonText: 'Ok'
            });
        </script>
    @endif
</body>
</html>