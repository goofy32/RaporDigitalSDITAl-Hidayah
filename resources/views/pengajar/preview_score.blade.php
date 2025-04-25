@extends('layouts.pengajar.app')

@section('title', 'Preview Nilai')

@section('content')
<div class="p-4 mt-16 bg-white shadow-md rounded-lg">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">
            Kelas {{ $mataPelajaran->kelas->nomor_kelas }} {{ $mataPelajaran->kelas->nama_kelas }} - {{ $mataPelajaran->nama_pelajaran }}
        </h2>
        <div class="flex gap-4">
            <a href="{{ route('pengajar.score.input_score', $mataPelajaran->id) }}"
            class="bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-green-800">
                Edit Nilai
            </a>
            <a href="{{ route('pengajar.score.index') }}" 
            class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                Kembali
            </a>
        </div>
    </div>
    <!-- Tambahkan ini di bagian atas konten, sebelum tabel -->
    <div class="flex justify-between items-center mb-4">
        <div class="bg-white rounded-lg p-4 shadow border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Informasi Penilaian</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600 mb-1">KKM: <span class="font-semibold">{{ $kkmValue }}</span></p>
                    <p class="text-sm text-gray-500">Nilai minimum untuk lulus mata pelajaran ini</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Bobot Nilai:</p>
                    <ul class="text-sm text-gray-500 list-disc list-inside ml-2">
                        <li>Sumatif TP: {{ number_format($bobotNilai->bobot_tp * 100, 0) }}%</li>
                        <li>Sumatif LM: {{ number_format($bobotNilai->bobot_lm * 100, 0) }}%</li>
                        <li>Sumatif Akhir Semester: {{ number_format($bobotNilai->bobot_as * 100, 0) }}%</li>
                    </ul>
                </div>
            </div>
            <div class="mt-3 flex items-center">
                <div class="w-4 h-4 bg-red-100 border border-red-300 mr-2"></div>
                <span class="text-sm text-gray-600">Nilai di bawah KKM</span>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-500 border-collapse">
            <!-- Header -->
            <thead>
                <tr>
                    <th rowspan="2" class="px-4 py-2 border">No</th>
                    <th rowspan="2" class="px-4 py-2 border">Nama Siswa</th>
                    <th colspan="{{ $mataPelajaran->lingkupMateris->sum(function($lm) { 
                        return $lm->tujuanPembelajarans->count(); 
                    }) }}" class="px-4 py-2 border text-center">
                        Sumatif Tujuan Pembelajaran
                    </th>
                    <th colspan="{{ $mataPelajaran->lingkupMateris->count() }}" 
                        class="px-4 py-2 border text-center">Sumatif Lingkup Materi</th>
                    <th rowspan="2" class="px-4 py-2 border">NA Sumatif TP</th>
                    <th rowspan="2" class="px-4 py-2 border">NA Sumatif LM</th>
                    <th colspan="2" class="px-4 py-2 border text-center">Sumatif Akhir Semester</th>
                    <th rowspan="2" class="px-4 py-2 border">NA Sumatif Akhir Semester</th>
                    <th rowspan="2" class="px-4 py-2 border">Nilai Akhir (Rapor)</th>
                </tr>
                <tr>
                    @foreach($mataPelajaran->lingkupMateris as $lm)
                        @foreach($lm->tujuanPembelajarans as $tp)
                            <th class="px-4 py-2 border">TP {{ $tp->kode_tp }}</th>
                        @endforeach
                    @endforeach
                    @foreach($mataPelajaran->lingkupMateris as $lm)
                        <th class="px-4 py-2 border">{{ $lm->judul_lingkup_materi }}</th>
                    @endforeach
                    <th class="px-4 py-2 border">Nilai Tes</th>
                    <th class="px-4 py-2 border">Nilai Non-Tes</th>
                </tr>
            </thead>
            
            <tbody>
                @foreach($students as $index => $student)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 border">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 border">{{ $student['name'] }}</td>
                        
                        <!-- Nilai TP -->
                        @foreach($mataPelajaran->lingkupMateris as $lm)
                            @foreach($lm->tujuanPembelajarans as $tp)
                                @php
                                    $nilaiTP = $existingScores[$student['id']]['tp'][$lm->id][$tp->id] ?? null;
                                    $belowKkm = $nilaiTP && $nilaiTP < $kkmValue;
                                @endphp
                                <td class="px-4 py-2 border text-center {{ $belowKkm ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $nilaiTP ?? '-' }}
                                </td>
                            @endforeach
                        @endforeach
                        
                        <!-- Nilai LM -->
                        @foreach($mataPelajaran->lingkupMateris as $lm)
                            @php
                                $nilaiLM = $existingScores[$student['id']]['lm'][$lm->id] ?? null;
                                $belowKkm = $nilaiLM && $nilaiLM < $kkmValue;
                            @endphp
                            <td class="px-4 py-2 border text-center {{ $belowKkm ? 'bg-red-100 text-red-800' : '' }}">
                                {{ $nilaiLM ?? '-' }}
                            </td>
                        @endforeach
                        
                        <!-- NA TP -->
                        @php
                            $naTP = $existingScores[$student['id']]['na_tp'] ?? null;
                            $belowKkm = $naTP && $naTP < $kkmValue;
                        @endphp
                        <td class="px-4 py-2 border text-center {{ $belowKkm ? 'bg-red-100 text-red-800' : '' }}">
                            {{ $naTP ?? '-' }}
                        </td>
                        
                        <!-- NA LM -->
                        @php
                            $naLM = $existingScores[$student['id']]['na_lm'] ?? null;
                            $belowKkm = $naLM && $naLM < $kkmValue;
                        @endphp
                        <td class="px-4 py-2 border text-center {{ $belowKkm ? 'bg-red-100 text-red-800' : '' }}">
                            {{ $naLM ?? '-' }}
                        </td>
                        
                        <!-- Nilai Tes -->
                        @php
                            $nilaiTes = $existingScores[$student['id']]['nilai_tes'] ?? null;
                            $belowKkm = $nilaiTes && $nilaiTes < $kkmValue;
                        @endphp
                        <td class="px-4 py-2 border text-center {{ $belowKkm ? 'bg-red-100 text-red-800' : '' }}">
                            {{ $nilaiTes ?? '-' }}
                        </td>
                        
                        <!-- Nilai Non-Tes -->
                        @php
                            $nilaiNonTes = $existingScores[$student['id']]['nilai_non_tes'] ?? null;
                            $belowKkm = $nilaiNonTes && $nilaiNonTes < $kkmValue;
                        @endphp
                        <td class="px-4 py-2 border text-center {{ $belowKkm ? 'bg-red-100 text-red-800' : '' }}">
                            {{ $nilaiNonTes ?? '-' }}
                        </td>
                        
                        <!-- NA Sumatif Akhir Semester -->
                        @php
                            $nilaiAkhirSemester = $existingScores[$student['id']]['nilai_akhir_semester'] ?? null;
                            $belowKkm = $nilaiAkhirSemester && $nilaiAkhirSemester < $kkmValue;
                        @endphp
                        <td class="px-4 py-2 border text-center {{ $belowKkm ? 'bg-red-100 text-red-800' : '' }}">
                            {{ $nilaiAkhirSemester ?? '-' }}
                        </td>
                        
                        <!-- Nilai Akhir Rapor -->
                        @php
                            $nilaiAkhirRapor = $existingScores[$student['id']]['nilai_akhir_rapor'] ?? null;
                            $belowKkm = $nilaiAkhirRapor && $nilaiAkhirRapor < $kkmValue;
                        @endphp
                        <td class="px-4 py-2 border text-center {{ $belowKkm ? 'bg-red-100 text-red-800' : '' }}">
                            {{ $nilaiAkhirRapor ?? '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Tambahkan setelah tabel di preview_score.blade.php -->
    <div class="mt-6 space-y-4">
        <h3 class="text-lg font-medium text-gray-900">Hasil Analisis Nilai</h3>
        
        @php
            $studentsNeedingRemedial = [];
            
            // Periksa semua siswa dan semua jenis nilai mereka
            foreach($students as $student) {
                $needRemedial = false;
                $belowKkmValues = [];
                
                // Cek Nilai TP
                foreach($mataPelajaran->lingkupMateris as $lm) {
                    foreach($lm->tujuanPembelajarans as $tp) {
                        $nilaiTP = $existingScores[$student['id']]['tp'][$lm->id][$tp->id] ?? null;
                        if ($nilaiTP && $nilaiTP < $kkmValue) {
                            $needRemedial = true;
                            $belowKkmValues[] = "TP {$tp->kode_tp}: {$nilaiTP}";
                        }
                    }
                }
                
                // Cek Nilai LM
                foreach($mataPelajaran->lingkupMateris as $lm) {
                    $nilaiLM = $existingScores[$student['id']]['lm'][$lm->id] ?? null;
                    if ($nilaiLM && $nilaiLM < $kkmValue) {
                        $needRemedial = true;
                        $belowKkmValues[] = "LM {$lm->judul_lingkup_materi}: {$nilaiLM}";
                    }
                }
                
                // Cek Nilai Tes, Non-Tes dan Nilai Akhir
                $nilaiTes = $existingScores[$student['id']]['nilai_tes'] ?? null;
                if ($nilaiTes && $nilaiTes < $kkmValue) {
                    $needRemedial = true;
                    $belowKkmValues[] = "Nilai Tes: {$nilaiTes}";
                }
                
                $nilaiNonTes = $existingScores[$student['id']]['nilai_non_tes'] ?? null;
                if ($nilaiNonTes && $nilaiNonTes < $kkmValue) {
                    $needRemedial = true;
                    $belowKkmValues[] = "Nilai Non-Tes: {$nilaiNonTes}";
                }
                
                $nilaiAkhirSemester = $existingScores[$student['id']]['nilai_akhir_semester'] ?? null;
                if ($nilaiAkhirSemester && $nilaiAkhirSemester < $kkmValue) {
                    $needRemedial = true;
                    $belowKkmValues[] = "Nilai Akhir Semester: {$nilaiAkhirSemester}";
                }
                
                $nilaiAkhirRapor = $existingScores[$student['id']]['nilai_akhir_rapor'] ?? null;
                if ($nilaiAkhirRapor && $nilaiAkhirRapor < $kkmValue) {
                    $needRemedial = true;
                    $belowKkmValues[] = "Nilai Akhir Rapor: {$nilaiAkhirRapor}";
                }
                
                if ($needRemedial) {
                    $studentsNeedingRemedial[$student['id']] = [
                        'name' => $student['name'],
                        'belowKkmValues' => $belowKkmValues
                    ];
                }
            }
        @endphp
        
        @foreach($studentsNeedingRemedial as $studentId => $studentData)
            <div class="bg-red-50 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            <span class="font-bold">{{ $studentData['name'] }}</span> memiliki nilai di bawah KKM {{ $kkmValue }}.
                            Siswa ini perlu melakukan remedial untuk nilai berikut: 
                            <span class="font-medium">{{ implode(', ', $studentData['belowKkmValues']) }}</span>
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
        
        @if(count($studentsNeedingRemedial) == 0)
            <div class="bg-green-50 border-l-4 border-green-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            Semua siswa memiliki nilai di atas KKM. Tidak ada siswa yang memerlukan remedial.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection