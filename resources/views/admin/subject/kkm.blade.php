@extends('layouts.app')

@section('title', 'Kriteria Ketuntasan Minimal')

@section('content')
<div>
    <div class="p-4 bg-white mt-14">
        <!-- Header dengan tombol seperti screenshot -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-green-700">Kriteria Ketuntasan Minimal</h2>
            <div class="flex gap-2">
                <a href="{{ route('subject.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-medium">
                    Kembali
                </a>
                <button @click="saveKkm" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                    Simpan
                </button>
            </div>
        </div>

        <div x-data="kkmForm">
            <!-- Pengaturan Notifikasi KKM -->
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg mb-6">
                <h4 class="text-lg font-medium text-green-800 mb-2">Pengaturan Notifikasi KKM</h4>
                <div class="mb-4">
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="notification_complete_scores_only" 
                            x-model="kkmNotificationSettings.completeScoresOnly" 
                            class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500"
                        >
                        <label for="notification_complete_scores_only" class="ml-2 text-sm font-medium text-gray-900">
                            Hanya tampilkan notifikasi KKM rendah untuk nilai yang sudah lengkap
                        </label>
                    </div>
                    <p class="mt-2 text-xs text-gray-600">
                        Jika diaktifkan, notifikasi nilai dibawah KKM hanya akan muncul ketika semua komponen nilai (TP, LM, Tes, Non-Tes) sudah diisi lengkap.
                    </p>
                </div>
                <button 
                    @click="saveKkmNotificationSettings" 
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium"
                >
                    Simpan Pengaturan Notifikasi
                </button>
            </div>

            <!-- Form Input KKM - Layout seperti screenshot -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <!-- Pilih Kelas -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900">Pilih kelas</label>
                    <select x-model="selectedKelasId" 
                            @change="loadMataPelajaranByKelas()"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                        <option value="">Pilih kelas</option>
                        <template x-for="kelas in kelasData" :key="kelas.id">
                            <option :value="kelas.id" x-text="'Kelas ' + kelas.nomor_kelas + ' - ' + kelas.nama_kelas"></option>
                        </template>
                    </select>
                </div>

                <!-- Pilih Mata Pelajaran -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900">Pilih Mata Pelajaran</label>
                    <select x-model="kkmData.mata_pelajaran_id" 
                            @change="handleMapelChange()"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                        <option value="">Pilih mata pelajaran</option>
                        <template x-for="mapel in filteredMataPelajaran" :key="mapel.id">
                            <option :value="mapel.id" x-text="mapel.nama_pelajaran"></option>
                        </template>
                    </select>
                </div>

                <!-- Nilai KKM -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900">Nilai KKM</label>
                    <input type="number" x-model="kkmData.nilai" min="0" max="100" 
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                </div>
            </div>

            <!-- Tombol Tambah ke Tabel -->
            <div class="mb-6">
                <button @click="addToTable" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                    Tambah ke Tabel
                </button>
            </div>

            <!-- Daftar KKM dengan filter kelas seperti screenshot -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Daftar KKM</h3>
                    <div class="flex items-center gap-4">
                        <select x-model="filterKelasId" 
                                @change="filterKkmList()"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 p-2.5">
                            <option value="">Pilih kelas</option>
                            <template x-for="kelas in kelasData" :key="kelas.id">
                                <option :value="kelas.id" x-text="'Kelas ' + kelas.nomor_kelas + ' - ' + kelas.nama_kelas"></option>
                            </template>
                        </select>
                    </div>
                </div>
                
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 border-r">Kelas</th>
                                <th scope="col" class="px-6 py-3 border-r">Mata Pelajaran</th>
                                <th scope="col" class="px-6 py-3 border-r">Nilai KKM</th>
                                <th scope="col" class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(kkm, index) in filteredKkmList" :key="kkm.id">
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 border-r" x-text="kkm.mata_pelajaran && kkm.mata_pelajaran.kelas ? 'Kelas ' + kkm.mata_pelajaran.kelas.nomor_kelas + ' - ' + kkm.mata_pelajaran.kelas.nama_kelas : '-'"></td>
                                    <td class="px-6 py-4 border-r" x-text="kkm.mata_pelajaran ? kkm.mata_pelajaran.nama_pelajaran : '-'"></td>
                                    <td class="px-6 py-4 border-r text-center" x-text="kkm.nilai"></td>
                                    <td class="px-6 py-4">
                                        <button @click="deleteKkm(kkm.id)" class="text-red-600 hover:underline">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            
                            <tr x-show="filteredKkmList.length === 0">
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                    <span x-text="filterKelasId ? 'Belum ada data KKM untuk kelas ini' : 'Belum ada data KKM'"></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pengaturan KKM Massal -->
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 class="text-lg font-medium text-blue-800 mb-2">Pengaturan KKM Massal</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Nilai KKM untuk Semua Mata Pelajaran
                        </label>
                        <input type="number" x-model="globalKkmData.nilai" 
                               min="0" max="100" 
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                               placeholder="Contoh: 70">
                    </div>
                    
                    <div class="flex items-end">
                        <div class="flex items-center h-10">
                            <input type="checkbox" id="overwrite" x-model="globalKkmData.overwriteExisting" 
                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            <label for="overwrite" class="ml-2 text-sm text-gray-700">
                                Timpa nilai KKM yang sudah ada
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex items-end">
                        <button @click="applyGlobalKkm" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                            Terapkan KKM Massal
                        </button>
                    </div>
                </div>
                
                <p class="text-xs text-gray-500">
                    Nilai ini akan diterapkan ke semua mata pelajaran. Jika opsi "Timpa nilai KKM yang sudah ada" dicentang, maka nilai KKM yang sudah diatur sebelumnya akan diperbarui.
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('kkmForm', () => ({
        kelasData: [],
        kkmList: [],
        filteredKkmList: [],
        filteredMataPelajaran: [],
        selectedKelasId: '',
        filterKelasId: '',
        kkmData: {
            mata_pelajaran_id: '',
            nilai: 70
        },
        globalKkmData: {
            nilai: 70,
            overwriteExisting: false
        },
        kkmNotificationSettings: {
            completeScoresOnly: false
        },
        
        init() {
            this.fetchKelasData();
            this.fetchKkmList();
            this.initKkmNotificationSettings();
        },
        
        async fetchKelasData() {
            try {
                const response = await fetch('/admin/kelas/data');
                const data = await response.json();
                this.kelasData = data.kelas;
            } catch (error) {
                console.error('Error fetching kelas data:', error);
            }
        },
        
        async fetchKkmList() {
            try {
                const response = await fetch('/admin/kkm/list');
                const data = await response.json();
                this.kkmList = data.kkms;
                this.filteredKkmList = this.kkmList; // Initialize filtered list
            } catch (error) {
                console.error('Error fetching KKM list:', error);
            }
        },
        
        loadMataPelajaranByKelas() {
            if (!this.selectedKelasId) {
                this.filteredMataPelajaran = [];
                return;
            }
            
            const selectedKelas = this.kelasData.find(kelas => kelas.id == this.selectedKelasId);
            this.filteredMataPelajaran = selectedKelas ? selectedKelas.mata_pelajarans : [];
            
            // Reset mata pelajaran selection
            this.kkmData.mata_pelajaran_id = '';
            this.kkmData.nilai = 70;
        },
        
        filterKkmList() {
            if (!this.filterKelasId) {
                this.filteredKkmList = this.kkmList;
                return;
            }
            
            this.filteredKkmList = this.kkmList.filter(kkm => 
                kkm.mata_pelajaran && 
                kkm.mata_pelajaran.kelas && 
                kkm.mata_pelajaran.kelas.id == this.filterKelasId
            );
        },
        
        async initKkmNotificationSettings() {
            try {
                const response = await fetch('/admin/kkm/notification-settings');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                if (data.success) {
                    this.kkmNotificationSettings = data.settings;
                }
            } catch (error) {
                console.error('Error fetching KKM notification settings:', error);
            }
        },
        
        async handleMapelChange() {
            const selectedMapelId = this.kkmData.mata_pelajaran_id;
            if (!selectedMapelId) return;
            
            const existingKkm = this.kkmList.find(kkm => 
                kkm.mata_pelajaran_id === parseInt(selectedMapelId)
            );
            
            if (existingKkm) {
                this.kkmData.nilai = existingKkm.nilai;
            } else {
                this.kkmData.nilai = 70;
            }
        },
        
        async addToTable() {
            if (!this.kkmData.mata_pelajaran_id) {
                this.showAlert('error', 'Pilih mata pelajaran terlebih dahulu');
                return;
            }
            
            await this.saveKkm();
        },
        
        async deleteKkm(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus KKM ini?')) return;
            
            try {
                const response = await fetch(`/admin/kkm/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.fetchKkmList();
                    this.showAlert('success', 'KKM berhasil dihapus');
                } else {
                    this.showAlert('error', data.message || 'Gagal menghapus KKM');
                }
            } catch (error) {
                console.error('Error deleting KKM:', error);
                this.showAlert('error', 'Terjadi kesalahan saat menghapus KKM');
            }
        },
        
        async saveKkm() {
            if (!this.kkmData.mata_pelajaran_id) {
                this.showAlert('error', 'Pilih mata pelajaran terlebih dahulu');
                return;
            }
            
            try {
                const response = await fetch('/admin/kkm', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.kkmData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.fetchKkmList();
                    this.resetForms();
                    this.showAlert('success', 'KKM berhasil disimpan');
                } else {
                    this.showAlert('error', data.message || 'Gagal menyimpan KKM');
                }
            } catch (error) {
                console.error('Error saving KKM:', error);
                this.showAlert('error', 'Terjadi kesalahan saat menyimpan KKM');
            }
        },
        
        async applyGlobalKkm() {
            try {
                let confirmMessage = `Apakah Anda yakin ingin menerapkan nilai KKM ${this.globalKkmData.nilai} ke semua mata pelajaran?`;
                
                if (this.globalKkmData.overwriteExisting) {
                    confirmMessage += '<br/><br/><strong class="text-red-600">Perhatian!</strong> Tindakan ini akan menimpa nilai KKM yang sudah ada sebelumnya.';
                } else {
                    confirmMessage += '<br/><br/>Hanya mata pelajaran yang belum memiliki KKM yang akan diperbarui.';
                }
                
                const isConfirmed = await Swal.fire({
                    title: 'Konfirmasi Pengaturan KKM Massal',
                    html: confirmMessage,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Terapkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    return result.isConfirmed;
                });
                
                if (!isConfirmed) {
                    return;
                }
                
                Swal.fire({
                    title: 'Menerapkan KKM Massal...',
                    text: 'Mohon tunggu...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                const response = await fetch('/admin/kkm/global', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.globalKkmData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.fetchKkmList();
                    this.showAlert('success', `KKM massal berhasil diterapkan. ${data.count} mata pelajaran diperbarui.`);
                } else {
                    this.showAlert('error', data.message || 'Gagal menerapkan KKM massal');
                }
            } catch (error) {
                console.error('Error applying global KKM:', error);
                this.showAlert('error', 'Terjadi kesalahan saat menerapkan KKM massal');
            }
        },

        async saveKkmNotificationSettings() {
            try {
                Swal.fire({
                    title: 'Menyimpan pengaturan...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                const response = await fetch('/admin/kkm/notification-settings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.kkmNotificationSettings)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showAlert('success', 'Pengaturan notifikasi KKM berhasil disimpan');
                } else {
                    this.showAlert('error', data.message || 'Gagal menyimpan pengaturan notifikasi');
                }
            } catch (error) {
                console.error('Error saving KKM notification settings:', error);
                this.showAlert('error', 'Terjadi kesalahan saat menyimpan pengaturan notifikasi');
            }
        },
        
        resetForms() {
            this.kkmData = {
                mata_pelajaran_id: '',
                nilai: 70
            };
            this.selectedKelasId = '';
            this.filteredMataPelajaran = [];
        },

        showAlert(type, message) {
            if (window.Swal) {
                Swal.fire({
                    icon: type,
                    title: type === 'success' ? 'Berhasil!' : 'Perhatian!',
                    text: message,
                    timer: 3000,
                    showConfirmButton: false
                });
            } else {
                alert(message);
            }
        }
    }));
});
</script>
@endpush
@endsection