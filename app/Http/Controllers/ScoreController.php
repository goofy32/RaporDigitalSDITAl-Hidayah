<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\Nilai;
use App\Models\TujuanPembelajaran;
use App\Models\LingkupMateri;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ScoreController extends Controller
{
    public function index()
    {
        $guru = Auth::guard('guru')->user();
        Log::info('Guru ID: ' . $guru->id);
        
        $kelasData = Kelas::with(['mataPelajarans' => function($query) use ($guru) {
            $query->where('guru_id', $guru->id);
        }])
        ->whereHas('mataPelajarans', function($query) use ($guru) {
            $query->where('guru_id', $guru->id);
        })
        ->get();
        
        Log::info('Kelas Data:', $kelasData->toArray());
        
        return view('pengajar.score', ['kelasData' => $kelasData]);
    }
    
    public function inputScore($id)
    {
        try {
            $mataPelajaran = MataPelajaran::with([
                'kelas.siswas' => function($query) {
                    $query->orderBy('nama', 'asc');
                },
                'lingkupMateris.tujuanPembelajarans',
            ])->findOrFail($id);
    
            // Validasi akses guru
            $guru = Auth::guard('guru')->user();
            if ($mataPelajaran->guru_id !== $guru->id) {
                return redirect()->route('pengajar.score')
                    ->with('error', 'Anda tidak memiliki akses ke mata pelajaran ini');
            }
    
            // Periksa apakah ada TP untuk setiap Lingkup Materi
            $hasTp = $mataPelajaran->lingkupMateris->every(function($lm) {
                return $lm->tujuanPembelajarans->isNotEmpty();
            });
    
            if (!$hasTp) {
                return redirect()->route('pengajar.score')
                    ->with('warning', 'Harap isi Tujuan Pembelajaran untuk mata pelajaran ini terlebih dahulu.');
            }
    
            // Siapkan data
            $subject = [
                'id' => $mataPelajaran->id,
                'name' => $mataPelajaran->nama_pelajaran,
                'class' => $mataPelajaran->kelas->nama_kelas
            ];
    
            $students = $mataPelajaran->kelas->siswas
            ->sortBy('nama')  // Tambahkan sorting
            ->map(function($siswa) {
                return [
                    'id' => $siswa->id,
                    'name' => $siswa->nama
                ];
            });
    
            // Inisialisasi struktur data nilai
            $existingScores = [];
            foreach ($mataPelajaran->kelas->siswas as $siswa) {
                $existingScores[$siswa->id] = [
                    'tp' => [],
                    'lm' => [],
                    'na_tp' => null,
                    'na_lm' => null,
                    'nilai_tes' => null,
                    'nilai_non_tes' => null,
                    'nilai_akhir_semester' => null,
                    'nilai_akhir_rapor' => null
                ];
                foreach ($mataPelajaran->lingkupMateris as $lm) {
                    $existingScores[$siswa->id]['lm'][$lm->id] = null;
                    foreach ($lm->tujuanPembelajarans as $tp) {
                        $existingScores[$siswa->id]['tp'][$lm->id][$tp->id] = null;
                    }
                }
            }
    
            // Ambil semua nilai yang sudah ada
            $existingNilais = Nilai::where('mata_pelajaran_id', $id)->get();
            
            // Isi struktur data dengan nilai yang ada
            foreach ($existingNilais as $nilai) {
                if ($nilai->nilai_tp !== null) {
                    $existingScores[$nilai->siswa_id]['tp'][$nilai->lingkup_materi_id][$nilai->tujuan_pembelajaran_id] = $nilai->nilai_tp;
                }
                if ($nilai->nilai_lm !== null) {
                    $existingScores[$nilai->siswa_id]['lm'][$nilai->lingkup_materi_id] = $nilai->nilai_lm;
                }
                if ($nilai->na_tp !== null) {
                    $existingScores[$nilai->siswa_id]['na_tp'] = $nilai->na_tp;
                }
                if ($nilai->na_lm !== null) {
                    $existingScores[$nilai->siswa_id]['na_lm'] = $nilai->na_lm;
                }
                if ($nilai->nilai_akhir_semester !== null) {
                    $existingScores[$nilai->siswa_id]['nilai_akhir_semester'] = $nilai->nilai_akhir_semester;
                }
                if ($nilai->nilai_tes !== null) {
                    $existingScores[$nilai->siswa_id]['nilai_tes'] = $nilai->nilai_tes;
                }
                if ($nilai->nilai_non_tes !== null) {
                    $existingScores[$nilai->siswa_id]['nilai_non_tes'] = $nilai->nilai_non_tes;
                }
                if ($nilai->nilai_akhir_rapor !== null) {
                    $existingScores[$nilai->siswa_id]['nilai_akhir_rapor'] = $nilai->nilai_akhir_rapor;
                }
            }
    
            $mataPelajaranList = MataPelajaran::where('kelas_id', $mataPelajaran->kelas_id)
                ->where('guru_id', $guru->id)
                ->get();
    
            return view('pengajar.input_score', compact(
                'subject',
                'students',
                'mataPelajaran',
                'existingScores',
                'mataPelajaranList'
            ));
    
        } catch (\Exception $e) {
            Log::error('Error in ScoreController@inputScore: ' . $e->getMessage());
            return redirect()->route('pengajar.score')
                ->with('error', 'Terjadi kesalahan saat memuat data');
        }
    }

    private function compareScores($existingNilai, $newScoreData) 
    {
        if (!$existingNilai) return false;
    
        // Bandingkan semua jenis nilai
        return (float)$existingNilai->nilai_tp === (float)($newScoreData['tp'] ?? null) &&
               (float)$existingNilai->nilai_lm === (float)($newScoreData['lm'] ?? null) &&
               (float)$existingNilai->na_tp === (float)($newScoreData['na_tp'] ?? null) &&
               (float)$existingNilai->na_lm === (float)($newScoreData['na_lm'] ?? null) &&
               (float)$existingNilai->nilai_tes === (float)($newScoreData['nilai_tes'] ?? null) &&
               (float)$existingNilai->nilai_non_tes === (float)($newScoreData['nilai_non_tes'] ?? null) &&
               (float)$existingNilai->nilai_akhir_semester === (float)($newScoreData['nilai_akhir'] ?? null) &&
               (float)$existingNilai->nilai_akhir_rapor === (float)($newScoreData['nilai_akhir_rapor'] ?? null);
    }

    public function saveScore(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $savedData = [];
            $notSavedData = []; // Tracking data yang tidak tersimpan
    
            foreach($request->scores as $siswaId => $scoreData) {
                $studentData = [
                    'nama' => Siswa::find($siswaId)->nama,
                    'nilai' => []
                ];
                $studentNotSaved = []; // Tracking nilai yang tidak tersimpan per siswa
    
                // Simpan nilai TP dan LM
                if (isset($scoreData['tp']) && is_array($scoreData['tp'])) {
                    foreach($scoreData['tp'] as $lmId => $tpScores) {
                        foreach($tpScores as $tpId => $nilai) {
                            if ($nilai !== null && $nilai !== '' && $nilai > 0) {
                                try {
                                    $tp = TujuanPembelajaran::find($tpId);
                                    $lm = LingkupMateri::find($lmId);
                                    
                                    Nilai::updateOrCreate(
                                        [
                                            'siswa_id' => $siswaId,
                                            'mata_pelajaran_id' => $id,
                                            'lingkup_materi_id' => $lmId,
                                            'tujuan_pembelajaran_id' => $tpId,
                                        ],
                                        ['nilai_tp' => $nilai]
                                    );
    
                                    $studentData['nilai'][] = [
                                        'tipe' => 'TP',
                                        'kode' => $tp->kode_tp,
                                        'nilai' => $nilai
                                    ];
                                } catch (\Exception $e) {
                                    $studentNotSaved[] = "TP {$tp->kode_tp}: {$e->getMessage()}";
                                }
                            }
                        }
    
                        // Simpan nilai LM
                        if (isset($scoreData['lm'][$lmId]) && $scoreData['lm'][$lmId] > 0) {
                            try {
                                Nilai::updateOrCreate(
                                    [
                                        'siswa_id' => $siswaId,
                                        'mata_pelajaran_id' => $id,
                                        'lingkup_materi_id' => $lmId,
                                    ],
                                    ['nilai_lm' => $scoreData['lm'][$lmId]]
                                );
    
                                $studentData['nilai'][] = [
                                    'tipe' => 'LM',
                                    'nama' => $lm->judul_lingkup_materi,
                                    'nilai' => $scoreData['lm'][$lmId]
                                ];
                            } catch (\Exception $e) {
                                $studentNotSaved[] = "LM {$lm->judul_lingkup_materi}: {$e->getMessage()}";
                            }
                        }
                    }
                }
    
                // Simpan nilai akhir
                $finalScores = array_filter([
                    'na_tp' => $scoreData['na_tp'] ?? null,
                    'na_lm' => $scoreData['na_lm'] ?? null,
                    'nilai_tes' => $scoreData['nilai_tes'] ?? null,
                    'nilai_non_tes' => $scoreData['nilai_non_tes'] ?? null,
                    'nilai_akhir_semester' => $scoreData['nilai_akhir'] ?? null,
                    'nilai_akhir_rapor' => $scoreData['nilai_akhir_rapor'] ?? null
                ], function($value) {
                    return $value !== null && $value !== '' && $value > 0;
                });
    
                if (!empty($finalScores)) {
                    try {
                        Nilai::updateOrCreate(
                            [
                                'siswa_id' => $siswaId,
                                'mata_pelajaran_id' => $id,
                            ],
                            $finalScores
                        );
    
                        foreach($finalScores as $key => $value) {
                            $studentData['nilai'][] = [
                                'tipe' => str_replace('_', ' ', ucwords($key)),
                                'nilai' => $value
                            ];
                        }
                    } catch (\Exception $e) {
                        $studentNotSaved[] = "Nilai Akhir: {$e->getMessage()}";
                    }
                }
    
                if (!empty($studentData['nilai'])) {
                    $savedData[] = $studentData;
                }
                if (!empty($studentNotSaved)) {
                    $notSavedData[$studentData['nama']] = $studentNotSaved;
                }
            }
    
            DB::commit();
    
            $message = 'Nilai berhasil disimpan!';
            if (!empty($notSavedData)) {
                $message .= "\nBeberapa nilai tidak tersimpan:\n" . json_encode($notSavedData, JSON_PRETTY_PRINT);
                Log::warning('Some scores were not saved:', $notSavedData);
            }
    
            return response()->json([
                'success' => true,
                'message' => $message,
                'details' => $savedData,
                'warnings' => $notSavedData
            ]);
    
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error saving scores: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function hasChanges($existing, $new)
    {
        foreach ($new as $key => $value) {
            if ($existing->$key != $value) {
                return true;
            }
        }
        return false;
    }
    
    public function previewScore($id)
    {
        try {
            $mataPelajaran = MataPelajaran::with([
                'kelas.siswas' => function($query) {
                    $query->orderBy('nama', 'asc');
                },
                'lingkupMateris.tujuanPembelajarans',
            ])->findOrFail($id);
    
            // Validasi akses guru
            $guru = Auth::guard('guru')->user();
            if ($mataPelajaran->guru_id !== $guru->id) {
                return redirect()->route('pengajar.score')
                    ->with('error', 'Anda tidak memiliki akses ke mata pelajaran ini');
            }
    
            $students = $mataPelajaran->kelas->siswas
            ->sortBy('nama')
            ->map(function($siswa) {
                return [
                    'id' => $siswa->id,
                    'name' => $siswa->nama
                ];
            });
            
            // Inisialisasi struktur data nilai
            $existingScores = [];
            foreach ($mataPelajaran->kelas->siswas as $siswa) {
                $existingScores[$siswa->id] = [
                    'tp' => [],
                    'lm' => [],
                    'na_tp' => null,
                    'na_lm' => null,
                    'nilai_tes' => null,
                    'nilai_non_tes' => null,
                    'nilai_akhir_semester' => null,
                    'nilai_akhir_rapor' => null
                ];
                
                foreach ($mataPelajaran->lingkupMateris as $lm) {
                    $existingScores[$siswa->id]['lm'][$lm->id] = null;
                    foreach ($lm->tujuanPembelajarans as $tp) {
                        $existingScores[$siswa->id]['tp'][$lm->id][$tp->id] = null;
                    }
                }
            }
    
            // Ambil semua nilai
            $nilaiQuery = Nilai::where('mata_pelajaran_id', $id)
            ->where(function($q) {
                $q->where('nilai_tp', '>', 0)
                  ->orWhere('nilai_lm', '>', 0)
                  ->orWhere('na_tp', '>', 0)
                  ->orWhere('na_lm', '>', 0)
                  ->orWhere('nilai_tes', '>', 0)
                  ->orWhere('nilai_non_tes', '>', 0)
                  ->orWhere('nilai_akhir_semester', '>', 0)
                  ->orWhere('nilai_akhir_rapor', '>', 0);
            })
            ->get();
            
            Log::info('Nilai Query:', $nilaiQuery->toArray()); // Tambahkan logging untuk debug


            // Isi struktur data dengan nilai yang ada
            foreach ($nilaiQuery as $nilai) {
                if ($nilai->nilai_tp !== null && isset($existingScores[$nilai->siswa_id]['tp'][$nilai->lingkup_materi_id][$nilai->tujuan_pembelajaran_id])) {
                    $existingScores[$nilai->siswa_id]['tp'][$nilai->lingkup_materi_id][$nilai->tujuan_pembelajaran_id] = $nilai->nilai_tp;
                }
                
                if ($nilai->nilai_lm !== null && isset($existingScores[$nilai->siswa_id]['lm'][$nilai->lingkup_materi_id])) {
                    $existingScores[$nilai->siswa_id]['lm'][$nilai->lingkup_materi_id] = $nilai->nilai_lm;
                }
                
                if ($nilai->na_tp !== null) {
                    $existingScores[$nilai->siswa_id]['na_tp'] = $nilai->na_tp;
                }
                if ($nilai->na_lm !== null) {
                    $existingScores[$nilai->siswa_id]['na_lm'] = $nilai->na_lm;
                }
                if ($nilai->nilai_akhir_semester !== null) {
                    $existingScores[$nilai->siswa_id]['nilai_akhir_semester'] = $nilai->nilai_akhir_semester;
                }
                if ($nilai->nilai_tes !== null) {
                    $existingScores[$nilai->siswa_id]['nilai_tes'] = $nilai->nilai_tes;
                }
                if ($nilai->nilai_non_tes !== null) {
                    $existingScores[$nilai->siswa_id]['nilai_non_tes'] = $nilai->nilai_non_tes;
                }
                if ($nilai->nilai_akhir_rapor !== null) {
                    $existingScores[$nilai->siswa_id]['nilai_akhir_rapor'] = $nilai->nilai_akhir_rapor;
                }
            }
    
            return view('pengajar.preview_score', compact('mataPelajaran', 'existingScores', 'students'));
        } catch (\Exception $e) {
            Log::error('Error in ScoreController@previewScore: ' . $e->getMessage());
            return redirect()->route('pengajar.score')
                ->with('error', 'Terjadi kesalahan saat memuat data');
        }
    }
    
    public function deleteNilai(Request $request)
    {
        try {
            DB::beginTransaction();
            
            // Hapus semua nilai untuk siswa dan mata pelajaran tersebut
            Nilai::where([
                'siswa_id' => $request->siswa_id,
                'mata_pelajaran_id' => $request->mata_pelajaran_id,
            ])->delete();
    
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Nilai berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false, 
                'message' => 'Gagal menghapus nilai: ' . $e->getMessage()
            ]);
        }
    }
}