<!-- resources/views/data/school_data.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Meta tags dan lainnya -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <title>Data Sekolah</title>
</head>

<body>

    <x-admin.topbar></x-admin.topbar>
    <x-admin.sidebar></x-admin.sidebar>

    <div class="p-4 sm:ml-64">
        <div class="p-6 border border-gray-200 rounded-lg shadow-lg bg-white mt-14">
            <!-- Pesan Sukses atau Peringatan -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                    {{ session('warning') }}
                </div>
            @endif

            <!-- Bagian Header Data Sekolah -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-green-700">Data Sekolah</h2>
                <div class="space-x-2">
                    <a href="{{ route('profile.edit') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg">Edit</a>
                </div>
            </div>

            
            <!-- Bagian Konten Data Sekolah -->
            <div class="grid grid-cols-3 gap-4">
                <!-- Gambar Profil Kiri -->
                <div class="p-4 bg-gray-100 rounded-lg flex items-center justify-center">
                    @if(isset($profil->logo))
                        <img src="{{ asset('storage/' . $profil->logo) }}" 
                            alt="Logo Sekolah" 
                            class="w-64 h-64 object-cover object-center rounded-lg">
                    @else
                        <img src="https://via.placeholder.com/256" 
                            alt="Logo Sekolah" 
                            class="w-64 h-64 object-cover object-center rounded-lg">
                    @endif
                </div>

                <!-- Tabel Data Sekolah -->
                <div class="col-span-2">
                    <table class="w-full border-collapse border border-gray-300">
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Nama Instansi</td>
                            <td class="border border-gray-300 p-2">{{ $profil->nama_instansi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Nama Sekolah</td>
                            <td class="border border-gray-300 p-2">{{ $profil->nama_sekolah ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">NPSN</td>
                            <td class="border border-gray-300 p-2">{{ $profil->npsn ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Alamat</td>
                            <td class="border border-gray-300 p-2">{{ $profil->alamat ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Kode Pos</td>
                            <td class="border border-gray-300 p-2">{{ $profil->kode_pos ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Telepon</td>
                            <td class="border border-gray-300 p-2">{{ $profil->telepon ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Email Sekolah</td>
                            <td class="border border-gray-300 p-2">{{ $profil->email_sekolah ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Website</td>
                            <td class="border border-gray-300 p-2">{{ $profil->website ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Tahun Pelajaran</td>
                            <td class="border border-gray-300 p-2">{{ $profil->tahun_pelajaran ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Semester</td>
                            <td class="border border-gray-300 p-2">{{ $profil->semester ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Kepala Sekolah</td>
                            <td class="border border-gray-300 p-2">{{ $profil->kepala_sekolah ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Guru Kelas</td>
                            <td class="border border-gray-300 p-2">{{ $profil->guru_kelas ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Jumlah Kelas</td>
                            <td class="border border-gray-300 p-2">{{ $profil->kelas ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 p-2 font-semibold">Jumlah Siswa</td>
                            <td class="border border-gray-300 p-2">{{ $profil->jumlah_siswa ?? '-' }}</td>
                        </tr>
                        <!-- Tambahkan field lainnya jika diperlukan -->
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
