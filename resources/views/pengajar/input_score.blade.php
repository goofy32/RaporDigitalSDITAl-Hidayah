@extends('layouts.pengajar.app')

@section('title', 'Input Nilai Siswa')

@section('content')
<div class="p-4 mt-16 bg-white shadow-md rounded-lg">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700 flex items-center gap-2">
            <span>{{ $subject['class'] }} - </span>      
            <select class="border border-gray-300 rounded-lg px-4 py-2" 
                    onchange="window.location.href=this.value">
                @foreach($mataPelajaranList as $mapel)
                    <option value="{{ route('pengajar.input_score', $mapel->id) }}" 
                            {{ $mapel->id == $mataPelajaran->id ? 'selected' : '' }}>
                        {{ $mapel->nama_pelajaran }}
                    </option>
                @endforeach
            </select>
        </h2>
        


        <div class="flex gap-4">
            <!-- Tombol Simpan -->
            <button form="saveForm" 
                    type="submit" 
                    name="preview" 
                    value="true"
                    class="bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-green-800">
                Simpan & Preview
            </button>
        </div>
    </div>


    <form id="saveForm" method="POST" action="{{ route('pengajar.save_scores', $subject['id']) }}">
        @csrf
        <div class="overflow-x-auto">
            <table id="students-table" class="min-w-full text-sm text-left text-gray-500 border-collapse">
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
                            class="px-4 py-2 border text-center">Sumatif Lingkup Materi</th>
                        <th rowspan="2" class="px-4 py-2 border">NA Sumatif TP</th>
                        <th rowspan="2" class="px-4 py-2 border">NA Sumatif LM</th>
                        <!-- Tambah kolom baru -->
                        <th colspan="2" class="px-4 py-2 border text-center">Sumatif Akhir Semester</th>
                        <th rowspan="2" class="px-4 py-2 border">NA Sumatif Akhir Semester</th>
                        <th rowspan="2" class="px-4 py-2 border">Nilai Akhir (Rapor)</th>
                        <th rowspan="2" class="px-4 py-2 border">Aksi</th>
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
                        <!-- Tambah sub-header untuk Sumatif Akhir Semester -->
                        <th class="px-4 py-2 border">Nilai Tes</th>
                        <th class="px-4 py-2 border">Nilai Non-Tes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $index => $student)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border">{{ $index + 1 }}</td>
                            <td class="px-4 py-2 border student-name">{{ $student['name'] }}</td>
                            @foreach($mataPelajaran->lingkupMateris as $lm)
                                @foreach($lm->tujuanPembelajarans as $tp)
                                    <td class="px-4 py-2 border">
                                        <input type="number" 
                                               name="scores[{{ $student['id'] }}][tp][{{ $lm->id }}][{{ $tp->id }}]"
                                               class="w-20 border border-gray-300 rounded px-2 py-1 tp-score"
                                               data-lm="{{ $lm->id }}"
                                               value="{{ $existingScores[$student['id']][$lm->id][$tp->id]['nilai_tp'] ?? '' }}"
                                               min="0"
                                               max="100">
                                    </td>
                                @endforeach
                            @endforeach
                            @foreach($mataPelajaran->lingkupMateris as $lm)
                                <td class="px-4 py-2 border">
                                    <input type="number" 
                                           name="scores[{{ $student['id'] }}][lm][{{ $lm->id }}]"
                                           class="w-20 border border-gray-300 rounded px-2 py-1 lm-score"
                                           value="{{ $existingScores[$student['id']][$lm->id]['nilai_lm'] ?? '' }}"
                                           min="0"
                                           max="100">
                                </td>
                            @endforeach
                            <td class="px-4 py-2 border">
                                <input type="number" 
                                       name="scores[{{ $student['id'] }}][na_tp]"
                                       class="w-20 border border-gray-300 rounded px-2 py-1 na-tp"
                                       value="{{ $existingScores[$student['id']]['na_tp'] ?? '' }}"
                                       min="0"
                                       max="100"
                                       readonly>
                            </td>
                            <td class="px-4 py-2 border">
                                <input type="number" 
                                       name="scores[{{ $student['id'] }}][na_lm]"
                                       class="w-20 border border-gray-300 rounded px-2 py-1 na-lm"
                                       value="{{ $existingScores[$student['id']]['na_lm'] ?? '' }}"
                                       min="0"
                                       max="100"
                                       readonly>
                                                        </td>
                            <td class="px-4 py-2 border">
                                <input type="number" 
                                    name="scores[{{ $student['id'] }}][nilai_tes]"
                                    class="w-20 border border-gray-300 rounded px-2 py-1 nilai-semester"
                                    value="{{ $existingScores[$student['id']]['nilai_tes'] ?? '' }}"
                                    min="0"
                                    max="100">
                            </td>
                            <td class="px-4 py-2 border">
                                <input type="number" 
                                    name="scores[{{ $student['id'] }}][nilai_non_tes]"
                                    class="w-20 border border-gray-300 rounded px-2 py-1 nilai-semester"
                                    value="{{ $existingScores[$student['id']]['nilai_non_tes'] ?? '' }}"
                                    min="0"
                                    max="100">
                            </td>
                            <td class="px-4 py-2 border">
                                <input type="number" 
                                    name="scores[{{ $student['id'] }}][nilai_akhir]"
                                    class="w-20 border border-gray-300 rounded px-2 py-1 nilai-akhir"
                                    value="{{ $existingScores[$student['id']]['nilai_akhir_semester'] ?? '' }}"
                                    min="0"
                                    max="100"
                                    readonly>
                            </td>
                            <td class="px-4 py-2 border">
                                <input type="number" 
                                    name="scores[{{ $student['id'] }}][nilai_akhir_rapor]"
                                    class="w-20 border border-gray-300 rounded px-2 py-1 nilai-akhir-rapor"
                                    value="{{ $existingScores[$student['id']]['nilai_akhir_rapor'] ?? '' }}"
                                    readonly>
                            </td>
                            <td class="px-4 py-2 border">
                                <button type="button" 
                                        class="text-red-600 hover:text-red-800"
                                        onclick="deleteRow(this)">
                                        <img src="{{ asset('images/icons/delete.png') }}" alt="Extracurricular Icon" class="w-5 h-5">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
            <!-- Table content sama seperti sebelumnya -->
            <!-- Pastikan menggunakan $existingScores untuk menampilkan nilai yang sudah ada -->
        </div>
    </form>

