@extends('layouts.wali_kelas.app')

@section('title', 'Edit Data Absensi')

@section('content')
<div class="p-6 bg-white mt-14">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Form Edit Data Absensi</h2>
        <div>
            <a href="{{ route('wali_kelas.absence.index') }}" class="px-4 py-2 mr-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                Kembali
            </a>
            <button type="submit" form="editAbsenceForm" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Simpan
            </button>
        </div>
    </div>

    <!-- Form -->
    <form id="editAbsenceForm" action="{{ route('wali_kelas.absence.update', $absensi->id) }}" x-data="formProtection" @submit="handleSubmit" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Info Siswa (readonly) -->
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Siswa</label>
            <input type="text" value="{{ $absensi->siswa->nis }} - {{ $absensi->siswa->nama }}" 
                   class="block w-full p-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-900" readonly>
        </div>


        <div>
            <label for="semester" class="block mb-2 text-sm font-medium text-gray-900">Semester</label>
            <select id="semester" name="semester" required class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">
                <option value="1" {{ old('semester', $absensi->semester ?? '') == 1 ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                <option value="2" {{ old('semester', $absensi->semester ?? '') == 2 ? 'selected' : '' }}>Semester 2 (Genap)</option>
            </select>
            @error('semester')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Sakit -->
        <div>
            <label for="sakit" class="block mb-2 text-sm font-medium text-gray-900">Sakit (Hari)</label>
            <input type="number" id="sakit" name="sakit" value="{{ old('sakit', $absensi->sakit) }}" min="0" required
                   class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">
            @error('sakit')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Izin -->
        <div>
            <label for="izin" class="block mb-2 text-sm font-medium text-gray-900">Izin (Hari)</label>
            <input type="number" id="izin" name="izin" value="{{ old('izin', $absensi->izin) }}" min="0" required
                   class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">
            @error('izin')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tanpa Keterangan -->
        <div>
            <label for="tanpa_keterangan" class="block mb-2 text-sm font-medium text-gray-900">Tanpa Keterangan (Hari)</label>
            <input type="number" id="tanpa_keterangan" name="tanpa_keterangan" value="{{ old('tanpa_keterangan', $absensi->tanpa_keterangan) }}" min="0" required
                   class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">
            @error('tanpa_keterangan')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </form>
</div>
@endsection