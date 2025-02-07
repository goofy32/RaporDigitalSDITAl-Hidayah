@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div x-data="{ selectedKelas: '', mapelProgress: [] }" x-init="$store.notification.fetchNotifications(); $store.notification.startAutoRefresh()">
    <div x-data="notificationHandler">  
        <!-- Main Content Container -->
        <div class="flex flex-col lg:flex-row gap-4 mt-14">
            <!-- Statistics Grid - Takes 2/3 of the space -->
            <div class="lg:w-2/3">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <!-- Siswa Card -->
                    <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden cursor-pointer" onclick="navigateTo('{{ route('student') }}')">
                        <div class="p-4">
                            <p class="text-2xl font-bold text-green-600">{{ $totalStudents }}</p>
                            <p class="text-sm text-green-600">Siswa</p>
                        </div>
                    </div>
                    <!-- Guru Card -->
                    <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden cursor-pointer" onclick="navigateTo('{{ route('teacher') }}')">
                        <div class="p-4">
                            <p class="text-2xl font-bold text-green-600">{{ $totalTeachers }}</p>
                            <p class="text-sm text-green-600">Guru</p>
                        </div>
                    </div>
                    <!-- Mata Pelajaran Card -->
                    <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden cursor-pointer" onclick="navigateTo('{{ route('subject.index') }}')">
                        <div class="p-4">
                            <p class="text-2xl font-bold text-green-600">{{ $totalSubjects }}</p>
                            <p class="text-sm text-green-600">Mata Pelajaran</p>
                        </div>
                    </div>
                    <!-- Kelas Card -->
                    <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden cursor-pointer" onclick="navigateTo('{{ route('kelas.index') }}')">
                        <div class="p-4">
                            <p class="text-2xl font-bold text-green-600">{{ $totalClasses }}</p>
                            <p class="text-sm text-green-600">Kelas</p>
                        </div>
                    </div>
                    <!-- Ekstrakurikuler Card -->
                    <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden cursor-pointer" onclick="navigateTo('{{ route('ekstra.index') }}')">
                        <div class="p-4">
                            <p class="text-2xl font-bold text-green-600">{{ $totalExtracurriculars }}</p>
                            <p class="text-sm text-green-600">Ekstrakurikuler</p>
                        </div>
                    </div>
                    <!-- Progres Rapor Card -->
                    <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden">
                        <div class="p-4">
                            <p class="text-2xl font-bold text-green-600">{{ number_format($overallProgress, 2) }}%</p>
                            <p class="text-sm text-green-600">Progres Rapor</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Information Section - Takes 1/3 of the space -->
            <div class="lg:w-1/3">
                <div class="flex items-center justify-between mb-3">
                    <div class="bg-green-600 text-white px-3 py-1.5 rounded-lg inline-block">
                        <span class="flex items-center text-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            Informasi
                        </span>
                    </div>
                    <button type="button" 
                            @click="showModal = true"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </button>
                </div>

                <!-- Information Items -->
                <div class="h-[150px] overflow-y-auto">
                    <div class="relative pl-6 border-l-2 border-gray-200">
                        <template x-for="item in $store.notification.items" :key="item.id">
                            <div class="mb-4 relative h-[60px]">
                                <div class="absolute -left-8 w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div class="bg-white rounded-lg border shadow-sm p-3">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="text-sm font-medium" x-text="item.title"></h3>
                                            <p class="text-xs text-gray-600 line-clamp-2" x-text="item.content"></p>
                                        </div>
                                        <button type="button" 
                                                @click="$store.notification.deleteNotification(item.id)"
                                                class="text-red-500 hover:text-red-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dropdown Pilih Kelas -->
        <div class="mt-8">
            <label for="kelas" class="block text-sm font-medium text-gray-700">Pilih Kelas</label>
            <select id="kelas" 
                x-model="selectedKelas" 
                @change="fetchKelasProgress()"
                class="block w-full p-2 mt-1 rounded-lg border border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                <option value="">Pilih kelas...</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <!-- Chart Keseluruhan -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Progress Input Nilai Keseluruhan</h3>
                <div class="flex flex-col items-center">
                    <div class="w-64 h-64 relative">
                        <canvas id="overallPieChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Chart Per Kelas -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Progress Input Nilai <span x-text="selectedKelas ? 'Kelas ' + selectedKelas : 'Per Kelas'"></span></h3>
                <div class="flex flex-col items-center">
                    <div class="w-64 h-64 relative">
                        <canvas id="classProgressChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div x-show="showModal" 
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <!-- Modal backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" 
                 x-show="showModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showModal = false"></div>

            <!-- Modal content -->
            <div class="relative flex min-h-screen items-center justify-center p-4">
                <div class="relative w-full max-w-md rounded-lg bg-white shadow-xl"
                     x-show="showModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <!-- Modal Header -->
                    <div class="flex items-center justify-between p-4 border-b">
                        <h3 class="text-xl font-semibold">Tambah Informasi</h3>
                        <button type="button" 
                                @click="showModal = false"
                                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex items-center justify-center">
                            <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-4">
                        <form @submit.prevent="submitNotification">
                            <div class="mb-4">
                                <label class="block mb-2 text-sm font-medium text-gray-900">Judul informasi</label>
                                <input type="text" 
                                    x-model="notificationForm.title"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" 
                                    placeholder="Masukkan judul informasi" 
                                    required>
                            </div>

                            <div class="mb-4">
                                <label class="block mb-2 text-sm font-medium text-gray-900">Informasi untuk</label>
                                <select x-model="notificationForm.target"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" 
                                        required>
                                    <option value="">-- Pilih --</option>
                                    <option value="all">Semua</option>
                                    <option value="guru">Semua Guru</option>
                                    <option value="wali_kelas">Semua Wali Kelas</option>
                                    <option value="specific">Guru Tertentu</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block mb-2 text-sm font-medium text-gray-900">Isi</label>
                                <textarea x-model="notificationForm.content"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" 
                                        rows="4" 
                                        placeholder="Masukkan isi informasi" 
                                        required></textarea>
                            </div>

                            <!-- Specific teachers container -->
                            <div x-show="notificationForm.target === 'specific'" class="mb-4">
                                <label class="block mb-2 text-sm font-medium text-gray-900">Pilih Guru</label>
                                <div class="max-h-40 overflow-y-auto">
                                    @foreach($guru as $g)
                                    <div class="flex items-center mb-2">
                                        <input type="checkbox" 
                                            value="{{ $g->id }}" 
                                            x-model="notificationForm.specific_users"
                                            class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                            <label class="ml-2 text-sm text-gray-900">
                                            {{ $g->nama }} 
                                            @if($g->jabatan == 'wali_kelas')
                                                (Wali Kelas {{ $g->kelasPengajar->nama_kelas ?? '' }})
                                            @else
                                                (Guru {{ implode(', ', $g->mataPelajarans->pluck('nama_pelajaran')->toArray()) }})
                                            @endif
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Success/Error Messages -->
                            <div x-show="successMessage" x-text="successMessage" class="mb-4 text-green-600"></div>
                            <div x-show="errorMessage" x-text="errorMessage" class="mb-4 text-red-600"></div>

                            <button type="submit" 
                                    class="w-full text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                Simpan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let overallChart, classChart;
