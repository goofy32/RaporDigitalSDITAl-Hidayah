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
        $tahunAjaranId = session('tahun_ajaran_id');
        Log::info('Guru ID: ' . $guru->id);
        
        $kelasData = Kelas::with(['mataPelajarans' => function($query) use ($guru, $tahunAjaranId) {
            $query->where('guru_id', $guru->id);
            if ($tahunAjaranId) {
                $query->where('tahun_ajaran_id', $tahunAjaranId);
            }
        }])
        ->whereHas('mataPelajarans', function($query) use ($guru, $tahunAjaranId) {
            $query->where('guru_id', $guru->id);
            if ($tahunAjaranId) {
                $query->where('tahun_ajaran_id', $tahunAjaranId);
            }
        })
        ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
            return $query->where('tahun_ajaran_id', $tahunAjaranId);
        })
        ->get();
        
        Log::info('Kelas Data:', $kelasData->toArray());
        
        return view('pengajar.score', ['kelasData' => $kelasData]);
    }

    
    public function saveScore(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $savedData = [];
            $notSavedData = []; // Tracking data yang tidak tersimpan
            $tahunAjaranId = session('tahun_ajaran_id');
    
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
                                    
                                    $nilaiData = [
                                        'nilai_tp' => $nilai
                                    ];
                                    
                                    if ($tahunAjaranId) {
                                        $nilaiData['tahun_ajaran_id'] = $tahunAjaranId;
                                    }
                                    
                                    Nilai::updateOrCreate(
                                        [
                                            'siswa_id' => $siswaId,
                                            'mata_pelajaran_id' => $id,
                                            'lingkup_materi_id' => $lmId,
                                            'tujuan_pembelajaran_id' => $tpId,
                                            'tahun_ajaran_id' => $tahunAjaranId, // Tambahkan ini untuk pencarian
                                        ],
                                        $nilaiData
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
                    }
                }
    
                // Simpan nilai agregat
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
                        if ($tahunAjaranId) {
                            $finalScores['tahun_ajaran_id'] = $tahunAjaranId;
                        }
                        
                        Nilai::updateOrCreate(
                            [
                                'siswa_id' => $siswaId,
                                'mata_pelajaran_id' => $id,
                                'tahun_ajaran_id' => $tahunAjaranId, // Tambahkan ini untuk pencarian
                            ],
                            $finalScores
                        );
    
                        foreach($finalScores as $key => $value) {
                            if ($key !== 'tahun_ajaran_id') { // Skip this from display
                                $studentData['nilai'][] = [
                                    'tipe' => str_replace('_', ' ', ucwords($key)),
                                    'nilai' => $value
                                ];
                            }
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
    
            return response()->json([
                'success' => true,
                'message' => 'Nilai berhasil disimpan!',
                'details' => $savedData,
                'warnings' => $notSavedData
            ]);
    
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error saving scores: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
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

        // Filter siswa berdasarkan tahun ajaran yang aktif
        $tahunAjaranId = session('tahun_ajaran_id');
        $siswas = $mataPelajaran->kelas->siswas;
        
        if ($tahunAjaranId) {
            $siswas = $siswas->filter(function($siswa) use ($tahunAjaranId) {
                return $siswa->kelas && $siswa->kelas->tahun_ajaran_id == $tahunAjaranId;
            });
        }
        
        $students = $siswas->sortBy('nama')->map(function($siswa) {
            return [
                'id' => $siswa->id,
                'name' => $siswa->nama
            ];
        });

        // Inisialisasi struktur data nilai
        $existingScores = [];
        foreach ($siswas as $siswa) {
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

        // Ambil semua nilai yang sudah ada dengan filter tahun ajaran jika ada
        $existingNilaisQuery = Nilai::where('mata_pelajaran_id', $id);
        if ($tahunAjaranId) {
            $existingNilaisQuery->where('tahun_ajaran_id', $tahunAjaranId);
        }
        $existingNilais = $existingNilaisQuery->get();
        
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
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
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
            $tahunAjaranId = session('tahun_ajaran_id');
            
            // Load mata pelajaran dengan relasi yang diperlukan
            $mataPelajaran = MataPelajaran::with([
                'kelas.siswas' => function($query) {
                    $query->orderBy('nama', 'asc');
                },
                'lingkupMateris.tujuanPembelajarans',
                'lingkupMateris.nilais' => function($query) use ($tahunAjaranId) {
                    $query->select(
                        'nilais.*',
                        'siswa_id',
                        'lingkup_materi_id',
                        'tujuan_pembelajaran_id',
                        'nilai_tp',
                        'nilai_lm',
                        'na_tp',
                        'na_lm',
                        'nilai_tes',
                        'nilai_non_tes',
                        'nilai_akhir_semester',
                        'nilai_akhir_rapor'
                    );
                    
                    // Filter nilai berdasarkan tahun ajaran yang aktif
                    if ($tahunAjaranId) {
                        $query->where('tahun_ajaran_id', $tahunAjaranId);
                    }
                }
            ])->findOrFail($id);
    
            // Validasi akses guru
            $guru = Auth::guard('guru')->user();
            if ($mataPelajaran->guru_id !== $guru->id) {
                return redirect()->route('pengajar.score.index')
                    ->with('error', 'Anda tidak memiliki akses ke mata pelajaran ini');
            }
    
            // Filter siswa berdasarkan tahun ajaran aktif
            $students = $mataPelajaran->kelas->siswas
                ->when($tahunAjaranId, function($collection) use ($tahunAjaranId) {
                    return $collection->filter(function($siswa) use ($tahunAjaranId) {
                        return $siswa->kelas && $siswa->kelas->tahun_ajaran_id == $tahunAjaranId;
                    });
                })
                ->sortBy('nama')
                ->map(function($siswa) {
                    return [
                        'id' => $siswa->id,
                        'name' => $siswa->nama
                    ];
                });
            
            // Inisialisasi struktur data nilai
            $existingScores = [];
            foreach ($students as $student) {
                $existingScores[$student['id']] = [
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
                    $existingScores[$student['id']]['lm'][$lm->id] = null;
                    foreach ($lm->tujuanPembelajarans as $tp) {
                        $existingScores[$student['id']]['tp'][$lm->id][$tp->id] = null;
                    }
                }
            }
    
            // Ambil semua nilai dengan single query dan filter berdasarkan tahun ajaran
            $nilaiQuery = Nilai::where('mata_pelajaran_id', $id);
            
            if ($tahunAjaranId) {
                $nilaiQuery->where('tahun_ajaran_id', $tahunAjaranId);
            }
            
            $nilais = $nilaiQuery->get()->groupBy('siswa_id');
    
            // Isi struktur data dengan nilai yang ada
            foreach ($nilais as $siswaId => $nilaiSiswa) {
                if (!isset($existingScores[$siswaId])) continue;
                
                foreach ($nilaiSiswa as $nilai) {
                    // Isi nilai TP
                    if ($nilai->nilai_tp !== null && $nilai->tujuan_pembelajaran_id && $nilai->lingkup_materi_id) {
                        $existingScores[$siswaId]['tp'][$nilai->lingkup_materi_id][$nilai->tujuan_pembelajaran_id] = $nilai->nilai_tp;
                    }
                    
                    // Isi nilai LM
                    if ($nilai->nilai_lm !== null && $nilai->lingkup_materi_id) {
                        $existingScores[$siswaId]['lm'][$nilai->lingkup_materi_id] = $nilai->nilai_lm;
                    }
                    
                    // Isi nilai agregat
                    if ($nilai->na_tp !== null) {
                        $existingScores[$siswaId]['na_tp'] = $nilai->na_tp;
                    }
                    if ($nilai->na_lm !== null) {
                        $existingScores[$siswaId]['na_lm'] = $nilai->na_lm;
                    }
                    if ($nilai->nilai_tes !== null) {
                        $existingScores[$siswaId]['nilai_tes'] = $nilai->nilai_tes;
                    }
                    if ($nilai->nilai_non_tes !== null) {
                        $existingScores[$siswaId]['nilai_non_tes'] = $nilai->nilai_non_tes;
                    }
                    if ($nilai->nilai_akhir_semester !== null) {
                        $existingScores[$siswaId]['nilai_akhir_semester'] = $nilai->nilai_akhir_semester;
                    }
                    if ($nilai->nilai_akhir_rapor !== null) {
                        $existingScores[$siswaId]['nilai_akhir_rapor'] = $nilai->nilai_akhir_rapor;
                    }
                }
            }
    
            return view('pengajar.preview_score', compact('mataPelajaran', 'existingScores', 'students'));
        } catch (\Exception $e) {
            \Log::error('Error in previewScore: ' . $e->getMessage());
            return redirect()->route('pengajar.score.index')
                ->with('error', 'Terjadi kesalahan saat memuat data: ' . $e->getMessage());
        }
    }

    public function deleteNilai(Request $request)
    {
        try {
            DB::beginTransaction();
            $tahunAjaranId = session('tahun_ajaran_id');
            
            // Query dasar
            $query = Nilai::where([
                'siswa_id' => $request->siswa_id,
                'mata_pelajaran_id' => $request->mata_pelajaran_id,
            ]);
            
            // Tambahkan filter tahun ajaran jika ada
            if ($tahunAjaranId) {
                $query->where('tahun_ajaran_id', $tahunAjaranId);
            }
            
            // Jalankan delete
            $query->delete();
    
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