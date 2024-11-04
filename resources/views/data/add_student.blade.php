<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <title>Form Tambah Data Siswa</title>
</head>

<body>
    <x-admin.topbar></x-admin.topbar>
    <x-admin.sidebar></x-admin.sidebar>

    <div class="p-4 sm:ml-64">
        <div class="p-4 bg-white mt-14">
            <!-- Header Form -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-green-700">Form Tambah Data Siswa</h2>
                <div>
                    <button class="bg-green-700 text-white px-4 py-2 rounded mr-2">Simpan</button>
                    <button onclick="window.history.back()" class="bg-gray-600 text-white px-4 py-2 rounded">Kembali</button>
                </div>
            </div>

            <!-- Form Container -->
            <form class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Data Diri -->
                <div>
                    <h3 class="bg-green-700 text-white px-4 py-2 rounded-t">Data Diri</h3>
                    <div class="border p-4 space-y-4 rounded-b">
                        <div>
                            <label for="nis" class="block font-semibold">NIS</label>
                            <input type="text" id="nis" class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label for="nisn" class="block font-semibold">NISN</label>
                            <input type="text" id="nisn" class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label for="nama" class="block font-semibold">Nama</label>
                            <input type="text" id="nama" class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label for="tanggal_lahir" class="block font-semibold">Tanggal Lahir</label>
                            <input type="date" id="tanggal_lahir" class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label for="jenis_kelamin" class="block font-semibold">Jenis Kelamin</label>
                            <select id="jenis_kelamin" class="w-full p-2 border rounded">
                                <option>Pilih</option>
                                <option>Laki-laki</option>
                                <option>Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label for="agama" class="block font-semibold">Agama</label>
                            <input type="text" id="agama" class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label for="alamat" class="block font-semibold">Alamat</label>
                            <input type="text" id="alamat" class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label for="kelas" class="block font-semibold">Kelas</label>
                            <select id="kelas" class="w-full p-2 border rounded">
                                <option>Pilih Kelas</option>
                                <option>1 A</option>
                                <option>1 B</option>
                                <option>2 A</option>
                            </select>
                        </div>
                        <div>
                            <label for="photo" class="block font-semibold">Photo (ukuran 4×6 atau 2×3)</label>
                            <input type="file" id="photo" class="w-full p-2 border rounded">
                        </div>
                    </div>
                </div>

                <!-- Data Orang Tua -->
                <div>
                    <h3 class="bg-green-700 text-white px-4 py-2 rounded-t">Orang Tua</h3>
                    <div class="border p-4 space-y-4 rounded-b">
                        <div>
                            <label for="nama_ayah" class="block font-semibold">Nama Ayah</label>
                            <input type="text" id="nama_ayah" class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label for="nama_ibu" class="block font-semibold">Nama Ibu</label>
                            <input type="text" id="nama_ibu" class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label for="pekerjaan_ayah" class="block font-semibold">Pekerjaan Ayah</label>
                            <input type="text" id="pekerjaan_ayah" class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label for="pekerjaan_ibu" class="block font-semibold">Pekerjaan Ibu</label>
                            <input type="text" id="pekerjaan_ibu" class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label for="wali_siswa" class="block font-semibold">Wali Siswa</label>
                            <input type="text" id="wali_siswa" class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label for="pekerjaan_wali" class="block font-semibold">Pekerjaan Wali Siswa</label>
                            <input type="text" id="pekerjaan_wali" class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label for="alamat_wali" class="block font-semibold">Alamat Orang Tua/Wali</label>
                            <input type="text" id="alamat_wali" class="w-full p-2 border rounded">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

</body>

</html>
