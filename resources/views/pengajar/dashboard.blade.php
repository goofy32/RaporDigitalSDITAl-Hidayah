@extends('layouts.pengajar.app')

@section('title', 'Dashboard Pengajar')

@section('content')
<div x-data="{ selectedKelas: '', mapelProgress: [] }">
    <!-- Main Content Container -->
    <div class="flex flex-col lg:flex-row gap-4 mt-14">
    <!-- Statistics Grid - Takes 2/3 of the space -->
        <div class="lg:w-2/3">
        <!-- Stats Cards - Using consistent grid and spacing -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <!-- Kelas Card -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                    <div class="p-4">
                        <p class="text-2xl font-bold text-green-600">{{ $kelasCount }}</p>
                        <p class="text-sm text-green-600">Kelas</p>
                    </div>
                </div>
                
                <!-- Siswa Card -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                    <div class="p-4">
                        <p class="text-2xl font-bold text-green-600">{{ $siswaCount }}</p>
                        <p class="text-sm text-green-600">Siswa</p>
                    </div>
                </div>
                
                <!-- Mata Pelajaran Card -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                    <div class="p-4">
                        <p class="text-2xl font-bold text-green-600">{{ $mapelCount }}</p>
                        <p class="text-sm text-green-600">Mata Pelajaran</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Information Section - Takes 1/3 of the space -->
        <div class="lg:w-1/3">
            <div class="bg-white rounded-lg shadow">
                <div class="flex items-center justify-between p-4 border-b">
                    <span class="flex items-center text-sm bg-green-600 text-white px-3 py-1.5 rounded-lg">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        Informasi
                    </span>
                </div>
                
                <!-- Timeline notifikasi with read status -->
                <div class="relative pl-6 border-l-2 border-gray-200 p-4">
                    @foreach($notifications as $notification)
                    <div class="mb-4 relative" x-data="{ isRead: {{ $notification->is_read ? 'true' : 'false' }} }">
                        <div class="absolute -left-8 w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
                            <svg class="w-3 h-3" :class="isRead ? 'text-green-600' : 'text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div @click="if(!isRead) { await $store.notification.markAsRead({{ $notification->id }}); isRead = true; }" 
                             class="bg-white rounded-lg border shadow-sm p-3 cursor-pointer transition-colors"
                             :class="{ 'hover:bg-gray-50': !isRead }">
                            <div>
                                <h3 class="text-sm font-medium">{{ $notification->title }}</h3>
                                <p class="text-xs text-gray-600">{{ $notification->content }}</p>
                                <span class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
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

async function markAsRead(notificationId) {
    try {
        const response = await fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            // Update UI to show notification as read
            this.isRead = true;
            // Update unread count in notification store
            Alpine.store('notification').fetchUnreadCount();
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
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
// Implementasi Alpine.js data
document.addEventListener('alpine:init', () => {
    Alpine.data('dashboard', () => ({
        selectedKelas: '',
        mapelProgress: [],
        
        init() {
            initCharts();
            this.$watch('selectedKelas', value => {
                if (value) {
                    fetchKelasProgress();
                }
            });
        }
    }));
});

function isDashboardPage() {
    return window.location.pathname.includes('/pengajar/dashboard');
}

// Fungsi untuk menangani inisialisasi dashboard
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

// Event Listeners
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

// Cleanup
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
</script>
@endpush
@endsection