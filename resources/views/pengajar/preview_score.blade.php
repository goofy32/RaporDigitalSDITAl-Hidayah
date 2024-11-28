@extends('layouts.pengajar.app')

@section('title', 'Preview Nilai')

@section('content')
<div class="p-4 mt-16 bg-white shadow-md rounded-lg">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">
            {{ $mataPelajaran->kelas->nama_kelas }} - {{ $mataPelajaran->nama_pelajaran }}
        </h2>
        <div class="flex gap-4">
            <a href="{{ route('pengajar.input_score', $mataPelajaran->id) }}" 
               class="bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-green-800">
                Edit Nilai
            </a>
            <a href="{{ route('pengajar.score') }}" 
               class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                Kembali
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-500 border-collapse">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th rowspan="2" class="px-4 py-2 border">No</th>
                    <th rowspan="2" class="px-4 py-2 border">Nama Siswa</th>
                    <th colspan="{{ $mataPelajaran->lingkupMateris->sum(function($lm) { 
                        return $lm->tujuanPembelajarans->count(); 
                    }) }}" class="px-4 py-2 border text-center">
                        Sumatif Tujuan Pembelajaran
                    </th>
                    <th colspan="{{ $mataPelajaran->lingkupMateris->count() }}" 
                        class="px-4 py-2 border text-center">
                        Sumatif Lingkup Materi
                    </th>
                    <th rowspan="2" class="px-4 py-2 border">NA Sumatif TP</th>
                    <th rowspan="2" class="px-4 py-2 border">NA Sumatif LM</th>
                    <th rowspan="2" class="px-4 py-2 border">NA Sumatif Akhir Semester</th>
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
                </tr>
            </thead>
            <tbody>
                @foreach($mataPelajaran->kelas->siswas as $index => $siswa)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 border">{{ $index + 1 }}</td>
                    <td class="px-4 py-2 border">{{ $siswa->nama }}</td>
                    
                    <!-- Nilai TP -->
                    @foreach($mataPelajaran->lingkupMateris as $lm)
                        @foreach($lm->tujuanPembelajarans as $tp)
                            <td class="px-4 py-2 border text-center">
                                {{ $existingScores[$siswa->id][$lm->id][$tp->id]['nilai_tp'] ?? '-' }}
                            </td>
                        @endforeach
                    @endforeach
                    
                    <!-- Nilai LM -->
                    @foreach($mataPelajaran->lingkupMateris as $lm)
                        <td class="px-4 py-2 border text-center">
                            {{ $existingScores[$siswa->id][$lm->id]['nilai_lm'] ?? '-' }}
                        </td>
                    @endforeach
                    
                    <!-- NA TP -->
                    <td class="px-4 py-2 border text-center">
                        {{ $existingScores[$siswa->id]['na_tp'] ?? '-' }}
                    </td>
                    
                    <!-- NA LM -->
                    <td class="px-4 py-2 border text-center">
                        {{ $existingScores[$siswa->id]['na_lm'] ?? '-' }}
                    </td>
                    
                    <!-- Nilai Akhir Semester -->
                    <td class="px-4 py-2 border text-center">
                        {{ $existingScores[$siswa->id]['nilai_akhir_semester'] ?? '-' }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection