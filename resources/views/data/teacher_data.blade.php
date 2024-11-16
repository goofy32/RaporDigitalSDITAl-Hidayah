<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <title>Detail Data Pengajar</title>
</head>

<body>
    <x-admin.topbar></x-admin.topbar>
    <x-admin.sidebar></x-admin.sidebar>

    <div class="p-4 sm:ml-64">
        <div class="p-4 bg-white mt-14">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-green-700">Detail Data Pengajar</h2>
                <div class="flex space-x-2">
                    <button class="bg-green-600 text-white font-medium py-2 px-4 rounded hover:bg-green-700" onclick="window.history.back()">Kembali</button>
                    <button class="bg-blue-600 text-white font-medium py-2 px-4 rounded hover:bg-blue-700">Edit</button>
                </div>
            </div>

            <!-- Detail Pengajar -->
            <div class="flex space-x-8">
                <!-- Foto Placeholder -->
                <div class="flex items-center justify-center w-32 h-32 bg-gray-200 rounded-full shadow-md">
                    <svg class="w-16 h-16 text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2a10 10 0 100 20 10 10 0 000-20zM8 10a4 4 0 118 0 4 4 0 01-8 0zm12 9a8 8 0 00-16 0h16z" />
                    </svg>
                </div>

                <!-- Informasi Detail -->
                <div class="w-full">
                    <table class="w-full text-sm text-left text-gray-500">
                        <tbody>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">NIP</th>
                                <td class="px-4 py-2">{{ $teacher['nip'] ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Nama</th>
                                <td class="px-4 py-2">{{ $teacher['nama'] ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Jenis Kelamin</th>
                                <td class="px-4 py-2">{{ $teacher['jenis_kelamin'] ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Tanggal Lahir</th>
                                <td class="px-4 py-2">{{ $teacher['tanggal_lahir'] ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">No Handphone</th>
                                <td class="px-4 py-2">{{ $teacher['no_handphone'] ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Email</th>
                                <td class="px-4 py-2">{{ $teacher['email'] ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Alamat</th>
                                <td class="px-4 py-2">{{ $teacher['alamat'] ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Jabatan</th>
                                <td class="px-4 py-2">{{ $teacher['jabatan'] ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Kelas Mengajar</th>
                                <td class="px-4 py-2">{{ $teacher['kelas_mengajar'] ?? 'Belum Diisi' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
