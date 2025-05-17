@extends('layouts.pengajar.app')

@section('title', 'Dashboard Pengajar')

@section('content')
<div x-data="dashboard" x-init="$store.notification.fetchNotifications(); $store.notification.startAutoRefresh()">
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
                            <div class="bg-white rounded-lg border shadow-sm p-3">
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
        <label for="subject" class="block text-sm font-medium text-gray-700">Pilih Mata Pelajaran</label>
        <select id="subject" 
            x-model="selectedSubject" 
            @change="fetchSubjectProgress"
            class="block w-full p-2 mt-1 rounded-lg border border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
            <option value="">Pilih mata pelajaran...</option>
            @foreach($kelas as $k)
                <optgroup label="Kelas {{ $k->nomor_kelas }} {{ $k->nama_kelas }}">
                    @php
                        // Gunakan array untuk melacak mata pelajaran yang sudah ditambahkan
                        $addedSubjects = [];
                    @endphp
                    
                    @foreach($k->mataPelajarans->where('guru_id', auth()->guard('guru')->id()) as $mapel)
                        @php
                            // Buat kunci unik untuk setiap mata pelajaran
                            $mapelKey = $mapel->nama_pelajaran . '_' . $mapel->id;
                        @endphp
                        
                        @if(!in_array($mapelKey, $addedSubjects))
                            <option value="{{ $mapel->id }}">{{ $mapel->nama_pelajaran }}</option>
                            @php
                                $addedSubjects[] = $mapelKey;
                            @endphp
                        @endif
                    @endforeach
                </optgroup>
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
            
            <!-- Chart Per Mata Pelajaran -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Progress Input Nilai Per Mata Pelajaran</h3>
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
        // Get a safe progress value (0-100)
        const safeProgress = Math.min(100, Math.max(0, {{ $overallProgress ?? 0 }}));
        
        overallChart = new Chart(overallCtx, {
            type: 'doughnut',
            data: {
                labels: ['Selesai', 'Belum'],
                datasets: [{
                    data: [safeProgress, 100 - safeProgress],
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
                    
                    const text = Math.round(safeProgress) + '%';
                    const textX = Math.round((width - ctx.measureText(text).width) / 2);
                    const textY = height / 2;

                    ctx.fillStyle = '#1F2937';
                    ctx.fillText(text, textX, textY);
                    ctx.save();
                }
            }]
        });
    }

    // Class Progress Chart (Initially empty)
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

function fetchSubjectProgress(subjectId) {
    const selectedSubject = subjectId || document.getElementById('subject').value;
    if (selectedSubject) {
        fetch(`/pengajar/mata-pelajaran-progress/${selectedSubject}`)
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
                console.error('Error fetching subject progress:', error);
                updateClassChart(0);
            });
    } else {
        updateClassChart(0);
    }
}


// Event handlers untuk initialization
document.addEventListener('alpine:init', () => {
    Alpine.data('dashboard', () => ({
        selectedSubject: '',
        mapelProgress: [],
        
        init() {
            setTimeout(() => {
                this.initCharts();
            }, 100);

            this.$watch('selectedSubject', value => {
                if (value) {
                    this.fetchSubjectProgress();
                }
            });
        },
        
        fetchSubjectProgress() {
            if (!this.selectedSubject) return;
            
            // Panggil fungsi global untuk memastikan kompatibilitas
            window.fetchSubjectProgress(this.selectedSubject);
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
        }
    }
}

// Event Listeners untuk navigasi dan reload


document.addEventListener('DOMContentLoaded', handleDashboardInit);
document.addEventListener('turbo:load', handleDashboardInit);
document.addEventListener('turbo:render', handleDashboardInit);

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