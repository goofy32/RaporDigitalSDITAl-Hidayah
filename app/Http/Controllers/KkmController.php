<?php

namespace App\Http\Controllers;

use App\Models\Kkm;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    /**
     * Get KKM notification settings
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotificationSettings()
    {
        try {
            // Retrieve setting from database
            $completeScoresOnly = DB::table('settings')
                ->where('key', 'kkm_notification_complete_scores_only')
                ->first();
            
            $settings = [
                'completeScoresOnly' => $completeScoresOnly ? (bool)$completeScoresOnly->value : false
            ];
            
            return response()->json([
                'success' => true,
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching KKM notification settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save KKM notification settings
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveNotificationSettings(Request $request)
    {
        try {
            $validated = $request->validate([
                'completeScoresOnly' => 'required|boolean',
            ]);
            
            // Using database to store settings
            DB::table('settings')->updateOrInsert(
                ['key' => 'kkm_notification_complete_scores_only'],
                [
                    'value' => $validated['completeScoresOnly'] ? 1 : 0,
                    'updated_at' => now()
                ]
            );
            
            // Log the change for audit
            $user = auth()->user();
            Log::info('KKM notification settings updated', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'settings' => $validated,
                'timestamp' => now()->toDateTimeString()
            ]);
            
            // Add to AuditLog if model is available
            if (class_exists('App\Models\AuditLog')) {
                \App\Models\AuditLog::create([
                    'user_type' => get_class($user),
                    'user_id' => $user->id,
                    'action' => 'update',
                    'model_type' => 'Settings',
                    'model_id' => 0, // No specific model ID for settings
                    'description' => 'Perubahan pengaturan notifikasi KKM',
                    'old_values' => null, // We're not tracking old values here
                    'new_values' => $validated,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan notifikasi KKM berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving KKM notification settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}