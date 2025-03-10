@extends('layouts.pengajar.app')

@section('title', 'Dashboard Pengajar')

@section('content')
<div x-data="{ selectedKelas: '', mapelProgress: [] }" x-init="$store.notification.fetchNotifications(); $store.notification.startAutoRefresh()">
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
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                <div class="h-[150px] overflow-y-auto">
                    <div class="relative pl-6 border-l-2 border-gray-200 p-4">
                        <template x-for="item in $store.notification.items" :key="item.id">
                            <div class="mb-4 relative h-[60px]">
                                <div class="absolute -left-8 w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
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
                        <template x-if="$store.notification.items.length === 0">
                            <div class="flex items-center justify-center h-[150px]">
                                <p class="text-gray-500 text-sm">Belum ada notifikasi</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Dropdown and Charts Section -->
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
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Progress Input Nilai Per Kelas</h3>
                <div class="flex flex-col items-center">
                    <div class="w-64 h-64 relative">
                        <canvas id="classProgressChart"></canvas>
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
const PENGAJAR_DASHBOARD_KEY = 'pengajarDashboardLoaded';

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

    // Overall Progress Chart
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

    // Class Progress Chart
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

function updateClassChart(progress) {
    kelasProgress = Math.min(100, Math.max(0, progress));
    if (classChart) {
        classChart.data.datasets[0].data = [kelasProgress, 100 - kelasProgress];
        classChart.update();
    }
}

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

// Event handlers untuk initialization
document.addEventListener('alpine:init', () => {
    Alpine.data('dashboard', () => ({
        selectedKelas: '',
        mapelProgress: [],
        
        init() {
            setTimeout(() => {
                this.initCharts();
                this.fetchKelasProgress();
            }, 100);

            this.$watch('selectedKelas', value => {
                if (value) {
                    fetchKelasProgress();
                }
            });
        }
    }));
});

// Function untuk mengecek apakah di halaman dashboard
function isDashboardPage() {
    return window.location.pathname.includes('/pengajar/dashboard');
}

// Function untuk menangani inisialisasi dashboard
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

// Event Listeners untuk navigasi dan reload
document.addEventListener('DOMContentLoaded', () => {
    handleDashboardInit();
});

document.addEventListener('turbo:load', () => {
    handleDashboardInit();
});

document.addEventListener('turbo:render', () => {
    handleDashboardInit();
});

document.addEventListener('turbo:visit', () => {
    if (!isDashboardPage()) {
        sessionStorage.removeItem(PENGAJAR_DASHBOARD_KEY);
    }
});

// Event listener untuk dropdown kelas
document.addEventListener('change', function(event) {
    if (event.target && event.target.id === 'kelas') {
        fetchKelasProgress();
    }
});

// Cleanup saat navigasi
document.addEventListener('turbo:before-cache', () => {
    destroyCharts();
});

// Handle unload
window.addEventListener('unload', () => {
    destroyCharts();
    if (!isDashboardPage()) {
        sessionStorage.removeItem(PENGAJAR_DASHBOARD_KEY);
    }
});

// Inisialisasi awal
if (document.readyState === 'complete') {
    handleDashboardInit();
} else {
    window.addEventListener('load', handleDashboardInit);
}

// Function untuk navigasi
function navigateTo(url) {
    window.location.href = url;
}
</script>
<script>
    const overallProgress = {{ $overallProgress ?? 0 }};
</script>
@endpush
@endsection