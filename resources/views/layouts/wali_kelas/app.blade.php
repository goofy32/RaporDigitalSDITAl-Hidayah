<!-- resources/views/layouts/wali_kelas/app.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="turbo-cache-control" content="no-cache">
    <meta name="turbo-visit-control" content="reload">
    
    <title>@yield('title')</title>
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body x-data="{ 
        firstLoad: true,
        init() {
            // Preload topbar dan sidebar saat pertama kali halaman dimuat
            if (this.firstLoad) {
                document.addEventListener('DOMContentLoaded', () => {
                    this.firstLoad = false;
                    // Pastikan gambar di topbar dan sidebar sudah dimuat
                    const images = document.querySelectorAll('#topbar img, #sidebar img');
                    let loadedImages = 0;
                    
                    const checkAllImagesLoaded = () => {
                        loadedImages++;
                        if (loadedImages >= images.length) {
                            // Semua gambar sudah dimuat, tandai sidebar/topbar sebagai sudah diinisialisasi
                            document.querySelector('#topbar').setAttribute('data-initialized', 'true');
                            document.querySelector('#sidebar').setAttribute('data-initialized', 'true');
                        }
                    };
                    
                    images.forEach(img => {
                        if (img.complete) {
                            checkAllImagesLoaded();
                        } else {
                            img.addEventListener('load', checkAllImagesLoaded);
                            img.addEventListener('error', checkAllImagesLoaded);
                        }
                    });
                });
            }
        }
    }">
    
    <!-- Permanent Components - Won't be reloaded by Turbo -->
    <x-admin.topbar data-turbo-permanent id="topbar"></x-admin.topbar>
    <x-wali-kelas.sidebar data-turbo-permanent id="sidebar"></x-wali-kelas.sidebar>
    <x-session-timeout-alert data-turbo-permanent id="session-alert" />

    <!-- Main Content Area -->
    <div class="p-4 sm:ml-64">
        <div id="main">
            @if(session('success'))
                <x-alert type="success" :message="session('success')" />
            @endif

            @if(session('error'))
                <x-alert type="error" :message="session('error')" />
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Placeholder to detect when Turbo has loaded the page completely -->
    <div id="turbo-loaded" data-turbo-permanent x-data x-init="
        document.addEventListener('turbo:load', () => {
            // Perform initialization when Turbo completes loading
            if (typeof initFlowbite === 'function') {
                initFlowbite();
            }
            
            if (window.Alpine) {
                window.Alpine.initTree(document.body);
            }
            
            // Fire a custom event that components can listen to
            document.dispatchEvent(new CustomEvent('app:page-loaded'));
        });
    "></div>

    @stack('scripts')
</body>
</html>