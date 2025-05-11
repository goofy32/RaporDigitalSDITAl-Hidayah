@extends('layouts.wali_kelas.app')

@section('title', 'Dashboard Wali Kelas')

@section('content') 
<div x-data="{ mapelProgress: [] }" x-init="$store.notification.fetchNotifications(); $store.notification.startAutoRefresh()">

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-14">
        <!-- Left Section - Stats (col-span-2) -->
        <div class="lg:col-span-2">
            <!-- Top Row - 2 Cards -->
            <div class="grid grid-cols-2 gap-4 mb-4">
                <!-- Siswa Card -->
                <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden cursor-pointer hover:bg-gray-50" onclick="navigateTo('{{ route('wali_kelas.student.index') }}')">
                    <div class="p-4">
                        <p class="text-2xl font-bold text-green-600">{{ $totalSiswa }}</p>
                        <p class="text-sm text-green-600">Siswa</p>
                    </div>
                </div>

                <!-- Absensi Card -->
                <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden cursor-pointer hover:bg-gray-50" onclick="navigateTo('{{ route('wali_kelas.absence.index') }}')">
                    <div class="p-4">
                        <p class="text-2xl font-bold text-green-600">{{ $totalAbsensi ?? 0 }}</p>
                        <p class="text-sm text-green-600">Absensi</p>
                    </div>
                </div>
            </div>

            <!-- Bottom Row - 1 Card -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Ekstrakurikuler Card -->
                <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden cursor-pointer hover:bg-gray-50" onclick="navigateTo('{{ route('wali_kelas.ekstrakurikuler.index') }}')">
                    <div class="p-4">
                        <p class="text-2xl font-bold text-green-600">{{ $totalEkskul }}</p>
                        <p class="text-sm text-green-600">Ekstrakurikuler</p>
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

    <!-- Dropdown & Charts Section (Full Width) -->
    <div class="mt-8">
        <!-- Dropdown Pilih Kelas -->
        <div class="mb-8">
            <h3 class="block text-sm font-medium text-gray-700">Kelas Yang Diwalikan</h3>
            <div class="p-3 mt-1 rounded-lg border border-gray-300 bg-gray-50">
                @if(isset($kelas))
                    <span class="font-medium text-green-700">{{ $kelas->nomor_kelas }} {{ $kelas->nama_kelas }}</span>
                @else
                    <span class="text-yellow-600">Anda belum ditugaskan sebagai wali kelas</span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let overallChart, classChart;
let kelasProgress = 0;
const WALIKELAS_DASHBOARD_KEY = 'walikelasDashboardLoaded';

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
                    data: [60, 40], // Nilai default, akan diupdate oleh fetchKelasProgress
                    backgroundColor: ['rgb(34, 197, 94)', 'rgb(229, 231, 235)'],
                    borderWidth: 0
                }]
            },
            options: {
                ...defaultOptions,
                cutout: '60%'
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
                    
                    const data = chart.data.datasets[0].data;
                    const text = Math.round(data[0]) + '%';
                    const textX = Math.round((width - ctx.measureText(text).width) / 2);
                    const textY = height / 2;

                    ctx.fillStyle = '#1F2937';
                    ctx.fillText(text, textX, textY);
                    ctx.save();
                }
            }]
        });
    }

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
                cutout: '60%'
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
                    
                    const data = chart.data.datasets[0].data;
                    const text = Math.round(data[0]) + '%';
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

function updateCharts(overallProgress, classProgress) {
    if (overallChart) {
        overallChart.data.datasets[0].data = [overallProgress, 100 - overallProgress];
        overallChart.update();
    }
    if (classChart) {
        classChart.data.datasets[0].data = [classProgress, 100 - classProgress];
        classChart.update();
    }
}

function fetchKelasProgress() {
    Promise.all([
        fetch("{{ route('wali_kelas.overall.progress') }}", {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }),
        fetch("{{ route('wali_kelas.kelas.progress') }}", {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
    ])
    .then(responses => Promise.all(responses.map(r => r.json())))
    .then(([overallData, kelasData]) => {
        if (overallData.progress !== undefined && kelasData.progress !== undefined) {
            updateCharts(overallData.progress, kelasData.progress);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        updateCharts(0, 0);
    });
}

// Inisialisasi
document.addEventListener('DOMContentLoaded', () => {
    initCharts();
    fetchKelasProgress();
});

// Event Listeners
document.addEventListener('turbo:load', () => {
    if (window.location.pathname.includes('/wali-kelas/dashboard')) {
        destroyCharts();
        setTimeout(() => {
            initCharts();
            fetchKelasProgress();
        }, 100);
    }
});

document.addEventListener('turbo:before-cache', destroyCharts);

function navigateTo(url) {
    window.location.href = url;
}
</script>

@endpush