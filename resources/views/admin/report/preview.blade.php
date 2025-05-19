@extends('layouts.app')

@section('title', 'Preview Rapor Siswa')

@section('content')
<div class="container mx-auto px-4">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Preview Rapor {{ $siswa->nama }}</h1>
        <p class="text-gray-600">{{ $siswa->kelas->nama_kelas }} - Semester {{ session('semester') }}</p>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <!-- Data Siswa -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-medium mb-4">Data Siswa</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Nama</span>
                    <span class="font-medium">{{ $siswa->nama }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">NISN</span>
                    <span class="font-medium">{{ $siswa->nisn }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">NIS</span>
                    <span class="font-medium">{{ $siswa->nis }}</span>
                </div>
            </div>
        </div>

        <!-- Preview Controls -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-medium mb-4">Preview Rapor</h2>
            <form action="{{ route('wali_kelas.rapor.generate', $siswa->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Jenis Rapor
                    </label>
                    <select name="type" required class="w-full rounded-lg border-gray-300">
                        <option value="UTS">UTS</option>
                        <option value="UAS">UAS</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" name="action" value="preview" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Preview
                    </button>
                    <button type="submit" name="action" value="download" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Download
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Nilai Section -->
    <div class="mt-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-medium mb-4">Nilai Akademik</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Mata Pelajaran
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nilai
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Predikat
                            </th>
                        </tr>
                    </thead>
                        @foreach($siswa->nilais->where('tahun_ajaran_id', session('tahun_ajaran_id'))->groupBy('mataPelajaran.nama_pelajaran') as $mapel => $nilaiGroup)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $mapel }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    // Ambil nilai akhir rapor dari kelompok nilai (pilih yang tidak null)
                                    $nilaiAkhir = $nilaiGroup->where('nilai_akhir_rapor', '!=', null)->first();
                                    $nilai = $nilaiAkhir ? $nilaiAkhir->nilai_akhir_rapor : '-';
                                @endphp
                                {{ $nilai }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $predikat = '';
                                    if ($nilaiAkhir) {
                                        if (method_exists($nilaiAkhir, 'getPredikat')) {
                                            $predikat = $nilaiAkhir->getPredikat();
                                        } else {
                                            // Buat predikat manual jika method tidak ada
                                            if ($nilai >= 90) $predikat = 'A';
                                            elseif ($nilai >= 80) $predikat = 'B';
                                            elseif ($nilai >= 70) $predikat = 'C';
                                            elseif ($nilai >= 60) $predikat = 'D';
                                            else $predikat = 'E';
                                        }
                                    }
                                @endphp
                                {{ $predikat }}
                            </td>
                        </tr>
                        @endforeach
                </table>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-medium mb-4">Debug Info</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Tahun Ajaran ID</span>
                    <span class="font-medium">{{ session('tahun_ajaran_id') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Nilai</span>
                    <span class="font-medium">{{ $siswa->nilais->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Nilai Tahun Ajaran Aktif</span>
                    <span class="font-medium">{{ $siswa->nilais->where('tahun_ajaran_id', session('tahun_ajaran_id'))->count() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection