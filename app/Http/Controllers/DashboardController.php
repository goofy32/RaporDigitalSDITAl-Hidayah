<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\Nilai;
use App\Models\TujuanPembelajaran;
use App\Models\LingkupMateri;

class DashboardController extends Controller
{
    public function pengajarDashboard()
    {
        $guru = Auth::guard('guru')->user();
        
        // Hitung jumlah kelas yang diajar
        $kelasCount = MataPelajaran::where('guru_id', $guru->id)
            ->distinct('kelas_id')
            ->count('kelas_id');
            
        // Hitung jumlah mata pelajaran yang diajar
        $mapelCount = MataPelajaran::where('guru_id', $guru->id)->count();
            
        // Hitung jumlah siswa yang diajar (unique)
        $siswaCount = Siswa::whereIn('kelas_id', function($query) use ($guru) {
            $query->select('kelas_id')
                ->from('mata_pelajarans')
                ->where('guru_id', $guru->id)
                ->distinct();
        })->count();
        
        // Ambil daftar kelas
        $kelas = Kelas::whereIn('id', function($query) use ($guru) {
            $query->select('kelas_id')
                ->from('mata_pelajarans')
                ->where('guru_id', $guru->id)
                ->distinct();
        })->get();
        
        // Hitung progress keseluruhan
        $overallProgress = $this->calculateOverallProgress($guru->id);
        
        return view('pengajar.dashboard', compact(
            'kelas',
            'overallProgress',
            'kelasCount',
            'mapelCount',
            'siswaCount'
        ));
    }

    private function calculateProgressByClass($guruId, $kelasId)
    {
        // Hitung total TP
        $totalTP = DB::table('mata_pelajarans')
            ->join('lingkup_materis', 'mata_pelajarans.id', '=', 'lingkup_materis.mata_pelajaran_id')
            ->join('tujuan_pembelajarans', 'lingkup_materis.id', '=', 'tujuan_pembelajarans.lingkup_materi_id')
            ->where('mata_pelajarans.guru_id', $guruId)
            ->where('mata_pelajarans.kelas_id', $kelasId)
            ->count();

        if ($totalTP === 0) {
            return 0;
        }

        // Hitung TP yang sudah ada nilainya
        $completedTP = DB::table('mata_pelajarans')
            ->join('lingkup_materis', 'mata_pelajarans.id', '=', 'lingkup_materis.mata_pelajaran_id')
            ->join('tujuan_pembelajarans', 'lingkup_materis.id', '=', 'tujuan_pembelajarans.lingkup_materi_id')
            ->join('nilais', function($join) {
                $join->on('mata_pelajarans.id', '=', 'nilais.mata_pelajaran_id')
                    ->on('tujuan_pembelajarans.id', '=', 'nilais.tujuan_pembelajaran_id')
                    ->on('lingkup_materis.id', '=', 'nilais.lingkup_materi_id')
                    ->whereNotNull('nilais.nilai_tp');
            })
            ->where('mata_pelajarans.guru_id', $guruId)
            ->where('mata_pelajarans.kelas_id', $kelasId)
            ->count();

        return ($completedTP / $totalTP) * 100;
    }

    private function calculateOverallProgress($guruId)
    {
        $kelasIds = MataPelajaran::where('guru_id', $guruId)
            ->distinct()
            ->pluck('kelas_id');

        if ($kelasIds->isEmpty()) {
            return 0;
        }

        $totalProgress = 0;
        foreach ($kelasIds as $kelasId) {
            $totalProgress += $this->calculateProgressByClass($guruId, $kelasId);
        }

        return $totalProgress / $kelasIds->count();
    }
    
    public function getKelasProgress(Request $request, $kelasId)
    {
        $guru = Auth::guard('guru')->user();
        
        // Verifikasi akses
        $hasAccess = MataPelajaran::where('guru_id', $guru->id)
            ->where('kelas_id', $kelasId)
            ->exists();
            
        if (!$hasAccess) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $progress = $this->calculateProgressByClass($guru->id, $kelasId);
        
        // Ambil progress per mata pelajaran
        $mapelProgress = [];
        $mataPelajarans = MataPelajaran::where('guru_id', $guru->id)
            ->where('kelas_id', $kelasId)
            ->get();
            
        foreach ($mataPelajarans as $mapel) {
            $totalTP = DB::table('lingkup_materis')
                ->join('tujuan_pembelajarans', 'lingkup_materis.id', '=', 'tujuan_pembelajarans.lingkup_materi_id')
                ->where('lingkup_materis.mata_pelajaran_id', $mapel->id)
                ->count();

            $completedTP = DB::table('lingkup_materis')
                ->join('tujuan_pembelajarans', 'lingkup_materis.id', '=', 'tujuan_pembelajarans.lingkup_materi_id')
                ->join('nilais', function($join) {
                    $join->on('tujuan_pembelajarans.id', '=', 'nilais.tujuan_pembelajaran_id')
                        ->on('lingkup_materis.id', '=', 'nilais.lingkup_materi_id')
                        ->whereNotNull('nilais.nilai_tp');
                })
                ->where('lingkup_materis.mata_pelajaran_id', $mapel->id)
                ->count();

            $mapelProgress[] = [
                'nama' => $mapel->nama_pelajaran,
                'progress' => $totalTP > 0 ? ($completedTP / $totalTP) * 100 : 0
            ];
        }
        
        return response()->json([
            'progress' => $progress,
            'mapelProgress' => $mapelProgress
        ]);
    }
}