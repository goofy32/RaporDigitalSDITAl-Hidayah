@extends('layouts.wali_kelas.app')

@section('content')
<div class="p-4 bg-white mt-14">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-green-700">Kelola Capaian Kompetensi</h2>
            <p class="text-sm text-gray-600 mt-1">
                {{ $mataPelajaran->nama_pelajaran }} - Kelas {{ $mataPelajaran->kelas->nomor_kelas }}{{ $mataPelajaran->kelas->nama_kelas }}
            </p>
        </div>
        <a href="{{ route('wali_kelas.capaian_kompetensi.index') }}" 
           class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
            Kembali
        </a>
    </div>

    <!-- Info Section -->
    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-amber-700">
                    <strong>Petunjuk:</strong> Sistem akan otomatis menghasilkan capaian kompetensi berdasarkan nilai siswa. 
                    Anda dapat menambahkan teks kustomisasi yang akan ditambahkan setelah capaian otomatis. 
                    Kosongkan jika hanya ingin menggunakan capaian otomatis.
                </p>
            </div>
        </div>
    </div>

    <form action="{{ route('wali_kelas.capaian_kompetensi.update', $mataPelajaran->id) }}" method="POST" 
          x-data="capaianKompetensiForm()" 
          x-on:submit.prevent="submitForm">
        @csrf
        @method('PUT')

        <!-- Filter dan Info dengan Tombol Simpan -->
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <div class="flex items-center justify-between flex-wrap gap-4 mb-4">
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-600">
                        <strong>Total Siswa:</strong> {{ $siswaList->count() }}
                    </div>
                    <div class="text-sm text-gray-600">
                        <strong>Dikustomisasi:</strong> <span x-text="customizedCount">{{ $existingCapaian->count() }}</span>
                    </div>
                </div>
                
                <!-- Search Box -->
                <div class="relative">
                    <input type="text" 
                           x-model="searchTerm"
                           placeholder="Cari nama siswa..."
                           class="w-64 pl-10 pr-4 py-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Tombol Simpan di Atas -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <div class="text-sm text-gray-600">
                    <span x-show="hasChanges" x-transition class="text-orange-600 font-medium">
                        ⚠ Ada perubahan yang belum disimpan
                    </span>
                    <span x-show="!hasChanges" class="text-gray-500">
                        Lakukan perubahan pada form di bawah, lalu klik tombol simpan
                    </span>
                </div>
                
                <button type="submit" 
                        x-bind:disabled="isSubmitting"
                        x-bind:class="isSubmitting ? 'opacity-50 cursor-not-allowed' : ''"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center space-x-2">
                    <span x-show="!isSubmitting">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Simpan Semua Perubahan
                    </span>
                    <span x-show="isSubmitting" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Menyimpan...
                    </span>
                </button>
            </div>
        </div>

        <!-- Students List -->
        <div class="space-y-6">
            @foreach($siswaList as $index => $siswa)
                @php
                    $existingCapaianText = $existingCapaian->get($siswa->id)?->custom_capaian ?? '';
                    
                    // Get nilai for preview
                    $nilai = $siswa->nilais()
                        ->where('mata_pelajaran_id', $mataPelajaran->id)
                        ->where('tahun_ajaran_id', session('tahun_ajaran_id'))
                        ->first();
                    $nilaiAkhir = $nilai ? $nilai->nilai_akhir_rapor : null;
                @endphp
                
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm" 
                     x-show="searchTerm === '' || '{{ strtolower($siswa->nama) }}'.includes(searchTerm.toLowerCase())"
                     x-transition>
                    
                    <!-- Student Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                <span class="text-sm font-medium text-green-600">
                                    {{ substr($siswa->nama, 0, 2) }}
                                </span>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-900">{{ $siswa->nama }}</h3>
                                <p class="text-xs text-gray-500">NIS: {{ $siswa->nis }}</p>
                            </div>
                        </div>
                        
                        @if($nilaiAkhir)
                            <div class="text-right">
                                <div class="text-sm font-semibold text-gray-900">Nilai: {{ $nilaiAkhir }}</div>
                                <div class="text-xs">
                                    @if($nilaiAkhir >= 90)
                                        <span class="text-green-600 font-medium">Sangat Baik</span>
                                    @elseif($nilaiAkhir >= 80)
                                        <span class="text-green-400 font-medium">Baik</span>
                                    @elseif($nilaiAkhir >= 70)
                                        <span class="text-yellow-600 font-medium">Cukup</span>
                                    @else
                                        <span class="text-red-600 font-medium">Perlu Bimbingan</span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="text-xs text-gray-400">Nilai belum tersedia</div>
                        @endif
                    </div>

                    <!-- Auto Generated Preview -->
                    <div class="mb-4 p-3 bg-gray-50 rounded-md border-l-4 border-green-400">
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Capaian Otomatis:
                        </label>
                        <p class="text-sm text-gray-700">
                            @if($nilaiAkhir)
                                @php
                                    $autoCapaian = \App\Http\Controllers\CapaianKompetensiController::generateCapaianForRapor(
                                        $siswa->id, 
                                        $mataPelajaran->id, 
                                        session('tahun_ajaran_id')
                                    );
                                @endphp
                                {{ $autoCapaian }}
                            @else
                                <em class="text-gray-500">Nilai belum tersedia untuk menghasilkan capaian otomatis</em>
                            @endif
                        </p>
                    </div>

                    <!-- Custom Input -->
                    <div>
                        <label for="capaian_{{ $siswa->id }}" class="block text-sm font-medium text-gray-700 mb-2">
                            Tambahan Kustomisasi (Opsional):
                        </label>
                        <textarea name="capaian[{{ $siswa->id }}]" 
                                  id="capaian_{{ $siswa->id }}"
                                  rows="3"
                                  class="w-full px-3 py-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500"
                                  placeholder="Tambahkan catatan khusus untuk {{ $siswa->nama }}..."
                                  x-on:input="updateCustomizedCount(); checkForChanges()"
                                  >{{ $existingCapaianText }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            Teks ini akan ditambahkan setelah capaian otomatis. Kosongkan jika hanya ingin menggunakan capaian otomatis.
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Floating Save Button (Optional - muncul saat scroll) -->
        <div class="fixed bottom-6 right-6 z-50" 
             x-show="hasChanges && !isSubmitting" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform translate-y-2">
            <button type="submit" 
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-full shadow-lg flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Simpan</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function capaianKompetensiForm() {
    return {
        searchTerm: '',
        isSubmitting: false,
        hasChanges: false,
        customizedCount: {{ $existingCapaian->count() }},
        originalValues: {},
        
        init() {
            // Store original values
            this.$nextTick(() => {
                this.$el.querySelectorAll('textarea[name^="capaian"]').forEach(textarea => {
                    this.originalValues[textarea.name] = textarea.value;
                });
            });
        },
        
        updateCustomizedCount() {
            let count = 0;
            this.$el.querySelectorAll('textarea[name^="capaian"]').forEach(textarea => {
                if (textarea.value.trim() !== '') {
                    count++;
                }
            });
            this.customizedCount = count;
        },
        
        checkForChanges() {
            let hasChanges = false;
            this.$el.querySelectorAll('textarea[name^="capaian"]').forEach(textarea => {
                if (textarea.value !== this.originalValues[textarea.name]) {
                    hasChanges = true;
                }
            });
            this.hasChanges = hasChanges;
        },
        
        async submitForm(event) {
            if (this.isSubmitting) return;
            
            this.isSubmitting = true;
            
            try {
                // Submit form normally
                event.target.submit();
            } catch (error) {
                console.error('Error submitting form:', error);
                this.isSubmitting = false;
            }
        }
    }
}

// Warning before leaving page if there are unsaved changes
window.addEventListener('beforeunload', function(e) {
    const form = document.querySelector('[x-data*="capaianKompetensiForm"]');
    if (form && form.__x.$data.hasChanges && !form.__x.$data.isSubmitting) {
        e.preventDefault();
        e.returnValue = 'Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
        return e.returnValue;
    }
});
</script>
@endpush
@endsection