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
    <form id="updatePrestasiForm" action="{{ route('achievement.update', $prestasi->id) }}"  @submit="handleSubmit" x-data="formProtection" method="post" class="space-y-6" data-needs-protection>
        @csrf
        @method('PUT')

        <input type="hidden" name="tahun_ajaran_id" value="{{ session('tahun_ajaran_id') }}">
        <!-- Menyimpan siswa_id dalam hidden input untuk memastikan datanya terkirim -->
        <input type="hidden" name="siswa_id" value="{{ $prestasi->siswa_id }}">

        <!-- Kelas (Read-only) -->
        <div>
            <label for="kelas_display" class="block mb-2 text-sm font-medium text-gray-900">Kelas</label>
            <input 
                type="text" 
                id="kelas_display" 
                value="Kelas {{ $prestasi->kelas->nomor_kelas ?? '' }} - {{ $prestasi->kelas->nama_kelas ?? '' }}"
                disabled
                readonly
                class="block w-full p-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 cursor-not-allowed"
            >
            <!-- Hidden input to ensure kelas_id is still submitted -->
            <input type="hidden" name="kelas_id" value="{{ $prestasi->kelas_id }}">
            <p class="text-sm text-gray-500 mt-1"></p>
            @error('kelas_id')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nama Siswa (Read-only) -->
        <div>
            <label for="nama_siswa_display" class="block mb-2 text-sm font-medium text-gray-900">Nama Siswa</label>
            <input 
                type="text" 
                id="nama_siswa_display" 
                value="{{ $prestasi->siswa->nis ?? '' }} - {{ $prestasi->siswa->nama ?? '' }}"
                disabled
                readonly
                class="block w-full p-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 cursor-not-allowed"
            >
            <p class="text-sm text-gray-500 mt-1"></p>
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
                // Tidak perlu melakukan apapun pada edit karena kita tidak bisa mengganti siswa
            }
        }
    }
</script>
@endpush
@endsection