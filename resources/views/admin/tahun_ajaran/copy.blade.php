<!-- resources/views/admin/tahun_ajaran/copy.blade.php -->
@extends('layouts.app')

@section('title', 'Salin Tahun Ajaran')

@section('content')
<div class="p-4">
    <div class="mb-4">
        <h2 class="text-2xl font-bold">Salin Tahun Ajaran</h2>
        <p class="text-gray-600">Buat tahun ajaran baru dengan menyalin data dari tahun ajaran yang sudah ada</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('tahun.ajaran.process-copy', $sourceTahunAjaran->id) }}" method="POST" id="formCopyTahunAjaran"
              x-data="{ 
                  isActive: false, 
                  semester: {{ $sourceTahunAjaran->semester }},
                  copyKelas: true,
                  copyMataPelajaran: true,
                  copyTemplates: true,
                  showSemesterInfo: false
              }"
              x-init="$watch('semester', value => {
                  showSemesterInfo = value != {{ $sourceTahunAjaran->semester }};
              })">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold mb-3 text-gray-800">Tahun Ajaran Sumber</h3>
                    <div class="bg-gray-50 p-4 rounded-md">
                        <p><strong>Tahun Ajaran:</strong> {{ $sourceTahunAjaran->tahun_ajaran }}</p>
                        <p><strong>Semester:</strong> {{ $sourceTahunAjaran->semester == 1 ? 'Ganjil' : 'Genap' }}</p>
                        <p><strong>Periode:</strong> {{ $sourceTahunAjaran->tanggal_mulai->format('d/m/Y') }} - 
                            {{ $sourceTahunAjaran->tanggal_selesai->format('d/m/Y') }}</p>
                        <p><strong>Status:</strong> 
                            @if($sourceTahunAjaran->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Tidak Aktif
                                </span>
                            @endif
                        </p>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-3 text-gray-800">Tahun Ajaran Baru</h3>
                    <div>
                        <!-- Tahun Ajaran -->
                        <div class="mb-4">
                            <label for="tahun_ajaran" class="block text-sm font-medium text-gray-700 mb-1">Tahun Ajaran <span class="text-red-500">*</span></label>
                            <input type="text" name="tahun_ajaran" id="tahun_ajaran" value="{{ old('tahun_ajaran', $newTahunAjaran) }}" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                   placeholder="2024/2025" required>
                            @error('tahun_ajaran')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Semester -->
                        <div class="mb-4">
                            <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">Semester <span class="text-red-500">*</span></label>
                            <select name="semester" id="semester" x-model="semester"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    required>
                                <option value="1">Ganjil</option>
                                <option value="2">Genap</option>
                            </select>
                            @error('semester')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanggal Mulai -->
                        <div class="mb-4">
                            <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" 
                                   value="{{ old('tanggal_mulai', now()->addYear()->startOfYear()->format('Y-m-d')) }}" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                   required>
                            @error('tanggal_mulai')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanggal Selesai -->
                        <div class="mb-4">
                            <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" 
                                   value="{{ old('tanggal_selesai', now()->addYear()->endOfYear()->format('Y-m-d')) }}" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                   required>
                            @error('tanggal_selesai')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-4">
                            <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" rows="2" 
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">{{ old('deskripsi', 'Tahun Ajaran ' . $newTahunAjaran) }}</textarea>
                        </div>

                        <!-- Aktif -->
                        <div>
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" x-model="isActive"
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                       {{ old('is_active') ? 'checked' : '' }}>
                                <label for="is_active" class="ml-2 block text-sm font-medium text-gray-700">Aktifkan Tahun Ajaran Ini</label>
                            </div>
                            <p class="text-gray-500 text-sm mt-1">Mengaktifkan tahun ajaran ini akan menonaktifkan tahun ajaran lain</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Peringatan perubahan semester -->
            <div x-show="showSemesterInfo" x-cloak
                 class="mt-4 bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Informasi!</strong> Semester berbeda dengan tahun ajaran sumber. Semua data yang disalin (mata pelajaran, absensi, template rapor) akan menggunakan semester baru.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Opsi Penyalinan -->
            <div class="mt-6 border-t pt-6">
                <h3 class="text-lg font-semibold mb-3 text-gray-800">Data yang akan disalin</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="copy_kelas" id="copy_kelas" value="1" x-model="copyKelas"
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                               {{ old('copy_kelas', true) ? 'checked' : '' }}>
                        <label for="copy_kelas" class="ml-2 block text-sm font-medium text-gray-700">Salin Kelas</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="copy_mata_pelajaran" id="copy_mata_pelajaran" value="1" x-model="copyMataPelajaran"
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                               {{ old('copy_mata_pelajaran', true) ? 'checked' : '' }}>
                        <label for="copy_mata_pelajaran" class="ml-2 block text-sm font-medium text-gray-700">Salin Mata Pelajaran</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="copy_templates" id="copy_templates" value="1" x-model="copyTemplates"
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                               {{ old('copy_templates', true) ? 'checked' : '' }}>
                        <label for="copy_templates" class="ml-2 block text-sm font-medium text-gray-700">Salin Template Rapor</label>
                    </div>
                </div>
            </div>

            <!-- Tombol -->
            <div class="mt-6 flex items-center justify-end gap-4">
                <a href="{{ route('tahun.ajaran.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Kembali
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Salin Tahun Ajaran
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tambahan validasi form client-side jika diperlukan
        const form = document.getElementById('formCopyTahunAjaran');
        
        form.addEventListener('submit', function(e) {
            // Validasi format tahun ajaran (2023/2024)
            const tahunAjaranInput = document.getElementById('tahun_ajaran');
            const tahunAjaranPattern = /^\d{4}\/\d{4}$/;
            
            if (!tahunAjaranPattern.test(tahunAjaranInput.value)) {
                e.preventDefault();
                alert('Format tahun ajaran harus XXXX/XXXX, contoh: 2023/2024');
                tahunAjaranInput.focus();
            }
        });
    });
</script>
@endpush
@endsection