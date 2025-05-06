@extends('layouts.pengajar.app')

@section('title', 'Dashboard Pengajar')

@section('content')
<div x-data="dashboard" x-init="initDashboard()">
    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-14">
        <!-- Left Section - Stats (col-span-2) -->
        <div class="lg:col-span-2">
            <!-- Top Row - 2 Cards -->
            <div class="grid grid-cols-2 gap-4 mb-4">
                <!-- Kelas Card -->
                <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden cursor-pointer hover:bg-gray-50">
                    <div class="p-4">
                        <p class="text-2xl font-bold text-green-600">{{ $kelasCount }}</p>
                        <p class="text-sm text-green-600">Kelas</p>
                    </div>
                </div>
                
                <!-- Siswa Card -->
                <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden cursor-pointer hover:bg-gray-50">
                    <div class="p-4">
                        <p class="text-2xl font-bold text-green-600">{{ $siswaCount }}</p>
                        <p class="text-sm text-green-600">Siswa</p>
                    </div>
                </div>
            </div>

            <!-- Bottom Row - 1 Card -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Mata Pelajaran Card -->
                <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden cursor-pointer hover:bg-gray-50">
                    <div class="p-4">
                        <p class="text-2xl font-bold text-green-600">{{ $mapelCount }}</p>
                        <p class="text-sm text-green-600">Mata Pelajaran</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Section - Information -->
        <div class="lg:col-span-1">
            <div class="flex items-center justify-between mb-3">
                <div class="bg-green-600 text-white px-3 py-1.5 rounded-lg inline-block">
                    <span class="flex items-center text-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        Informasi
                    </span>
                </div>
            </div>

            <!-- Information Items -->
            <div class="h-[150px] overflow-y-auto">
                <div class="relative pl-14">
                    <!-- Garis vertikal di tengah icon -->
                    <div class="absolute left-5 top-0 bottom-0 w-[2px] bg-gray-200"></div>
                    
                    <!-- Daftar notifikasi -->
                    <template x-for="item in $store.notification.items" :key="item.id">
                        <div class="mb-4 relative min-h-[80px]">
                            <!-- Ikon amplop di tengah garis -->
                            <div class="absolute -left-12 top-3 w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center z-10">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            
                            <!-- Konten notifikasi -->
                            <div @click="!item.is_read && $store.notification.markAsRead(item.id)" 
                                class="bg-white rounded-lg border shadow-sm p-3"
                                :class="{ 'cursor-pointer hover:bg-gray-50': !item.is_read }">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-sm font-medium" x-text="item.title"></h3>
                                        <p class="text-xs text-gray-600 line-clamp-2" x-text="item.content"></p>
                                    </div>
                                    <span class="text-xs text-gray-500" x-text="item.created_at"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                    
                    <!-- Tampilan saat tidak ada notifikasi -->
                    <template x-if="$store.notification.items.length === 0">
                        <div class="flex items-center justify-center h-[150px]">
                            <p class="text-gray-500 text-sm">Belum ada notifikasi</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>


    <!-- Dropdown and Charts Section -->
    <div class="mt-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Kelas Dropdown -->
            <div>
                <label for="kelas" class="block text-sm font-medium text-gray-700">Pilih Kelas</label>
                <select id="kelas" 
                    x-model="selectedKelas" 
                    @change="onKelasChange()"
                    class="block w-full p-2 mt-1 rounded-lg border border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                    <option value="">Pilih kelas...</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}">{{ $k->nomor_kelas }} {{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Mata Pelajaran Dropdown (appears when class is selected) -->
            <div x-show="selectedKelas !== ''">
                <label for="mapel" class="block text-sm font-medium text-gray-700">Pilih Mata Pelajaran</label>
                <select id="mapel" 
                    x-model="selectedMapel" 
                    @change="onMapelChange()"
                    class="block w-full p-2 mt-1 rounded-lg border border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua mata pelajaran</option>
                    <template x-for="mapel in mapelList" :key="mapel.id">
                        <option :value="mapel.id" x-text="mapel.nama_pelajaran"></option>
                    </template>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <!-- Chart Kelas Yang Diajar -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Progress Input Nilai Per Kelas</h3>
                <div class="flex flex-col items-center">
                    <div class="w-64 h-64 relative">
                        <canvas id="overallPieChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Chart Mata Pelajaran -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Progress Input Nilai Mata Pelajaran</h3>
                <div class="flex flex-col items-center">
                    <div class="w-64 h-64 relative">
                        <canvas id="classProgressChart"></canvas>
                    </div>
                    <div x-show="!selectedKelas" class="text-gray-500 text-sm mt-4">
                        Pilih kelas terlebih dahulu untuk melihat progress mata pelajaran
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Constants
const PENGAJAR_DASHBOARD_KEY = 'pengajarDashboardLoaded';

// Global chart variables
let overallChart = null;
let classChart = null;
let overallProgress = {{ $overallProgress ?? 0 }};
let kelasProgress = 0;

// Utility function for destroying charts
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

// Initialize charts function
function initCharts() {
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                enabled: true
            }
        }
    };

    // Overall Progress Chart (Kelas yang diajar)
    const overallCtx = document.getElementById('overallPieChart')?.getContext('2d');
    if (overallCtx) {
        overallChart = new Chart(overallCtx, {
            type: 'doughnut',
            data: {
                labels: ['Selesai', 'Belum'],
                datasets: [{
                    data: [
                        Math.min(100, Math.max(0, overallProgress)),
                        Math.min(100, Math.max(0, 100 - overallProgress))
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
                    
                    const text = Math.round(overallProgress) + '%';
                    const textX = Math.round((width - ctx.measureText(text).width) / 2);
                    const textY = height / 2;

                    ctx.fillStyle = '#1F2937';
                    ctx.fillText(text, textX, textY);
                    ctx.save();
                }
            }]
        });
    }

    // Class Progress Chart (Mata Pelajaran)
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

// Update class chart with progress
function updateClassChart(progress) {
    kelasProgress = Math.min(100, Math.max(0, progress));
    if (classChart) {
        classChart.data.datasets[0].data = [kelasProgress, 100 - kelasProgress];
        classChart.update();
    }
}

// Fetch progress for a specific class
function fetchKelasProgress() {
    const selectedKelas = document.getElementById('kelas').value;
    if (selectedKelas) {
        fetch(`/pengajar/kelas-progress/${selectedKelas}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Progress data:', data);
                updateClassChart(data.progress);
            })
            .catch(error => {
                console.error('Error fetching kelas progress:', error);
                updateClassChart(0);
            });
    } else {
        updateClassChart(0);
    }
}

// Function to check if on dashboard page
function isDashboardPage() {
    return window.location.pathname.includes('/pengajar/dashboard');
}

// Function to handle dashboard initialization
function handleDashboardInit() {
    if (isDashboardPage()) {
        const isLoaded = sessionStorage.getItem(PENGAJAR_DASHBOARD_KEY);
        if (!isLoaded) {
            sessionStorage.setItem(PENGAJAR_DASHBOARD_KEY, 'true');
            window.location.reload();
        } else {
            destroyCharts();
            initCharts();
            fetchKelasProgress();
        }
    }
}

// Alpine Data Component
document.addEventListener('alpine:init', () => {
    Alpine.data('dashboard', () => ({
        selectedKelas: '',
        selectedMapel: '',
        mapelList: [],
        
        // Initialize the component
        initDashboard() {
            $store.notification.fetchNotifications();
            $store.notification.startAutoRefresh();
            
            setTimeout(() => {
                initCharts();
            }, 100);

            this.$watch('selectedKelas', value => {
                if (value) {
                    this.onKelasChange();
                }
            });
        },
        
        // When kelas is changed
        async onKelasChange() {
            if (!this.selectedKelas) {
                this.mapelList = [];
                this.selectedMapel = '';
                updateClassChart(0);
                return;
            }
            
            try {
                // Fetch mata pelajaran for selected kelas
                const response = await fetch(`/pengajar/mata-pelajaran/kelas/${this.selectedKelas}`);
                if (!response.ok) throw new Error('Failed to fetch mata pelajaran');
                
                const data = await response.json();
                this.mapelList = data.mapel || [];
                
                // Reset selected mapel
                this.selectedMapel = '';
                
                // Fetch progress for this class
                fetchKelasProgress();
            } catch (error) {
                console.error('Error fetching mata pelajaran:', error);
                updateClassChart(0);
            }
        },
        
        // When mapel is changed
        onMapelChange() {
            // If a specific subject is selected, we need to fetch its progress
            if (this.selectedKelas && this.selectedMapel) {
                fetch(`/pengajar/mata-pelajaran/progress/${this.selectedKelas}?mapel_id=${this.selectedMapel}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        updateClassChart(data.progress);
                    })
                    .catch(error => {
                        console.error('Error fetching mapel progress:', error);
                        updateClassChart(0);
                    });
            } else {
                // If no specific subject, just show class progress
                fetchKelasProgress();
            }
        }
    }));
});

// Event listeners
document.addEventListener('DOMContentLoaded', handleDashboardInit);
document.addEventListener('turbo:load', handleDashboardInit);
document.addEventListener('turbo:render', handleDashboardInit);

document.addEventListener('turbo:visit', () => {
    if (!isDashboardPage()) {
        sessionStorage.removeItem(PENGAJAR_DASHBOARD_KEY);
    }
});

document.addEventListener('change', function(event) {
    if (event.target && event.target.id === 'kelas') {
        fetchKelasProgress();
    }
});

document.addEventListener('turbo:before-cache', () => {
    destroyCharts();
});

window.addEventListener('unload', () => {
    destroyCharts();
    if (!isDashboardPage()) {
        sessionStorage.removeItem(PENGAJAR_DASHBOARD_KEY);
    }
});

// Initialize on load
if (document.readyState === 'complete') {
    handleDashboardInit();
} else {
    window.addEventListener('load', handleDashboardInit);
}
</script>
@endpush
@endsection