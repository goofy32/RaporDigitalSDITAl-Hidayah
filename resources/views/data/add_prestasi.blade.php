@extends('layouts.app')

@section('content')
<div class="p-6 bg-white mt-14" x-data="siswaFilter()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Form Tambah/Edit Data Prestasi</h2>
        <div>
            <a href="{{ route('achievement.index') }}" class="px-4 py-2 mr-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                Kembali
            </a>
            <button type="submit" form="prestasiForm" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Simpan
            </button>
        </div>
    </div>

    <!-- Form Tambah/Edit Data Prestasi -->
    <form 
        id="prestasiForm" 
        action="{{ isset($prestasi) ? route('achievement.update', $prestasi->id) : route('achievement.store') }}" 
        method="post" 
        class="space-y-6"
    >
        @csrf
        @if(isset($prestasi))
            @method('PUT')
        @endif

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
                    <option value="{{ $item->id }}" 
                        {{ isset($prestasi) && $prestasi->kelas_id == $item->id ? 'selected' : '' }}>
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
                        :selected="siswa.id == selectedSiswaId"
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
            <input 
                type="text" 
                id="jenis_prestasi" 
                name="jenis_prestasi" 
                value="{{ isset($prestasi) ? $prestasi->jenis_prestasi : old('jenis_prestasi') }}" 
                required
                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900"
            >
            @error('jenis_prestasi')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Keterangan -->
        <div>
            <label for="keterangan" class="block mb-2 text-sm font-medium text-gray-900">Keterangan</label>
            <textarea 
                id="keterangan" 
                name="keterangan" 
                rows="4" 
                required
                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900"
            >{{ isset($prestasi) ? $prestasi->keterangan : old('keterangan') }}</textarea>
            @error('keterangan')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </form>
</div>

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function siswaFilter() {
        return {
            selectedKelasId: {{ isset($prestasi) ? $prestasi->kelas_id : 'null' }},
            selectedSiswaId: {{ isset($prestasi) ? $prestasi->siswa_id : 'null' }},
            allSiswa: @json($siswa),
            
            init() {
                // Inisialisasi awal saat komponen dimuat
                this.updateSiswaOptions();
            },
            
            updateSiswaOptions() {
                // Reset selectedSiswaId jika kelas berubah
                const filteredSiswa = this.filteredSiswa;
                
                // Jika siswa yang dipilih sebelumnya tidak ada di kelas baru, reset
                if (!filteredSiswa.some(siswa => siswa.id == this.selectedSiswaId)) {
                    this.selectedSiswaId = null;
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