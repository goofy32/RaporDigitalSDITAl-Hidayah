@extends('layouts.pengajar.app')

@section('title', 'Dashboard Pengajar')

@section('content')
<div class="p-4" x-data="{ 
    selectedKelas: '', 
    mapelProgress: []
}">
    <!-- Statistik Utama -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3 mt-14">
        <div class="p-4 bg-white rounded-lg shadow-md border">
            <p class="text-sm font-semibold text-gray-600">KELAS</p>
            <p class="text-xl font-bold text-green-600">{{ $kelasCount }} Kelas</p>
        </div>
        <div class="p-4 bg-white rounded-lg shadow-md border">
            <p class="text-sm font-semibold text-gray-600">SISWA</p>
            <p class="text-xl font-bold text-green-600">{{ $siswaCount }} Siswa</p>
        </div>
        <div class="p-4 bg-white rounded-lg shadow-md border">
            <p class="text-sm font-semibold text-gray-600">MATA PELAJARAN</p>
            <p class="text-xl font-bold text-green-600">{{ $mapelCount }} Mata Pelajaran</p>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let overallChart, classChart;
let kelasProgress = 0;

function initCharts() {
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                enabled: false // Disable tooltip karena kita akan tampilkan nilai di tengah
            },
            datalabels: { // Plugin untuk menampilkan label di dalam chart
                color: '#fff',
                font: {
                    size: 16,
                    weight: 'bold'
                },
                formatter: function(value) {
                    return Math.round(value) + '%';
                }
            }
        }
    };

    // Overall Progress Chart
    const overallCtx = document.getElementById('overallPieChart')?.getContext('2d');
    if (overallCtx) {
        overallChart = new Chart(overallCtx, {
            type: 'doughnut', // Menggunakan doughnut untuk ruang di tengah
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
                cutout: '60%', // Ukuran lubang di tengah
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

                    ctx.fillStyle = '#1F2937'; // Warna teks
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
    kelasProgress = Math.min(100, Math.max(0, progress)); // Memastikan nilai antara 0-100
    if (classChart) {
        classChart.data.datasets[0].data = [kelasProgress, 100 - kelasProgress];
        classChart.update();
    }
}

function fetchKelasProgress() {
    const selectedKelas = document.getElementById('kelas').value;
    if (selectedKelas) {
        fetch(`/pengajar/kelas-progress/${selectedKelas}`)
            .then(response => response.json())
            .then(data => {
                updateClassChart(data.progress);
            })
            .catch(error => console.error('Error:', error));
    } else {
        updateClassChart(0);
    }
}

// Initialize charts on page load and when navigating back
document.addEventListener('DOMContentLoaded', initCharts);
document.addEventListener('turbo:render', initCharts);
document.addEventListener('turbo:load', initCharts);

// Cleanup charts before caching page
document.addEventListener('turbo:before-cache', () => {
    if (overallChart) {
        overallChart.destroy();
    }
    if (classChart) {
        classChart.destroy();
    }
});
</script>
@endpush
@endsection