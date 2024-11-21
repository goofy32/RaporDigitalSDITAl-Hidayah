<?php

namespace App\Http\Controllers;

use App\Models\TujuanPembelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class TujuanPembelajaranController extends Controller
{
    public function view($mata_pelajaran_id)
    {
        // Ambil Mata Pelajaran berdasarkan ID
        $mataPelajaran = \App\Models\MataPelajaran::with('lingkupMateris.tujuanPembelajarans')
            ->findOrFail($mata_pelajaran_id);

        // Pass data ke view 'add_tp.blade.php'
        return view('data.add_tp', compact('mataPelajaran'));
    }
    public function create($mata_pelajaran_id)
    {
        // Ambil Mata Pelajaran beserta Lingkup Materi terkait
        $mataPelajaran = \App\Models\MataPelajaran::with('lingkupMateris')->findOrFail($mata_pelajaran_id);
    
        // Kirim data ke view 'add_tp.blade.php'
        return view('data.add_tp', compact('mataPelajaran'));
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
