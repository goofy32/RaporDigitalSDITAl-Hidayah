@extends('layouts.wali_kelas.app')

@section('title', 'Manajemen Rapor')

@section('content')
<style>
    [x-cloak] { display: none !important; }
</style>

<div x-data="raporManager" class="p-4 bg-white mt-14">
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
                            :class="{'border-blue-500 text-blue-600': activeTab === 'UTS',
                                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'UTS',
                                    'cursor-not-allowed opacity-70': !templateUTSActive}"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                            type="button">
                        Rapor UTS
                        <span x-show="!templateUTSActive" x-cloak class="ml-1 text-xs text-red-500">(Nonaktif)</span>
                    </button>
                    <button @click="setActiveTab('UAS')"
                            :class="{'border-blue-500 text-blue-600': activeTab === 'UAS',
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
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', function() {
    Alpine.data('raporManager', () => ({
        activeTab: 'UTS',
        loading: false,
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
                        type: this.activeTab,
                        tahun_ajaran_id: this.tahunAjaranId
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

                // PERBAIKAN: Clone response untuk digunakan dua kali
                const responseClone = response.clone();
                
                // Cek tipe respons untuk mencegah error "body stream already read"
                const contentType = response.headers.get("content-type");
                
                if (contentType && contentType.includes("application/json")) {
                    // Jika respons adalah JSON, proses sebagai JSON
                    const data = await response.json();
                    // Proses data JSON jika perlu
                    
                    // Tampilkan notifikasi sukses
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Rapor berhasil diproses'
                    });
                } else {
                    // Jika respons adalah file blob, proses sebagai file download
                    const blob = await responseClone.blob();
                    await this.downloadFile(blob, `rapor_${this.activeTab.toLowerCase()}_${siswaId}.docx`);
                    
                    // Tampilkan notifikasi sukses
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Rapor berhasil digenerate dan diunduh'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                
                // Tampilkan alert yang lebih informatif dengan SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Generate Rapor',
                    html: `
                        <p>${error.message}</p>
                        <p class="mt-2 text-sm text-red-600">Pastikan:</p>
                        <ul class="text-left mt-2 text-sm list-disc pl-5">
                            <li>Data Nilai sudah lengkap untuk tahun ajaran yang dipilih</li>
                            <li>Data Absensi sudah diinput</li>
                            <li>Template rapor untuk tahun ajaran ini tersedia dan aktif</li>
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
                        tahun_ajaran_id: this.tahunAjaranId // Pastikan tahun ajaran disertakan
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
    }));
});
</script>
@endpush
@endsection