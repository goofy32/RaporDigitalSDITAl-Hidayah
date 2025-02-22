@extends('layouts.wali_kelas.app')

@section('title', 'Manajemen Rapor')

@section('content')
<div x-data="raporManager">
    <div class="p-4 bg-white mt-14">
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
                                <input id="checkbox-all" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
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
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            </div>
                        </td>
                        
                        <td class="px-6 py-4">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">{{ $s->nis }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $s->nama }}</td>
                        
                        <!-- Status Nilai dengan detail -->
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                @if($s->nilais->count() > 0)
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                        Lengkap ({{ $s->nilais->count() }} nilai diinput)
                                    </span>
                                @else
                                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                        Belum Lengkap
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        0 dari {{ $s->kelas->mataPelajarans->count() }} mata pelajaran
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
                                    <span class="text-xs text-gray-500">
                                        Absensi belum diisi
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Aksi dengan tooltip -->
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <button @click="checkAndPreview({{ $s->id }}, {{ $s->nilais->count() }}, {{ $s->absensi ? 'true' : 'false' }})"
                                        x-tooltip="getStatusMessage({{ $s->nilais->count() }}, {{ $s->absensi ? 'true' : 'false' }})"
                                        :disabled="!{{ $s->nilais->count() > 0 ? 'true' : 'false' }} || !{{ $s->absensi ? 'true' : 'false' }}"
                                        class="text-blue-600 hover:text-blue-900 disabled:opacity-50"
                                        title="Preview Rapor">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                                
                                <button @click="checkAndGenerate({{ $s->id }}, {{ $s->nilais->count() }}, {{ $s->absensi ? 'true' : 'false' }})"
                                        x-tooltip="getStatusMessage({{ $s->nilais->count() }}, {{ $s->absensi ? 'true' : 'false' }})"
                                        :disabled="loading || !{{ $s->nilais->count() > 0 ? 'true' : 'false' }} || !{{ $s->absensi ? 'true' : 'false' }}"
                                        class="text-green-600 hover:text-green-900 disabled:opacity-50"
                                        title="Generate Rapor">
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
        <template x-if="$store.report.showPreview">
            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="bg-white p-4 mx-auto my-12 max-w-4xl rounded shadow-lg relative">
                    <!-- Close button -->
                    <button @click="$store.report.closePreview()" 
                            class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    
                    <!-- PDF Viewer -->
                    <iframe 
                        :src="'data:application/pdf;base64,' + $store.report.pdfContent"
                        class="w-full h-[800px]"
                        type="application/pdf">
                    </iframe>
                    
                    <!-- Actions -->
                    <div class="flex justify-end gap-2 mt-4">
                        <button 
                            @click="window.print()"
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            Print
                        </button>
                        <button 
                            @click="$store.report.downloadPdf()"
                            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                            Download PDF
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
   Alpine.store('report', {
       loading: false,
       pdfContent: '',
       showPreview: false,

       async showPreviewModal(siswaId) {
           try {
               this.loading = true;
               const response = await fetch(`/wali-kelas/rapor/preview/${siswaId}`);
               const data = await response.json();
               
               if (data.success) {
                   this.pdfContent = data.pdf;
                   this.showPreview = true;
               }
           } catch (error) {
               console.error('Error:', error);
               alert('Terjadi kesalahan saat memuat preview rapor');
           } finally {
               this.loading = false;
           }
       },

       closePreview() {
           this.showPreview = false;
           this.pdfContent = '';
       }
   });

   Alpine.data('raporManager', () => ({
       activeTab: 'UTS',
       loading: false,
       selectedSiswa: [],
       searchQuery: '',
       
       init() {
           this.setupCheckboxHandlers();
       },

       setupCheckboxHandlers() {
           const checkAll = document.getElementById('checkbox-all');
           if (checkAll) {
               checkAll.addEventListener('change', (e) => {
                   document.querySelectorAll('tbody input[type="checkbox"]')
                       .forEach(cb => cb.checked = e.target.checked);
                   this.updateSelectedSiswa();
               });
           }
       },

       updateSelectedSiswa() {
           this.selectedSiswa = Array.from(
               document.querySelectorAll('tbody input[type="checkbox"]:checked')
           ).map(cb => cb.value);
       },

       handleSearch(event) {
           const searchValue = event.target.value.toLowerCase();
           const rows = document.querySelectorAll('tbody tr');
           rows.forEach(row => {
               const text = row.textContent.toLowerCase();
               row.style.display = text.includes(searchValue) ? '' : 'none';
           });
       },

       getStatusMessage(nilaiCount, hasAbsensi) {
           let messages = [];
           
           if (!nilaiCount || nilaiCount === 0) {
               messages.push("Data nilai belum lengkap");
           }
           if (!hasAbsensi) {
               messages.push("Data kehadiran belum lengkap");
           }
           
           if (messages.length > 0) {
               return "Tidak bisa generate/preview karena:\n" + messages.join("\n");
           }
           
           return "Data lengkap, siap diproses";
       },

       checkAndPreview(id, nilaiCount, hasAbsensi) {
           const messages = [];
           if (!nilaiCount || nilaiCount === 0) messages.push("- Data nilai belum lengkap");
           if (!hasAbsensi) messages.push("- Data kehadiran belum lengkap");
           
           if (messages.length > 0) {
               alert("Tidak bisa preview rapor karena:\n" + messages.join("\n"));
               return;
           }
           
           $store.report.showPreviewModal(id);
       },

       checkAndGenerate(id, nilaiCount, hasAbsensi) {
           const messages = [];
           if (!nilaiCount || nilaiCount === 0) messages.push("- Data nilai belum lengkap");
           if (!hasAbsensi) messages.push("- Data kehadiran belum lengkap");
           
           if (messages.length > 0) {
               alert("Tidak bisa generate rapor karena:\n" + messages.join("\n"));
               return;
           }
           
           this.generateReport(id);
       },

       async generateReport(siswaId) {
           if (this.loading) return;
           
           try {
               this.loading = true;
               const response = await fetch(`/wali-kelas/rapor/generate/${siswaId}`, {
                   method: 'POST',
                   headers: {
                       'Content-Type': 'application/json',
                       'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                   },
                   body: JSON.stringify({
                       type: this.activeTab
                   })
               });

               if (!response.ok) {
                   const error = await response.json();
                   throw new Error(error.message || 'Gagal generate rapor');
               }

               const blob = await response.blob();
               await this.downloadFile(blob, `rapor_${this.activeTab.toLowerCase()}_${siswaId}.docx`);
           } catch (error) {
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

       async downloadFile(blob, filename) {
           const url = window.URL.createObjectURL(blob);
           const a = document.createElement('a');
           a.href = url;
           a.download = filename;
           document.body.appendChild(a);
           a.click();
           window.URL.revokeObjectURL(url);
           document.body.removeChild(a);
       }
   }));
});
</script>
@endpush
@endsection