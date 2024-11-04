<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <title>Data Sekolah</title>
</head>

<body>

    <x-admin.topbar></x-admin.topbar>
    <x-admin.sidebar></x-admin.sidebar>

    <div class="p-4 sm:ml-64">
        <div class="p-6 border border-gray-200 rounded-lg shadow-lg bg-white mt-14">
            <!-- Bagian Header Data Sekolah -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-green-700">Data Sekolah</h2>
                <div class="space-x-2">
                    <a href="{{ route('profile') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg">Edit</a>
                    <button class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button>
                </div>
            </div>

            <!-- Bagian Konten Data Sekolah -->
            <div class="grid grid-cols-3 gap-4">
                <!-- Gambar Profil Kiri -->
                <div class="p-4 bg-gray-100 rounded-lg flex items-center justify-center">
                    <img src="https://via.placeholder.com/80" alt="Profile Image" class="w-20 h-20 rounded-full">
                </div>

                <!-- Tabel Data Sekolah -->
                <div class="col-span-2">
                    <table class="w-full border-collapse border border-gray-300">
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Nama Instansi</td>
                            <td class="border border-gray-300 p-2">xxxxxx</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Nama Sekolah</td>
                            <td class="border border-gray-300 p-2">xxxxxx</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">NPSN</td>
                            <td class="border border-gray-300 p-2">xxxxxx</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Alamat</td>
                            <td class="border border-gray-300 p-2">xxxxxx</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Kode Pos</td>
                            <td class="border border-gray-300 p-2">xxxxxx</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Telepon</td>
                            <td class="border border-gray-300 p-2">xxxxxx</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Tahun Pelajaran</td>
                            <td class="border border-gray-300 p-2">xxxxxx</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Semester</td>
                            <td class="border border-gray-300 p-2">xxxxxx</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Kepala Sekolah</td>
                            <td class="border border-gray-300 p-2">xxxxxx</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Guru Kelas</td>
                            <td class="border border-gray-300 p-2">xxxxxx</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Kelas</td>
                            <td class="border border-gray-300 p-2">xxxxxx</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Jumlah Siswa</td>
                            <td class="border border-gray-300 p-2">xxxxxx</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
