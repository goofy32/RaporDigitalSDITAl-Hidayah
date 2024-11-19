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
            <!-- Error Messages -->
            @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <strong class="font-bold">Terjadi kesalahan!</strong>
                <ul class="mt-2">
                    @foreach ($errors->all() as $error)
                        <li class="list-disc ml-4">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Success Message -->
            @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
            @endif

            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-green-700">Form Tambah Data Pengajar</h2>
                <div class="flex space-x-2">
                    <button onclick="window.history.back()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        Kembali
                    </button>
                </div>
            </div>

            <!-- Form -->
            <form action="{{ route('teacher.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-2 gap-6">
                @csrf
            
                <!-- Kolom Kiri -->
                <div>
                    <div class="mb-4">
                        <label for="nip" class="block text-sm font-medium text-gray-700">NIP</label>
                        <input type="text" id="nip" name="nuptk" value="{{ old('nuptk') }}" 
                            class="w-full mt-1 p-2 border @error('nuptk') border-red-500 @enderror border-gray-300 rounded-lg">
                        @error('nuptk')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="nama" class="block text-sm font-medium text-gray-700">Nama</label>
                        <input type="text" id="nama" name="nama" value="{{ old('nama') }}"
                            class="w-full mt-1 p-2 border @error('nama') border-red-500 @enderror border-gray-300 rounded-lg">
                        @error('nama')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="jenis_kelamin" class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                        <select id="jenis_kelamin" name="jenis_kelamin" 
                            class="w-full mt-1 p-2 border @error('jenis_kelamin') border-red-500 @enderror border-gray-300 rounded-lg">
                            <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('jenis_kelamin')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}"
                            class="w-full mt-1 p-2 border @error('tanggal_lahir') border-red-500 @enderror border-gray-300 rounded-lg">
                        @error('tanggal_lahir')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="no_handphone" class="block text-sm font-medium text-gray-700">No Handphone</label>
                        <input type="text" id="no_handphone" name="no_handphone" value="{{ old('no_handphone') }}"
                            class="w-full mt-1 p-2 border @error('no_handphone') border-red-500 @enderror border-gray-300 rounded-lg">
                        @error('no_handphone')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            class="w-full mt-1 p-2 border @error('email') border-red-500 @enderror border-gray-300 rounded-lg">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="3"
                            class="w-full mt-1 p-2 border @error('alamat') border-red-500 @enderror border-gray-300 rounded-lg">{{ old('alamat') }}</textarea>
                        @error('alamat')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            
                <!-- Kolom Kanan -->
                <div>
                    <div class="mb-4">
                        <label for="jabatan" class="block text-sm font-medium text-gray-700">Jabatan</label>
                        <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan') }}"
                            class="w-full mt-1 p-2 border @error('jabatan') border-red-500 @enderror border-gray-300 rounded-lg">
                        @error('jabatan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="kelas_pengajar_id" class="block text-sm font-medium text-gray-700">Kelas Mengajar</label>
                        <select id="kelas_pengajar_id" name="kelas_pengajar_id"
                            class="w-full mt-1 p-2 border @error('kelas_pengajar_id') border-red-500 @enderror border-gray-300 rounded-lg">
                            <option value="">Pilih Kelas</option>
                            @foreach ($classes as $class)
                            <option value="{{ $class->id }}" {{ old('kelas_pengajar_id') == $class->id ? 'selected' : '' }}>
                                Kelas {{ $class->nomor_kelas }} - {{ $class->nama_kelas }}
                            </option>
                            @endforeach
                        </select>
                        @error('kelas_pengajar_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" id="username" name="username" value="{{ old('username') }}"
                            class="w-full mt-1 p-2 border @error('username') border-red-500 @enderror border-gray-300 rounded-lg" required>
                        @error('username')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" id="password" name="password"
                            class="w-full mt-1 p-2 border @error('password') border-red-500 @enderror border-gray-300 rounded-lg" required>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="w-full mt-1 p-2 border border-gray-300 rounded-lg">
                    </div>

                    <div class="mb-4">
                        <label for="photo" class="block text-sm font-medium text-gray-700">Foto Pengajar (Opsional)</label>
                        <input type="file" id="photo" name="photo"
                            class="w-full mt-1 p-2 border @error('photo') border-red-500 @enderror border-gray-300 rounded-lg">
                        @error('photo')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Simpan Data
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('nip').addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15); // Maksimal 15 angka
        });
    
        document.getElementById('no_handphone').addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15); // Maksimal 15 angka
        });
    </script>
</body>

</html>