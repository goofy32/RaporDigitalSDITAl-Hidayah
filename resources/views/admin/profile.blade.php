<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <title>Profil Sekolah</title>
    <meta name="turbo-cache-control" content="no-cache">
    <meta name="turbo-visit-control" content="reload">
</head>

<body>
    <x-admin.topbar></x-admin.topbar>
    <x-admin.sidebar></x-admin.sidebar>
    
    @if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    
    <div class="p-4 sm:ml-64">
        <div class="p-4 bg-white mt-14">
            <form action="{{ route('profile.submit') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Tambahkan div debug yang hanya terlihat saat debugging -->
                <div x-show="debugInfo" class="mb-4 p-2 bg-yellow-100 border border-yellow-400 text-yellow-700">
                    <p class="font-bold">Debug Info:</p>
                    <p x-text="debugInfo"></p>
                    <p>Mapping data:</p>
                    <pre x-text="JSON.stringify(tahunAjaranMapping, null, 2)"></pre>
                </div>
                <!-- Logo Sekolah -->
                <div class="flex flex-col mb-4">
                    @if(isset($profil->logo))
                        <img id="profileImage" class="w-32 h-32 rounded-full shadow-lg items-center" src="{{ asset('storage/' . $profil->logo) }}" alt="Logo Sekolah">
                    @else
                        <img id="profileImage" class="w-32 h-32 rounded-full shadow-lg items-center" src="https://via.placeholder.com/150" alt="Logo Sekolah">
                    @endif

                    <label class="block mb-2 text-sm font-medium text-gray-900 mt-4" for="logo">Upload Logo</label>
                    <input
                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                        id="logo" name="logo" type="file" accept="image/*">
                    <p class="mt-1 text-sm text-gray-500">PNG, JPG (MAX. 800x400px).</p>

                    @error('logo')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Grid Form -->
                <div class="grid gap-6 mb-6 md:grid-cols-2">
                    <!-- Nama Instansi -->
                    <div>
                        <label for="nama_instansi" class="block mb-2 text-sm font-medium text-gray-900">Nama Instansi</label>
                        <input type="text" id="nama_instansi" name="nama_instansi" value="{{ old('nama_instansi', $profil->nama_instansi ?? '') }}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                        @error('nama_instansi')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama Sekolah -->
                    <div>
                        <label for="nama_sekolah" class="block mb-2 text-sm font-medium text-gray-900">Nama Sekolah</label>
                        <input type="text" id="nama_sekolah" name="nama_sekolah" value="{{ old('nama_sekolah', $profil->nama_sekolah ?? '') }}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                        @error('nama_sekolah')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- NPSN -->
                    <div>
                        <label for="npsn" class="block mb-2 text-sm font-medium text-gray-900">NPSN</label>
                        <input type="text" id="npsn" name="npsn" value="{{ old('npsn', $profil->npsn ?? '') }}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                        @error('npsn')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Alamat -->
                    <div>
                        <label for="alamat" class="block mb-2 text-sm font-medium text-gray-900">Alamat</label>
                        <input type="text" id="alamat" name="alamat" value="{{ old('alamat', $profil->alamat ?? '') }}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                        @error('alamat')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kelurahan -->
                    <div>
                        <label for="kelurahan" class="block mb-2 text-sm font-medium text-gray-900">Kelurahan/Desa</label>
                        <input type="text" id="kelurahan" name="kelurahan" value="{{ old('kelurahan', $profil->kelurahan ?? '') }}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                        @error('kelurahan')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kecamatan -->
                    <div>
                        <label for="kecamatan" class="block mb-2 text-sm font-medium text-gray-900">Kecamatan</label>
                        <input type="text" id="kecamatan" name="kecamatan" value="{{ old('kecamatan', $profil->kecamatan ?? '') }}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                        @error('kecamatan')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kabupaten/Kota -->
                    <div>
                        <label for="kabupaten" class="block mb-2 text-sm font-medium text-gray-900">Kabupaten/Kota</label>
                        <input type="text" id="kabupaten" name="kabupaten" value="{{ old('kabupaten', $profil->kabupaten ?? '') }}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                        @error('kabupaten')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Provinsi -->
                    <div>
                        <label for="provinsi" class="block mb-2 text-sm font-medium text-gray-900">Provinsi</label>
                        <input type="text" id="provinsi" name="provinsi" value="{{ old('provinsi', $profil->provinsi ?? '') }}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                        @error('provinsi')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kode POS -->
                    <div>
                        <label for="kode_pos" class="block mb-2 text-sm font-medium text-gray-900">Kode POS</label>
                        <input type="text" id="kode_pos" name="kode_pos" value="{{ old('kode_pos', $profil->kode_pos ?? '') }}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                        @error('kode_pos')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Telepon -->
                    <div>
                        <label for="telepon" class="block mb-2 text-sm font-medium text-gray-900">Telepon</label>
                        <input type="text" id="telepon" name="telepon" value="{{ old('telepon', $profil->telepon ?? '') }}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                        @error('telepon')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Sekolah -->
                    <div>
                        <label for="email_sekolah" class="block mb-2 text-sm font-medium text-gray-900">Email Sekolah</label>
                        <input type="email" id="email_sekolah" name="email_sekolah" value="{{ old('email_sekolah', $profil->email_sekolah ?? '') }}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                        @error('email_sekolah')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Website -->
                    <div>
                        <label for="website" class="block mb-2 text-sm font-medium text-gray-900">Website (Opsional)</label>
                        <input type="url" id="website" name="website" value="{{ old('website', $profil->website ?? '') }}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                        @error('website')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tahun Pelajaran -->
                    <div>
                        <label for="tahun_pelajaran" class="block mb-2 text-sm font-medium text-gray-900">Tahun Pelajaran</label>
                        <select id="tahun_pelajaran" name="tahun_pelajaran" 
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                            <option value="">Pilih Tahun Pelajaran</option>
                            @foreach($tahunAjarans as $ta)
                                <option value="{{ $ta->tahun_ajaran }}" data-semester="{{ $ta->semester }}" {{ (old('tahun_pelajaran', $profil->tahun_pelajaran ?? '') == $ta->tahun_ajaran) ? 'selected' : '' }}>
                                    {{ $ta->tahun_ajaran }} - {{ $ta->semester == 1 ? 'Ganjil' : 'Genap' }}
                                </option>
                            @endforeach
                        </select>
                        @error('tahun_pelajaran')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Semester -->
                    <div>
                        <label for="semester" class="block mb-2 text-sm font-medium text-gray-900">Semester</label>
                        <select id="semester" name="semester" 
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                            <option value="">Pilih Semester</option>
                            <option value="1" {{ (old('semester', $profil->semester ?? '') == 1) ? 'selected' : '' }}>Ganjil</option>
                            <option value="2" {{ (old('semester', $profil->semester ?? '') == 2) ? 'selected' : '' }}>Genap</option>
                        </select>
                        <p id="semester-info" class="text-sm text-gray-500 mt-1" style="display: none;">
                            Semester otomatis diisi berdasarkan tahun ajaran yang dipilih
                        </p>
                        @error('semester')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kepala Sekolah -->
                    <div>
                        <label for="kepala_sekolah" class="block mb-2 text-sm font-medium text-gray-900">Kepala Sekolah</label>
                        <input type="text" id="kepala_sekolah" name="kepala_sekolah" value="{{ old('kepala_sekolah', $profil->kepala_sekolah ?? '') }}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                        @error('kepala_sekolah')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- NIP Kepala Sekolah -->
                    <div>
                        <label for="nip_kepala_sekolah" class="block mb-2 text-sm font-medium text-gray-900">NIP Kepala Sekolah</label>
                        <input type="text" id="nip_kepala_sekolah" name="nip_kepala_sekolah" value="{{ old('nip_kepala_sekolah', $profil->nip_kepala_sekolah ?? '') }}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                        @error('nip_kepala_sekolah')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- NIP Wali Kelas (Default untuk Template UAS) -->
                    <div>
                        <label for="nip_wali_kelas" class="block mb-2 text-sm font-medium text-gray-900">NIP Wali Kelas (Default)</label>
                        <input type="text" id="nip_wali_kelas" name="nip_wali_kelas" value="{{ old('nip_wali_kelas', $profil->nip_wali_kelas ?? '') }}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                        @error('nip_wali_kelas')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kelas (Opsional) -->
                    <div>
                        <label for="kelas" class="block mb-2 text-sm font-medium text-gray-900">Jumlah Kelas</label>
                        <input type="number" id="kelas" name="kelas" value="{{ old('kelas', $profil->kelas ?? '') }}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                        @error('kelas')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Guru Kelas (Opsional) -->
                    <div>
                        <label for="guru_kelas" class="block mb-2 text-sm font-medium text-gray-900">Jumlah Guru Kelas</label>
                        <input type="number" id="guru_kelas" name="guru_kelas" value="{{ old('guru_kelas', $profil->guru_kelas ?? '') }}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                        @error('guru_kelas')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jumlah Siswa (Opsional) -->
                    <div>
                        <label for="jumlah_siswa" class="block mb-2 text-sm font-medium text-gray-900">Jumlah Siswa</label>
                        <input type="number" id="jumlah_siswa" name="jumlah_siswa" value="{{ old('jumlah_siswa', $profil->jumlah_siswa ?? '') }}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                        @error('jumlah_siswa')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Tempat Terbit -->
                    <div>
                        <label for="tempat_terbit" class="block mb-2 text-sm font-medium text-gray-900">Tempat Terbit Rapor</label>
                        <input type="text" id="tempat_terbit" name="tempat_terbit" value="{{ old('tempat_terbit', $profil->tempat_terbit ?? '') }}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                        @error('tempat_terbit')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal Terbit -->
                    <div>
                        <label for="tanggal_terbit" class="block mb-2 text-sm font-medium text-gray-900">Tanggal Terbit Rapor</label>
                        <input type="date" id="tanggal_terbit" name="tanggal_terbit" value="{{ old('tanggal_terbit', $profil->tanggal_terbit ?? '') }}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                        @error('tanggal_terbit')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Tombol Submit -->
                <button type="submit"
                    class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">
                    Simpan
                </button>
        </div>
    </div>
    
    <!-- Script untuk Preview Gambar -->
    <script>
        document.getElementById('logo').addEventListener('change', function(event) {
            var input = event.target;
            var reader = new FileReader();
            reader.onload = function(){
                var dataURL = reader.result;
                var output = document.getElementById('profileImage');
                output.src = dataURL;
                output.classList.add('object-cover'); // Pastikan gambar tidak terpotong
                output.classList.add('object-center'); // Posisikan gambar di tengah
            };
            reader.readAsDataURL(input.files[0]);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tahunSelect = document.getElementById('tahun_pelajaran');
            const semesterSelect = document.getElementById('semester');
            const semesterInfo = document.getElementById('semester-info');
            
            // Cek apakah ini load pertama atau form submission dengan error
            const hasOldSemester = "{{ old('semester') }}" !== "";
            
            console.log('Initial state:', {
                'Selected tahun ajaran': tahunSelect.value,
                'Current semester value': semesterSelect.value,
                'Has old semester value': hasOldSemester,
                'Old semester value': "{{ old('semester') }}",
                'DB semester value': "{{ $profil->semester ?? '' }}"
            });
            
            // Fungsi untuk update semester berdasarkan tahun ajaran
            function updateSemester() {
                // Hanya update jika tidak ada old data (form belum pernah disubmit dengan error)
                if (hasOldSemester) {
                    console.log('Keeping old semester value:', semesterSelect.value);
                    return;
                }
                
                const selectedOption = tahunSelect.options[tahunSelect.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const semester = selectedOption.getAttribute('data-semester');
                    if (semester) {
                        console.log('Setting semester to:', semester);
                        semesterSelect.value = semester;
                        semesterInfo.style.display = 'block';
                    }
                }
            }
            
            // Tambahkan event listener untuk perubahan tahun ajaran
            tahunSelect.addEventListener('change', function() {
                console.log('Tahun ajaran changed to:', this.value);
                // Selalu update saat user secara aktif mengubah tahun ajaran
                const selectedOption = tahunSelect.options[tahunSelect.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const semester = selectedOption.getAttribute('data-semester');
                    if (semester) {
                        console.log('Setting semester to:', semester);
                        semesterSelect.value = semester;
                        semesterInfo.style.display = 'block';
                    }
                } else {
                    semesterInfo.style.display = 'none';
                }
            });
            
            // Jika tahun ajaran sudah terpilih dan ini adalah load pertama,
            // kita bisa update semester
            if (tahunSelect.value && !hasOldSemester) {
                console.log('Initial load with pre-selected tahun ajaran');
                updateSemester();
            } else {
                console.log('Using existing semester value');
            }
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
</body>
</html>