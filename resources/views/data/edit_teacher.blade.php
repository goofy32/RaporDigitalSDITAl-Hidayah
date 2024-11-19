<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <title>Form Edit Data Pengajar</title>
</head>

<body>
    <x-admin.topbar></x-admin.topbar>
    <x-admin.sidebar></x-admin.sidebar>

    <div class="p-4 sm:ml-64">
        <div class="p-4 bg-white mt-14 rounded-lg shadow">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-green-700">Form Edit Data Pengajar</h2>
                <div class="flex space-x-2">
                    <button onclick="window.history.back()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        Kembali
                    </button>
                    <button form="editTeacherForm" type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Simpan
                    </button>
                </div>
            </div>

            <!-- Form -->
            <form id="editTeacherForm" action="{{ route('teacher.update', $teacher->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Ganti name="nip" menjadi name="nuptk" -->
                

                <!-- Kolom Kiri -->
                <div>
                    <label for="nip" class="block text-sm font-medium text-gray-700">NIP</label>
                    <input type="text" id="nip" name="nuptk" value="{{ $teacher->nuptk }}" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" required>

                    <label for="nama" class="block mt-4 text-sm font-medium text-gray-700">Nama</label>
                    <input type="text" id="nama" name="nama" value="{{ $teacher->nama }}" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" required>

                    <label for="jenis_kelamin" class="block mt-4 text-sm font-medium text-gray-700">Jenis Kelamin</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" class="w-full mt-1 p-2 border border-gray-300 rounded-lg">
                        <option value="Laki-laki" {{ $teacher->jenis_kelamin === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ $teacher->jenis_kelamin === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>

                    <label for="tanggal_lahir" class="block mt-4 text-sm font-medium text-gray-700">Tanggal Lahir</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ $teacher->tanggal_lahir }}" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" required>

                    <label for="no_handphone" class="block mt-4 text-sm font-medium text-gray-700">No Handphone</label>
                    <input type="text" id="no_handphone" name="no_handphone" value="{{ $teacher->no_handphone }}" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" required>

                    <label for="email" class="block mt-4 text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" value="{{ $teacher->email }}" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" required>
                </div>

                <!-- Kolom Kanan -->
                <div>
                    <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
                    <textarea id="alamat" name="alamat" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" rows="4" required>{{ $teacher->alamat }}</textarea>

                    <label for="jabatan" class="block mt-4 text-sm font-medium text-gray-700">Jabatan</label>
                    <input type="text" id="jabatan" name="jabatan" value="{{ $teacher->jabatan }}" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" required>

                    <label for="kelas_pengajar_id" class="block mt-4 text-sm font-medium text-gray-700">Kelas Mengajar</label>
                    <select id="kelas_pengajar_id" name="kelas_pengajar_id" class="w-full mt-1 p-2 border border-gray-300 rounded-lg">
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" {{ $teacher->kelas_pengajar_id == $class->id ? 'selected' : '' }}>
                                {{ $class->nama_kelas }}
                            </option>
                        @endforeach
                    </select>

                    <label for="photo" class="block mt-4 text-sm font-medium text-gray-700">Photo (ukuran 4x6 atau 2x3)</label>
                    <input type="file" id="photo" name="photo" class="w-full mt-1 p-2 border border-gray-300 rounded-lg">

                    <label for="username" class="block mt-4 text-sm font-medium text-gray-700">Username</label>
                    <input type="text" id="username" name="username" value="{{ $teacher->username }}" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" required>
                    
                     <!-- Tambahkan field password_confirmation -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="block mt-4 text-sm font-medium text-gray-700">Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="w-full mt-1 p-2 border border-gray-300 rounded-lg">
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
