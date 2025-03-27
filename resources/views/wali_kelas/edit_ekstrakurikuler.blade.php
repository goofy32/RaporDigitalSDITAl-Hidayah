@extends('layouts.wali_kelas.app')

@section('title', 'Edit Data Ekstrakurikuler')

@section('content')
<div class="p-6 bg-white mt-14">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Form Edit Data Ekstrakurikuler</h2>
        <div>
            <a href="{{ route('wali_kelas.ekstrakurikuler.index') }}" class="px-4 py-2 mr-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                Kembali
            </a>
            <button type="submit" form="editEkskulForm" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Simpan
            </button>
        </div>
    </div>

    <!-- Form -->
    <form id="editEkskulForm" action="{{ route('wali_kelas.ekstrakurikuler.update', $nilaiEkstrakurikuler->id) }}" x-data="formProtection" @submit="handleSubmit" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <input type="hidden" name="tahun_ajaran_id" value="{{ session('tahun_ajaran_id') }}">

        <!-- Info Siswa (readonly) -->
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">NIS - Nama Siswa</label>
            <input type="text" value="{{ $nilaiEkstrakurikuler->siswa->nis }} - {{ $nilaiEkstrakurikuler->siswa->nama }}" 
                   class="block w-full p-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-900" readonly>
        </div>

        <!-- Info Ekstrakurikuler (readonly) -->
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Ekstrakurikuler</label>
            <input type="text" value="{{ $nilaiEkstrakurikuler->ekstrakurikuler->nama_ekstrakurikuler }}" 
                   class="block w-full p-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-900" readonly>
        </div>

        <!-- Predikat -->
        <div>
            <label for="predikat" class="block mb-2 text-sm font-medium text-gray-900">Predikat</label>
            <select id="predikat" name="predikat" required
                    class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">
                <option value="">Pilih Predikat</option>
                <option value="Sangat Baik" {{ old('predikat', $nilaiEkstrakurikuler->predikat) == 'Sangat Baik' ? 'selected' : '' }}>Sangat Baik</option>
                <option value="Baik" {{ old('predikat', $nilaiEkstrakurikuler->predikat) == 'Baik' ? 'selected' : '' }}>Baik</option>
                <option value="Cukup" {{ old('predikat', $nilaiEkstrakurikuler->predikat) == 'Cukup' ? 'selected' : '' }}>Cukup</option>
                <option value="Kurang" {{ old('predikat', $nilaiEkstrakurikuler->predikat) == 'Kurang' ? 'selected' : '' }}>Kurang</option>
            </select>
            @error('predikat')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Deskripsi -->
        <div>
            <label for="deskripsi" class="block mb-2 text-sm font-medium text-gray-900">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="4"
                      class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">{{ old('deskripsi', $nilaiEkstrakurikuler->deskripsi) }}</textarea>
            @error('deskripsi')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </form>
</div>
@endsection