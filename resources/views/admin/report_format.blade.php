<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <title>Format Rapot</title>
</head>

<body>
    <x-admin.topbar></x-admin.topbar>
    <x-admin.sidebar></x-admin.sidebar>

    <div class="p-4 sm:ml-64">
        <div class="p-4 bg-white mt-14 rounded-lg shadow">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-green-700">
                    Format Rapot - {{ strtoupper(request('type', 'UTS')) }}
                </h2>
                <div class="flex space-x-2">
                    <button class="px-4 py-2 text-white bg-green-700 rounded hover:bg-green-800">
                        Edit
                    </button>
                    <button class="px-4 py-2 text-white bg-green-700 rounded hover:bg-blue-800">
                        Upload Format
                    </button>
                </div>
            </div>
            
            <!-- Placeholder Area -->
            <div class="p-8 border border-gray-300 bg-gray-50 rounded-lg shadow">
                <!-- Header Section -->
                <div class="flex justify-between items-start mb-4 px-6">
                    <!-- Placeholder Logo Kiri -->
                    <div class="w-32 h-32 border-2 border-dashed border-gray-400 flex items-center justify-center">
                        <span class="text-xs text-gray-400">Placeholder Logo Kiri</span>
                    </div>
                    <!-- Header Text -->
                    <div class="text-center flex-1">
                        <div class="text-lg font-bold uppercase">PEMERINTAH KABUPATEN</div>
                        <div class="text-sm font-semibold uppercase">KOORDINATOR WILAYAH DIKPORA KECAMATAN</div>
                        <div class="text-sm font-semibold uppercase">SD IT AL-HIDAYAH LOGAM</div>
                        <div class="text-md mt-2 font-semibold underline">
                            RAPOR TENGAH SEMESTER {{ strtoupper(request('type', 'UTS')) }}
                        </div>
                    </div>
                    <!-- Placeholder Logo Kanan -->
                    <div class="w-32 h-32 border-2 border-dashed border-gray-400 flex items-center justify-center">
                        <span class="text-xs text-gray-400">Placeholder Logo Kanan</span>
                    </div>
                </div>

                <!-- Garis Panjang -->
                <div class="mt-2 border-t-2 underline border-black"></div>

                <!-- Informasi Siswa -->
                <div class="px-6">
                    <table class="w-full text-sm border-collapse">
                        <tr>
                            <td class="font-semibold w-1/4">Nama Siswa:</td>
                            <td class="border border-gray-300 px-2 py-1"></td>
                            <td class="font-semibold w-1/4">Kelas:</td>
                            <td class="border border-gray-300 px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="font-semibold">NIS/NISN:</td>
                            <td class="border border-gray-300 px-2 py-1"></td>
                            <td class="font-semibold">Tahun Pelajaran:</td>
                            <td class="border border-gray-300 px-2 py-1"></td>
                        </tr>
                    </table>
                </div>

                <!-- Tabel Mata Pelajaran -->
                <div class="px-6">
                    <table class="w-full text-sm border-collapse border border-gray-300 mb-6">
                        <thead class="bg-gray-200 text-gray-700 font-semibold">
                            <tr>
                                <th class="border border-gray-300 px-3 py-2 text-center">No</th>
                                <th class="border border-gray-300 px-3 py-2 text-left">Mata Pelajaran</th>
                                <th class="border border-gray-300 px-3 py-2 text-center">Nilai</th>
                                <th class="border border-gray-300 px-3 py-2 text-left">Capaian Kompetensi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 1; $i <= 10; $i++)
                                <tr>
                                    <td class="border border-gray-300 px-3 py-2 text-center">{{ $i }}</td>
                                    <td class="border border-gray-300 px-3 py-2"></td>
                                    <td class="border border-gray-300 px-3 py-2 text-center"></td>
                                    <td class="border border-gray-300 px-3 py-2"></td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

                <!-- Ekstrakurikuler -->
                <div class="mb-6 px-6">
                    <div class="font-semibold mb-2">Ekstrakurikuler</div>
                    <table class="w-full text-sm border-collapse border border-gray-300">
                        <thead class="bg-gray-200 text-gray-700 font-semibold">
                            <tr>
                                <th class="border border-gray-300 px-3 py-2 text-center">No</th>
                                <th class="border border-gray-300 px-3 py-2 text-left">Kegiatan</th>
                                <th class="border border-gray-300 px-3 py-2 text-left">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 1; $i <= 4; $i++)
                                <tr>
                                    <td class="border border-gray-300 px-3 py-2 text-center">{{ $i }}</td>
                                    <td class="border border-gray-300 px-3 py-2"></td>
                                    <td class="border border-gray-300 px-3 py-2"></td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

                <!-- Catatan Guru -->
                <div class="mb-6 px-6">
                    <div class="font-semibold">Catatan Guru</div>
                    <div class="border border-gray-300 p-4 h-16"></div>
                </div>

                <!-- Ketidakhadiran -->
                <div class="mb-6 px-6">
                    <table class="w-full text-sm border-collapse">
                        <tr>
                            <td class="font-semibold w-1/4">Sakit:</td>
                            <td class="border border-gray-300 px-2 py-1 w-1/4"></td>
                            <td class="font-semibold">Hari</td>
                        </tr>
                        <tr>
                            <td class="font-semibold">Izin:</td>
                            <td class="border border-gray-300 px-2 py-1 w-1/4"></td>
                            <td class="font-semibold">Hari</td>
                        </tr>
                        <tr>
                            <td class="font-semibold">Tanpa Keterangan:</td>
                            <td class="border border-gray-300 px-2 py-1 w-1/4"></td>
                            <td class="font-semibold">Hari</td>
                        </tr>
                    </table>
                </div>

                <!-- Footer -->
                <div class="flex justify-between mt-6 px-6 text-sm">
                    <div class="text-center">
                        Mengetahui,<br>
                        Orang Tua/Wali
                    </div>
                    <div class="text-center">
                        Kepala Sekolah
                    </div>
                    <div class="text-center">
                        Wali Kelas
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../path/to/flowbite/dist/flowbite.min.js"></script>
</body>

</html>
