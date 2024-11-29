<?php

namespace App\Http\Controllers;

use App\Models\TujuanPembelajaran;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TujuanPembelajaranController extends Controller
{
    public function view($mata_pelajaran_id)
    {
        // Cek jika user adalah guru, gunakan view pengajar
        if (auth()->guard('guru')->check()) {
            $guru = auth()->guard('guru')->user();
            $mataPelajaran = MataPelajaran::with('lingkupMateris.tujuanPembelajarans')
                ->where('guru_id', $guru->id)
                ->findOrFail($mata_pelajaran_id);
            
                return view('pengajar.view_tp', compact('mataPelajaran'));

            }

        // Jika admin, gunakan view admin
        $mataPelajaran = MataPelajaran::with('lingkupMateris.tujuanPembelajarans')
            ->findOrFail($mata_pelajaran_id);
        return view('data.add_tp', compact('mataPelajaran'));
    }

    public function create($mata_pelajaran_id)
    {
        // Cek jika user adalah guru
        if (auth()->guard('guru')->check()) {
            $guru = auth()->guard('guru')->user();
            $mataPelajaran = MataPelajaran::with('lingkupMateris')
                ->where('guru_id', $guru->id)
                ->findOrFail($mata_pelajaran_id);
            
            return view('pengajar.add_tp', compact('mataPelajaran'));
        }

        // Jika admin
        $mataPelajaran = MataPelajaran::with('lingkupMateris')
            ->findOrFail($mata_pelajaran_id);
        return view('data.add_tp', compact('mataPelajaran'));
    }

    public function teacherCreate($mata_pelajaran_id)
    {
        $guru = auth()->guard('guru')->user();
        $mataPelajaran = MataPelajaran::with('lingkupMateris')
            ->where('guru_id', $guru->id)
            ->findOrFail($mata_pelajaran_id);
        
        return view('pengajar.add_tp', compact('mataPelajaran'));
    }

    public function store(Request $request)
    {
        $tpData = $request->input('tpData');
        $mataPelajaranId = $request->input('mataPelajaranId');
    
        // Log data yang diterima untuk debugging
        Log::info('tpData:', $tpData);
        Log::info('mataPelajaranId:', [$mataPelajaranId]);
    
        // Validasi
        if (!is_array($tpData) || empty($tpData) || !$mataPelajaranId) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid'], 400);
        }

        // Jika user adalah guru, validasi kepemilikan mata pelajaran
        if (auth()->guard('guru')->check()) {
            $guru = auth()->guard('guru')->user();
            $mataPelajaran = MataPelajaran::where('guru_id', $guru->id)
                ->where('id', $mataPelajaranId)
                ->first();

            if (!$mataPelajaran) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Anda tidak memiliki akses ke mata pelajaran ini'
                ], 403);
            }
        }
    
        // Simpan setiap Tujuan Pembelajaran
        foreach ($tpData as $tp) {
            TujuanPembelajaran::create([
                'lingkup_materi_id' => $tp['lingkupMateriId'],
                'kode_tp' => $tp['kodeTP'],
                'deskripsi_tp' => $tp['deskripsiTP'],
            ]);
        }
    
        return response()->json(['success' => true]);
    }

    public function teacherStore(Request $request)
    {
        $guru = auth()->guard('guru')->user();
        $tpData = $request->input('tpData');
        $mataPelajaranId = $request->input('mataPelajaranId');
    
        // Log data
        Log::info('Teacher TP Data:', $tpData);
        Log::info('Teacher MataPelajaranId:', [$mataPelajaranId]);
    
        // Validasi
        if (!is_array($tpData) || empty($tpData) || !$mataPelajaranId) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid'], 400);
        }

        // Validasi kepemilikan mata pelajaran
        $mataPelajaran = MataPelajaran::where('guru_id', $guru->id)
            ->where('id', $mataPelajaranId)
            ->first();

        if (!$mataPelajaran) {
            return response()->json([
                'success' => false, 
                'message' => 'Anda tidak memiliki akses ke mata pelajaran ini'
            ], 403);
        }
    
        // Simpan setiap Tujuan Pembelajaran
        foreach ($tpData as $tp) {
            TujuanPembelajaran::create([
                'lingkup_materi_id' => $tp['lingkupMateriId'],
                'kode_tp' => $tp['kodeTP'],
                'deskripsi_tp' => $tp['deskripsiTP'],
            ]);
        }
    
        return response()->json(['success' => true]);
    }
}