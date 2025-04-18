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

    <!-- Gemini Chat Widget -->
    <div x-data="geminiChat" x-cloak class="fixed bottom-4 right-4 z-50">
        <!-- Chat Toggle Button -->
        <button @click="toggleChat" class="bg-blue-600 hover:bg-blue-700 text-white rounded-full p-3 shadow-lg flex items-center justify-center transition-all duration-300">
            <svg x-show="!isOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <svg x-show="isOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        
        <!-- Chat Window -->
        <div x-show="isOpen" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-90"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-90"
            class="absolute bottom-16 right-0 w-80 md:w-96 bg-white rounded-lg shadow-xl border border-gray-200 flex flex-col overflow-hidden">
            
            <!-- Chat Header -->
            <div class="bg-blue-600 text-white p-4 flex items-center justify-between">
                <h3 class="font-medium">Gemini Assistant</h3>
                <button @click="toggleChat" class="text-white hover:text-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- Chat Messages -->
            <div x-ref="chatContainer" class="flex-1 p-4 overflow-y-auto max-h-80 space-y-4">
                <template x-if="chats.length === 0">
                    <div class="text-center text-gray-500 py-4">
                        <p>Belum ada percakapan.</p>
                        <p class="text-sm">Mulai chat dengan mengirim pesan!</p>
                    </div>
                </template>
                
                <template x-for="(chat, index) in chats" :key="index">
                    <div class="space-y-2">
                        <!-- User Message -->
                        <div class="flex justify-end">
                            <div class="bg-blue-100 rounded-lg p-3 max-w-[80%]">
                                <p class="text-blue-900" x-text="chat.message"></p>
                            </div>
                        </div>
                        
                        <!-- Gemini Response -->
                        <div class="flex justify-start">
                            <div class="bg-gray-100 rounded-lg p-3 max-w-[80%]">
                                <p class="text-gray-900 whitespace-pre-line" x-text="chat.response"></p>
                            </div>
                        </div>
                    </div>
                </template>
                
                <!-- Loading Indicator -->
                <div x-show="isLoading" class="flex justify-center py-2">
                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Chat Input -->
            <div class="border-t p-3">
                <form @submit.prevent="sendMessage" class="flex space-x-2">
                    <input 
                        type="text" 
                        x-model="message" 
                        placeholder="Ketik pesan..."
                        class="flex-1 border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :disabled="isLoading"
                    >
                    <button 
                        type="submit" 
                        class="bg-blue-600 text-white rounded-lg px-4 py-2 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50 disabled:opacity-50"
                        :disabled="!message.trim() || isLoading"
                    >
                        <svg x-show="!isLoading" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                        </svg>
                        <svg x-show="isLoading" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>