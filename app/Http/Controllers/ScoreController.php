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
    
            // Ambil semua nilai yang sudah ada
            $existingNilais = Nilai::where('mata_pelajaran_id', $id)->get();
            
            // Restruktur data nilai
            $existingScores = [];
            foreach ($existingNilais as $nilai) {
                if ($nilai->nilai_tp !== null) {
                    $existingScores[$nilai->siswa_id][$nilai->lingkup_materi_id][$nilai->tujuan_pembelajaran_id]['nilai_tp'] = $nilai->nilai_tp;
                }
                if ($nilai->nilai_lm !== null) {
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
    

    public function saveScore(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $mataPelajaran = MataPelajaran::findOrFail($id);
            $guru = Auth::guard('guru')->user();
            
            if ($mataPelajaran->guru_id !== $guru->id) {
                return back()->with('error', 'Anda tidak memiliki akses ke mata pelajaran ini');
            }
    
            foreach($request->scores as $siswaId => $scoreData) {
                // Simpan nilai TP
                if (isset($scoreData['tp'])) {
                    foreach($scoreData['tp'] as $lmId => $tpScores) {
                        foreach($tpScores as $tpId => $nilai) {
                            // Cari nilai yang sudah ada
                            $existingNilai = Nilai::where([
                                'siswa_id' => $siswaId,
                                'mata_pelajaran_id' => $id,
                                'lingkup_materi_id' => $lmId,
                                'tujuan_pembelajaran_id' => $tpId,
                            ])->first();
    
                            if ($existingNilai) {
                                // Update nilai yang sudah ada
                                $existingNilai->nilai_tp = $nilai;
                                $existingNilai->save();
                            } else if ($nilai !== null && $nilai !== '') {
                                // Buat nilai baru jika belum ada
                                Nilai::create([
                                    'siswa_id' => $siswaId,
                                    'mata_pelajaran_id' => $id,
                                    'lingkup_materi_id' => $lmId,
                                    'tujuan_pembelajaran_id' => $tpId,
                                    'nilai_tp' => $nilai,
                                ]);
                            }
                        }
                    }
                }
    
                // Simpan nilai LM
                if (isset($scoreData['lm'])) {
                    foreach($scoreData['lm'] as $lmId => $nilai) {
                        $existingNilai = Nilai::where([
                            'siswa_id' => $siswaId,
                            'mata_pelajaran_id' => $id,
                            'lingkup_materi_id' => $lmId,
                        ])->first();
    
                        if ($existingNilai) {
                            $existingNilai->nilai_lm = $nilai;
                            $existingNilai->save();
                        } else if ($nilai !== null && $nilai !== '') {
                            Nilai::create([
                                'siswa_id' => $siswaId,
                                'mata_pelajaran_id' => $id,
                                'lingkup_materi_id' => $lmId,
                                'nilai_lm' => $nilai,
                            ]);
                        }
                    }
                }
    
                // Update nilai akhir untuk siswa ini
                $nilaiAkhir = Nilai::firstOrNew([
                    'siswa_id' => $siswaId,
                    'mata_pelajaran_id' => $id,
                ]);
    
                $nilaiAkhir->na_tp = $scoreData['na_tp'] ?? null;
                $nilaiAkhir->na_lm = $scoreData['na_lm'] ?? null;
                $nilaiAkhir->nilai_akhir_semester = $scoreData['nilai_akhir'] ?? null;
                $nilaiAkhir->save();
            }
    
            DB::commit();
            return redirect()->route('pengajar.score')
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
    
            // Ambil semua nilai untuk mata pelajaran ini
            $nilaiQuery = Nilai::where('mata_pelajaran_id', $id)->get();
            
            // Restruktur data nilai dengan cara yang lebih baik
            $existingScores = [];
            foreach ($nilaiQuery as $nilai) {
                // Untuk nilai TP
                if ($nilai->nilai_tp !== null && $nilai->tujuan_pembelajaran_id !== null) {
                    $existingScores[$nilai->siswa_id][$nilai->lingkup_materi_id][$nilai->tujuan_pembelajaran_id] = [
                        'nilai_tp' => $nilai->nilai_tp
                    ];
                }
                
                // Untuk nilai LM
                if ($nilai->nilai_lm !== null && $nilai->lingkup_materi_id !== null) {
                    if (!isset($existingScores[$nilai->siswa_id][$nilai->lingkup_materi_id])) {
                        $existingScores[$nilai->siswa_id][$nilai->lingkup_materi_id] = [];
                    }
                    $existingScores[$nilai->siswa_id][$nilai->lingkup_materi_id]['nilai_lm'] = $nilai->nilai_lm;
                }
                
                // Untuk nilai NA
                if (!isset($existingScores[$nilai->siswa_id])) {
                    $existingScores[$nilai->siswa_id] = [];
                }
                
                // Simpan nilai NA TP, NA LM, dan Nilai Akhir Semester
                if ($nilai->na_tp !== null) {
                    $existingScores[$nilai->siswa_id]['na_tp'] = $nilai->na_tp;
                }
                if ($nilai->na_lm !== null) {
                    $existingScores[$nilai->siswa_id]['na_lm'] = $nilai->na_lm;
                }
                if ($nilai->nilai_akhir_semester !== null) {
                    $existingScores[$nilai->siswa_id]['nilai_akhir_semester'] = $nilai->nilai_akhir_semester;
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
                'tujuan_pembelajaran_id' => $request->tp_id,
                'lingkup_materi_id' => $request->lm_id,
            ])->first();

            if ($nilai) {
                $nilai->nilai_tp = null;
                $nilai->save();
                return response()->json(['success' => true]);
            }

            return response()->json(['success' => false, 'message' => 'Nilai tidak ditemukan']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}