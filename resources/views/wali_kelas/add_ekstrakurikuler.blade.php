@extends('layouts.wali_kelas.app')

@section('title', 'Tambah Data Ekstrakurikuler')

@section('content')
<div class="p-4 bg-white rounded-lg shadow-sm">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Form Tambah Data Ekstrakurikuler</h2>
    </div>

    <form action="{{ route('wali_kelas.ekstrakurikuler.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- NIS -->
        <div>
            <label for="nis" class="block text-sm font-medium text-gray-700 mb-1">NIS</label>
            <input type="text" id="nis" name="nis" 
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5 @error('nis') border-red-500 @enderror"
                value="{{ old('nis') }}" required>
            @error('nis')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nama -->
        <div>
            <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
            <input type="text" id="nama" name="nama" 
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5"
                value="{{ old('nama') }}" readonly>
        </div>

        <!-- Ekstrakurikuler -->
        <div>
            <label for="ekstrakurikuler" class="block text-sm font-medium text-gray-700 mb-1">Ekstrakurikuler</label>
            <select id="ekstrakurikuler" name="ekstrakurikuler" 
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5 @error('ekstrakurikuler') border-red-500 @enderror"
                required>
                <option value="">Pilih Ekstrakurikuler</option>
                    <option value="{{ $ekskul->id }}" {{ old('ekstrakurikuler') == $ekskul->id ? 'selected' : '' }}>
                        {{ $ekskul->nama }}
                    </option>
            </select>
            @error('ekstrakurikuler')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Predikat -->
        <div>
            <label for="predikat" class="block text-sm font-medium text-gray-700 mb-1">Predikat</label>
            <select id="predikat" name="predikat" 
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5 @error('predikat') border-red-500 @enderror"
                required>
                <option value="">Pilih Predikat</option>
                <option value="A" {{ old('predikat') == 'A' ? 'selected' : '' }}>A</option>
                <option value="B" {{ old('predikat') == 'B' ? 'selected' : '' }}>B</option>
                <option value="C" {{ old('predikat') == 'C' ? 'selected' : '' }}>C</option>
                <option value="D" {{ old('predikat') == 'D' ? 'selected' : '' }}>D</option>
            </select>
            @error('predikat')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Deskripsi -->
        <div>
            <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="4"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5 @error('deskripsi') border-red-500 @enderror"
                required>{{ old('deskripsi') }}</textarea>
            @error('deskripsi')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Buttons -->
        <div class="flex justify-end space-x-2">
            <button type="submit"
                class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5">
                Simpan
            </button>
            <a href="{{ route('wali_kelas.ekstrakurikuler.index') }}"
                class="text-gray-900 bg-gray-300 hover:bg-gray-400 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5">
                Kembali
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('formData', () => ({
            async fetchStudentData() {
                const nis = document.getElementById('nis').value;
                if (nis) {
                    try {
                        const response = await fetch(`/api/students/${nis}`);
                        const data = await response.json();
                        if (data.student) {
                            document.getElementById('nama').value = data.student.nama;
                        }
                    } catch (error) {
                        console.error('Error fetching student data:', error);
                    }
                }
            }
        }));
    });

    // Auto-populate student name when NIS is entered
    document.getElementById('nis').addEventListener('blur', function() {
        Alpine.data('formData')().fetchStudentData();
    });
</script>
@endpush
@endsection