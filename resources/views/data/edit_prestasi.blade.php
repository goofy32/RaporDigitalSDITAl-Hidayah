@extends('layouts.app')

@section('title', 'Edit Data Prestasi')

@section('content')
<div class="p-6 bg-white mt-14" x-data="siswaFilter()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Form Edit Data Prestasi</h2>
        <div>
            <a href="{{ route('achievement.index') }}" class="px-4 py-2 mr-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                Kembali
            </a>
            <button type="submit" form="updatePrestasiForm" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Simpan
            </button>
        </div>
    </div>

    <!-- Form Edit Data Prestasi -->
    <form id="updatePrestasiForm" action="{{ route('achievement.update', $prestasi->id) }}"  @submit="handleSubmit" x-data="formProtection" method="post" class="space-y-6">
        @csrf
        @method('PUT')

        <input type="hidden" name="tahun_ajaran_id" value="{{ session('tahun_ajaran_id') }}">

        <!-- Kelas -->
        <div>
            <label for="kelas" class="block mb-2 text-sm font-medium text-gray-900">Kelas</label>
            <select 
                id="kelas" 
                name="kelas_id" 
                x-model="selectedKelasId" 
                required
                @change="updateSiswaOptions"
                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900"
            >
                <option value="">Pilih Kelas</option>
                @foreach ($kelas as $item)
                    <option value="{{ $item->id }}" {{ $prestasi->kelas_id == $item->id ? 'selected' : '' }}>
                        Kelas {{ $item->nomor_kelas }} - {{ $item->nama_kelas }}
                    </option>
                @endforeach
            </select>
            @error('kelas_id')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nama Siswa -->
        <div>
            <label for="nama_siswa" class="block mb-2 text-sm font-medium text-gray-900">Nama Siswa</label>
            <select 
                id="nama_siswa" 
                name="siswa_id" 
                x-model="selectedSiswaId"
                required
                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900"
            >
                <option value="">Pilih Siswa</option>
                <template x-for="siswa in filteredSiswa" :key="siswa.id">
                    <option 
                        :value="siswa.id" 
                        x-text="siswa.nama"
                        :selected="siswa.id == {{ $prestasi->siswa_id }}"
                    ></option>
                </template>
            </select>
            @error('siswa_id')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Jenis Prestasi -->
        <div>
            <label for="jenis_prestasi" class="block mb-2 text-sm font-medium text-gray-900">Jenis Prestasi</label>
            <input type="text" id="jenis_prestasi" name="jenis_prestasi" value="{{ $prestasi->jenis_prestasi }}" required
                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">
            @error('jenis_prestasi')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Keterangan -->
        <div>
            <label for="keterangan" class="block mb-2 text-sm font-medium text-gray-900">Keterangan</label>
            <textarea id="keterangan" name="keterangan" rows="4" required
                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">{{ $prestasi->keterangan }}</textarea>
            @error('keterangan')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </form>
</div>

@push('scripts')
<script>
    function siswaFilter() {
        return {
            selectedKelasId: {{ $prestasi->kelas_id }},
            selectedSiswaId: {{ $prestasi->siswa_id }},
            allSiswa: @json($siswa),
            
            init() {
                // Pastikan dropdown siswa terisi saat halaman dimuat
                this.updateSiswaOptions();
                
                // Pastikan siswa yang sedang diedit tetap terpilih
                this.selectedSiswaId = {{ $prestasi->siswa_id }};
            },
            
            updateSiswaOptions() {
                // Pastikan dropdown siswa diperbarui
                const filteredSiswa = this.filteredSiswa;
                
                // Selalu tampilkan siswa dari kelas yang sedang diedit
                if (this.selectedSiswaId && !filteredSiswa.some(siswa => siswa.id == this.selectedSiswaId)) {
                    // Tambahkan siswa yang sedang diedit meskipun tidak sesuai kelas yang dipilih
                    const currentSiswa = this.allSiswa.find(siswa => siswa.id == this.selectedSiswaId);
                    if (currentSiswa) {
                        filteredSiswa.push(currentSiswa);
                    }
                }
            },
            
            get filteredSiswa() {
                if (!this.selectedKelasId) return [];
                
                return this.allSiswa
                    .filter(siswa => siswa.kelas_id == this.selectedKelasId)
                    .sort((a, b) => a.nama.localeCompare(b.nama));
            }
        }
    }
</script>
@endpush
@endsection