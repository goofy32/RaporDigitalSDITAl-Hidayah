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
    
            // Ambil nilai yang sudah ada
            $existingScores = Nilai::where('mata_pelajaran_id', $id)
                ->get()
                ->groupBy(['siswa_id', 'lingkup_materi_id', 'tujuan_pembelajaran_id']);
    
            // Mata pelajaran untuk dropdown
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
                // Save TP scores
                if (isset($scoreData['tp'])) {
                    foreach($scoreData['tp'] as $lmId => $tpScores) {
                        foreach($tpScores as $tpNumber => $nilai) {
                            if ($nilai !== null && $nilai !== '') {
                                Nilai::updateOrCreate(
                                    [
                                        'siswa_id' => $siswaId,
                                        'mata_pelajaran_id' => $id,
                                        'lingkup_materi_id' => $lmId,
                                        'tp_number' => $tpNumber,
                                    ],
                                    ['nilai_tp' => $nilai]
                                );
                            }
                        }
                    }
                }
    
                // Save LM scores
                if (isset($scoreData['lm'])) {
                    foreach($scoreData['lm'] as $lmId => $nilai) {
                        if ($nilai !== null && $nilai !== '') {
                            Nilai::updateOrCreate(
                                [
                                    'siswa_id' => $siswaId,
                                    'mata_pelajaran_id' => $id,
                                    'lingkup_materi_id' => $lmId,
                                ],
                                ['nilai_lm' => $nilai]
                            );
                        }
                    }
                }
    
                // Save NA scores
                if (isset($scoreData['na_tp'])) {
                    Nilai::updateOrCreate(
                        [
                            'siswa_id' => $siswaId,
                            'mata_pelajaran_id' => $id,
                        ],
                        ['na_tp' => $scoreData['na_tp']]
                    );
                }
    
                if (isset($scoreData['na_lm'])) {
                    Nilai::updateOrCreate(
                        [
                            'siswa_id' => $siswaId,
                            'mata_pelajaran_id' => $id,
                        ],
                        ['na_lm' => $scoreData['na_lm']]
                    );
                }
    
                if (isset($scoreData['nilai_akhir'])) {
                    Nilai::updateOrCreate(
                        [
                            'siswa_id' => $siswaId,
                            'mata_pelajaran_id' => $id,
                        ],
                        ['nilai_akhir_semester' => $scoreData['nilai_akhir']]
                    );
                }
            }
    
            DB::commit();
            return back()->with('success', 'Nilai berhasil disimpan!');
    
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error saving scores: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menyimpan nilai');
        }
    }
}