let kelasProgress = 0;
const ADMIN_DASHBOARD_KEY = 'adminDashboardLoaded';

function handleInitialLoad() {
    if (window.location.pathname.includes('/admin/dashboard')) {
        const isLoaded = sessionStorage.getItem(ADMIN_DASHBOARD_KEY);
        if (!isLoaded) {
            sessionStorage.setItem(ADMIN_DASHBOARD_KEY, 'true');
            window.location.reload();
        } else {
            initCharts();
            fetchKelasProgress();
        }
    } else {
        sessionStorage.removeItem(ADMIN_DASHBOARD_KEY);
    }
}

document.getElementById('target-select').addEventListener('change', function() {
    const specificContainer = document.getElementById('specific-teachers-container');
    if (this.value === 'specific') {
        specificContainer.classList.remove('hidden');
    } else {
        specificContainer.classList.add('hidden');
    }
});

function destroyCharts() {
    if (overallChart) {
        overallChart.destroy();
        overallChart = null;
    }
    if (classChart) {
        classChart.destroy();
        classChart = null;
    }
}

function initCharts() {
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                enabled: false
            }
        }
    };

    const overallCtx = document.getElementById('overallPieChart')?.getContext('2d');
    if (overallCtx) {
        overallChart = new Chart(overallCtx, {
            type: 'doughnut',
            data: {
                labels: ['Selesai', 'Belum'],
                datasets: [{
                    data: [
                        Math.min(100, Math.max(0, {{ $overallProgress }})), 
                        Math.min(100, Math.max(0, 100 - {{ $overallProgress }}))
                    ],
                    backgroundColor: ['rgb(34, 197, 94)', 'rgb(229, 231, 235)'],
                    borderWidth: 0
                }]
            },
            options: {
                ...defaultOptions,
                cutout: '60%',
            },
            plugins: [{
                id: 'centerText',
                afterDraw: function(chart) {
                    const width = chart.width;
                    const height = chart.height;
                    const ctx = chart.ctx;
                    
                    ctx.restore();
                    const fontSize = (height / 114).toFixed(2);
                    ctx.font = fontSize + 'em sans-serif';
                    ctx.textBaseline = 'middle';
                    
                    const text = Math.round({{ $overallProgress }}) + '%';
                    const textX = Math.round((width - ctx.measureText(text).width) / 2);
                    const textY = height / 2;

                    ctx.fillStyle = '#1F2937';
                    ctx.fillText(text, textX, textY);
                    ctx.save();
                }
            }]
        });
    }

    initClassChart();
}

