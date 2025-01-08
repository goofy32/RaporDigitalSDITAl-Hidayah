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
                'kelas.siswas',
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
    
            $students = $mataPelajaran->kelas->siswas->map(function($siswa) {
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
            $dataChanged = false;
            $guru = Auth::guard('guru')->user();
    
            // Validasi data input
            $request->validate([
                'scores' => 'required|array',
                'scores.*' => 'array'
            ]);
    
            foreach($request->scores as $siswaId => $scoreData) {
                $hasNonEmptyValue = false;
                
                // Periksa apakah ada nilai yang tidak kosong
                if (isset($scoreData['tp']) && is_array($scoreData['tp'])) {
                    foreach($scoreData['tp'] as $lmId => $tpScores) {
                        foreach($tpScores as $tpId => $nilai) {
                            if ($nilai !== null && $nilai !== '') {
                                $hasNonEmptyValue = true;
                                
                                // Simpan nilai TP
                                Nilai::updateOrCreate(
                                    [
                                        'siswa_id' => $siswaId,
                                        'mata_pelajaran_id' => $id,
                                        'lingkup_materi_id' => $lmId,
                                        'tujuan_pembelajaran_id' => $tpId,
                                    ],
                                    ['nilai_tp' => $nilai]
                                );
                                
                                $dataChanged = true;
                            }
                        }
                    }
                }
    
                // Hanya simpan nilai lainnya jika ada setidaknya satu nilai TP
                if ($hasNonEmptyValue) {
                    // Simpan nilai-nilai lain
                    $mainRecord = Nilai::updateOrCreate(
                        [
                            'siswa_id' => $siswaId,
                            'mata_pelajaran_id' => $id,
                        ],
                        [
                            'na_tp' => $scoreData['na_tp'] ?? null,
                            'na_lm' => $scoreData['na_lm'] ?? null,
                            'nilai_tes' => $scoreData['nilai_tes'] ?? null,
                            'nilai_non_tes' => $scoreData['nilai_non_tes'] ?? null,
                            'nilai_akhir_semester' => $scoreData['nilai_akhir'] ?? null,
                            'nilai_akhir_rapor' => $scoreData['nilai_akhir_rapor'] ?? null
                        ]
                    );
                    
                    $dataChanged = true;
                }
            }
    
            if (!$dataChanged) {
                DB::rollback();
                return back()
                    ->with('warning', 'Tidak ada perubahan nilai yang dilakukan');
            }
    
            DB::commit();
    
            // Clear cache progress untuk memastikan chart diupdate
            Cache::tags(['progress', 'guru-'.$guru->id])->flush();
    
            return redirect()
                ->route('pengajar.preview_score', $id)
                ->with('success', 'Nilai berhasil disimpan!');
    
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error saving scores: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menyimpan nilai');
        }
    }
    
    public function previewScore($id)
    {
        try {
            $mataPelajaran = MataPelajaran::with([
                'kelas.siswas',
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
                    ->with('warning', 'Tidak dapat menampilkan preview. Harap isi Tujuan Pembelajaran terlebih dahulu.');
            }
    
            // Inisialisasi struktur data nilai
            $existingScores = [];
            foreach ($mataPelajaran->kelas->siswas as $siswa) {
                foreach ($mataPelajaran->lingkupMateris as $lm) {
                    foreach ($lm->tujuanPembelajarans as $tp) {
                        $existingScores[$siswa->id][$lm->id][$tp->id] = [
                            'nilai_tp' => null,
                            'nilai_lm' => null,
                        ];
                    }
                    $existingScores[$siswa->id][$lm->id]['nilai_lm'] = null;
                }
                $existingScores[$siswa->id]['na_tp'] = null;
                $existingScores[$siswa->id]['na_lm'] = null;
                $existingScores[$siswa->id]['nilai_akhir_semester'] = null;
                $existingScores[$siswa->id]['nilai_tes'] = null;
                $existingScores[$siswa->id]['nilai_non_tes'] = null;
                $existingScores[$siswa->id]['nilai_akhir_rapor'] = null;
            }
    
            // Ambil semua nilai untuk mata pelajaran ini
            $nilaiQuery = Nilai::where('mata_pelajaran_id', $id)->get();
            
            // Isi struktur data dengan nilai yang ada
            foreach ($nilaiQuery as $nilai) {
                if ($nilai->nilai_tp !== null && $nilai->tujuan_pembelajaran_id !== null) {
                    $existingScores[$nilai->siswa_id][$nilai->lingkup_materi_id][$nilai->tujuan_pembelajaran_id]['nilai_tp'] = $nilai->nilai_tp;
                }
                
                if ($nilai->nilai_lm !== null && $nilai->lingkup_materi_id !== null) {
                    $existingScores[$nilai->siswa_id][$nilai->lingkup_materi_id]['nilai_lm'] = $nilai->nilai_lm;
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
                    $existingScores[$siswa->id]['nilai_akhir_rapor'] = $nilai->nilai_akhir_rapor;
                }
            }
    
            Log::info('Existing Scores:', $existingScores); // Untuk debugging
    
            return view('pengajar.preview_score', compact('mataPelajaran', 'existingScores'));
        } catch (\Exception $e) {
            Log::error('Error in ScoreController@previewScore: ' . $e->getMessage());
            return redirect()->route('pengajar.score')
                ->with('error', 'Terjadi kesalahan saat memuat data');
        }
    }
    
    public function deleteNilai(Request $request)
    {
        try {
            $nilai = Nilai::where([
                'siswa_id' => $request->siswa_id,
                'mata_pelajaran_id' => $request->mata_pelajaran_id,
            ])->update([
                'nilai_tp' => null,
                'nilai_lm' => null,
                'na_tp' => null,
                'na_lm' => null,
                'nilai_tes' => null,
                'nilai_non_tes' => null,
                'nilai_akhir_semester' => null,
                'nilai_akhir_rapor' => null
            ]);
    
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}