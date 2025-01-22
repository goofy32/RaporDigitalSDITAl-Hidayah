@extends('layouts.wali_kelas.app')

@section('title', 'Dashboard Wali Kelas')

@section('content') 
<div x-data="{ selectedKelas: '', mapelProgress: [] }">
    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-14">
        <!-- Left Section - Stats (2 columns) -->
        <div class="lg:col-span-2">
            <div class="grid grid-cols-2 gap-3">
                <!-- Siswa Card -->
                <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden cursor-pointer" onclick="navigateTo('{{ route('wali_kelas.student.index') }}')">
                    <div class="p-4">
                        <p class="text-2xl font-bold text-green-600">{{ $totalSiswa }}</p>
                        <p class="text-sm text-green-600">Siswa</p>
                    </div>
                </div>

                <!-- Mata Pelajaran Card -->
                <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-4">
                        <p class="text-2xl font-bold text-green-600">{{ $totalMapel }}</p>
                        <p class="text-sm text-green-600">Mata Pelajaran</p>
                    </div>
                </div>
            </div>

            <!-- Ekstrakurikuler Card (Full Width) -->
            <div class="mt-3">
                <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden cursor-pointer" onclick="navigateTo('{{ route('wali_kelas.ekstrakurikuler.index') }}')">
                    <div class="p-4">
                        <p class="text-2xl font-bold text-green-600">{{ $totalEkskul }}</p>
                        <p class="text-sm text-green-600">Ekstrakurikuler</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Section - Information -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm h-full">
                <div class="p-3 border-b border-gray-200">
                    <span class="flex items-center text-sm font-medium text-green-600">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        Informasi
                    </span>
                </div>

                <!-- Information Items (Scrollable) -->
                <div class="p-3 max-h-[300px] overflow-y-auto">
                    <div class="relative pl-6 border-l-2 border-gray-200">
                        @forelse($notifications as $notification)
                        <div class="mb-4 relative">
                            <div class="absolute -left-8 w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
                                <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="bg-white rounded-lg border shadow-sm p-3">
                                <div>
                                    <div class="flex justify-between items-start mb-1">
                                        <h3 class="text-sm font-medium">{{ $notification->title }}</h3>
                                        <span class="text-xs text-gray-400">{{ $notification->created_at_formatted }}</span>
                                    </div>
                                    <p class="text-xs text-gray-600">{{ $notification->content }}</p>
                                    @if(!$notification->isReadBy(auth()->guard('guru')->id()))
                                    <div class="mt-2">
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700">
                                            Baru
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <p class="text-gray-500 text-sm">Belum ada informasi</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dropdown & Charts Section (Full Width) -->
    <div class="mt-8">
        <!-- Dropdown Pilih Kelas -->
        <div class="mb-8">
            <label for="kelas" class="block text-sm font-medium text-gray-700">Pilih Kelas</label>
            <select id="kelas" 
                x-model="selectedKelas" 
                @change="fetchKelasProgress()"
                class="block w-full p-2 mt-1 rounded-lg border border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                <option value="">Pilih kelas...</option>
                @if(isset($kelas))
                    <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                @endif
            </select>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let overallChart, classChart;
let kelasProgress = 0;

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

   // Initialize Overall Progress Chart
   const overallCtx = document.getElementById('overallPieChart')?.getContext('2d');
   if (overallCtx) {
       overallChart = new Chart(overallCtx, {
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
                   
                   const text = '0%';
                   const textX = Math.round((width - ctx.measureText(text).width) / 2);
                   const textY = height / 2;

                   ctx.fillStyle = '#1F2937';
                   ctx.fillText(text, textX, textY);
                   ctx.save();
               }
           }]
       });
   }

   // Initialize Class Progress Chart
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
                   
                   const text = '0%';
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

function updateOverallChart(progress) {
   if (overallChart) {
       overallChart.data.datasets[0].data = [progress, 100 - progress];
       overallChart.options.plugins.centerText = {
           text: `${Math.round(progress)}%`
       };
       overallChart.update();
   }
}

function updateClassChart(progress) {
   if (classChart) {
       classChart.data.datasets[0].data = [progress, 100 - progress];
       classChart.options.plugins.centerText = {
           text: `${Math.round(progress)}%`
       };
       classChart.update();
   }
}

function fetchKelasProgress() {
   const selectedKelas = document.getElementById('kelas').value;
   if (!selectedKelas) return;

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
       if (overallData.progress !== undefined) {
           updateOverallChart(overallData.progress);
       }
       if (kelasData.progress !== undefined) {
           updateClassChart(kelasData.progress);
       }
   })
   .catch(error => console.error('Error:', error));
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
   initCharts();
   fetchKelasProgress(); // Fetch initial data
});

// Event listener for kelas dropdown
document.getElementById('kelas')?.addEventListener('change', fetchKelasProgress);

// Clean up
document.addEventListener('turbo:before-cache', destroyCharts);

function navigateTo(url) {
   window.location.href = url;
}
</script>
@endpush