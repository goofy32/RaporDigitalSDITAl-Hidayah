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
                    <button onclick="window.location.href='{{ route('teacher.edit', $teacher->id) }}'" 
                        class="bg-blue-600 text-white font-medium py-2 px-4 rounded hover:bg-blue-700">
                        Edit
                    </button>
                </div>
            </div>

            <!-- Detail Pengajar -->
            <div class="flex space-x-8">
                <!-- Foto Placeholder -->
                <div class="flex items-start justify-center w-64 h-80 bg-gray-200 rounded-lg shadow-md overflow-hidden"> <!-- Ukuran diperbesar -->
                @if($teacher->photo)
                    <img src="{{ asset('storage/' . $teacher->photo) }}" 
                        alt="Foto Pengajar" 
                        class="w-full h-full object-cover">
                @else
                    <div class="flex items-center justify-center w-full h-full">
                        <svg class="w-32 h-32 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                    </div>
                @endif
            </div>

                <!-- Informasi Detail -->
                <div class="w-full">
                    <table class="w-full text-sm text-left text-gray-500">
                        <tbody>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">NIP</th>
                                <td class="px-4 py-2">{{ $teacher->nuptk ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Nama</th>
                                <td class="px-4 py-2">{{ $teacher->nama ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Jenis Kelamin</th>
                                <td class="px-4 py-2">{{ $teacher->jenis_kelamin ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Tanggal Lahir</th>
                                <td class="px-4 py-2">{{ $teacher->tanggal_lahir ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">No Handphone</th>
                                <td class="px-4 py-2">{{ $teacher->no_handphone ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Email</th>
                                <td class="px-4 py-2">{{ $teacher->email ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Alamat</th>
                                <td class="px-4 py-2">{{ $teacher->alamat ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Jabatan</th>
                                <td class="px-4 py-2">{{ $teacher->jabatan ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Kelas Mengajar</th>
                                <td class="px-4 py-2">
                                    {{ $teacher->kelasPengajar->nomor_kelas ?? '-' }} - {{ $teacher->kelasPengajar->nama_kelas ?? 'Belum Diisi' }}
                                </td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Username</th>
                                <td class="px-4 py-2">{{ $teacher->username ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Password</th>
                                <td class="px-4 py-2">••••••••</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
