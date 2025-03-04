@extends('layouts.wali_kelas.app')

@section('title', 'Manajemen Rapor')

@section('content')
<div x-data="raporManager()" class="p-4 bg-white mt-14">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Manajemen Rapor Kelas {{ auth()->user()->kelasWali->nama_kelas }}</h2>
    </div>

    <!-- Tabs -->
    <div class="mb-6">
        <div class="hidden sm:block">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="activeTab = 'UTS'"
                            :class="{'border-blue-500 text-blue-600': activeTab === 'UTS',
                                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'UTS'}"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                            type="button">
                        Rapor UTS
                    </button>
                    <button @click="activeTab = 'UAS'"
                            :class="{'border-blue-500 text-blue-600': activeTab === 'UAS',
                                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'UAS'}"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                            type="button">
                        Rapor UAS
                    </button>
                </nav>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <div class="flex gap-2">
            <button @click="generateBatchReport()"
                    :disabled="loading || selectedSiswa.length === 0"
                    class="flex items-center justify-center text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <template x-if="loading">
                    <svg class="animate-spin h-5 w-5 mr-2" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </template>
                <span x-text="loading ? 'Memproses...' : 'Cetak Semua Rapor'"></span>
            </button>
        </div>
        
        <!-- Search Box -->
        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="search" 
                   x-model="searchQuery"
                   @input="handleSearch($event)"
                   class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500" 
                   placeholder="Cari siswa...">
        </div>
    </div>

    <!-- Data Table -->
    <div class="overflow-x-auto shadow-md rounded-lg">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="p-4">
                        <div class="flex items-center">
                            <input id="checkbox-all" 
                                  type="checkbox"
                                  @change="handleCheckAll($event)"
                                  class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        </div>
                    </th>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">NIS</th>
                    <th class="px-6 py-3">Nama Siswa</th>
                    <th class="px-6 py-3">Status Nilai</th>
                    <th class="px-6 py-3">Status Kehadiran</th>
                    <th class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($siswa as $index => $s)
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="w-4 p-4">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                  value="{{ $s->id }}"
                                  @change="handleCheckSingle($event)"
                                  class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        </div>
                    </td>
                    
                    <td class="px-6 py-4">{{ $index + 1 }}</td>
                    <td class="px-6 py-4">{{ $s->nis }}</td>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $s->nama }}</td>
                    
                    <!-- Status Nilai dengan detail -->
                    <td class="px-6 py-4">
                        <div class="flex flex-col gap-1">
                            @php
                                $nilaiCount = $s->nilais()
                                    ->whereHas('mataPelajaran', function($q) {
                                        $q->where('semester', request('type', 'UTS') === 'UTS' ? 1 : 2);
                                    })->count();

                                $totalMapel = $s->kelas->mataPelajarans()
                                    ->where('semester', request('type', 'UTS') === 'UTS' ? 1 : 2)
                                    ->count();
                            @endphp
                            
                            @if($nilaiCount > 0)
                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    Lengkap ({{ $nilaiCount }} nilai diinput)
                                </span>
                            @else
                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    Belum Lengkap ({{ $nilaiCount }}/{{ $totalMapel }})
                                </span>
                            @endif
                        </div>
                    </td>

                    <!-- Status Kehadiran dengan detail -->
                    <td class="px-6 py-4">
                        <div class="flex flex-col gap-1">
                            @if($s->absensi)
                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    Lengkap
                                </span>
                                <span class="text-xs text-gray-500">
                                    S:{{ $s->absensi->sakit }}, 
                                    I:{{ $s->absensi->izin }}, 
                                    A:{{ $s->absensi->tanpa_keterangan }}
                                </span>
                            @else
                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    Belum Lengkap
                                </span>
                            @endif
                        </div>
                    </td>

                    <!-- Aksi -->
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <button @click="handlePreview({{ $s->id }}, {{ $nilaiCount }}, {{ $s->absensi ? 'true' : 'false' }})"
                                    :disabled="!{{ $nilaiCount > 0 ? 'true' : 'false' }} || !{{ $s->absensi ? 'true' : 'false' }}"
                                    class="text-blue-600 hover:text-blue-900 disabled:opacity-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                            
                            <button @click="handleGenerate({{ $s->id }}, {{ $nilaiCount }}, {{ $s->absensi ? 'true' : 'false' }})"
                                    :disabled="loading || !{{ $nilaiCount > 0 ? 'true' : 'false' }} || !{{ $s->absensi ? 'true' : 'false' }}"
                                    class="text-green-600 hover:text-green-900 disabled:opacity-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        Tidak ada data siswa
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Preview Modal -->
    <div x-show="showPreview" 
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
         x-cloak>
        <div class="relative bg-white rounded-lg mx-auto mt-10 max-w-4xl p-4">
            <button @click="showPreview = false" 
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            
            <div x-html="previewContent" class="mt-4"></div>
            
            <div class="mt-4 flex justify-end space-x-3">
                <button @click="printPreview()" 
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Print
                </button>
                <button @click="downloadPreview()" 
                        class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                    Download
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function raporManager() {
    return {
        activeTab: 'UTS',
        loading: false,
        selectedSiswa: [],
        searchQuery: '',
        showPreview: false,
        previewContent: '',
        
        init() {
            const savedTab = localStorage.getItem('activeRaporTab');
            if (savedTab) {
                this.activeTab = savedTab;
            }
        },

        handleCheckAll(event) {
            const isChecked = event.target.checked;
            document.querySelectorAll('tbody input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = isChecked;
                this.updateSelectedSiswa(checkbox);
            });
        },

        handleCheckSingle(event) {
            this.updateSelectedSiswa(event.target);
        },

        updateSelectedSiswa(checkbox) {
            if (checkbox.checked) {
                if (!this.selectedSiswa.includes(checkbox.value)) {
                    this.selectedSiswa.push(checkbox.value);
                }
            } else {
                this.selectedSiswa = this.selectedSiswa.filter(id => id !== checkbox.value);
            }
        },

        handleSearch(event) {
            const searchValue = event.target.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        },

        async handlePreview(siswaId, nilaiCount, hasAbsensi) {
            if (!this.validateData(nilaiCount, hasAbsensi)) return;
            
            try {
                this.loading = true;
                const response = await fetch(`/wali-kelas/rapor/preview/${siswaId}`);
                const data = await response.json();
                
                if (data.success) {
                    this.previewContent = data.html;
                    this.showPreview = true;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memuat preview rapor');
            } finally {
                this.loading = false;
            }
        },

        async handleGenerate(siswaId, nilaiCount, hasAbsensi) {
            if (!this.validateData(nilaiCount, hasAbsensi)) return;
            
            try {
                this.loading = true;
                const response = await fetch(`/wali-kelas/rapor/generate/${siswaId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        type: this.activeTab
                    })
                });

                // Cek status respons
                if (!response.ok) {
                    // Jika response adalah JSON, ambil pesan error
                    const contentType = response.headers.get("content-type");
                    if (contentType && contentType.includes("application/json")) {
                        const error = await response.json();
                        
                        // Tampilkan pesan error yang spesifik
                        let errorMessage = error.message || 'Gagal generate rapor';
                        
                        // Tambahkan instruksi berdasarkan error_type
                        if (error.error_type === 'template_missing' || error.error_type === 'template_invalid') {
                            errorMessage += '. Hubungi admin untuk perbaiki template rapor.';
                        } else if (error.error_type === 'data_incomplete') {
                            errorMessage += '. Pastikan semua data nilai dan kehadiran sudah dilengkapi.';
                        }
                        
                        throw new Error(errorMessage);
                    } else {
                        throw new Error(`Gagal generate rapor (${response.status})`);
                    }
                }

                // Jika sukses, akan mendapatkan file blob
                const blob = await response.blob();
                await this.downloadFile(blob, `rapor_${this.activeTab.toLowerCase()}_${siswaId}.docx`);
                
                // Tampilkan notifikasi sukses
                alert('Rapor berhasil digenerate dan diunduh');
                
            } catch (error) {
                console.error('Error:', error);
                alert(error.message);
            } finally {
                this.loading = false;
            }
        },

        async generateBatchReport() {
            if (this.loading || this.selectedSiswa.length === 0) {
                alert('Pilih siswa terlebih dahulu');
                return;
            }

            try {
                this.loading = true;
                const response = await fetch('/wali-kelas/rapor/batch-generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        siswa_ids: this.selectedSiswa,
                        type: this.activeTab
                    })
                });

                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.message || 'Gagal generate batch rapor');
                }

                const blob = await response.blob();
                await this.downloadFile(blob, `rapor_batch_${this.activeTab.toLowerCase()}.zip`);
            } catch (error) {
                alert(error.message);
            } finally {
                this.loading = false;
            }
        },

        validateData(nilaiCount, hasAbsensi) {
            const messages = [];
            if (!nilaiCount || nilaiCount === 0) messages.push("- Data nilai belum lengkap");
            if (!hasAbsensi) messages.push("- Data kehadiran belum lengkap");
            
            if (messages.length > 0) {
                alert("Tidak bisa melanjutkan karena:\n" + messages.join("\n"));
                return false;
            }
            return true;
        },

        async downloadFile(blob, filename) {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        },

        printPreview() {
            window.print();
        },

        downloadPreview() {
            if (this.previewContent) {
                const blob = new Blob([this.previewContent], { type: 'text/html' });
                this.downloadFile(blob, 'preview_rapor.html');
            }
        }
    }
}
</script>
@endpush
@endsection