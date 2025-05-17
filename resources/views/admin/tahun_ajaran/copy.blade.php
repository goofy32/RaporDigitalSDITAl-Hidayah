@extends('layouts.app')

@section('title', 'Salin Tahun Ajaran')

@section('content')
<div class="p-4">
    <div class="bg-white rounded-lg shadow p-6">
        <!-- Header dengan tombol -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
            <div>
                <h2 class="text-2xl font-bold text-green-700">Salin Tahun Ajaran</h2>
                <p class="text-gray-600">Buat tahun ajaran baru dengan menyalin data dari tahun ajaran yang sudah ada</p>
            </div>
            
            <div class="flex gap-2">
                <a href="{{ route('tahun.ajaran.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                    Kembali
                </a>
                <button type="submit" form="formCopyTahunAjaran" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Simpan
                </button>
            </div>
        </div>

        <form action="{{ route('tahun.ajaran.process-copy', $sourceTahunAjaran->id) }}" method="POST" id="formCopyTahunAjaran">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Tahun Ajaran Sumber</h3>
                    <div class="bg-green-50 p-4 rounded-md border border-green-100">
                        <p><strong>Tahun Ajaran Aktif:</strong> {{ $sourceTahunAjaran->tahun_ajaran }}</p>
                        <p><strong>Semester:</strong> {{ $sourceTahunAjaran->semester == 1 ? 'Ganjil' : 'Genap' }}</p>
                        <p><strong>Periode:</strong> {{ $sourceTahunAjaran->tanggal_mulai->format('d/m/Y') }} - 
                            {{ $sourceTahunAjaran->tanggal_selesai->format('d/m/Y') }}</p>
                        <p><strong>Status:</strong> Aktif</p>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Tahun Ajaran Baru</h3>
                    
                    <div class="mb-4">
                        <label for="tahun_ajaran" class="block text-sm font-medium text-gray-700 mb-1">Tahun Ajaran <span class="text-red-500">*</span></label>
                        <input type="text" name="tahun_ajaran" id="tahun_ajaran" value="{{ old('tahun_ajaran', $newTahunAjaran) }}" 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 p-2" required>
                    </div>

                    <div class="mb-4">
                        <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                        <div class="w-full p-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700">
                            Ganjil (Semester 1)
                            <p class="mt-1 text-xs text-gray-500">Tahun ajaran baru selalu dimulai dengan semester ganjil</p>
                        </div>
                        <input type="hidden" name="semester" value="1">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" 
                           value="{{ old('tanggal_mulai', now()->addYear()->startOfYear()->format('Y-m-d')) }}" 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 p-2" required>
                </div>

                <div>
                    <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_selesai" id="tanggal_selesai" 
                           value="{{ old('tanggal_selesai', now()->addYear()->endOfYear()->format('Y-m-d')) }}" 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 p-2" required>
                </div>
            </div>

            <div class="mt-6">
                <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" rows="2" 
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 p-2">{{ old('deskripsi', 'Tahun Ajaran ' . $newTahunAjaran) }}</textarea>
            </div>

            <!-- Data yang akan disalin section - dalam satu box besar -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Data yang akan disalin</h3>
                
                <!-- Seluruh konten dalam satu box hijau muda -->
                <div class="bg-green-50 border border-green-100 rounded-md p-4">
                    <!-- Pengaturan Khusus Kelas -->
                    <h4 class="font-medium text-green-800 mb-3">Pengaturan Khusus Kelas</h4>
                    
                    <div class="mb-1">
                        <div class="flex items-center">
                            <input type="checkbox" name="increment_kelas" id="increment_kelas" value="1" checked
                                  class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <label for="increment_kelas" class="ml-2 text-gray-700">Tingkatkan nomor kelas (+1) saat menyalin.</label>
                        </div>
                        <div class="ml-7 text-gray-500 text-sm">Contoh: Kelas 1A → Kelas 2A, Kelas 3B → Kelas 4B</div>
                    </div>
                    
                    <div class="mb-5">
                        <div class="flex items-center">
                            <input type="checkbox" name="create_kelas_one" id="create_kelas_one" value="1"
                                  class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <label for="create_kelas_one" class="ml-2 text-gray-700">Buat kelas 1 baru.</label>
                        </div>
                        <div class="ml-7 text-gray-500 text-sm">Untuk siswa baru pada tahun ajaran ini</div>
                    </div>
                    
                    <!-- Pilihan data yang ingin disalin -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 mb-5">
                        <div class="flex items-center">
                            <input type="checkbox" name="copy_kelas" id="copy_kelas" value="1" checked
                                   class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <label for="copy_kelas" class="ml-2 text-gray-700">Kelas <span class="text-gray-500">(termasuk data wali kelas)</span></label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="copy_mata_pelajaran" id="copy_mata_pelajaran" value="1" checked
                                   class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <label for="copy_mata_pelajaran" class="ml-2 text-gray-700">Mata Pelajaran <span class="text-gray-500">(termasuk lingkup materi dan TP)</span></label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="copy_templates" id="copy_templates" value="1" checked
                                   class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <label for="copy_templates" class="ml-2 text-gray-700">Template Rapor</label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="copy_ekstrakurikuler" id="copy_ekstrakurikuler" value="1" checked
                                   class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <label for="copy_ekstrakurikuler" class="ml-2 text-gray-700">Ekstrakurikuler</label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="copy_kkm" id="copy_kkm" value="1" checked
                                   class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <label for="copy_kkm" class="ml-2 text-gray-700">KKM</label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="copy_bobot_nilai" id="copy_bobot_nilai" value="1" checked
                                   class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <label for="copy_bobot_nilai" class="ml-2 text-gray-700">Bobot Nilai</label>
                        </div>
                    </div>
                    
                    <!-- Penjelasan di bawah -->
                    <p class="text-gray-700">Pilih data yang ingin disalin dari tahun ajaran sumber. Secara default, semua data akan disalin.</p>
                    <p class="text-gray-700">Perhatian: <span class="font-medium text-green-800">Hapus centang</span> pada data yang <span class="font-medium text-green-800">TIDAK</span> ingin disalin ke tahun ajaran baru.</p>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fix submit button
        const submitButton = document.querySelector('button[type="submit"]');
        
        // Make sure the button is found before adding event listener
        if (submitButton) {
            submitButton.addEventListener('click', function(e) {
                // Disable button to prevent double submission
                this.disabled = true;
                
                // Add loading state
                this.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Menyimpan...
                `;
                
                // Get the form
                const form = document.getElementById('formCopyTahunAjaran');
                
                // Validate form before submitting
                const tahunAjaranInput = document.getElementById('tahun_ajaran');
                const tahunAjaranPattern = /^\d{4}\/\d{4}$/;
                
                if (!tahunAjaranPattern.test(tahunAjaranInput.value)) {
                    e.preventDefault();
                    alert('Format tahun ajaran harus XXXX/XXXX, contoh: 2023/2024');
                    tahunAjaranInput.focus();
                    
                    // Reset button state
                    this.disabled = false;
                    this.innerHTML = 'Simpan';
                    return;
                }
                
                // Submit the form
                form.submit();
            });
        }
        
        // Initialize Flowbite components if needed
        if (typeof initFlowbite === 'function') {
            initFlowbite();
        }
    });
</script>
@endpush
@endsection