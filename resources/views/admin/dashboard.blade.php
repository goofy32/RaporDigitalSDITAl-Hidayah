@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div x-data="{ selectedKelas: '', mapelProgress: [] }">
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
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex items-center justify-center" data-modal-target="addInfoModal" data-modal-toggle="addInfoModal">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </button>
            </div>

            <!-- Information Items -->
            <div class="relative pl-6 border-l-2 border-gray-200">
                @foreach($informationItems as $item)
                <div class="mb-4 relative">
                    <div class="absolute -left-8 w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="bg-white rounded-lg border shadow-sm p-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-sm font-medium">{{ $item->title }}</h3>
                                <p class="text-xs text-gray-600">{{ $item->content }}</p>
                            </div>
                            <button class="text-red-500 hover:text-red-700" onclick="deleteInformation({{ $item->id }})">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
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
</div>

<!-- Add Information Modal -->
<div id="addInfoModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <div class="relative bg-white rounded-lg shadow">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-xl font-semibold">
                    Tambah Informasi
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex items-center justify-center" data-modal-hide="addInfoModal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            </div>
            <!-- Modal body -->
            <div class="p-4">
                <form>
                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-900">Judul informasi</label>
                        <input type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" placeholder="Masukkan judul informasi">
                    </div>
                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-900">Informasi untuk</label>
                        <select class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                            <option selected>-- Pilih --</option>
                            <option value="all">Semua</option>
                            <option value="guru">Guru</option>
                            <option value="wali_kelas">Wali Kelas</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-900">Isi</label>
                        <textarea class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" rows="4" placeholder="Masukkan isi informasi"></textarea>
                    </div>
                    <button type="submit" class="w-full text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>

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
    destroyCharts();
    
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
            plugins: [createCenterTextPlugin({{ $overallProgress }})]
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
                cutout: '60%',
            },
            plugins: [createCenterTextPlugin(0)]
        });
    }
}

function createCenterTextPlugin(value) {
    return {
        id: 'centerText',
        afterDraw(chart) {
            const {ctx, width, height} = chart;
            ctx.restore();
            const fontSize = (height / 114).toFixed(2);
            ctx.font = `${fontSize}em sans-serif`;
            ctx.textBaseline = 'middle';
            const text = `${Math.round(value)}%`;
            const textX = Math.round((width - ctx.measureText(text).width) / 2);
            const textY = height / 2;
            ctx.fillStyle = '#1F2937';
            ctx.fillText(text, textX, textY);
            ctx.save();
        }
    };
}

function updateDashboardData() {
    fetch('/admin/dashboard-data', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.querySelectorAll('[data-statistic]').forEach(element => {
            const key = element.dataset.statistic;
            if (data[key] !== undefined) {
                element.textContent = data[key];
            }
        });
        updateOverallProgress(data.overallProgress);
    });
}

function updateClassChart(progress) {
    kelasProgress = Math.min(100, Math.max(0, progress));
    if (classChart) {
        classChart.data.datasets[0].data = [kelasProgress, 100 - kelasProgress];
        classChart.plugins[0] = createCenterTextPlugin(kelasProgress);
        classChart.update();
    }
}

function fetchKelasProgress() {
    const selectedKelas = document.getElementById('kelas').value;
    if (selectedKelas) {
        fetch(`/admin/kelas-progress/${selectedKelas}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            updateClassChart(data.progress);
            updateProgressDetails(data.details);
        })
        .catch(error => console.error('Error:', error));
    } else {
        updateClassChart(0);
    }
}

function updateProgressDetails(details) {
    if (details && details.mapelProgress) {
        // Update mata pelajaran progress jika ada
        const container = document.getElementById('mapelProgressContainer');
        if (container) {
            container.innerHTML = details.mapelProgress.map(item => `
                <div class="progress-item">
                    <span>${item.nama}</span>
                    <div class="progress-bar">
                        <div class="progress" style="width: ${item.progress}%"></div>
                    </div>
                    <span>${Math.round(item.progress)}%</span>
                </div>
            `).join('');
        }
    }
}

// Dashboard reload handling
if (window.location.pathname.includes('/admin/dashboard')) {
    if (!window.dashboardLoaded) {
        window.dashboardLoaded = true;
        window.location.reload();
    }
} else {
    window.dashboardLoaded = false;
}

// Event Listeners
window.addEventListener('load', () => {
    if (document.readyState === 'complete') {
        initCharts();
        fetchKelasProgress();
        updateDashboardData();
    }
});

document.addEventListener('turbo:load', () => {
    destroyCharts();
    setTimeout(() => {
        initCharts();
        fetchKelasProgress();
        updateDashboardData();
    }, 100);
});

document.addEventListener('turbo:before-cache', destroyCharts);

// Cleanup on page unload
window.addEventListener('unload', destroyCharts);

// Modal functionality
const addInfoModal = document.getElementById('addInfoModal');
const addInfoModalBtn = document.querySelectorAll('[data-modal-target="addInfoModal"], [data-modal-toggle="addInfoModal"]');

addInfoModalBtn.forEach(btn => {
    btn.addEventListener('click', () => {
        addInfoModal.classList.toggle('hidden');
    });
});

function deleteInformation(id) {
    if (confirm('Are you sure you want to delete this information?')) {
        fetch(`/admin/information/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    }
}
</script>
@endpush
@endsection