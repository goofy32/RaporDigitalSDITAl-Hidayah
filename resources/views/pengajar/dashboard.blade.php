@extends('layouts.pengajar.app')

@section('title', 'Dashboard Pengajar')

@section('content')
<div class="p-4" x-data="{ 
    selectedKelas: '', 
    mapelProgress: [],
    fetchingData: false
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

    <!-- Progress per Mata Pelajaran -->
    <div class="mt-8" x-show="selectedKelas && mapelProgress.length > 0">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Progress Input Nilai Per Mata Pelajaran</h3>
        <template x-for="(mapel, index) in mapelProgress" :key="index">
            <div class="mb-4 p-4 bg-white rounded-lg shadow">
                <h4 class="font-medium text-gray-700 mb-2" x-text="mapel.nama"></h4>
                <div class="relative pt-1">
                    <div class="overflow-hidden h-2 text-xs flex rounded bg-green-200">
                        <div 
                            class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500 transition-all duration-500"
                            :style="`width: ${mapel.progress}%`">
                        </div>
                    </div>
                    <div class="text-right mt-1">
                        <span class="text-sm font-semibold text-gray-700" x-text="`${Math.round(mapel.progress)}%`"></span>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Progress Keseluruhan -->
    <div class="mt-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Progress Input Nilai Keseluruhan</h3>
        <div class="p-4 bg-white rounded-lg shadow">
            <div class="relative pt-1">
                <div class="overflow-hidden h-2 text-xs flex rounded bg-green-200">
                    <div 
                        class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500"
                        style="width: {{ $overallProgress }}%">
                    </div>
                </div>
                <div class="text-right mt-1">
                    <span class="text-sm font-semibold text-gray-700">{{ round($overallProgress) }}%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fetchKelasProgress() {
    if (this.selectedKelas && !this.fetchingData) {
        this.fetchingData = true;
        fetch(`/pengajar/kelas-progress/${this.selectedKelas}`)
            .then(response => response.json())
            .then(data => {
                this.mapelProgress = data.mapelProgress;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengambil data');
            })
            .finally(() => {
                this.fetchingData = false;
            });
    } else {
        this.mapelProgress = [];
    }
}
</script>
@endsection