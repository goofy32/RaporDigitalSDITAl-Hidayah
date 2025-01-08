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
                    <option value="{{ route('pengajar.score.input_score', $mapel->id) }}" 
                            {{ $mapel->id == $mataPelajaran->id ? 'selected' : '' }}>
                        {{ $mapel->nama_pelajaran }}
                    </option>
                @endforeach
            </select>
        </h2>

        <div class="flex gap-4">
            <button form="saveForm" 
                    type="submit" 
                    name="preview" 
                    value="true"
                    class="bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-green-800">
                Simpan & Preview
            </button>
        </div>
    </div>

    <form id="saveForm" method="POST" action="{{ route('pengajar.score.save_scores', $subject['id']) }}">
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
                        <th class="px-4 py-2 border">Nilai Tes</th>
                        <th class="px-4 py-2 border">Nilai Non-Tes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $index => $student)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border">{{ $index + 1 }}</td>
                            <td class="px-4 py-2 border student-name">{{ $student['name'] }}</td>
                            
                            <!-- Nilai TP -->
                            @foreach($mataPelajaran->lingkupMateris as $lm)
                                @foreach($lm->tujuanPembelajarans as $tp)
                                    <td class="px-4 py-2 border">
                                        <input type="number" 
                                               name="scores[{{ $student['id'] }}][tp][{{ $lm->id }}][{{ $tp->id }}]"
                                               class="w-20 border border-gray-300 rounded px-2 py-1 tp-score"
                                               data-lm="{{ $lm->id }}"
                                               value="{{ $existingScores[$student['id']]['tp'][$lm->id][$tp->id] ?? '' }}"
                                               min="0"
                                               max="100">
                                    </td>
                                @endforeach
                            @endforeach
                            
                            <!-- Nilai LM -->
                            @foreach($mataPelajaran->lingkupMateris as $lm)
                                <td class="px-4 py-2 border">
                                    <input type="number" 
                                           name="scores[{{ $student['id'] }}][lm][{{ $lm->id }}]"
                                           class="w-20 border border-gray-300 rounded px-2 py-1 lm-score"
                                           value="{{ $existingScores[$student['id']]['lm'][$lm->id] ?? '' }}"
                                           min="0"
                                           max="100">
                                </td>
                            @endforeach
                            
                            <!-- NA TP -->
                            <td class="px-4 py-2 border">
                                <input type="number" 
                                       name="scores[{{ $student['id'] }}][na_tp]"
                                       class="w-20 border border-gray-300 rounded px-2 py-1 na-tp"
                                       value="{{ $existingScores[$student['id']]['na_tp'] ?? '' }}"
                                       min="0"
                                       max="100"
                                       readonly>
                            </td>
                            
                            <!-- NA LM -->
                            <td class="px-4 py-2 border">
                                <input type="number" 
                                       name="scores[{{ $student['id'] }}][na_lm]"
                                       class="w-20 border border-gray-300 rounded px-2 py-1 na-lm"
                                       value="{{ $existingScores[$student['id']]['na_lm'] ?? '' }}"
                                       min="0"
                                       max="100"
                                       readonly>
                            </td>
                            
                            <!-- Nilai Tes -->
                            <td class="px-4 py-2 border">
                                <input type="number" 
                                       name="scores[{{ $student['id'] }}][nilai_tes]"
                                       class="w-20 border border-gray-300 rounded px-2 py-1 nilai-semester"
                                       value="{{ $existingScores[$student['id']]['nilai_tes'] ?? '' }}"
                                       min="0"
                                       max="100">
                            </td>
                            
                            <!-- Nilai Non-Tes -->
                            <td class="px-4 py-2 border">
                                <input type="number" 
                                       name="scores[{{ $student['id'] }}][nilai_non_tes]"
                                       class="w-20 border border-gray-300 rounded px-2 py-1 nilai-semester"
                                       value="{{ $existingScores[$student['id']]['nilai_non_tes'] ?? '' }}"
                                       min="0"
                                       max="100">
                            </td>
                            
                            <!-- NA Sumatif Akhir Semester -->
                            <td class="px-4 py-2 border">
                                <input type="number" 
                                       name="scores[{{ $student['id'] }}][nilai_akhir]"
                                       class="w-20 border border-gray-300 rounded px-2 py-1 nilai-akhir"
                                       value="{{ $existingScores[$student['id']]['nilai_akhir_semester'] ?? '' }}"
                                       min="0"
                                       max="100"
                                       readonly>
                            </td>
                            
                            <!-- Nilai Akhir Rapor -->
                            <td class="px-4 py-2 border">
                                <input type="number" 
                                       name="scores[{{ $student['id'] }}][nilai_akhir_rapor]"
                                       class="w-20 border border-gray-300 rounded px-2 py-1 nilai-akhir-rapor"
                                       value="{{ $existingScores[$student['id']]['nilai_akhir_rapor'] ?? '' }}"
                                       readonly>
                            </td>
                            
                            <!-- Aksi -->
                            <td class="px-4 py-2 border">
                                <button type="button" 
                                        class="text-red-600 hover:text-red-800"
                                        onclick="deleteNilai({{ $student['id'] }}, {{ $subject['id'] }})">
                                    <img src="{{ asset('images/icons/delete.png') }}" alt="Delete Icon" class="w-5 h-5">
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
        // 1. Calculate NA Sumatif TP (Rata-rata nilai TP dengan bobot)
        let tpInputs = row.querySelectorAll('.tp-score');
        let tpSum = 0;
        let tpCount = 0;
        let validTpCount = 0;

        tpInputs.forEach(input => {
            let value = parseFloat(input.value) || 0;
            if (value > 0) {
                // Memberikan bobot lebih tinggi untuk nilai TP yang lebih tinggi
                let weight = value >= 90 ? 1.1 : 
                            value >= 80 ? 1.0 : 
                            value >= 70 ? 0.9 : 0.8;
                tpSum += (value * weight);
                validTpCount++;
            }
            tpCount++;
        });

        let naTP = validTpCount > 0 ? (tpSum / validTpCount) : 0;
        naTP = Math.min(100, naTP); // Memastikan tidak melebihi 100
        row.querySelector('.na-tp').value = naTP.toFixed(2);

        // 2. Calculate NA Sumatif LM (Rata-rata nilai LM dengan progress learning)
        let lmInputs = row.querySelectorAll('.lm-score');
        let lmSum = 0;
        let lmCount = 0;
        let validLmCount = 0;
        let progressFactor = 1.05; // Faktor progress learning

        lmInputs.forEach(input => {
            let value = parseFloat(input.value) || 0;
            if (value > 0) {
                // Menerapkan faktor progress learning
                lmSum += (value * progressFactor);
                validLmCount++;
                progressFactor += 0.05; // Meningkatkan faktor untuk setiap LM berikutnya
            }
            lmCount++;
        });

        let naLM = validLmCount > 0 ? (lmSum / validLmCount) : 0;
        naLM = Math.min(100, naLM); // Memastikan tidak melebihi 100
        row.querySelector('.na-lm').value = naLM.toFixed(2);

        // 3. Calculate NA Sumatif Akhir Semester
        let nilaiTes = parseFloat(row.querySelector('input[name*="[nilai_tes]"]').value) || 0;
        let nilaiNonTes = parseFloat(row.querySelector('input[name*="[nilai_non_tes]"]').value) || 0;
        
        // Bobot penilaian: Tes (60%) dan Non-Tes (40%)
        let naAkhirSemester = 0;
        if (nilaiTes > 0 || nilaiNonTes > 0) {
            naAkhirSemester = (nilaiTes * 0.6) + (nilaiNonTes * 0.4);
            row.querySelector('input[name*="[nilai_akhir]"]').value = naAkhirSemester.toFixed(2);
        }

        // 4. Calculate Nilai Akhir Rapor dengan pembobotan:
        // - NA Sumatif TP: 30%
        // - NA Sumatif LM: 30%
        // - NA Sumatif Akhir Semester: 40%
        if (naTP > 0 || naLM > 0 || naAkhirSemester > 0) {
            let nilaiAkhirRapor = (naTP * 0.3) + (naLM * 0.3) + (naAkhirSemester * 0.4);
            
            // Pembulatan nilai akhir
            nilaiAkhirRapor = Math.round(nilaiAkhirRapor);
            
            // Penerapan batas minimal kelulusan
            const nilaiMinimal = 75; // Batas KKM
            if (nilaiAkhirRapor < nilaiMinimal) {
                // Jika nilai di bawah KKM, berikan kesempatan perbaikan
                // dengan menambahkan poin bonus berdasarkan progress
                let progressBonus = Math.min(5, (nilaiMinimal - nilaiAkhirRapor) * 0.2);
                nilaiAkhirRapor += progressBonus;
            }

            row.querySelector('input[name*="[nilai_akhir_rapor]"]').value = Math.min(100, nilaiAkhirRapor).toFixed(0);
        }
    }
    function validateForm() {
        const form = document.getElementById('saveForm');
        const inputs = form.querySelectorAll('input[type="number"]');
        let hasEmptyValues = false;
        let isFirstInput = true;

        inputs.forEach(input => {
            if (!input.value && input.getAttribute('readonly') === null) {
                hasEmptyValues = true;
            }
        });

        if (hasEmptyValues && isFirstInput) {
            const proceed = confirm('Beberapa nilai masih kosong. Apakah Anda yakin ingin melanjutkan?');
            if (!proceed) {
                return false;
            }
        }
        return true;
    }

    document.getElementById('saveForm').addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
        }
    });

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
    function validateScore(input) {
        let value = parseFloat(input.value);
        if (value < 0) input.value = 0;
        if (value > 100) input.value = 100;
        if (isNaN(value)) input.value = '';
        calculateAverages(input.closest('tr'));
    }
    
    // Initialize calculations on page load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('#students-table tbody tr').forEach(row => {
            calculateAverages(row);
        });
    });
    </script>

@endsection