function initClassChart() {
    const classCtx = document.getElementById('classProgressChart')?.getContext('2d');
    if (classCtx) {
        classChart = new Chart(classCtx, {
            type: 'doughnut',
            data: {
                labels: ['Selesai', 'Belum'],
                datasets: [{
                    data: [0, 100],
                    backgroundColor: ['rgb(34, 197, 94)', 'rgb(229, 231, 235)'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { enabled: false }
                },
                cutout: '60%',
            },
            plugins: [{
                id: 'centerText',
                afterDraw: function(chart) {
                    const width = chart.width;
                    const height = chart.height;
                    const ctx = chart.ctx;
                    
                    ctx.restore();
                    const fontSize = (height / 114).toFixed(2);
                    ctx.font = fontSize + 'em sans-serif';
                    ctx.textBaseline = 'middle';
                    
                    const text = Math.round(kelasProgress) + '%';
                    const textX = Math.round((width - ctx.measureText(text).width) / 2);
                    const textY = height / 2;

                    ctx.fillStyle = '#1F2937';
                    ctx.fillText(text, textX, textY);
                    ctx.save();
                }
            }]
        });
    }
}

function updateClassChart(progress) {
    // Pastikan progress adalah angka valid
    kelasProgress = !isNaN(progress) ? Math.min(100, Math.max(0, progress)) : 0;
    
    if (classChart) {
        classChart.data.datasets[0].data = [kelasProgress, 100 - kelasProgress];
        classChart.update();

        // Update teks di tengah chart
        const centerText = document.querySelector('.class-progress-text');
        if (centerText) {
            centerText.textContent = `${Math.round(kelasProgress)}%`;
        }
    }
}

function fetchKelasProgress() {
    const selectedKelas = document.getElementById('kelas')?.value;
    if (selectedKelas) {
        fetch(`/admin/kelas-progress/${selectedKelas}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Progress data:', data); // Untuk debugging
            if (data.success && !isNaN(data.progress)) {
                updateClassChart(data.progress);
            } else {
                console.error('Invalid progress data:', data);
                updateClassChart(0);
            }
        })
        .catch(error => {
            console.error('Error fetching progress:', error);
            updateClassChart(0);
        });
    } else {
        updateClassChart(0);
    }
}

// Event Handlers
document.addEventListener('alpine:init', () => {
    Alpine.data('dashboard', () => ({
        selectedKelas: '',
        mapelProgress: [],
        
        init() {
            this.$watch('selectedKelas', value => {
                if (value) fetchKelasProgress();
            });
        }
    }));
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    const isLoaded = sessionStorage.getItem(ADMIN_DASHBOARD_KEY);
    if (!isLoaded && window.location.pathname.includes('/admin/dashboard')) {
        sessionStorage.setItem(ADMIN_DASHBOARD_KEY, 'true');
        window.location.reload();
    } else {
        initCharts();
        updateDashboardData();
    }
});

// Handle Turbo navigation
document.addEventListener('turbo:load', () => {
    if (!window.location.pathname.includes('/admin/dashboard')) {
        sessionStorage.removeItem(ADMIN_DASHBOARD_KEY);
    }
    destroyCharts();
    setTimeout(() => {
        initCharts();
        updateDashboardData();
    }, 100);
});

// Clean up
document.addEventListener('turbo:before-cache', destroyCharts);

// Event listener for dropdown changes
document.addEventListener('change', (e) => {
    if (e.target.id === 'kelas') {
        fetchKelasProgress();
    }
});


// Cleanup
document.addEventListener('turbo:before-cache', () => {
    destroyCharts();
});

// Cleanup
document.addEventListener('turbo:before-visit', () => {
    if (!window.location.pathname.includes('/admin/dashboard')) {
        sessionStorage.removeItem(ADMIN_DASHBOARD_KEY);
    }
});

// Reinitialize pada navigasi
document.addEventListener('turbo:render', () => {
    if (window.location.pathname.includes('/admin/dashboard')) {
        destroyCharts();
        setTimeout(() => {
            initCharts();
            fetchKelasProgress();
        }, 100);
    }
});
function initModal() {
    const modal = document.getElementById('addInfoModal');
    const openButtons = document.querySelectorAll('[data-modal-target="addInfoModal"]');
    const closeButtons = document.querySelectorAll('[data-modal-hide="addInfoModal"]');
    const modalForm = modal?.querySelector('form');

    // Open modal
    openButtons.forEach(button => {
        button.addEventListener('click', () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });

    // Close modal
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });
    });

    // Close on outside click
    modal?.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });

        // Handle form submission
        modalForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const submitButton = modalForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = `
            <svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Menyimpan...
        `;

        const formData = new FormData(modalForm);
        const data = {
            title: formData.get('title'),
            target: formData.get('target'),
            content: formData.get('content')
        };

        if (formData.get('target') === 'specific') {
            data.specific_users = Array.from(formData.getAll('specific_users[]'))
                .map(id => parseInt(id, 10));
        }

        try {
            const response = await fetch('/admin/information', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                modalForm.reset();
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                updateInformationSection();
                showNotification('Informasi berhasil ditambahkan', 'success');
            } else {
                showNotification('Gagal menambahkan informasi', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan', 'error');
        } finally {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Simpan';
        }
    });
}

// Delete information handler
function deleteInformation(id) {
    if (confirm('Apakah Anda yakin ingin menghapus informasi ini?')) {
        fetch(`/admin/information/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh information section
                updateInformationSection();
                showNotification('Informasi berhasil dihapus', 'success');
            } else {
                showNotification('Gagal menghapus informasi', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan', 'error');
        });
    }
}

// Update information section
function updateInformationSection() {
    fetch('/admin/information/list', {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.querySelector('.information-items');
        if (container && data.items) {
            container.innerHTML = data.items.map(item => `
                <div class="mb-4 relative">
                    <div class="absolute -left-8 w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="bg-white rounded-lg border shadow-sm p-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-sm font-medium">${item.title}</h3>
                                <p class="text-xs text-gray-600">${item.content}</p>
                            </div>
                            <button class="text-red-500 hover:text-red-700" onclick="deleteInformation(${item.id})">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }
    });
}

// Notification handler
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white ${
        type === 'success' ? 'bg-green-600' : 'bg-red-600'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Initialize modal when page loads
document.addEventListener('DOMContentLoaded', initModal);
document.addEventListener('turbo:load', initModal);

</script>
@endpush
@endsection