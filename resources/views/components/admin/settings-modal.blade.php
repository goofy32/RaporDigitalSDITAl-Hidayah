<!-- resources/views/components/admin/settings-modal.blade.php -->
<div x-data="adminSettings" x-cloak class="fixed z-50 inset-0 overflow-y-auto flex items-center justify-center" 
     x-show="isOpen" 
     @open-settings.window="open()"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50" @click="close"></div>
    
    <!-- Modal -->
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 z-10"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95">
        
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b">
            <h2 class="text-xl font-semibold text-gray-700">Pengaturan Rapor</h2>
            <button @click="close" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Content -->
        <div class="p-4">
            <!-- Tab Controls -->
            <div class="border-b mb-4">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                    <li class="mr-2" @click="activeTab = 'kkm'">
                        <button :class="{'text-blue-600 border-blue-600 border-b-2 rounded-t-lg active': activeTab === 'kkm', 'text-gray-500 hover:text-gray-600 border-b-2 border-transparent hover:border-gray-300': activeTab !== 'kkm'}" 
                                class="inline-block p-4">
                            KKM (Kriteria Ketuntasan Minimal)
                        </button>
                    </li>
                    <li class="mr-2" @click="activeTab = 'bobot'">
                        <button :class="{'text-blue-600 border-blue-600 border-b-2 rounded-t-lg active': activeTab === 'bobot', 'text-gray-500 hover:text-gray-600 border-b-2 border-transparent hover:border-gray-300': activeTab !== 'bobot'}" 
                                class="inline-block p-4">
                            Bobot Nilai
                        </button>
                    </li>
                </ul>
            </div>
            
            <!-- KKM Tab -->
            <div x-show="activeTab === 'kkm'" class="space-y-4">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                KKM adalah nilai minimum yang harus dicapai siswa untuk dinyatakan tuntas dalam suatu mata pelajaran.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="mata_pelajaran_id" class="block mb-2 text-sm font-medium text-gray-900">Mata Pelajaran</label>
                    <select id="mata_pelajaran_id" x-model="kkmData.mata_pelajaran_id" 
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option value="">Pilih Mata Pelajaran</option>
                        <template x-for="kelas in kelasData" :key="kelas.id">
                            <optgroup :label="'Kelas ' + kelas.nomor_kelas + ' - ' + kelas.nama_kelas">
                                <template x-for="mapel in kelas.mata_pelajarans" :key="mapel.id">
                                    <option :value="mapel.id" x-text="mapel.nama_pelajaran"></option>
                                </template>
                            </optgroup>
                        </template>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="nilai_kkm" class="block mb-2 text-sm font-medium text-gray-900">Nilai KKM</label>
                    <input type="number" id="nilai_kkm" x-model="kkmData.nilai" min="0" max="100" 
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <p class="mt-1 text-sm text-gray-500">Nilai dari 0-100</p>
                </div>
                
                <!-- KKM List Table -->
                <div class="mt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Daftar KKM</h3>
                    <div class="overflow-x-auto relative">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Kelas</th>
                                    <th scope="col" class="px-6 py-3">Mata Pelajaran</th>
                                    <th scope="col" class="px-6 py-3">KKM</th>
                                    <th scope="col" class="px-6 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="kkm in kkmList" :key="kkm.id">
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4" x-text="kkm.mata_pelajaran && kkm.mata_pelajaran.kelas ? 'Kelas ' + kkm.mata_pelajaran.kelas.nomor_kelas + ' - ' + kkm.mata_pelajaran.kelas.nama_kelas : '-'"></td>
                                        <td class="px-6 py-4" x-text="kkm.mata_pelajaran ? kkm.mata_pelajaran.nama_pelajaran : '-'"></td>
                                        <td class="px-6 py-4" x-text="kkm.nilai"></td>
                                        <td class="px-6 py-4">
                                            <button @click="editKkm(kkm)" class="text-blue-600 hover:underline mr-2">
                                                Edit
                                            </button>
                                            <button @click="deleteKkm(kkm.id)" class="text-red-600 hover:underline" onclick="return confirm('Yakin ingin menghapus KKM ini?')">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="kkmList.length === 0">
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                        Belum ada data KKM
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="mt-4 flex justify-end">
                    <button @click="saveKkm" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Simpan KKM
                    </button>
                </div>
            </div>
            
            <!-- Bobot Nilai Tab -->
            <div x-show="activeTab === 'bobot'" class="space-y-4">
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Bobot nilai menentukan persentase masing-masing komponen dalam perhitungan nilai akhir rapor.
                                Total bobot harus 100% (1.0).
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900">Bobot Sumatif Tujuan Pembelajaran (S.TP)</label>
                    <div class="flex items-center">
                        <input type="number" x-model="bobotData.bobot_tp" step="0.01" min="0" max="1" 
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <span class="ml-2 text-gray-700">x 100% = <span x-text="Math.round(bobotData.bobot_tp * 100) + '%'"></span></span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900">Bobot Sumatif Lingkup Materi (S.LM)</label>
                    <div class="flex items-center">
                        <input type="number" x-model="bobotData.bobot_lm" step="0.01" min="0" max="1" 
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <span class="ml-2 text-gray-700">x 100% = <span x-text="Math.round(bobotData.bobot_lm * 100) + '%'"></span></span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900">Bobot Sumatif Akhir Semester (S.AS)</label>
                    <div class="flex items-center">
                        <input type="number" x-model="bobotData.bobot_as" step="0.01" min="0" max="1" 
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <span class="ml-2 text-gray-700">x 100% = <span x-text="Math.round(bobotData.bobot_as * 100) + '%'"></span></span>
                    </div>
                </div>
                
                <div class="mt-2 mb-4">
                    <div class="flex items-center">
                        <span class="font-medium text-gray-700">Total Bobot: </span>
                        <span class="ml-2" :class="isTotalValid ? 'text-green-600' : 'text-red-600'" 
                              x-text="Math.round((parseFloat(bobotData.bobot_tp) + parseFloat(bobotData.bobot_lm) + parseFloat(bobotData.bobot_as)) * 100) + '%'"></span>
                    </div>
                    <p x-show="!isTotalValid" class="mt-1 text-sm text-red-600">
                        Total bobot harus 100%. Mohon sesuaikan nilai di atas.
                    </p>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4">
                    <h4 class="text-md font-medium text-gray-900 mb-2">Rumus Perhitungan Nilai Akhir:</h4>
                    <p class="text-sm text-gray-700">
                        <span class="font-medium">NA RAPOR</span> = 
                        (<span x-text="bobotData.bobot_tp"></span> * S.TP + 
                        <span x-text="bobotData.bobot_lm"></span> * S.LM + 
                        <span x-text="bobotData.bobot_as"></span> * S.AS)
                    </p>
                </div>
                
                <div class="mt-4 flex justify-end">
                    <button @click="saveBobot" :disabled="!isTotalValid" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                        Simpan Bobot Nilai
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('adminSettings', () => ({
        isOpen: false,
        activeTab: 'kkm',
        kelasData: [],
        kkmList: [],
        kkmData: {
            mata_pelajaran_id: '',
            nilai: 70
        },
        bobotData: {
            bobot_tp: 0.25,
            bobot_lm: 0.25,
            bobot_as: 0.50
        },
        
        init() {
            this.fetchKelasData();
            this.fetchKkmList();
            this.fetchBobotData();
        },
        
        get isTotalValid() {
            const total = parseFloat(this.bobotData.bobot_tp) + 
                          parseFloat(this.bobotData.bobot_lm) + 
                          parseFloat(this.bobotData.bobot_as);
            return Math.abs(total - 1) < 0.01; // Allow small floating point errors
        },
        
        open() {
            this.isOpen = true;
            this.fetchKelasData();
            this.fetchKkmList();
            this.fetchBobotData();
        },
        
        close() {
            this.isOpen = false;
            this.resetForms();
        },
        
        resetForms() {
            this.kkmData = {
                mata_pelajaran_id: '',
                nilai: 70
            };
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
            } catch (error) {
                console.error('Error fetching KKM list:', error);
            }
        },
        
        async fetchBobotData() {
            try {
                const response = await fetch('/admin/bobot-nilai/data');
                const data = await response.json();
                this.bobotData = {
                    bobot_tp: data.bobot_tp,
                    bobot_lm: data.bobot_lm,
                    bobot_as: data.bobot_as
                };
            } catch (error) {
                console.error('Error fetching bobot data:', error);
            }
        },
        
        editKkm(kkm) {
            this.kkmData = {
                mata_pelajaran_id: kkm.mata_pelajaran_id,
                nilai: kkm.nilai
            };
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
        
        async saveBobot() {
            if (!this.isTotalValid) {
                this.showAlert('error', 'Total bobot harus 100%');
                return;
            }
            
            try {
                const response = await fetch('/admin/bobot-nilai', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.bobotData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showAlert('success', 'Bobot nilai berhasil disimpan');
                } else {
                    this.showAlert('error', data.message || 'Gagal menyimpan bobot nilai');
                }
            } catch (error) {
                console.error('Error saving bobot nilai:', error);
                this.showAlert('error', 'Terjadi kesalahan saat menyimpan bobot nilai');
            }
        },

        showAlert(type, message) {
            // Use SweetAlert2 if available
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