@extends('layouts.app')

@section('title', 'Buat Tahun Ajaran Berikutnya')

@section('content')
<div class="p-4">
    <div class="bg-white rounded-lg shadow p-6">
        <!-- Header dengan tombol -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
            <div>
                <h2 class="text-2xl font-bold text-green-700">Buat Tahun Ajaran Berikutnya</h2>
                <p class="text-gray-600">Buat tahun ajaran baru dengan kenaikan kelas dan pengaturan dari tahun ajaran saat ini</p>
            </div>
            
            <div class="flex gap-2">
                <a href="{{ route('tahun.ajaran.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                    Kembali
                </a>
                <button type="submit" form="formCopyTahunAjaran" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Buat Tahun Ajaran
                </button>
            </div>
        </div>

        <!-- Info box untuk semester genap -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-green-400 text-lg"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">Informasi Pembuatan Tahun Ajaran Berikutnya</h3>
                    <p class="mt-1 text-sm text-green-700">
                        Fitur ini digunakan untuk membuat tahun ajaran baru di akhir semester genap. Sistem akan:
                    </p>
                    <ul class="mt-2 text-sm text-green-700 list-disc list-inside">
                        <li>Menaikkan nomor kelas siswa (Kelas 1 → Kelas 2, dst.)</li>
                        <li>Mempertahankan struktur kelas dan pengaturan guru</li>
                        <li>Menyalin pengaturan mata pelajaran, KKM, dan template rapor</li>
                        <li>Membuat kelas 1 baru untuk siswa baru</li>
                    </ul>
                </div>
            </div>
        </div>

        <form action="{{ route('tahun.ajaran.process-copy', $sourceTahunAjaran->id) }}" method="POST" id="formCopyTahunAjaran">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Tahun Ajaran Sumber</h3>
                    <div class="bg-green-50 p-4 rounded-md border border-green-100">
                        <p><strong>Tahun Ajaran:</strong> {{ $sourceTahunAjaran->tahun_ajaran }}</p>
                        <p><strong>Semester:</strong> {{ $sourceTahunAjaran->semester == 1 ? 'Ganjil' : 'Genap' }}</p>
                        <p><strong>Periode:</strong> {{ $sourceTahunAjaran->tanggal_mulai->format('d/m/Y') }} - 
                            {{ $sourceTahunAjaran->tanggal_selesai->format('d/m/Y') }}</p>
                        <p><strong>Status:</strong> {{ $sourceTahunAjaran->is_active ? 'Aktif' : 'Tidak Aktif' }}</p>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Tahun Ajaran Berikutnya</h3>
                    
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
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Pengaturan Kenaikan Kelas dan Data</h3>
                
                <!-- Seluruh konten dalam satu box hijau muda -->
                <div class="bg-green-50 border border-green-100 rounded-md p-4">
                    <!-- Pengaturan Khusus Kelas -->
                    <h4 class="font-medium text-green-800 mb-3">Pengaturan Kenaikan Kelas</h4>
                    
                    <!-- Pengaturan Opsional -->
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-md">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-cog text-green-600 mr-2"></i>
                            <span class="text-green-800 font-medium text-sm">Pengaturan Opsional</span>
                        </div>
                        <div class="mb-1">
                            <div class="flex items-center">
                                <input type="checkbox" name="increment_kelas" id="increment_kelas" value="1" checked
                                      class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                                <label for="increment_kelas" class="ml-2 text-gray-700">Naikkan kelas siswa (+1 tingkat).</label>
                            </div>
                            <div class="ml-7 text-gray-500 text-sm">Contoh: Kelas 1A → Kelas 2A, Kelas 3B → Kelas 4B (Kelas 6 akan lulus)</div>
                        </div>
                    </div>

                    <!-- Pengaturan Wajib (tidak bisa diubah) -->
                    <div class="mb-4 p-3 bg-green-100 border border-green-300 rounded-md">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-lock text-green-600 mr-2"></i>
                            <span class="text-green-800 font-medium text-sm">Pengaturan Wajib (Tidak Dapat Diubah)</span>
                        </div>
                        <div class="mb-1">
                            <div class="flex items-center">
                                <input type="checkbox" name="preserve_teacher_assignments_display" id="preserve_teacher_assignments_display" 
                                       value="1" checked disabled
                                       class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500 opacity-75 cursor-not-allowed">
                                <label for="preserve_teacher_assignments_display" class="ml-2 text-gray-700">
                                    Pertahankan guru untuk tingkat kelas yang sama. 
                                    <span class="text-green-600 font-medium text-xs">(WAJIB)</span>
                                </label>
                            </div>
                            <div class="ml-7 text-gray-500 text-sm">Contoh: Guru kelas 1 akan tetap mengajar kelas 1 di tahun ajaran baru</div>
                            <!-- Hidden input untuk memastikan nilai tetap terkirim -->
                            <input type="hidden" name="preserve_teacher_assignments" value="1">
                        </div>
                        
                        <div class="mb-1">
                            <div class="flex items-center">
                                <input type="checkbox" name="create_kelas_one_display" id="create_kelas_one_display" 
                                       value="1" checked disabled
                                       class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500 opacity-75 cursor-not-allowed">
                                <label for="create_kelas_one_display" class="ml-2 text-gray-700">
                                    Buat kelas 1 baru untuk siswa baru.
                                    <span class="text-green-600 font-medium text-xs">(WAJIB)</span>
                                </label>
                            </div>
                            <div class="ml-7 text-gray-500 text-sm">Untuk menampung siswa baru yang akan masuk</div>
                            <!-- Hidden input untuk memastikan nilai tetap terkirim -->
                            <input type="hidden" name="create_kelas_one" value="1">
                        </div>
                    </div>
                    
                    <!-- Pilihan data yang ingin disalin -->
                    <h4 class="font-medium text-green-800 mb-3 mt-6">Data yang Akan Disalin</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 mb-5">
                        <!-- Kelas wajib disalin untuk kenaikan kelas -->
                        <div class="flex items-center opacity-75">
                            <input type="checkbox" name="copy_kelas_display" id="copy_kelas_display" value="1" checked disabled
                                   class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-not-allowed">
                            <label for="copy_kelas_display" class="ml-2 text-gray-700">
                                Struktur Kelas <span class="text-green-600 font-medium text-xs">(WAJIB)</span>
                            </label>
                            <!-- Hidden input untuk memastikan nilai tetap terkirim -->
                            <input type="hidden" name="copy_kelas" value="1">
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="copy_mata_pelajaran" id="copy_mata_pelajaran" value="1" checked
                                   class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <label for="copy_mata_pelajaran" class="ml-2 text-gray-700">Mata Pelajaran <span class="text-gray-500">(dan tujuan pembelajaran)</span></label>
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
                            <label for="copy_kkm" class="ml-2 text-gray-700">Standar KKM</label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="copy_bobot_nilai" id="copy_bobot_nilai" value="1" checked
                                   class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <label for="copy_bobot_nilai" class="ml-2 text-gray-700">Bobot Penilaian</label>
                        </div>
                    </div>
                    
                    <!-- Penjelasan di bawah -->
                    <div class="bg-white p-3 rounded border border-green-200">
                        <p class="text-gray-700 text-sm"><strong>Catatan Penting:</strong></p>
                        <div class="mt-1 space-y-1">
                            <div class="flex items-start">
                                <i class="fas fa-lock text-green-600 mr-2 mt-0.5 text-xs"></i>
                                <span class="text-gray-700 text-sm">
                                    <span class="text-green-600 font-medium">Pengaturan WAJIB:</span> Diperlukan untuk kenaikan kelas yang benar dan kontinuitas pembelajaran
                                </span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-cog text-green-600 mr-2 mt-0.5 text-xs"></i>
                                <span class="text-gray-700 text-sm">
                                    <span class="text-green-600 font-medium">Pengaturan Opsional:</span> Dapat disesuaikan dengan kebutuhan sekolah
                                </span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-gray-600 mr-2 mt-0.5 text-xs"></i>
                                <span class="text-gray-700 text-sm">
                                    Siswa akan naik kelas secara otomatis kecuali kelas 6 (akan lulus)
                                </span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-refresh text-gray-600 mr-2 mt-0.5 text-xs"></i>
                                <span class="text-gray-700 text-sm">
                                    Data nilai dan absensi akan dimulai dari kosong untuk tahun ajaran baru
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fix submit button
        const form = document.getElementById('formCopyTahunAjaran');
        const submitButton = document.querySelector('button[type="submit"]');
        
        // Flag untuk mencegah multiple submissions
        let isSubmitting = false;
        
        if (form && submitButton) {
            form.addEventListener('submit', function(e) {
                // Jika sedang submit, hentikan
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }
                
                // Validasi form before submitting
                const tahunAjaranInput = document.getElementById('tahun_ajaran');
                const tahunAjaranPattern = /^\d{4}\/\d{4}$/;
                const incrementKelas = document.getElementById('increment_kelas');
                
                if (!tahunAjaranPattern.test(tahunAjaranInput.value)) {
                    e.preventDefault();
                    alert('Format tahun ajaran harus XXXX/XXXX, contoh: 2024/2025');
                    tahunAjaranInput.focus();
                    return false;
                }
                
                // Konfirmasi sebelum membuat tahun ajaran baru
                const confirmed = confirm(`Apakah Anda yakin ingin membuat tahun ajaran berikutnya?

Tindakan ini akan:
• Menaikkan kelas siswa (${incrementKelas.checked ? 'AKTIF' : 'TIDAK AKTIF'})
• Mempertahankan guru pada tingkat kelas yang sama (WAJIB)
• Membuat kelas 1 baru untuk siswa baru (WAJIB)
• Menyalin pengaturan yang dipilih dari tahun ajaran saat ini

Proses ini tidak dapat dibatalkan setelah dimulai.`);
                
                if (!confirmed) {
                    e.preventDefault();
                    return false;
                }
                
                // Set flag untuk mencegah multiple submissions
                isSubmitting = true;
                
                // Disable button to prevent double submission
                submitButton.disabled = true;
                
                // Add loading state
                submitButton.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Membuat Tahun Ajaran...
                `;
            });
        }
        
        // Initialize Flowbite components if needed
        if (typeof initFlowbite === 'function') {
            initFlowbite();
        }
        
        // Add tooltips for disabled checkboxes
        const disabledCheckboxes = document.querySelectorAll('input[disabled]');
        disabledCheckboxes.forEach(checkbox => {
            checkbox.parentElement.title = 'Pengaturan ini wajib untuk kenaikan kelas yang benar';
        });
    });
</script>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
.fa-lock, .fa-cog, .fa-info-circle, .fa-refresh {
    width: 12px;
    text-align: center;
}

/* Style untuk checkbox yang disabled */
input[type="checkbox"]:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

input[type="checkbox"]:disabled + label {
    opacity: 0.8;
    cursor: not-allowed;
}

/* Highlight untuk pengaturan wajib */
.bg-green-100 {
    background-color: rgba(34, 197, 94, 0.1);
}

.bg-green-50 {
    background-color: rgba(59, 130, 246, 0.05);
}
</style>
@endpush
@endsection