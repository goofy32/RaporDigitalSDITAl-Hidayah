<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <title>Dashboard</title>
</head>

<body>

    <x-admin.topbar></x-admin.topbar>
    <x-wali-kelas.sidebar></x-wali-kelas.sidebar>

    <div class="p-4 sm:ml-64">
        <div class="p-4 grid grid-cols-3 gap-4 mt-14">
            <!-- Box TAHUN AJARAN -->
            <div class="flex flex-col justify-center h-24 rounded-lg shadow bg-white border border-gray-200 p-4">
                <p class="text-sm font-semibold text-gray-600">TAHUN AJARAN</p>
                <p class="text-lg font-bold text-green-600">{{ $schoolProfile->tahun_pelajaran ?? '-' }}</p>
            </div>
            <!-- Box GURU -->
            <div class="flex flex-col justify-center h-24 rounded-lg shadow bg-white border border-gray-200 p-4">
                <p class="text-sm font-semibold text-gray-600">GURU</p>
                <p class="text-lg font-bold text-green-600">15 Guru</p>
            </div>
            <!-- Box SISWA -->
            <div class="flex flex-col justify-center h-24 rounded-lg shadow bg-white border border-gray-200 p-4">
                <p class="text-sm font-semibold text-gray-600">SISWA</p>
                <p class="text-lg font-bold text-green-600">240 Siswa</p>
            </div>
            <!-- Box MATA PELAJARAN -->
            <div class="flex flex-col justify-center h-24 rounded-lg shadow bg-white border border-gray-200 p-4">
                <p class="text-sm font-semibold text-gray-600">MATA PELAJARAN</p>
                <p class="text-lg font-bold text-green-600">10 Mata Pelajaran</p>
            </div>
            <!-- Box KELAS -->
            <div class="flex flex-col justify-center h-24 rounded-lg shadow bg-white border border-gray-200 p-4">
                <p class="text-sm font-semibold text-gray-600">KELAS</p>
                <p class="text-lg font-bold text-green-600">12 Kelas</p>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>


</body>

</html>
