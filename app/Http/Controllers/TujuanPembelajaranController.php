<?php

namespace App\Http\Controllers;

use App\Models\TujuanPembelajaran;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;



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
       Log::info('Request received:', $request->all());
       
       try {
           $guru = auth()->guard('guru')->user();
           // Parse data dari request body
           $data = json_decode($request->getContent(), true);
           Log::info('Parsed JSON data:', $data);
           
           if (!isset($data['tpData']) || !isset($data['mataPelajaranId'])) {
               return response()->json([
                   'success' => false,
                   'message' => 'Data tidak lengkap'
               ], 400);
           }
    
           $tpData = $data['tpData'];
           $mataPelajaranId = $data['mataPelajaranId'];
    
           // Validasi
           if (!is_array($tpData) || empty($tpData) || !$mataPelajaranId) {
               return response()->json([
                   'success' => false,
                   'message' => 'Data tidak valid'
               ], 400);
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
    
           DB::beginTransaction();
           try {
               // Simpan setiap Tujuan Pembelajaran
               foreach ($tpData as $tp) {
                   if (!isset($tp['lingkupMateriId']) || !isset($tp['kodeTP']) || !isset($tp['deskripsiTP'])) {
                       throw new \Exception('Data TP tidak lengkap');
                   }
    
                   TujuanPembelajaran::create([
                       'lingkup_materi_id' => $tp['lingkupMateriId'],
                       'kode_tp' => $tp['kodeTP'],
                       'deskripsi_tp' => $tp['deskripsiTP']
                   ]);
               }
               
               DB::commit();
               
               return response()->json([
                   'success' => true,
                   'message' => 'Data berhasil disimpan'
               ]);
               
           } catch (\Exception $e) {
               DB::rollback();
               throw $e;
           }
           
       } catch (\Exception $e) {
           Log::error('Error in teacherStore:', [
               'error' => $e->getMessage(),
               'trace' => $e->getTraceAsString()
           ]);
           
           return response()->json([
               'success' => false,
               'message' => 'Terjadi kesalahan: ' . $e->getMessage()
           ], 500);
       }
    }
}