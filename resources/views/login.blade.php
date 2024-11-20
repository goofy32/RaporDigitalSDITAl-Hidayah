<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Rapor Digital SDIT Al-Hidayah Logam</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.css">
    <style>
        body {
            background-color: #f8fafc; /* Background yang bersih */
        }

        .shadow-card {
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); /* Bayangan lembut */
        }
    </style>
</head>

<body class="flex flex-col items-center justify-center min-h-screen p-4 bg-gray-100">
    <!-- Nama Sekolah -->
    <div class="text-center mb-6">
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-green-700 mb-4 leading-snug">
            RAPOR DIGITAL <br> SDIT AL-HIDAYAH LOGAM
        </h1>
    </div>

    <!-- Form Login -->
    <div class="bg-white p-6 sm:p-8 rounded-none w-full max-w-sm sm:max-w-md shadow-card">
        <!-- Logo -->
        <div class="flex flex-col items-center mb-6">
            <img src="{{ asset('images/icons/sdit-logo.png') }}" alt="Logo Sekolah"
                class="w-32 h-32 object-contain mb-4">
        </div>

        @if ($errors->any())
        <div class="mb-4 p-4 text-sm text-red-800 rounded-lg bg-red-50">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Form -->
        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <!-- Username -->
            <div class="mb-4">
                <label for="username" class="block mb-2 text-sm font-medium text-gray-700">Username</label>
                <input type="text" name="username" id="username" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label for="password" class="block mb-2 text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <!-- Role -->
            <div class="mb-6">
                <label for="role" class="block mb-2 text-sm font-medium text-gray-700">Role</label>
                <select name="role" id="role" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="" disabled selected>Pilih Role</option>
                    <option value="admin">Admin</option>
                    <option value="guru">Guru</option>
                    <option value="wali_kelas">Wali Kelas</option>
                </select>
            </div>

            <!-- Button -->
            <div class="flex justify-center">
                <button type="submit"
                    class="w-full px-4 py-2 text-white bg-green-700 rounded-lg hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500">
                    Login
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.js"></script>
</body>

</html>
