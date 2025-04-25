<?php

namespace App\Http\Controllers;

use App\Models\Kkm;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KkmController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.dashboard')
            ->with('error', 'Pengaturan KKM tersedia melalui menu pengaturan di navbar');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'nilai' => 'required|numeric|min:0|max:100',
        ]);
        
        $tahunAjaranId = session('tahun_ajaran_id');
        
        try {
            $mataPelajaran = MataPelajaran::find($request->mata_pelajaran_id);
            
            Kkm::updateOrCreate(
                [
                    'mata_pelajaran_id' => $request->mata_pelajaran_id,
                    'tahun_ajaran_id' => $tahunAjaranId
                ],
                [
                    'nilai' => $request->nilai,
                    'kelas_id' => $mataPelajaran->kelas_id
                ]
            );
            
            return response()->json([
                'success' => true,
                'message' => 'KKM berhasil disimpan!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan KKM: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Menerapkan nilai KKM secara massal ke semua mata pelajaran
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function applyGlobalKkm(Request $request)
    {
        $request->validate([
            'nilai' => 'required|numeric|min:0|max:100',
            'overwriteExisting' => 'boolean',
        ]);
        
        $tahunAjaranId = session('tahun_ajaran_id');
        
        try {
            DB::beginTransaction();
            
            // Ambil semua mata pelajaran dari tahun ajaran yang aktif
            $query = MataPelajaran::where('tahun_ajaran_id', $tahunAjaranId);
            
            // Jika overwriteExisting = false, kita hanya mengatur mapel yang belum punya KKM
            if (!$request->overwriteExisting) {
                $mapelIdsWithKkm = Kkm::where('tahun_ajaran_id', $tahunAjaranId)
                    ->pluck('mata_pelajaran_id')
                    ->toArray();
                
                $query->whereNotIn('id', $mapelIdsWithKkm);
            }
            
            $mataPelajarans = $query->get();
            $count = 0;
            
            foreach ($mataPelajarans as $mataPelajaran) {
                Kkm::updateOrCreate(
                    [
                        'mata_pelajaran_id' => $mataPelajaran->id,
                        'tahun_ajaran_id' => $tahunAjaranId
                    ],
                    [
                        'nilai' => $request->nilai,
                        'kelas_id' => $mataPelajaran->kelas_id
                    ]
                );
                $count++;
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'KKM massal berhasil diterapkan!',
                'count' => $count
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menerapkan KKM massal: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getKkm($mapelId)
    {
        $kkm = Kkm::where('mata_pelajaran_id', $mapelId)
               ->where('tahun_ajaran_id', session('tahun_ajaran_id'))
               ->first();
               
        return response()->json(['kkm' => $kkm ? $kkm->nilai : 70]);
    }
    
    /**
     * Get list of KKM values as JSON
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getKkmList()
    {
        $tahunAjaranId = session('tahun_ajaran_id');
        
        $kkms = Kkm::with(['mataPelajaran.kelas'])
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->get();
            
        return response()->json([
            'success' => true,
            'kkms' => $kkms
        ]);
    }
    
    /**
     * Hapus KKM
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $kkm = Kkm::findOrFail($id);
            $kkm->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'KKM berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus KKM: ' . $e->getMessage()
            ], 500);
        }
    }
}