</div>

<script>
    let formChanged = false;
    
    // Initialize form change tracking
    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('change', () => {
            formChanged = true;
        });
    });
    
    // Handle page leave
    window.onbeforeunload = function(e) {
        if (formChanged) {
            e.preventDefault();
            return "Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?";
        }
    };
    
    // Handle form submission
    document.getElementById('saveForm').addEventListener('submit', () => {
        formChanged = false;
    });
    
    // Calculate averages when input changes
    document.addEventListener('input', function(e) {
        if (e.target.matches('.tp-score, .lm-score, .nilai-semester')) {
            calculateAverages(e.target.closest('tr'));
            formChanged = true;
        }
    });
    
    function calculateAverages(row) {
        // Calculate NA TP
        let tpInputs = row.querySelectorAll('.tp-score');
        let tpSum = 0;
        let tpCount = 0;
    
        tpInputs.forEach(input => {
            let value = parseFloat(input.value) || 0;
            if (value > 0) {
                tpSum += value;
                tpCount++;
            }
        });
    
        let naTP = tpCount > 0 ? (tpSum / tpCount) : 0;
        row.querySelector('.na-tp').value = naTP.toFixed(2);
    
        // Calculate NA LM
        let lmInputs = row.querySelectorAll('.lm-score');
        let lmSum = 0;
        let lmCount = 0;
    
        lmInputs.forEach(input => {
            let value = parseFloat(input.value) || 0;
            if (value > 0) {
                lmSum += value;
                lmCount++;
            }
        });
    
        let naLM = lmCount > 0 ? (lmSum / lmCount) : 0;
        row.querySelector('.na-lm').value = naLM.toFixed(2);
    
        // Calculate NA Sumatif Akhir Semester
        let nilaiTes = parseFloat(row.querySelector('input[name*="[nilai_tes]"]').value) || 0;
        let nilaiNonTes = parseFloat(row.querySelector('input[name*="[nilai_non_tes]"]').value) || 0;
        
        let naAkhirSemester = 0;
        if (nilaiTes > 0 || nilaiNonTes > 0) {
            naAkhirSemester = (nilaiTes + nilaiNonTes) / 2;
            row.querySelector('input[name*="[nilai_akhir]"]').value = naAkhirSemester.toFixed(2);
        }
    
        // Calculate Nilai Akhir Rapor
        if (naTP > 0 || naLM > 0 || naAkhirSemester > 0) {
            let nilaiAkhirRapor = (naTP + naLM + naAkhirSemester) / 3;
            row.querySelector('input[name*="[nilai_akhir_rapor]"]').value = nilaiAkhirRapor.toFixed(2);
        }
    }
    
    function deleteNilai(siswaId, mapelId) {
        if (confirm('Apakah Anda yakin ingin menghapus nilai ini?')) {
            fetch('/pengajar/nilai/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    siswa_id: siswaId,
                    mata_pelajaran_id: mapelId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let row = document.querySelector(`input[name^="scores[${siswaId}]"]`).closest('tr');
                    // Reset all inputs
                    row.querySelectorAll('input[type="number"]').forEach(input => {
                        input.value = '';
                    });
                    calculateAverages(row);
                    formChanged = true;
                } else {
                    alert('Gagal menghapus nilai');
                }
            });
        }
    }
    
    // Initialize calculations on page load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('#students-table tbody tr').forEach(row => {
            calculateAverages(row);
        });
    });
    </script>

@endsection


