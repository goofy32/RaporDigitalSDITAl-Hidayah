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
    .loading-overlay {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(2px);
    }
</style>
@endpush

<!-- Main Container with Single Alpine Instance -->
<div x-data="raporManager" x-cloak class="p-4 bg-white mt-14">
    
    <!-- Loading State -->
    <div x-show="!initialized" class="flex items-center justify-center p-12">
        <div class="flex items-center space-x-2">
            <svg class="animate-spin h-8 w-8 text-green-600" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p class="text-gray-600">Memuat data rapor...</p>
        </div>
    </div>

    <!-- No Template Active State -->
    <div x-show="initialized && !templateUTSActive && !templateUASActive" class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak Ada Template Aktif</h3>
        <p class="mt-1 text-sm text-gray-500">Admin belum mengaktifkan template rapor untuk kelas ini.</p>
        <div class="mt-6">
            <button type="button" @click="refreshPage()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Muat Ulang
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div x-show="initialized && (templateUTSActive || templateUASActive)">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">
                Manajemen Rapor Kelas {{ auth()->user()->kelasWali->nama_kelas ?? 'N/A' }}
            </h2>
        </div>

        <!-- Debug Panel (Remove in production) -->
        <div x-show="false" class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h3 class="font-bold text-yellow-800">Debug Info</h3>
            <div class="mt-2 text-sm">
                <p>Initialized: <span x-text="initialized"></span></p>
                <p>UTS Active: <span x-text="templateUTSActive"></span></p>
                <p>UAS Active: <span x-text="templateUASActive"></span></p>
                <p>Active Tab: <span x-text="activeTab"></span></p>
                <p>Tahun Ajaran ID: <span x-text="tahunAjaranId"></span></p>
            </div>
            <div class="mt-2 space-x-2">
                <button @click="testConnection()" class="px-3 py-1 bg-blue-500 text-white rounded text-sm">
                    Test Connection
                </button>
                <button @click="checkTemplatesManual()" class="px-3 py-1 bg-green-500 text-white rounded text-sm">
                    Check Templates
                </button>
            </div>
        </div>

        <!-- Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="setActiveTab('UTS')"
                            :class="{
                                'border-green-500 text-green-600': activeTab === 'UTS',
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'UTS',
                                'cursor-not-allowed opacity-70': !templateUTSActive
                            }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                            type="button"
                            :disabled="!templateUTSActive">
                        Rapor UTS
                        <span x-show="!templateUTSActive" class="ml-1 text-xs text-red-500">(Nonaktif)</span>
                    </button>
                    <button @click="setActiveTab('UAS')"
                            :class="{
                                'border-green-500 text-green-600': activeTab === 'UAS',
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'UAS',
                                'cursor-not-allowed opacity-70': !templateUASActive
                            }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                            type="button"
                            :disabled="!templateUASActive">
                        Rapor UAS
                        <span x-show="!templateUASActive" class="ml-1 text-xs text-red-500">(Nonaktif)</span>
                    </button>
                </nav>
            </div>
        </div>

        <!-- Search Box -->
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
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

        <!-- <div x-show="true" class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h3 class="font-bold text-yellow-800 mb-2">Load Testing Panel</h3>
            <div class="flex flex-wrap gap-2">
                <button @click="simulateSimultaneousDownloads({{ $siswa->first()->id ?? 1 }}, 5, true, 'Test User', 'docx')"
                        class="px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600">
                    Test 10x DOCX Bersamaan
                </button>
                
                <button @click="simulateSequentialDownloads({{ $siswa->first()->id ?? 1 }}, 5, true, 'Test User', 'docx')"
                        class="px-3 py-1 bg-green-500 text-white rounded text-sm hover:bg-green-600">
                    Test 10x DOCX (1s interval)
                </button>
                
                <button @click="simulateSimultaneousDownloads({{ $siswa->first()->id ?? 1 }}, 5, true, 'Test User', 'pdf')"
                        class="px-3 py-1 bg-red-500 text-white rounded text-sm hover:bg-red-600">
                    Test 10x PDF Bersamaan
                </button>
                
                <button @click="simulateSequentialDownloads({{ $siswa->first()->id ?? 1 }}, 5, true, 'Test User', 'pdf')"
                        class="px-3 py-1 bg-purple-500 text-white rounded text-sm hover:bg-purple-600">
                    Test 10x PDF (1s interval)
                </button>
            </div>
            <p class="text-xs text-yellow-700 mt-2">
                ⚠️ Testing Panel - Akan mengirim 10 request sekaligus. Check console untuk detail logs.
            </p>
        </div> -->
        
        <!-- Data Table -->
        <div class="overflow-x-auto shadow-md rounded-lg">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
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
                    <tr class="bg-white border-b hover:bg-gray-50 transition-colors">                    
                        <td class="px-6 py-4">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">{{ $s->nis }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $s->nama }}</td>
                        
                        <!-- Status Nilai -->
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                @if($diagnosisResults[$s->id]['nilai_status'])
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                        Lengkap
                                    </span>
                                @else
                                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full relative group">
                                        Belum Lengkap
                                        <div class="absolute left-0 top-full mt-2 w-64 p-2 bg-gray-800 text-white text-xs rounded shadow-lg 
                                                opacity-0 invisible group-hover:opacity-100 group-hover:visible transition z-10">
                                            <p>Masalah terdeteksi:</p>
                                            <p class="font-medium mt-1">{{ $diagnosisResults[$s->id]['nilai_message'] }}</p>
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

                        <!-- Status Kehadiran -->
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                @if($diagnosisResults[$s->id]['absensi_status'])
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                        Lengkap
                                    </span>
                                @else
                                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full relative group">
                                        Belum Lengkap
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

                        <!-- Actions -->
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">                                
                                <!-- Download DOCX Button -->
                                <button @click="handleGenerate({{ $s->id }}, {{ $nilaiCounts[$s->id] ?? 0 }}, {{ $s->absensi ? 'true' : 'false' }}, '{{ $s->nama }}')"
                                    :disabled="!{{ $nilaiCounts[$s->id] ?? 0 }} || !{{ $s->absensi ? 'true' : 'false' }}"
                                    :class="{ 
                                        'opacity-50 cursor-not-allowed': !{{ $nilaiCounts[$s->id] ?? 0 }} || !{{ $s->absensi ? 'true' : 'false' }}, 
                                        'text-green-600 hover:text-green-900': {{ $nilaiCounts[$s->id] ?? 0 }} && {{ $s->absensi ? 'true' : 'false' }} 
                                    }"
                                    class="transition-colors"
                                    title="Unduh Rapor DOCX">
                                    <img src="{{ asset('images/icons/download.png') }}" alt="Download" class="action-icon">
                                </button>
                                
                                <!-- Preview PDF Button -->
                                <button @click="handlePreviewPdf({{ $s->id }}, {{ $nilaiCounts[$s->id] ?? 0 }}, {{ $s->absensi ? 'true' : 'false' }})"
                                        :disabled="!{{ $nilaiCounts[$s->id] ?? 0 }} || !{{ $s->absensi ? 'true' : 'false' }} || loading"
                                        :class="{ 
                                            'opacity-50 cursor-not-allowed': !{{ $nilaiCounts[$s->id] ?? 0 }} || !{{ $s->absensi ? 'true' : 'false' }} || loading, 
                                            'text-purple-600 hover:text-purple-900': {{ $nilaiCounts[$s->id] ?? 0 }} && {{ $s->absensi ? 'true' : 'false' }} && !loading 
                                        }"
                                        class="transition-colors"
                                        title="Preview PDF">
                                    <img src="{{ asset('images/icons/detail.png') }}" alt="Preview" class="action-icon">
                                </button>

                                <button @click="handleDownloadPdf({{ $s->id }}, {{ $nilaiCounts[$s->id] ?? 0 }}, {{ $s->absensi ? 'true' : 'false' }}, '{{ $s->nama }}')"
                                        :disabled="!{{ $nilaiCounts[$s->id] ?? 0 }} || !{{ $s->absensi ? 'true' : 'false' }} || loading"
                                        :class="{ 
                                            'opacity-50 cursor-not-allowed': !{{ $nilaiCounts[$s->id] ?? 0 }} || !{{ $s->absensi ? 'true' : 'false' }} || loading, 
                                            'text-red-600 hover:text-red-900': {{ $nilaiCounts[$s->id] ?? 0 }} && {{ $s->absensi ? 'true' : 'false' }} && !loading 
                                        }"
                                        class="transition-colors"
                                        title="Unduh Rapor PDF">
                                    <template x-if="loadingPdf === {{ $s->id }}">
                                        <svg class="action-icon animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </template>
                                    <template x-if="loadingPdf !== {{ $s->id }}">
                                        <svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </template>
                                </button>                                
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            Tidak ada data siswa
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Preview Modal -->
    <div x-show="showPreview" 
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
         x-cloak
         @click.away="showPreview = false">
        <div class="relative bg-white rounded-lg mx-auto mt-10 max-w-4xl p-4 m-4">
            <button @click="showPreview = false" 
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-500 z-10">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            
            <div x-html="previewContent" class="mt-4 max-h-96 overflow-y-auto"></div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div x-show="loading" 
         class="fixed inset-0 loading-overlay flex items-center justify-center z-40"
         x-cloak>
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center space-x-3">
                <svg class="animate-spin h-6 w-6 text-green-600" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span class="text-gray-700">Memproses...</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', function() {
    Alpine.data('raporManager', () => ({
        // State variables
        activeTab: '{{ $type }}',
        loading: false,
        initialized: false,
        searchQuery: '',
        showPreview: false,
        previewContent: '',
        templateUTSActive: false,
        templateUASActive: false,
        loadingPdf: null,
        tahunAjaranId: "{{ session('tahun_ajaran_id') }}",
        semester: {{ $semester }},
        
        // Initialization
        init() {
            console.log('=== RAPOR MANAGER INIT ===');
            console.log('Initial activeTab:', this.activeTab);
            console.log('TahunAjaranId:', this.tahunAjaranId);
            console.log('Semester:', this.semester);
            
            this.initializeTemplates();
        },
        
        async initializeTemplates() {
            try {
                const data = await this.checkActiveTemplates();
                console.log('Template check result:', data);
                
                this.templateUTSActive = data.UTS_active || false;
                this.templateUASActive = data.UAS_active || false;
                
                // Set appropriate active tab
                if (!this.templateUTSActive && !this.templateUASActive) {
                    // No templates active
                    console.log('No templates active');
                } else if (this.activeTab === 'UTS' && !this.templateUTSActive) {
                    // Switch to UAS if UTS not active
                    this.activeTab = this.templateUASActive ? 'UAS' : 'UTS';
                } else if (this.activeTab === 'UAS' && !this.templateUASActive) {
                    // Switch to UTS if UAS not active
                    this.activeTab = this.templateUTSActive ? 'UTS' : 'UAS';
                }
                
                // Check localStorage
                const savedTab = localStorage.getItem('activeRaporTab');
                if (savedTab && 
                    ((savedTab === 'UAS' && this.templateUASActive) || 
                     (savedTab === 'UTS' && this.templateUTSActive))) {
                    this.activeTab = savedTab;
                }
                
                localStorage.setItem('activeRaporTab', this.activeTab);
                this.initialized = true;
                
                console.log('Final state:', {
                    templateUTSActive: this.templateUTSActive,
                    templateUASActive: this.templateUASActive,
                    activeTab: this.activeTab,
                    initialized: this.initialized
                });
                
            } catch (error) {
                console.error('Error initializing templates:', error);
                this.initialized = true;
                this.templateUTSActive = true; // Fallback
                this.templateUASActive = false;
            }
        },
        
        async checkActiveTemplates() {
            try {
                const response = await fetch('/wali-kelas/rapor/check-templates', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                return {
                    UTS_active: data.UTS_active || false,
                    UAS_active: data.UAS_active || false
                };
            } catch (error) {
                console.error('Error checking templates:', error);
                return { UTS_active: true, UAS_active: false };
            }
        },

        // Tab management
        setActiveTab(tab) {
            if (tab === 'UAS' && !this.templateUASActive) {
                Swal.fire({
                    icon: 'info',
                    title: 'Rapor UAS Belum Aktif',
                    text: 'Admin belum mengaktifkan template rapor UAS.',
                });
                return;
            }
            
            if (tab === 'UTS' && !this.templateUTSActive) {
                Swal.fire({
                    icon: 'info',
                    title: 'Rapor UTS Belum Aktif', 
                    text: 'Admin belum mengaktifkan template rapor UTS.',
                });
                return;
            }
            
            this.activeTab = tab;
            localStorage.setItem('activeRaporTab', tab);
            console.log('Tab changed to:', tab);
        },

        // Search functionality
        handleSearch(event) {
            const searchValue = event.target.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        },

        // Validation
        validateData(nilaiCount, hasAbsensi) {
            const messages = [];
            if (!nilaiCount || nilaiCount === 0) messages.push("- Data nilai belum lengkap");
            if (!hasAbsensi) messages.push("- Data kehadiran belum lengkap");
            if (!this.tahunAjaranId) messages.push("- Tahun ajaran tidak ditemukan");
            
            if (messages.length > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Tidak Lengkap',
                    html: `
                        <p>Tidak bisa melanjutkan karena:</p>
                        <ul class="text-left mt-2">
                            ${messages.map(msg => `<li>${msg}</li>`).join('')}
                        </ul>
                        <p class="mt-2">Semester aktif saat ini: ${this.semester}</p>
                    `,
                    confirmButtonText: 'Mengerti'
                });
                return false;
            }
            return true;
        },

        // Preview functionality
        async handlePreview(siswaId, nilaiCount, hasAbsensi) {
            if (!this.validateData(nilaiCount, hasAbsensi)) return;
            
            try {
                this.loading = true;
                const type = this.activeTab;
                
                const response = await fetch(`/wali-kelas/rapor/preview/${siswaId}?tahun_ajaran_id=${this.tahunAjaranId}&type=${type}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`Server error: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    this.previewContent = data.html;
                    this.showPreview = true;
                } else {
                    throw new Error(data.message || 'Preview tidak berhasil');
                }
            } catch (error) {
                console.error('Error in handlePreview:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Preview',
                    text: error.message
                });
            } finally {
                this.loading = false;
            }
        },

        // Generate DOCX
        async handleGenerate(siswaId, nilaiCount, hasAbsensi, namaSiswa) {
            if (!this.validateData(nilaiCount, hasAbsensi)) return;
            
            try {
                this.loading = true;
                const type = this.activeTab;
                
                const response = await fetch(`/wali-kelas/rapor/generate/${siswaId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        type: type,
                        tahun_ajaran_id: this.tahunAjaranId,
                        action: 'download'
                    })
                });

                const contentType = response.headers.get("content-type");
                if (contentType && contentType.includes("application/json")) {
                    const data = await response.json();
                    
                    if (!response.ok) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Generate Rapor',
                            text: data.message || 'Terjadi kesalahan saat memproses rapor'
                        });
                        return;
                    }
                    
                    if (data.success && data.file_url) {
                        window.location.href = data.file_url;
                        return;
                    }
                }
                
                if (response.ok) {
                    const blob = await response.blob();
                    const cleanName = namaSiswa.replace(/[^\w\s]/gi, '').replace(/\s+/g, '_');
                    const fileName = `Rapor_${this.activeTab}_${cleanName}.docx`;
                    
                    await this.downloadFile(blob, fileName);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Rapor berhasil digenerate dan diunduh',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    throw new Error(`Gagal mengunduh rapor: ${response.status}`);
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Generate Rapor',
                    text: error.message
                });
            } finally {
                this.loading = false;
            }
        },

        // Generate PDF
        async handleDownloadPdf(siswaId, nilaiCount, hasAbsensi, namaSiswa) {
            if (!this.validateData(nilaiCount, hasAbsensi)) return;
            
            try {
                this.loadingPdf = siswaId;
                
                console.log('🚀 Starting PDF download request', {
                    siswaId,
                    type: this.activeTab,
                    tahunAjaranId: this.tahunAjaranId
                });
                
                // Step 1: Request PDF generation
                const response = await fetch(`/wali-kelas/rapor/request-pdf/${siswaId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        type: this.activeTab,
                        tahun_ajaran_id: this.tahunAjaranId
                    })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                console.log('📨 PDF request response:', data);
                
                if (data.success) {
                    if (data.ready) {
                        // PDF ready (from cache), download immediately
                        console.log('⚡ PDF ready from cache');
                        this.downloadPdfFile(data.download_url, data.filename, namaSiswa, data.cached);
                    } else {
                        // PDF being generated, show progress
                        console.log('🔄 PDF generating, starting progress tracking');
                        this.showPdfProgressEnhanced(data.request_id, namaSiswa, data.estimated_time);
                    }
                } else {
                    throw new Error(data.message || 'Gagal memproses PDF');
                }
                
            } catch (error) {
                console.error('❌ Error in handleDownloadPdf:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Generate PDF',
                    html: `
                        <p>${error.message}</p>
                        <div class="mt-4 text-xs text-gray-500">
                            <p>Kemungkinan penyebab:</p>
                            <ul class="text-left list-disc list-inside">
                                <li>Koneksi internet tidak stabil</li>
                                <li>Server sedang sibuk</li>
                                <li>Queue worker tidak berjalan</li>
                            </ul>
                            <p class="mt-2">Coba lagi dalam beberapa saat.</p>
                        </div>
                    `
                });
            } finally {
                this.loadingPdf = null;
            }
        },

        showPdfProgressEnhanced(requestId, namaSiswa, estimatedTime) {
            let checkCount = 0;
            const maxChecks = 30; // 1 minute max (30 * 2s)
            let consecutiveErrors = 0;
            const maxConsecutiveErrors = 3;
            
            console.log('📊 Starting progress tracking', {
                requestId,
                maxChecks,
                estimatedTime
            });
            
            const progressInterval = setInterval(async () => {
                try {
                    checkCount++;
                    console.log(`📈 Progress check ${checkCount}/${maxChecks} for ${requestId}`);
                    
                    const response = await fetch(`/wali-kelas/rapor/pdf-progress/${requestId}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    
                    const data = await response.json();
                    console.log('📊 Progress response:', data);
                    
                    // Reset consecutive errors on successful response
                    consecutiveErrors = 0;
                    
                    if (data.success && data.progress) {
                        const progressData = data.progress;
                        
                        if (progressData.completed) {
                            console.log('✅ PDF generation completed');
                            clearInterval(progressInterval);
                            Swal.close();
                            
                            if (progressData.error) {
                                console.log('❌ PDF generation failed');
                                Swal.fire({
                                    icon: 'error',
                                    title: 'PDF Generation Failed',
                                    html: `
                                        <p>${progressData.message}</p>
                                        <p class="text-sm text-gray-600 mt-2">Request ID: ${requestId}</p>
                                        <p class="text-xs text-gray-500 mt-2">Coba download lagi, mungkin sudah siap.</p>
                                    `
                                });
                            } else {
                                console.log('🎉 PDF ready for download');
                                this.downloadPdfFile(
                                    progressData.download_url, 
                                    progressData.filename, 
                                    namaSiswa,
                                    progressData.cached || false
                                );
                            }
                        } else {
                            // Update progress
                            const progress = Math.max(0, Math.min(100, progressData.percentage || 0));
                            console.log(`📊 Progress update: ${progress}%`);
                            
                            Swal.update({
                                html: `
                                    <div class="text-center">
                                        <div class="mb-4">${progressData.message || 'Processing...'}</div>
                                        <div class="w-full bg-gray-200 rounded-full h-3">
                                            <div class="bg-blue-600 h-3 rounded-full transition-all duration-500" 
                                                style="width: ${progress}%"></div>
                                        </div>
                                        <div class="mt-2 text-sm text-gray-600">${progress}%</div>
                                        <div class="mt-2 text-xs text-gray-500">Est. ${estimatedTime}</div>
                                        <div class="mt-3 text-xs text-gray-400">Check ${checkCount}/${maxChecks}</div>
                                    </div>
                                `
                            });
                        }
                    } else {
                        console.log('⚠️ Invalid progress response:', data);
                        consecutiveErrors++;
                        
                        if (consecutiveErrors >= maxConsecutiveErrors) {
                            throw new Error(data.message || 'Invalid progress response');
                        }
                    }
                    
                    // Timeout check
                    if (checkCount >= maxChecks) {
                        console.log('⏰ Progress tracking timeout');
                        clearInterval(progressInterval);
                        Swal.close();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Progress Timeout',
                            html: `
                                <p>Proses terlalu lama atau tidak dapat dilacak.</p>
                                <p class="text-sm text-gray-600 mt-2">PDF mungkin masih sedang diproses di background.</p>
                                <p class="text-sm text-blue-600 mt-2">Coba download lagi dalam 1-2 menit.</p>
                            `
                        });
                    }
                    
                } catch (error) {
                    console.error('❌ Progress check error:', error);
                    consecutiveErrors++;
                    
                    if (consecutiveErrors >= maxConsecutiveErrors || checkCount >= maxChecks) {
                        clearInterval(progressInterval);
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Connection Error',
                            html: `
                                <p>Tidak dapat memeriksa progress.</p>
                                <p class="text-sm text-gray-600 mt-2">Error: ${error.message}</p>
                                <p class="text-sm text-blue-600 mt-3">
                                    💡 <strong>Tip:</strong> PDF mungkin masih diproses. 
                                    Coba klik download lagi dalam 30-60 detik.
                                </p>
                            `
                        });
                    }
                }
            }, 2000); // Check every 2 seconds
            
            // Initial progress dialog
            Swal.fire({
                title: 'Generating PDF',
                html: `
                    <div class="text-center">
                        <div class="mb-4">Memulai generate PDF untuk ${namaSiswa}...</div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-blue-600 h-3 rounded-full transition-all duration-500" style="width: 5%"></div>
                        </div>
                        <div class="mt-2 text-sm text-gray-600">5%</div>
                        <div class="mt-2 text-xs text-gray-500">Est. ${estimatedTime}</div>
                        <div class="mt-3 text-xs text-gray-400">Request ID: ${requestId}</div>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Batal',
                didOpen: () => {
                    const cancelBtn = Swal.getCancelButton();
                    if (cancelBtn) {
                        cancelBtn.addEventListener('click', () => {
                            console.log('🚫 User cancelled progress tracking');
                            clearInterval(progressInterval);
                        });
                    }
                }
            });
        },
        showPdfProgress(requestId, namaSiswa, estimatedTime) {
            let progress = 0;
            let checkCount = 0;
            const maxChecks = 60; // 2 minutes max
            
            const progressInterval = setInterval(async () => {
                try {
                    checkCount++;
                    
                    const response = await fetch(`/wali-kelas/rapor/pdf-progress/${requestId}`);
                    const data = await response.json();
                    
                    if (data.success && data.progress) {
                        const progressData = data.progress;
                        
                        if (progressData.completed) {
                            clearInterval(progressInterval);
                            Swal.close();
                            
                            if (progressData.error) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'PDF Generation Failed',
                                    html: `
                                        <p>${progressData.message}</p>
                                        <p class="text-sm text-gray-600 mt-2">Request ID: ${requestId}</p>
                                    `
                                });
                            } else {
                                this.downloadPdfFile(
                                    progressData.download_url, 
                                    progressData.filename, 
                                    namaSiswa,
                                    progressData.cached
                                );
                            }
                        } else {
                            // Update progress
                            progress = progressData.percentage;
                            Swal.update({
                                html: `
                                    <div class="text-center">
                                        <div class="mb-4">${progressData.message}</div>
                                        <div class="w-full bg-gray-200 rounded-full h-3">
                                            <div class="bg-blue-600 h-3 rounded-full transition-all duration-500" 
                                                style="width: ${progress}%"></div>
                                        </div>
                                        <div class="mt-2 text-sm text-gray-600">${progress}%</div>
                                        <div class="mt-2 text-xs text-gray-500">Est. ${estimatedTime}</div>
                                    </div>
                                `
                            });
                        }
                    } else {
                        // Progress not found or error
                        if (checkCount > 3) { // Give it a few tries
                            clearInterval(progressInterval);
                            Swal.close();
                            Swal.fire({
                                icon: 'error',
                                title: 'PDF Generation Error',
                                text: 'Tidak dapat melacak progress. Silakan coba lagi.'
                            });
                        }
                    }
                    
                    // Timeout after max checks
                    if (checkCount >= maxChecks) {
                        clearInterval(progressInterval);
                        Swal.close();
                        Swal.fire({
                            icon: 'warning',
                            title: 'PDF Generation Timeout',
                            text: 'Proses terlalu lama. Silakan coba lagi nanti.'
                        });
                    }
                    
                } catch (error) {
                    console.error('Progress check error:', error);
                    checkCount++;
                    
                    if (checkCount >= 5) {
                        clearInterval(progressInterval);
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Connection Error',
                            text: 'Tidak dapat memeriksa progress. Silakan coba lagi.'
                        });
                    }
                }
            }, 2000); // Check every 2 seconds
            
            // Initial progress dialog
            Swal.fire({
                title: 'Generating PDF',
                html: `
                    <div class="text-center">
                        <div class="mb-4">Memulai generate PDF untuk ${namaSiswa}...</div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-blue-600 h-3 rounded-full transition-all duration-500" style="width: 10%"></div>
                        </div>
                        <div class="mt-2 text-sm text-gray-600">10%</div>
                        <div class="mt-2 text-xs text-gray-500">Est. ${estimatedTime}</div>
                        <div class="mt-3 text-xs text-gray-400">Request ID: ${requestId}</div>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Batal',
                didOpen: () => {
                    // Handle cancel
                    const cancelBtn = Swal.getCancelButton();
                    cancelBtn.addEventListener('click', () => {
                        clearInterval(progressInterval);
                        // Optionally cancel the job here
                    });
                }
            });
        },

        // Enhanced download with cache info
        downloadPdfFile(downloadUrl, filename, namaSiswa, isCached = false) {
            const a = document.createElement('a');
            a.href = downloadUrl;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            
            Swal.fire({
                icon: 'success',
                title: 'PDF Ready!',
                html: `
                    <div>
                        <p>Rapor PDF untuk <strong>${namaSiswa}</strong> berhasil diunduh</p>
                        ${isCached ? 
                            '<p class="text-sm text-blue-600 mt-2">⚡ Dari cache (instan)</p>' : 
                            '<p class="text-sm text-green-600 mt-2">✨ Freshly generated</p>'
                        }
                    </div>
                `,
                timer: 3000,
                showConfirmButton: false
            });
        },
        // Preview PDF
        async handlePreviewPdf(siswaId, nilaiCount, hasAbsensi) {
            if (!this.validateData(nilaiCount, hasAbsensi)) return;
            
            try {
                this.loading = true;
                const type = this.activeTab;
                const url = `/wali-kelas/rapor/preview-pdf/${siswaId}?type=${type}&tahun_ajaran_id=${this.tahunAjaranId}`;
                
                const newWindow = window.open(url, '_blank');
                
                if (!newWindow) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Popup Diblokir',
                        html: `<a href="${url}" target="_blank" class="text-blue-600 underline">Buka PDF Preview</a>`
                    });
                }
                
            } catch (error) {
                console.error('Error previewing PDF:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Preview PDF',
                    text: error.message || 'Terjadi kesalahan saat membuka preview PDF'
                });
            } finally {
                this.loading = false;
            }
        },

        // Utility functions
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

        refreshPage() {
            window.location.reload();
        },

        // Debug functions (remove in production)
        async testConnection() {
            try {
                const response = await fetch('/wali-kelas/test-connection');
                const data = await response.json();
                console.log('Connection test result:', data);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Connection Test',
                    html: `<pre class="text-left text-sm">${JSON.stringify(data, null, 2)}</pre>`
                });
            } catch (error) {
                console.error('Connection test failed:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Connection Failed',
                    text: error.message
                });
            }
        },

        async checkTemplatesManual() {
            try {
                const data = await this.checkActiveTemplates();
                console.log('Templates check result:', data);
                
                Swal.fire({
                    icon: 'info',
                    title: 'Template Status',
                    html: `
                        <div class="text-left">
                            <p>UTS Active: ${data.UTS_active ? '✅' : '❌'}</p>
                            <p>UAS Active: ${data.UAS_active ? '✅' : '❌'}</p>
                            ${data.error ? `<p class="text-red-600">Error: ${data.error}</p>` : ''}
                        </div>
                    `
                });
            } catch (error) {
                console.error('Template check failed:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Template Check Failed',
                    text: error.message
                });
            }
        },
        
        async simulateSimultaneousDownloads(siswaId, nilaiCount, hasAbsensi, namaSiswa, testType = 'docx') {
            console.log(`🚀 Starting simultaneous test for ${testType.toUpperCase()}`);
            
            const startTime = Date.now();
            const promises = [];
            const results = [];
            
            // Simulate 10 concurrent users
            for (let i = 0; i < 10; i++) {
                const promise = this.performDownloadTest(siswaId, nilaiCount, hasAbsensi, namaSiswa, testType, i + 1)
                    .then(result => {
                        results.push(result);
                        console.log(`✅ User ${i + 1} completed:`, result);
                        return result;
                    })
                    .catch(error => {
                        const errorResult = { 
                            userId: i + 1, 
                            success: false, 
                            error: error.message,
                            duration: Date.now() - startTime
                        };
                        results.push(errorResult);
                        console.error(`❌ User ${i + 1} failed:`, error);
                        return errorResult;
                    });
                
                promises.push(promise);
            }
            
            try {
                await Promise.all(promises);
                
                const endTime = Date.now();
                const totalDuration = endTime - startTime;
                
                const successCount = results.filter(r => r.success).length;
                const failCount = results.filter(r => !r.success).length;
                
                const avgDuration = results.reduce((sum, r) => sum + r.duration, 0) / results.length;
                
                const summary = {
                    totalRequests: 10,
                    successCount,
                    failCount,
                    totalDuration,
                    avgDuration: Math.round(avgDuration),
                    results
                };
                
                console.log('📊 Load Test Summary:', summary);
                this.showLoadTestResults(summary, testType);
                
            } catch (error) {
                console.error('Load test failed:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Load Test Failed',
                    text: error.message
                });
            }
        },

        // Metode 2: Sequential Testing (1 detik interval)
        async simulateSequentialDownloads(siswaId, nilaiCount, hasAbsensi, namaSiswa, testType = 'docx') {
            console.log(`🚀 Starting sequential test for ${testType.toUpperCase()} (1s interval)`);
            
            const results = [];
            const startTime = Date.now();
            
            for (let i = 0; i < 10; i++) {
                try {
                    console.log(`⏱️ Starting request ${i + 1}/10`);
                    
                    const result = await this.performDownloadTest(siswaId, nilaiCount, hasAbsensi, namaSiswa, testType, i + 1);
                    results.push(result);
                    
                    console.log(`✅ User ${i + 1} completed:`, result);
                    
                    // Wait 1 second before next request (except for last one)
                    if (i < 9) {
                        console.log(`⏳ Waiting 1 second...`);
                        await new Promise(resolve => setTimeout(resolve, 1000));
                    }
                    
                } catch (error) {
                    const errorResult = { 
                        userId: i + 1, 
                        success: false, 
                        error: error.message,
                        duration: Date.now() - startTime
                    };
                    results.push(errorResult);
                    console.error(`❌ User ${i + 1} failed:`, error);
                }
            }
            
            const endTime = Date.now();
            const totalDuration = endTime - startTime;
            
            const successCount = results.filter(r => r.success).length;
            const failCount = results.filter(r => !r.success).length;
            const avgDuration = results.reduce((sum, r) => sum + r.duration, 0) / results.length;
            
            const summary = {
                totalRequests: 10,
                successCount,
                failCount,
                totalDuration,
                avgDuration: Math.round(avgDuration),
                results
            };
            
            console.log('📊 Sequential Test Summary:', summary);
            this.showLoadTestResults(summary, testType);
        },

        // Helper method untuk perform individual test
        async performDownloadTest(siswaId, nilaiCount, hasAbsensi, namaSiswa, testType, userId) {
            const startTime = Date.now();
            
            try {
                if (testType === 'docx') {
                    // Test DOCX download
                    const response = await fetch(`/wali-kelas/rapor/generate/${siswaId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            type: this.activeTab,
                            tahun_ajaran_id: this.tahunAjaranId,
                            action: 'download'
                        })
                    });
                    
                    const duration = Date.now() - startTime;
                    
                    if (response.ok) {
                        const contentType = response.headers.get("content-type");
                        if (contentType && contentType.includes("application/json")) {
                            const data = await response.json();
                            return {
                                userId,
                                success: data.success || false,
                                duration,
                                responseSize: JSON.stringify(data).length,
                                statusCode: response.status
                            };
                        } else {
                            // Binary response (actual file)
                            const blob = await response.blob();
                            return {
                                userId,
                                success: true,
                                duration,
                                responseSize: blob.size,
                                statusCode: response.status
                            };
                        }
                    } else {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    
                } else if (testType === 'pdf') {
                    // Test PDF download
                    const url = `/wali-kelas/rapor/download-pdf/${siswaId}?type=${this.activeTab}&tahun_ajaran_id=${this.tahunAjaranId}`;
                    
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/pdf,application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const duration = Date.now() - startTime;
                    
                    if (response.ok) {
                        const blob = await response.blob();
                        return {
                            userId,
                            success: true,
                            duration,
                            responseSize: blob.size,
                            statusCode: response.status
                        };
                    } else {
                        const errorData = await response.json();
                        throw new Error(errorData.message || `HTTP ${response.status}`);
                    }
                }
                
            } catch (error) {
                const duration = Date.now() - startTime;
                return {
                    userId,
                    success: false,
                    duration,
                    error: error.message,
                    statusCode: 0
                };
            }
        },

        // Method untuk show results
        showLoadTestResults(summary, testType) {
            const successRate = (summary.successCount / summary.totalRequests * 100).toFixed(1);
            
            let statusColor = 'success';
            if (successRate < 70) statusColor = 'error';
            else if (successRate < 90) statusColor = 'warning';
            
            Swal.fire({
                icon: statusColor,
                title: `Load Test Results - ${testType.toUpperCase()}`,
                html: `
                    <div class="text-left space-y-2">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <strong>Total Requests:</strong> ${summary.totalRequests}
                            </div>
                            <div>
                                <strong>Success Rate:</strong> 
                                <span class="text-green-600">${successRate}%</span>
                            </div>
                            <div>
                                <strong>Successful:</strong> 
                                <span class="text-green-600">${summary.successCount}</span>
                            </div>
                            <div>
                                <strong>Failed:</strong> 
                                <span class="text-red-600">${summary.failCount}</span>
                            </div>
                            <div>
                                <strong>Total Duration:</strong> ${summary.totalDuration}ms
                            </div>
                            <div>
                                <strong>Avg Duration:</strong> ${summary.avgDuration}ms
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <strong>Individual Results:</strong>
                            <div class="max-h-40 overflow-y-auto mt-2 text-xs">
                                ${summary.results.map(r => `
                                    <div class="flex justify-between ${r.success ? 'text-green-600' : 'text-red-600'}">
                                        <span>User ${r.userId}:</span>
                                        <span>${r.success ? '✅' : '❌'} ${r.duration}ms</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                `,
                width: 600,
                confirmButtonText: 'Close'
            });
        }
    }));
});
</script>
@endpush

@endsection