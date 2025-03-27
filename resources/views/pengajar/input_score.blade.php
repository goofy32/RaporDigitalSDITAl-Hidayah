@extends('layouts.pengajar.app')

@section('title', 'Input Nilai Siswa')

@section('content')
<div class="p-4 mt-16 bg-white shadow-md rounded-lg">
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
        <button type="button" 
                x-data
                @click="window.saveData()"
                x-bind:disabled="$store.formProtection.isSubmitting"
                class="bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-green-800">
            <span x-text="$store.formProtection.isSubmitting ? 'Menyimpan...' : 'Simpan & Preview'"></span>
        </button>
        </div>
    </div>

    <form id="saveForm" method="POST" action="{{ route('pengajar.score.save_scores', $subject['id']) }}" x-data="formProtection" >
        @csrf

        <input type="hidden" name="tahun_ajaran_id" value="{{ session('tahun_ajaran_id') }}">

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

                @php
                $siswas = $mataPelajaran->kelas->siswas()->orderBy('nama', 'asc')->get();
                @endphp

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

    function calculateAverages(row) {
        // 1. Calculate NA Sumatif TP
        let tpInputs = row.querySelectorAll('.tp-score');
        let tpSum = 0;
        let validTpCount = 0;

        tpInputs.forEach(input => {
            let value = parseFloat(input.value);
            if (!isNaN(value) && value > 0) {
                tpSum += value;
                validTpCount++;
            }
        });

        if (validTpCount > 0) {
            let naTP = tpSum / validTpCount;
            row.querySelector('.na-tp').value = naTP.toFixed(2);
        }

        // 2. Calculate NA Sumatif LM
        let lmInputs = row.querySelectorAll('.lm-score');
        let lmSum = 0;
        let validLmCount = 0;

        lmInputs.forEach(input => {
            let value = parseFloat(input.value);
            if (!isNaN(value) && value > 0) {
                lmSum += value;
                validLmCount++;
            }
        });

        if (validLmCount > 0) {
            let naLM = lmSum / validLmCount;
            row.querySelector('.na-lm').value = naLM.toFixed(2);
        }

        // 3. Calculate NA Sumatif Akhir Semester
        let nilaiTes = parseFloat(row.querySelector('input[name*="[nilai_tes]"]').value) || 0;
        let nilaiNonTes = parseFloat(row.querySelector('input[name*="[nilai_non_tes]"]').value) || 0;

        if (nilaiTes > 0 || nilaiNonTes > 0) {
            let nilaiAkhirSemester = (nilaiTes * 0.6) + (nilaiNonTes * 0.4);
            row.querySelector('input[name*="[nilai_akhir]"]').value = nilaiAkhirSemester.toFixed(2);
        }

        // 4. Calculate Nilai Akhir Rapor
        let naTP = parseFloat(row.querySelector('.na-tp').value) || 0;
        let naLM = parseFloat(row.querySelector('.na-lm').value) || 0;
        let nilaiAkhirSemester = parseFloat(row.querySelector('input[name*="[nilai_akhir]"]').value) || 0;

        if (naTP > 0 || naLM > 0 || nilaiAkhirSemester > 0) {
            let nilaiAkhirRapor = (naTP * 0.3) + (naLM * 0.3) + (nilaiAkhirSemester * 0.4);
            row.querySelector('input[name*="[nilai_akhir_rapor]"]').value = Math.round(nilaiAkhirRapor);
        }
    }

    function validateForm() {
        const form = document.getElementById('saveForm');
        const inputs = form.querySelectorAll('input[type="number"]:not([readonly])');
        let hasEmptyValues = false;

        inputs.forEach(input => {
            if (!input.value && !input.readOnly) {
                hasEmptyValues = true;
            }
        });

        if (hasEmptyValues) {
            return confirm('Beberapa nilai masih kosong. Apakah Anda yakin ingin melanjutkan?');
        }
        return true;
    }

    function deleteNilai(siswaId, mapelId) {
        Swal.fire({
            title: 'Hapus Nilai?',
            text: "Nilai yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/pengajar/score/nilai/delete', {
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
                        row.querySelectorAll('input[type="number"]').forEach(input => {
                            input.value = '';
                        });
                        calculateAverages(row);
                        formChanged = true;
                        
                        Swal.fire(
                            'Terhapus!',
                            'Nilai berhasil dihapus.',
                            'success'
                        );
                    } else {
                        Swal.fire(
                            'Gagal!',
                            data.message || 'Gagal menghapus nilai',
                            'error'
                        );
                    }
                });
            }
        });
    }

    window.saveData = async function() {
        try {
            if (!validateForm()) {
                return;
            }

            Swal.fire({
                title: 'Menyimpan Nilai...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const formData = new FormData(document.getElementById('saveForm'));
            
            const response = await fetch('{{ route("pengajar.score.save_scores", $subject["id"]) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();
            
            if (data.success) {
                Alpine.store('formProtection').reset();
                
                // Simpan data untuk ditampilkan nanti jika user klik detail
                const detailData = data;
                
                const result = await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Nilai berhasil disimpan!',
                    confirmButtonText: 'Lihat Preview',
                    showCancelButton: true,
                    cancelButtonText: 'Lihat Detail',
                    reverseButtons: true
                });
                
                if (result.isConfirmed) {
                    // User memilih "Lihat Preview"
                    window.location.href = '{{ route("pengajar.score.preview_score", $subject["id"]) }}';
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    // User memilih "Lihat Detail"
                    let detailMessage = '<ul class="text-left max-h-60 overflow-y-auto">';
                    detailData.details.forEach(student => {
                        detailMessage += `<li class="mb-2"><strong>${student.nama}</strong>:<br>`;
                        student.nilai.forEach(nilai => {
                            detailMessage += `- ${nilai.tipe}: ${nilai.nilai}<br>`;
                        });
                        detailMessage += '</li>';
                    });
                    detailMessage += '</ul>';

                    if (detailData.warnings && Object.keys(detailData.warnings).length > 0) {
                        detailMessage += '<div class="mt-4 p-3 bg-yellow-100 text-yellow-700 rounded">';
                        detailMessage += '<strong>Peringatan:</strong><br>';
                        Object.entries(detailData.warnings).forEach(([siswa, warnings]) => {
                            detailMessage += `<strong>${siswa}:</strong><br>`;
                            warnings.forEach(warning => {
                                detailMessage += `- ${warning}<br>`;
                            });
                        });
                        detailMessage += '</div>';
                    }
                    
                    const detailResult = await Swal.fire({
                        icon: 'info',
                        title: 'Detail Nilai',
                        html: detailMessage,
                        width: '600px',
                        confirmButtonText: 'Lihat Preview'
                    });
                    
                    if (detailResult.isConfirmed) {
                        window.location.href = '{{ route("pengajar.score.preview_score", $subject["id"]) }}';
                    }
                }
            } else {
                throw new Error(data.message || 'Terjadi kesalahan saat menyimpan nilai');
            }
        } catch (error) {
            console.error('Error:', error);
            Alpine.store('formProtection').isSubmitting = false;
            await Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: error.message || 'Terjadi kesalahan saat menyimpan nilai'
            });
        }
    };
    // Calculate averages when input changes
    document.addEventListener('input', function(e) {
        if (e.target.matches('.tp-score, .lm-score, .nilai-semester')) {
            calculateAverages(e.target.closest('tr'));
            formChanged = true;
        }
    });

    // Handle page navigation warnings
    window.addEventListener('beforeunload', (e) => {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = 'Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
            return e.returnValue;
        }
    });

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('#students-table tbody tr').forEach(row => {
            calculateAverages(row);
        });
    });

    // Handle Turbo navigation
    document.addEventListener('turbo:before-visit', (event) => {
        if (formChanged) {
            if (!confirm('Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?')) {
                event.preventDefault();
            }
        }
    });

    // Handle back/forward browser navigation
    document.addEventListener('turbo:before-cache', () => {
        if (formChanged) {
            if (!confirm('Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?')) {
                window.history.forward();
            }
        }
    });
</script>
@endsection