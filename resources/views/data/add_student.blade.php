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
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-green-700">Form Tambah Data Siswa</h2>
            </div>

            <form action="{{ route('student.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @csrf
                <!-- Data Diri -->
                <div>
                    <h3 class="bg-green-700 text-white px-4 py-2 rounded-t">Data Diri</h3>
                    <div class="border p-4 space-y-4 rounded-b">
                        <div>
                            <label for="nis" class="block font-semibold">NIS</label>
                            <input type="text" id="nis" name="nis" class="w-full p-2 border rounded @error('nis') border-red-500 @enderror" value="{{ old('nis') }}" required>
                            @error('nis')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nisn" class="block font-semibold">NISN</label>
                            <input type="text" id="nisn" name="nisn" class="w-full p-2 border rounded @error('nisn') border-red-500 @enderror" value="{{ old('nisn') }}" required>
                            @error('nisn')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nama" class="block font-semibold">Nama</label>
                            <input type="text" id="nama" name="nama" class="w-full p-2 border rounded @error('nama') border-red-500 @enderror" value="{{ old('nama') }}" required>
                            @error('nama')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tanggal_lahir" class="block font-semibold">Tanggal Lahir</label>
                            <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="w-full p-2 border rounded @error('tanggal_lahir') border-red-500 @enderror" value="{{ old('tanggal_lahir') }}" required>
                            @error('tanggal_lahir')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jenis_kelamin" class="block font-semibold">Jenis Kelamin</label>
                            <select id="jenis_kelamin" name="jenis_kelamin" class="w-full p-2 border rounded @error('jenis_kelamin') border-red-500 @enderror" required>
                                <option value="">Pilih</option>
                                <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('jenis_kelamin')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="agama" class="block font-semibold">Agama</label>
                            <input type="text" id="agama" name="agama" class="w-full p-2 border rounded @error('agama') border-red-500 @enderror" value="{{ old('agama') }}" required>
                            @error('agama')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="alamat" class="block font-semibold">Alamat</label>
                            <textarea id="alamat" name="alamat" class="w-full p-2 border rounded @error('alamat') border-red-500 @enderror" required>{{ old('alamat') }}</textarea>
                            @error('alamat')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="kelas_id" class="block font-semibold">Kelas</label>
                            <select id="kelas_id" name="kelas_id" class="w-full p-2 border rounded @error('kelas_id') border-red-500 @enderror" required>
                                <option value="">Pilih Kelas</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nomor_kelas }} {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kelas_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="photo" class="block font-semibold">Photo (ukuran 4×6 atau 2×3)</label>
                            <input type="file" id="photo" name="photo" class="w-full p-2 border rounded @error('photo') border-red-500 @enderror">
                            @error('photo')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Data Orang Tua -->
                <div>
                    <h3 class="bg-green-700 text-white px-4 py-2 rounded-t">Data Orang Tua</h3>
                    <div class="border p-4 space-y-4 rounded-b">
                        <div>
                            <label for="nama_ayah" class="block font-semibold">Nama Ayah</label>
                            <input type="text" id="nama_ayah" name="nama_ayah" class="w-full p-2 border rounded @error('nama_ayah') border-red-500 @enderror" value="{{ old('nama_ayah') }}">
                            @error('nama_ayah')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nama_ibu" class="block font-semibold">Nama Ibu</label>
                            <input type="text" id="nama_ibu" name="nama_ibu" class="w-full p-2 border rounded @error('nama_ibu') border-red-500 @enderror" value="{{ old('nama_ibu') }}">
                            @error('nama_ibu')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="pekerjaan_ayah" class="block font-semibold">Pekerjaan Ayah (Opsional)</label>
                            <input type="text" id="pekerjaan_ayah" name="pekerjaan_ayah" class="w-full p-2 border rounded @error('pekerjaan_ayah') border-red-500 @enderror" value="{{ old('pekerjaan_ayah') }}">
                            @error('pekerjaan_ayah')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="pekerjaan_ibu" class="block font-semibold">Pekerjaan Ibu (Opsional)</label>
                            <input type="text" id="pekerjaan_ibu" name="pekerjaan_ibu" class="w-full p-2 border rounded @error('pekerjaan_ibu') border-red-500 @enderror" value="{{ old('pekerjaan_ibu') }}">
                            @error('pekerjaan_ibu')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="alamat_orangtua" class="block font-semibold">Alamat Orang Tua (Opsional)</label>
                            <textarea id="alamat_orangtua" name="alamat_orangtua" class="w-full p-2 border rounded @error('alamat_orangtua') border-red-500 @enderror">{{ old('alamat_orangtua') }}</textarea>
                            @error('alamat_orangtua')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <h3 class="bg-green-700 text-white px-4 py-2 rounded-t">Data Wali (Opsional)</h3>
                        <div class="border p-4 space-y-4 rounded-b">
                            <div>
                                <label for="wali_siswa" class="block font-semibold">Nama Wali</label>
                                <input type="text" id="wali_siswa" name="wali_siswa" 
                                    class="w-full p-2 border rounded @error('wali_siswa') border-red-500 @enderror" 
                                    value="{{ old('wali_siswa') }}">
                                @error('wali_siswa')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
    
                            <div>
                                <label for="pekerjaan_wali" class="block font-semibold">Pekerjaan Wali</label>
                                <input type="text" id="pekerjaan_wali" name="pekerjaan_wali" 
                                    class="w-full p-2 border rounded @error('pekerjaan_wali') border-red-500 @enderror" 
                                    value="{{ old('pekerjaan_wali') }}">
                                @error('pekerjaan_wali')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
    
                        </div>
                    </div>    
                </div>


                <!-- Tombol Submit -->
                <div class="col-span-2 flex justify-end space-x-2 mt-4">
                    <button type="submit" class="bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800">Simpan</button>
                    <a href="{{ route('student') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>