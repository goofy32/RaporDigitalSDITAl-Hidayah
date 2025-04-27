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

            @if(session('tahun_ajaran_id') && isset($activeTahunAjaran) && session('tahun_ajaran_id') != $activeTahunAjaran->id)
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Perhatian:</strong> Anda sedang melihat data untuk tahun ajaran <strong>{{ App\Models\TahunAjaran::find(session('tahun_ajaran_id'))->tahun_ajaran }}</strong>, sedangkan tahun ajaran aktif adalah <strong>{{ $activeTahunAjaran->tahun_ajaran }}</strong>.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
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