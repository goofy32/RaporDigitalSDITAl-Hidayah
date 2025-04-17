@extends('layouts.pengajar.app')

@section('title', 'Preview Nilai')

@section('content')
<div class="p-4 mt-16 bg-white shadow-md rounded-lg">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-green-700">
                {{ $mataPelajaran->kelas->nomor_kelas }} {{ $mataPelajaran->kelas->nama_kelas }} - {{ $mataPelajaran->nama_pelajaran }}
            </h2>
            <div class="mt-2 flex items-center">
                <span class="text-sm text-gray-600 mr-4">KKM: {{ $kkmSetting->nilai_kkm ?? 70 }}</span>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-red-100 mr-1"></div>
                    <span class="text-sm text-gray-600">Nilai di bawah KKM</span>
                </div>
            </div>
        </div>
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
                                    $nilaiTp = $existingScores[$student['id']]['tp'][$lm->id][$tp->id] ?? '-';
                                    $isBelow = $nilaiTp !== '-' && $nilaiTp < $kkmSetting->nilai_kkm;
                                @endphp
                                <td class="px-4 py-2 border text-center {{ $isBelow ? 'bg-red-100' : '' }}">
                                    {{ $nilaiTp }}
                                </td>
                            @endforeach
                        @endforeach
                        
                        <!-- Nilai LM -->
                        @foreach($mataPelajaran->lingkupMateris as $lm)
                            @php
                                $nilaiLm = $existingScores[$student['id']]['lm'][$lm->id] ?? '-';
                                $isBelow = $nilaiLm !== '-' && $nilaiLm < $kkmSetting->nilai_kkm;
                            @endphp
                            <td class="px-4 py-2 border text-center {{ $isBelow ? 'bg-red-100' : '' }}">
                                {{ $nilaiLm }}
                            </td>
                        @endforeach
                        
                        <!-- NA TP -->
                        @php
                            $naTp = $existingScores[$student['id']]['na_tp'] ?? '-';
                            $isBelow = $naTp !== '-' && $naTp < $kkmSetting->nilai_kkm;
                        @endphp
                        <td class="px-4 py-2 border text-center {{ $isBelow ? 'bg-red-100' : '' }}">
                            {{ $naTp }}
                        </td>
                        
                        <!-- NA LM -->
                        @php
                            $naLm = $existingScores[$student['id']]['na_lm'] ?? '-';
                            $isBelow = $naLm !== '-' && $naLm < $kkmSetting->nilai_kkm;
                        @endphp
                        <td class="px-4 py-2 border text-center {{ $isBelow ? 'bg-red-100' : '' }}">
                            {{ $naLm }}
                        </td>
                        
                        <!-- Nilai Tes -->
                        @php
                            $nilaiTes = $existingScores[$student['id']]['nilai_tes'] ?? '-';
                            $isBelow = $nilaiTes !== '-' && $nilaiTes < $kkmSetting->nilai_kkm;
                        @endphp
                        <td class="px-4 py-2 border text-center {{ $isBelow ? 'bg-red-100' : '' }}">
                            {{ $nilaiTes }}
                        </td>
                        
                        <!-- Nilai Non-Tes -->
                        @php
                            $nilaiNonTes = $existingScores[$student['id']]['nilai_non_tes'] ?? '-';
                            $isBelow = $nilaiNonTes !== '-' && $nilaiNonTes < $kkmSetting->nilai_kkm;
                        @endphp
                        <td class="px-4 py-2 border text-center {{ $isBelow ? 'bg-red-100' : '' }}">
                            {{ $nilaiNonTes }}
                        </td>
                        
                        <!-- NA Sumatif Akhir Semester -->
                        @php
                            $nilaiAS = $existingScores[$student['id']]['nilai_akhir_semester'] ?? '-';
                            $isBelow = $nilaiAS !== '-' && $nilaiAS < $kkmSetting->nilai_kkm;
                        @endphp
                        <td class="px-4 py-2 border text-center {{ $isBelow ? 'bg-red-100' : '' }}">
                            {{ $nilaiAS }}
                        </td>
                        
                        <!-- Nilai Akhir Rapor -->
                        @php
                            $nilaiRapor = $existingScores[$student['id']]['nilai_akhir_rapor'] ?? '-';
                            $isBelow = $nilaiRapor !== '-' && $nilaiRapor < $kkmSetting->nilai_kkm;
                        @endphp
                        <td class="px-4 py-2 border text-center {{ $isBelow ? 'bg-red-100' : '' }}">
                            {{ $nilaiRapor }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection