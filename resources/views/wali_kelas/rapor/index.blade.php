@extends('layouts.wali_kelas.app')

@section('title', 'Manajemen Rapor')

@section('content')
@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .action-icon {
        width: 20px;
        height: 20px;
        object-fit: contain;
    }
</style>
@endpush

<div x-data="raporManager" 
     x-cloak 
     class="p-4 bg-white mt-14"
     x-bind:class="{ 'hidden': !initialized || (!templateUTSActive && !templateUASActive) }">
     
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Manajemen Rapor Kelas {{ auth()->user()->kelasWali->nama_kelas }}</h2>
    </div>

    <!-- Tabs -->
    <div class="mb-6">
        <div class="hidden sm:block">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="setActiveTab('UTS')"
                            :class="{'border-green-500 text-green-600': activeTab === 'UTS',
                                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'UTS',
                                    'cursor-not-allowed opacity-70': !templateUTSActive}"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                            type="button">
                        Rapor UTS
                        <span x-show="!templateUTSActive" x-cloak class="ml-1 text-xs text-red-500">(Nonaktif)</span>
                    </button>
                    <button @click="setActiveTab('UAS')"
                            :class="{'border-green-500 text-green-600': activeTab === 'UAS',
                                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'UAS',
                                    'cursor-not-allowed opacity-70': !templateUASActive}"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                            type="button">
                        Rapor UAS
                        <span x-show="!templateUASActive" x-cloak class="ml-1 text-xs text-red-500">(Nonaktif)</span>
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
                class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-green-500 focus:border-green-500" 
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
                                  class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500">
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
                                  class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500">
                        </div>
                    </td>
                    
                    <td class="px-6 py-4">{{ $index + 1 }}</td>
                    <td class="px-6 py-4">{{ $s->nis }}</td>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $s->nama }}</td>
                    
                    <!-- Status Nilai dengan detail -->
                    <td class="px-6 py-4">
                        <div class="flex flex-col gap-1">
                            @if($diagnosisResults[$s->id]['nilai_status'])
                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    Lengkap
                                </span>
                            @else
                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full relative group">
                                    Belum Lengkap
                                    <!-- Tooltip dengan masalah spesifik -->
                                    <div class="absolute left-0 top-full mt-2 w-64 p-2 bg-gray-800 text-white text-xs rounded shadow-lg 
                                            opacity-0 invisible group-hover:opacity-100 group-hover:visible transition z-10">
                                        <p>Masalah terdeteksi:</p>
                                        <p class="font-medium mt-1">{{ $diagnosisResults[$s->id]['nilai_message'] }}</p>
                                        
                                        <!-- Saran tindakan berdasarkan pesan -->
                                        <p class="mt-2">Solusi:</p>
                                        @if(strpos($diagnosisResults[$s->id]['nilai_message'], 'nilai akhir rapor belum dihitung') !== false)
                                            <p>Minta pengajar untuk menyimpan nilai dengan klik "Simpan & Preview"</p>
                                        @elseif(strpos($diagnosisResults[$s->id]['nilai_message'], 'Tidak ada mata pelajaran') !== false)
                                            <p>Tambahkan mata pelajaran untuk semester ini</p>
                                        @else
                                            <p>Minta pengajar mengisi nilai siswa terlebih dahulu</p>
                                        @endif
                                    </div>
                                </span>
                            @endif
                        </div>
                    </td>

                    <!-- Status Kehadiran dengan detail -->
                    <td class="px-6 py-4">
                        <div class="flex flex-col gap-1">
                            @if($diagnosisResults[$s->id]['absensi_status'])
                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    Lengkap
                                </span>
                            @else
                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full relative group">
                                    Belum Lengkap
                                    <!-- Tooltip dengan masalah spesifik -->
                                    <div class="absolute left-0 top-full mt-2 w-64 p-2 bg-gray-800 text-white text-xs rounded shadow-lg 
                                            opacity-0 invisible group-hover:opacity-100 group-hover:visible transition z-10">
                                        <p>Masalah terdeteksi:</p>
                                        <p class="font-medium mt-1">{{ $diagnosisResults[$s->id]['absensi_message'] }}</p>
                                        
                                        <p class="mt-2">Solusi:</p>
                                        <p>Input data absensi dengan memilih semester {{ request('type', 'UTS') === 'UTS' ? '1 (Ganjil)' : '2 (Genap)' }}</p>
                                    </div>
                                </span>
                            @endif
                        </div>
                    </td>

                    <!-- Aksi -->
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <!-- Debug button - add this inside the loop -->
                            <button @click="debugSiswaData({{ $s->id }})" 
                                    class="text-blue-600 hover:text-blue-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>

                            <button @click="handlePreview({{ $s->id }}, {{ $nilaiCounts[$s->id] ?? 0 }}, {{ $s->absensi ? 'true' : 'false' }})"
                                :disabled="!{{ $nilaiCounts[$s->id] ?? 0 }} || !{{ $s->absensi ? 'true' : 'false' }}"
                                class="text-green-600 hover:text-green-900 disabled:opacity-50">
                                <img src="{{ asset('images/icons/detail.png') }}" alt="Preview" class="action-icon">
                            </button>
                            
                            <button @click="handleGenerate({{ $s->id }}, {{ $nilaiCounts[$s->id] ?? 0 }}, {{ $s->absensi ? 'true' : 'false' }}, '{{ $s->nama }}')"
                                :disabled="!{{ $nilaiCounts[$s->id] ?? 0 }} || !{{ $s->absensi ? 'true' : 'false' }}"
                                :class="{ 'opacity-50 cursor-not-allowed': !{{ $nilaiCounts[$s->id] ?? 0 }} || !{{ $s->absensi ? 'true' : 'false' }}, 'text-green-600 hover:text-green-900': {{ $nilaiCounts[$s->id] ?? 0 }} && {{ $s->absensi ? 'true' : 'false' }} }">
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
        </div>
    </div>
</div>

<div x-data="raporManager"
     x-cloak
     x-show="!initialized"
     class="p-4 bg-white mt-14">
    <div class="flex items-center justify-center p-12">
        <div class="flex items-center space-x-2">
            <svg class="animate-spin h-8 w-8 text-green-600" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p class="text-gray-600">Memuat data rapor...</p>
        </div>
    </div>
</div>


<div x-data="raporManager"
     x-cloak
     x-show="initialized && !templateUTSActive && !templateUASActive"
     class="p-4 bg-white mt-14">
     <div class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak Ada Template Aktif</h3>
        <p class="mt-1 text-sm text-gray-500">Admin belum mengaktifkan template rapor untuk kelas ini.</p>
        <div class="mt-6">
            <button type="button" onclick="refreshPage()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Muat Ulang
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', function() {
    Alpine.data('raporManager', () => ({
        activeTab: 'UTS',
        loading: false,
        initialized: false,
        selectedSiswa: [],
        searchQuery: '',
        showPreview: false,
        previewContent: '',
        templateUASActive: false,
        templateUTSActive: false,
        tahunAjaranId: "{{ session('tahun_ajaran_id') }}",
        
        init() {
            console.log('Initializing raporManager');
            // Cek template yang aktif terlebih dahulu
            this.checkActiveTemplates().then((data) => {
                this.initialized = true;

                // Kita perlu tahu mana template yang aktif
                const utsActive = data.UTS_active;
                const uasActive = data.UAS_active;
                
                // Tentukan tab default berdasarkan template yang aktif
                if (uasActive) {
                    this.activeTab = 'UAS'; // Jika UAS aktif, tampilkan tab UAS secara default
                } else if (utsActive) {
                    this.activeTab = 'UTS'; // Jika hanya UTS yang aktif
                } else {
                    // Jika keduanya tidak aktif, tetap di UTS tapi munculkan pesan
                    this.activeTab = 'UTS';
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tidak Ada Template Aktif',
                            text: 'Tidak ada template rapor yang aktif. Silakan hubungi admin untuk mengaktifkan template.',
                            confirmButtonColor: '#3085d6',
                        });
                    }, 500);
                }
                
                // Baru kemudian cek localStorage, tapi prioritaskan template yang aktif
                const savedTab = localStorage.getItem('activeRaporTab');
                if (savedTab) {
                    // Validasi apakah template untuk tab tersebut aktif
                    if (savedTab === 'UAS' && uasActive) {
                        this.activeTab = 'UAS';
                    } else if (savedTab === 'UTS' && utsActive) {
                        this.activeTab = 'UTS';
                    }
                    // Jika tidak aktif, tetap gunakan default yang sudah diatur di atas
                }
                
                // Simpan tab yang aktif ke localStorage
                localStorage.setItem('activeRaporTab', this.activeTab);
            });
        },
        
        async checkActiveTemplates() {
            try {
                const response = await fetch('/wali-kelas/rapor/check-templates');
                const data = await response.json();
                
                this.templateUTSActive = data.UTS_active;
                this.templateUASActive = data.UAS_active;
                return data;
            } catch (error) {
                console.error('Error checking templates:', error);
                this.templateUTSActive = true; // Default nilai jika terjadi error
                this.templateUASActive = false;
                return { UTS_active: true, UAS_active: false };
            }
        },
        setActiveTab(tab) {
            // Validasi akses UAS
            if (tab === 'UAS' && !this.templateUASActive) {
                Swal.fire({
                    icon: 'info',
                    title: 'Rapor UAS Belum Aktif',
                    text: 'Admin belum mengaktifkan template rapor UAS. Silakan hubungi admin untuk mengaktifkan template UAS terlebih dahulu.',
                    confirmButtonColor: '#3085d6',
                });
                return;
            }
            
            // Validasi akses UTS
            if (tab === 'UTS' && !this.templateUTSActive) {
                Swal.fire({
                    icon: 'info',
                    title: 'Rapor UTS Belum Aktif',
                    text: 'Admin belum mengaktifkan template rapor UTS. Silakan hubungi admin untuk mengaktifkan template UTS terlebih dahulu.',
                    confirmButtonColor: '#3085d6',
                });
                return;
            }
            
            this.activeTab = tab;
            localStorage.setItem('activeRaporTab', tab);
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
                console.log('Fetching preview for siswa ID:', siswaId);
                
                // Tambahkan query parameter tahun_ajaran_id
                const response = await fetch(`/wali-kelas/rapor/preview/${siswaId}?tahun_ajaran_id=${this.tahunAjaranId}`);
                console.log('Preview response status:', response.status);
                
                // Jika tidak sukses, tampilkan detail error
                if (!response.ok) {
                    // Coba ambil teks error
                    const errorText = await response.text();
                    console.error('Error response text:', errorText);
                    throw new Error(`Server error: ${response.status} - ${errorText.substring(0, 200)}...`);
                }
                
                // Parse response JSON
                const data = await response.json();
                console.log('Preview data received:', data);
                
                if (data.success) {
                    this.previewContent = data.html;
                    this.showPreview = true;
                } else {
                    throw new Error(data.message || 'Preview tidak berhasil');
                }
            } catch (error) {
                console.error('Error in handlePreview:', error);
                alert('Terjadi kesalahan saat memuat preview rapor: ' + error.message);
            } finally {
                this.loading = false;
            }
        },

    async handleGenerate(siswaId, nilaiCount, hasAbsensi, namaSiswa) {
        if (!this.validateData(nilaiCount, hasAbsensi)) return;
        
        try {
            this.loading = true;
            
            // Add logging to help diagnose the issue
            console.log('Generating report for:', {
                siswaId, 
                type: this.activeTab,
                tahunAjaranId: this.tahunAjaranId
            });
            
            const response = await fetch(`/wali-kelas/rapor/generate/${siswaId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    type: this.activeTab,
                    tahun_ajaran_id: this.tahunAjaranId, // Make sure this is included!
                    action: 'download'
                })
            });

            // Better error handling - log response status
            console.log('Response status:', response.status);
            
            // Check for JSON response first to handle error messages
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.includes("application/json")) {
                const data = await response.json();
                
                if (!response.ok) {
                    // Detailed error message with SweetAlert
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Generate Rapor',
                        html: `
                            <p>${data.message || 'Terjadi kesalahan saat memproses rapor'}</p>
                            <p class="mt-2 text-sm font-semibold">Error detail:</p>
                            <p class="text-red-600">${data.error_type || ''}</p>
                        `,
                        confirmButtonText: 'Mengerti'
                    });
                    return;
                }
                
                // If JSON response is successful, might contain file URL
                if (data.success && data.file_url) {
                    window.location.href = data.file_url;
                    return;
                }
            }
            
            // Handle non-JSON response (file download)
            if (response.ok) {
                const blob = await response.blob();
                const cleanName = namaSiswa.replace(/[^\w\s]/gi, '').replace(/\s+/g, '_');
                const fileName = `rapor_${this.activeTab.toLowerCase()}_${cleanName}.docx`;
                await this.downloadFile(blob, fileName);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Rapor berhasil digenerate dan diunduh'
                });
            } else {
                throw new Error(`Gagal mengunduh rapor: ${response.status}`);
            }
        } catch (error) {
            console.error('Error:', error);
            
            // More detailed error alert
            Swal.fire({
                icon: 'error',
                title: 'Gagal Generate Rapor',
                html: `
                    <p>${error.message}</p>
                    <p class="mt-2 text-sm font-semibold">Kemungkinan penyebab:</p>
                    <ul class="text-left mt-2 text-sm list-disc pl-5">
                        <li>Template rapor tidak ditemukan untuk kelas ini (tipe: ${this.activeTab})</li>
                        <li>Data nilai belum disimpan dengan "Simpan & Preview"</li>
                        <li>Tahun ajaran yang dipilih: ${this.tahunAjaranId || 'tidak diketahui'}</li>
                        <li>Ada error server (periksa console)</li>
                    </ul>
                `,
                confirmButtonText: 'Mengerti'
            });
        } finally {
            this.loading = false;
        }
    },

        async generateBatchReport() {
            if (this.loading || this.selectedSiswa.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Pilih siswa terlebih dahulu'
                });
                return;
            }

            // Validasi sebelum mengirim request
            const invalidSiswa = [];
            document.querySelectorAll('tbody tr').forEach(row => {
                // Ambil checkbox yang dicek
                const checkbox = row.querySelector('input[type="checkbox"]');
                if (checkbox && checkbox.checked) {
                    // Ambil status nilai dan kehadiran
                    const hasNilai = row.querySelector('.bg-green-100.text-green-800') !== null;
                    const hasAbsensi = row.querySelector('td:nth-child(6) .bg-green-100') !== null;
                    
                    if (!hasNilai || !hasAbsensi) {
                        // Ambil nama siswa
                        const namaSiswa = row.querySelector('td:nth-child(4)').textContent.trim();
                        invalidSiswa.push(namaSiswa);
                    }
                }
            });

            // Jika ada siswa yang datanya belum lengkap
            if (invalidSiswa.length > 0) {
                // Gunakan SweetAlert untuk konfirmasi yang lebih baik
            const result = await Swal.fire({
                    icon: 'warning',
                    title: 'Data Tidak Lengkap',
                    html: `
                        <p>Beberapa siswa belum memiliki data lengkap:</p>
                        <ul class="text-left mt-2 text-sm">
                            ${invalidSiswa.map(nama => `<li>- ${nama}</li>`).join('')}
                        </ul>
                        <p class="mt-2">Lanjutkan cetak hanya untuk siswa dengan data lengkap?</p>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal'
                });
                
                if (!result.isConfirmed) {
                    return;
                }
            }

            try {
                this.loading = true;
                
                // Tampilkan loading indicator yang informatif
                const loadingAlert = Swal.fire({
                    title: 'Memproses Rapor',
                    html: 'Mohon tunggu sebentar...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                const response = await fetch('/wali-kelas/rapor/batch-generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        siswa_ids: this.selectedSiswa,
                        type: this.activeTab,
                        tahun_ajaran_id: this.tahunAjaranId
                    })
                });

                // Tutup loading alert
                loadingAlert.close();

                if (!response.ok) {
                    // Coba ambil pesan error dalam format JSON
                    try {
                        const error = await response.json();
                        throw new Error(error.message || 'Gagal generate batch rapor');
                    } catch (jsonError) {
                        // Jika bukan JSON, ambil teks error
                        const errorText = await response.text();
                        throw new Error(`Gagal generate batch rapor (${response.status}): ${errorText.substring(0, 100)}...`);
                    }
                }

                const blob = await response.blob();
                // Untuk batch, tetap menggunakan format nama file yang umum
                await this.downloadFile(blob, `rapor_batch_${this.activeTab.toLowerCase()}_${new Date().getTime()}.zip`);
                
                // Notifikasi sukses
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Batch rapor berhasil digenerate dan diunduh'
                });
            } catch (error) {
                console.error('Error:', error);
                // Alert yang lebih informatif
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mencetak Rapor',
                    html: `
                        <p>${error.message}</p>
                        <p class="mt-2 text-sm text-red-600">Kemungkinan penyebab:</p>
                        <ul class="text-left mt-2 text-sm list-disc pl-5">
                            <li>Data siswa tidak lengkap untuk tahun ajaran ${this.tahunAjaranId || 'yang dipilih'}</li>
                            <li>Template rapor tidak tersedia atau tidak aktif</li>
                            <li>Error server saat memproses (periksa log server)</li>
                        </ul>
                    `,
                    confirmButtonText: 'Mengerti'
                });
            } finally {
                this.loading = false;
            }
        },

        validateData(nilaiCount, hasAbsensi) {
            const messages = [];
            if (!nilaiCount || nilaiCount === 0) messages.push("- Data nilai belum lengkap");
            if (!hasAbsensi) messages.push("- Data kehadiran belum lengkap");
            if (!this.tahunAjaranId) messages.push("- Tahun ajaran tidak ditemukan");
            
            if (messages.length > 0) {
                // Better error feedback with SweetAlert instead of plain alert
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Tidak Lengkap',
                    html: `
                        <p>Tidak bisa melanjutkan karena:</p>
                        <ul class="text-left mt-2">
                            ${messages.map(msg => `<li>${msg}</li>`).join('')}
                        </ul>
                    `,
                    confirmButtonText: 'Mengerti'
                });
                return false;
            }
            return true;
        },

        async debugSiswaData(siswaId) {
            try {
                this.loading = true;
                const response = await fetch(`/wali-kelas/rapor/diagnose/${siswaId}?type=${this.activeTab}`);
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        title: 'Diagnosis Data Siswa',
                        html: `
                            <div class="text-left">
                                <h3 class="font-bold mb-2">Status Data:</h3>
                                <p>Nilai: ${data.data.nilai_status ? '✅ Lengkap' : '❌ Tidak lengkap'}</p>
                                <p>Absensi: ${data.data.absensi_status ? '✅ Lengkap' : '❌ Tidak lengkap'}</p>
                                <p>Template ${this.activeTab}: ${data.data.template_status ? '✅ Tersedia' : '❌ Tidak tersedia'}</p>
                                
                                <h3 class="font-bold mt-4 mb-2">Detail:</h3>
                                <p>${data.data.detail}</p>
                                
                                <h3 class="font-bold mt-4 mb-2">Sesi:</h3>
                                <p>Tahun Ajaran ID: ${data.data.tahun_ajaran_id}</p>
                            </div>
                        `,
                        icon: 'info',
                        confirmButtonText: 'Tutup'
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal Mendapatkan Diagnosis',
                        text: data.message,
                        icon: 'error'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Terjadi kesalahan saat mendiagnosa data',
                    icon: 'error'
                });
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
    }));
});

function refreshPage() {
    window.location.reload();
}
</script>
@endpush
@endsection