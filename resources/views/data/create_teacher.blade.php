<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <title>Form Tambah Data Pengajar</title>
</head>

<body>
    <x-admin.topbar></x-admin.topbar>
    <x-admin.sidebar></x-admin.sidebar>

    <div class="p-4 sm:ml-64">
        <div class="p-4 bg-white mt-14 rounded-lg shadow">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-green-700">Form Tambah Data Pengajar</h2>
                <div class="flex space-x-2">
                    <button onclick="window.history.back()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        Kembali
                    </button>
                    <button form="teacherForm" type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Simpan
                    </button>
                </div>
            </div>

            <!-- Form -->
            <form id="teacherForm" action="x" method="POST" enctype="multipart/form-data" class="grid grid-cols-2 gap-6">
                @csrf

                <!-- Kolom Kiri -->
                <div>
                    <label for="nip" class="block text-sm font-medium text-gray-700">NIP</label>
                    <input type="text" id="nip" name="nip" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" required>

                    <label for="nama" class="block mt-4 text-sm font-medium text-gray-700">Nama</label>
                    <input type="text" id="nama" name="nama" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" required>

                    <label for="jenis_kelamin" class="block mt-4 text-sm font-medium text-gray-700">Jenis Kelamin</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" class="w-full mt-1 p-2 border border-gray-300 rounded-lg">
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>

                    <label for="tanggal_lahir" class="block mt-4 text-sm font-medium text-gray-700">Tanggal Lahir</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" required>

                    <label for="no_handphone" class="block mt-4 text-sm font-medium text-gray-700">No Handphone</label>
                    <input type="text" id="no_handphone" name="no_handphone" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" required>

                    <label for="email" class="block mt-4 text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" required>

                    <label for="alamat" class="block mt-4 text-sm font-medium text-gray-700">Alamat</label>
                    <textarea id="alamat" name="alamat" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" rows="3" required></textarea>
                </div>

                <!-- Kolom Kanan -->
                <div>
                    <label for="jabatan" class="block text-sm font-medium text-gray-700">Jabatan</label>
                    <input type="text" id="jabatan" name="jabatan" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" required>

                    <label for="kelas_mengajar" class="block mt-4 text-sm font-medium text-gray-700">Kelas Mengajar</label>
                    <select id="kelas_mengajar" name="kelas_mengajar" class="w-full mt-1 p-2 border border-gray-300 rounded-lg">
                        <option value="Kelas 1A">Kelas 1A</option>
                        <option value="Kelas 1B">Kelas 1B</option>
                        <option value="Kelas 2A">Kelas 2A</option>
                        <option value="Kelas 2B">Kelas 2B</option>
                    </select>

                    <label for="photo" class="block mt-4 text-sm font-medium text-gray-700">Photo (ukuran 4x6 atau 2x3)</label>
                    <input type="file" id="photo" name="photo" class="w-full mt-1 p-2 border border-gray-300 rounded-lg">

                </div>
            </form>
        </div>
    </div>
</body>

</html>
