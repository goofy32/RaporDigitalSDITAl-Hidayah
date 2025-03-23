@extends('layouts.app')

@section('title', 'Tambah Tahun Ajaran')

@section('content')
<div class="p-4 bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Tambah Tahun Ajaran Baru</h2>
        <a href="{{ route('tahun.ajaran.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p class="font-bold">Terjadi kesalahan:</p>
        <ul class="list-disc ml-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('tahun.ajaran.store') }}" method="POST" class="space-y-6" data-needs-protection>
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="tahun_ajaran" class="block text-sm font-medium text-gray-700 mb-1">Tahun Ajaran <span class="text-red-500">*</span></label>
                <input type="text" name="tahun_ajaran" id="tahun_ajaran" value="{{ old('tahun_ajaran') }}" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                    placeholder="contoh: 2024/2025">
                <p class="mt-1 text-sm text-gray-500">Format: YYYY/YYYY (contoh: 2024/2025)</p>
            </div>

            <div>
                <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">Semester <span class="text-red-500">*</span></label>
                <select name="semester" id="semester" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                    <option value="">Pilih Semester</option>
                    <option value="1" {{ old('semester') == 1 ? 'selected' : '' }}>1 (Ganjil)</option>
                    <option value="2" {{ old('semester') == 2 ? 'selected' : '' }}>2 (Genap)</option>
                </select>
            </div>

            <div>
                <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
            </div>

            <div>
                <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_selesai" id="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
            </div>

            <div class="md:col-span-2">
                <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" rows="3"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                    placeholder="Berikan deskripsi singkat tentang tahun ajaran ini (opsional)">{{ old('deskripsi') }}</textarea>
            </div>

            <div class="md:col-span-2">
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}
                        class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700">
                        Aktifkan tahun ajaran ini sekarang
                    </label>
                </div>
                <p class="mt-1 text-sm text-gray-500">Jika diaktifkan, tahun ajaran lain akan dinonaktifkan secara otomatis.</p>
            </div>
        </div>

        <div class="flex justify-end space-x-3 mt-8">
            <a href="{{ route('tahun.ajaran.index') }}"
                class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Batal
            </a>
            <button type="submit"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Simpan Tahun Ajaran
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validasi format tahun ajaran
        const tahunAjaranInput = document.getElementById('tahun_ajaran');
        tahunAjaranInput.addEventListener('blur', function() {
            const value = this.value.trim();
            const pattern = /^\d{4}\/\d{4}$/;
            
            if (value && !pattern.test(value)) {
                const errorMsg = document.createElement('p');
                errorMsg.classList.add('text-red-500', 'text-sm', 'mt-1', 'tahun-ajaran-error');
                errorMsg.textContent = 'Format tahun ajaran harus YYYY/YYYY, contoh: 2024/2025';
                
                // Remove any existing error message
                const existingError = document.querySelector('.tahun-ajaran-error');
                if (existingError) existingError.remove();
                
                // Add the error message
                this.parentNode.appendChild(errorMsg);
                
                // Add invalid class to input
                this.classList.add('border-red-500');
            } else {
                // Remove error message if format is correct
                const existingError = document.querySelector('.tahun-ajaran-error');
                if (existingError) existingError.remove();
                
                // Remove invalid class
                this.classList.remove('border-red-500');
            }
        });
        
        // Validasi tanggal selesai harus setelah tanggal mulai
        const tanggalMulaiInput = document.getElementById('tanggal_mulai');
        const tanggalSelesaiInput = document.getElementById('tanggal_selesai');
        
        tanggalSelesaiInput.addEventListener('change', function() {
            if (tanggalMulaiInput.value && this.value) {
                const mulai = new Date(tanggalMulaiInput.value);
                const selesai = new Date(this.value);
                
                if (selesai <= mulai) {
                    const errorMsg = document.createElement('p');
                    errorMsg.classList.add('text-red-500', 'text-sm', 'mt-1', 'tanggal-error');
                    errorMsg.textContent = 'Tanggal selesai harus setelah tanggal mulai';
                    
                    // Remove any existing error message
                    const existingError = document.querySelector('.tanggal-error');
                    if (existingError) existingError.remove();
                    
                    // Add the error message
                    this.parentNode.appendChild(errorMsg);
                    
                    // Add invalid class
                    this.classList.add('border-red-500');
                } else {
                    // Remove error message
                    const existingError = document.querySelector('.tanggal-error');
                    if (existingError) existingError.remove();
                    
                    // Remove invalid class
                    this.classList.remove('border-red-500');
                }
            }
        });
        
        // Form validation before submit
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const errors = document.querySelectorAll('.text-red-500');
            if (errors.length > 0) {
                e.preventDefault();
                alert('Mohon perbaiki error pada form sebelum melanjutkan.');
            }
        });
    });
</script>
@endpush
@endsection