<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="turbo-cache-control" content="no-preview">

    <meta name="turbo-cache-control" content="no-preview">
    <meta name="turbo-visit-control" content="reload">
    <!-- Add this new meta tag -->
    <meta name="turbo-root" content="true">
    
    <!-- Preload critical images -->
    <link rel="preload" 
        href="{{ asset('images/icons/dashboard-icon.png') }}" 
        as="image" 
        fetchpriority="high">

    <link rel="preload" 
        href="{{ asset('images/icons/subject-icon.png') }}" 
        as="image" 
        fetchpriority="high">

    <link rel="preload" 
        href="{{ asset('images/icons/achievement-icon.png') }}" 
        as="image" 
        fetchpriority="high">

    <link rel="preload" 
        href="{{ asset('images/icons/class-icon.png') }}" 
        as="image" 
        fetchpriority="high">

    <link rel="preload" 
        href="{{ asset('images/icons/report-icon.png') }}" 
        as="image" 
        fetchpriority="high">

    <link rel="preload" 
        href="{{ asset('images/icons/teacher-icon.png') }}" 
        as="image" 
        fetchpriority="high">

    <link rel="preload" 
        href="{{ asset('images/icons/extracurricular-icon.png') }}" 
        as="image" 
        fetchpriority="high">

    <link rel="preload" 
        href="{{ asset('images/icons/student-icon.png') }}" 
        as="image" 
        fetchpriority="high">

    <link rel="preload" 
        href="{{ asset('images/icons/profile-icon.png') }}" 
        as="image" 
        fetchpriority="high">

    
    <title>@yield('title')</title>
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    
    <!-- React and ReactDOM -->
    <script src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    
    <!-- For production, use these instead -->
    <!-- <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script> -->
    <!-- <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script> -->
    
    <!-- Babel for JSX (development only - remove in production) -->
    <script src="https://unpkg.com/babel-standalone@6/babel.min.js"></script>
    <style>
        input:required:invalid,
        select:required:invalid {
            border-color: #EF4444;
        }

        .invalid-feedback {
            color: #EF4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
    <style>
        [x-cloak] { 
            display: none !important; 
        }
    
        #dropdown-rapor {
            display: none;
        }
    
        #dropdown-rapor.show {
            display: block;
        }
        
        .dropdown-transition {
            transition: opacity 150ms ease-in-out,
                        transform 150ms ease-in-out;
        }

        .sidebar-icon {
            width: 1.25rem;
            height: 1.25rem;
        }

        /* Mencegah flash saat navigasi */
        .turbo-progress-bar {
            background-color: #3B82F6 !important;
        }

        body.edit-subject-page #logo-sidebar {
            transform: translateX(0) !important;
        }

        body.edit-subject-page .sm\:ml-64 {
            margin-left: 16rem !important;
        }
    </style>
    
</head>
<body>
    <x-admin.topbar data-turbo-permanent id="topbar"></x-admin.topbar>
    <x-admin.sidebar data-turbo-permanent id="sidebar"></x-admin.sidebar>
    <x-session-timeout-alert data-turbo-permanent id="session-alert" />

    @if(Session::has('warning'))
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                html: "{{ Session::get('warning') }}",
                confirmButtonText: 'Mengerti'
            });
        </script>
    @endif

    <div class="p-4 sm:ml-64 min-h-screen bg-white">
        <div class="mt-14"> <!-- Padding top untuk navbar -->
            @if(session('success'))
                <x-alert type="success" :message="session('success')" />
            @endif
    
            @if(session('error'))
                <x-alert type="error" :message="session('error')" />
            @endif
    
            <div id="main" data-turbo-frame="main">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Base components for React -->
    <script type="text/babel">
        // Define base components that will be used across the app
        window.Card = ({ children, className = '' }) => (
            <div className={`bg-white shadow rounded-lg ${className}`}>
                {children}
            </div>
        );

        window.Button = ({ children, onClick, className = '', type = 'button' }) => (
            <button
                type={type}
                onClick={onClick}
                className={`px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 ${className}`}
            >
                {children}
            </button>
        );

        window.Input = ({ type = 'text', value, onChange, className = '' }) => (
            <input
                type={type}
                value={value}
                onChange={onChange}
                className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 ${className}`}
            />
        );
    </script>

    @stack('scripts')
</body>
</html>