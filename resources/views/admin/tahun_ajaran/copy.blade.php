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
                copyEkstrakurikuler: true,
                copyKkm: true,
                copyBobotNilai: true,
                incrementKelas: true,
                createKelasOne: false,
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
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                                   placeholder="2024/2025" required>
                            @error('tahun_ajaran')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Semester -->
                        <div class="mb-4">
                            <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                            <div class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700">
                                Ganjil (Semester 1)
                                <p class="mt-1 text-xs text-gray-500">Tahun ajaran baru selalu dimulai dengan semester ganjil</p>
                            </div>
                            <!-- Hidden input untuk memastikan nilai semester tetap dikirim -->
                            <input type="hidden" name="semester" value="1">
                        </div>

                        <!-- Tanggal Mulai -->
                        <div class="mb-4">
                            <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" 
                                   value="{{ old('tanggal_mulai', now()->addYear()->startOfYear()->format('Y-m-d')) }}" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50"
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
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                                   required>
                            @error('tanggal_selesai')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-4">
                            <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" rows="2" 
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50">{{ old('deskripsi', 'Tahun Ajaran ' . $newTahunAjaran) }}</textarea>
                        </div>

                        <!-- Aktif -->
                        <div>
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" x-model="isActive"
                                       class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50"
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
                            <strong>Informasi!</strong> Semester berbeda dengan tahun ajaran sumber. Semua data yang disalin akan menggunakan semester baru.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Opsi Penyalinan -->
            <div class="mt-6 border-t pt-6">
                <h3 class="text-lg font-semibold mb-3 text-gray-800">Data yang akan disalin</h3>

                <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg" x-show="copyKelas">
                    <h4 class="font-medium text-green-800 mb-2">Pengaturan Khusus Kelas</h4>
                    
                    <div class="flex items-center mb-3">
                        <input type="checkbox" name="increment_kelas" id="increment_kelas" value="1" checked
                            class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                        <label for="increment_kelas" class="ml-2 block text-sm font-medium text-gray-700">
                            Tingkatkan nomor kelas (+1) saat menyalin
                        </label>
                        <p class="ml-2 text-xs text-gray-500">Contoh: Kelas 1A → Kelas 2A, Kelas 3B → Kelas 4B</p>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="create_kelas_one" id="create_kelas_one" value="1"
                            class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                        <label for="create_kelas_one" class="ml-2 block text-sm font-medium text-gray-700">
                            Buat kelas 1 baru
                        </label>
                        <p class="ml-2 text-xs text-gray-500">Untuk siswa baru pada tahun ajaran ini</p>
                    </div>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <p class="text-green-800 text-sm mb-2">Pilih data yang ingin disalin dari tahun ajaran sumber. Secara default, semua data akan disalin.</p>
                    <p class="text-green-800 text-sm">Perhatian: <strong>Hapus centang</strong> pada data yang <strong>TIDAK</strong> ingin disalin ke tahun ajaran baru.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center p-3 bg-green-50 border border-green-100 rounded-lg">
                        <input type="checkbox" name="copy_kelas" id="copy_kelas" value="1" x-model="copyKelas"
                               class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                               {{ old('copy_kelas', true) ? 'checked' : '' }}>
                        <label for="copy_kelas" class="ml-2 block text-sm font-medium text-gray-700">Kelas</label>
                        <p class="ml-2 text-xs text-gray-500">Termasuk data wali kelas</p>
                    </div>
                    
                    <div class="flex items-center p-3 bg-green-50 border border-green-100 rounded-lg">
                        <input type="checkbox" name="copy_mata_pelajaran" id="copy_mata_pelajaran" value="1" x-model="copyMataPelajaran"
                               class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                               {{ old('copy_mata_pelajaran', true) ? 'checked' : '' }}>
                        <label for="copy_mata_pelajaran" class="ml-2 block text-sm font-medium text-gray-700">Mata Pelajaran</label>
                        <p class="ml-2 text-xs text-gray-500">Termasuk lingkup materi dan TP</p>
                    </div>
                    
                    <div class="flex items-center p-3 bg-green-50 border border-green-100 rounded-lg">
                        <input type="checkbox" name="copy_templates" id="copy_templates" value="1" x-model="copyTemplates"
                               class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                               {{ old('copy_templates', true) ? 'checked' : '' }}>
                        <label for="copy_templates" class="ml-2 block text-sm font-medium text-gray-700">Template Rapor</label>
                    </div>
                    
                    <div class="flex items-center p-3 bg-green-50 border border-green-100 rounded-lg">
                        <input type="checkbox" name="copy_ekstrakurikuler" id="copy_ekstrakurikuler" value="1" x-model="copyEkstrakurikuler"
                               class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                               {{ old('copy_ekstrakurikuler', true) ? 'checked' : '' }}>
                        <label for="copy_ekstrakurikuler" class="ml-2 block text-sm font-medium text-gray-700">Ekstrakurikuler</label>
                    </div>
                    
                    <div class="flex items-center p-3 bg-green-50 border border-green-100 rounded-lg">
                        <input type="checkbox" name="copy_kkm" id="copy_kkm" value="1" x-model="copyKkm"
                               class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                               {{ old('copy_kkm', true) ? 'checked' : '' }}>
                        <label for="copy_kkm" class="ml-2 block text-sm font-medium text-gray-700">KKM</label>
                    </div>
                    
                    <div class="flex items-center p-3 bg-green-50 border border-green-100 rounded-lg">
                        <input type="checkbox" name="copy_bobot_nilai" id="copy_bobot_nilai" value="1" x-model="copyBobotNilai"
                               class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                               {{ old('copy_bobot_nilai', true) ? 'checked' : '' }}>
                        <label for="copy_bobot_nilai" class="ml-2 block text-sm font-medium text-gray-700">Bobot Nilai</label>
                    </div>
                </div>
                
                <div x-show="!copyKelas && (copyMataPelajaran || copyTemplates || copyKkm)" class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Perhatian!</strong> Anda tidak menyalin data kelas, namun menyalin data yang terhubung dengan kelas. Hal ini dapat menyebabkan masalah relasi data.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol -->
            <div class="mt-6 flex items-center justify-end gap-4">
                <a href="{{ route('tahun.ajaran.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Kembali
                </a>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
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