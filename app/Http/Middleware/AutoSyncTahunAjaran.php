<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\TahunAjaran;

class AutoSyncTahunAjaran
{
    public function handle(Request $request, Closure $next)
    {
        // Cek tahun ajaran di session
        $tahunAjaranId = session('tahun_ajaran_id');
        
        // Jika tidak ada tahun ajaran di session atau ID tidak valid, gunakan yang aktif
        if (!$tahunAjaranId || !$this->isValidTahunAjaranId($tahunAjaranId)) {
            $activeTahunAjaran = TahunAjaran::where('is_active', true)->first();
            
            if ($activeTahunAjaran) {
                session(['tahun_ajaran_id' => $activeTahunAjaran->id]);
                $tahunAjaranId = $activeTahunAjaran->id;
                \Log::info("Auto-sync tahun ajaran: Set ke tahun ajaran aktif (ID: {$tahunAjaranId})");
            } else {
                // Gunakan tahun ajaran terbaru jika tidak ada yang aktif
                $latestTahunAjaran = TahunAjaran::orderBy('id', 'desc')->first();
                if ($latestTahunAjaran) {
                    session(['tahun_ajaran_id' => $latestTahunAjaran->id]);
                    $tahunAjaranId = $latestTahunAjaran->id;
                    \Log::info("Auto-sync tahun ajaran: Set ke tahun ajaran terbaru (ID: {$tahunAjaranId})");
                } else {
                    \Log::warning("Auto-sync tahun ajaran: Tidak ada tahun ajaran yang tersedia");
                }
            }
        }
        
        // Jika melihat data mata pelajaran tertentu, periksa tahun ajaran mata pelajaran
        if ($request->route('id') && $request->is('*/score/*')) {
            try {
                $mapelId = $request->route('id');
                $mataPelajaran = \App\Models\MataPelajaran::find($mapelId);
                
                if ($mataPelajaran && $mataPelajaran->tahun_ajaran_id) {
                    // Periksa apakah tahun ajaran mata pelajaran valid
                    if ($this->isValidTahunAjaranId($mataPelajaran->tahun_ajaran_id)) {
                        // Simpan tahun ajaran mata pelajaran untuk halaman ini
                        session(['page_tahun_ajaran_id' => $mataPelajaran->tahun_ajaran_id]);
                        
                        \Log::info("Auto-sync tahun ajaran: Set page_tahun_ajaran_id ke {$mataPelajaran->tahun_ajaran_id} untuk mata pelajaran {$mapelId}");
                    } else {
                        // Tahun ajaran mata pelajaran tidak valid, perbarui ke tahun ajaran yang valid
                        $validTahunAjaran = TahunAjaran::where('is_active', true)->first() 
                            ?? TahunAjaran::orderBy('id', 'desc')->first();
                            
                        if ($validTahunAjaran) {
                            // Update mata pelajaran ke tahun ajaran yang valid
                            $mataPelajaran->tahun_ajaran_id = $validTahunAjaran->id;
                            $mataPelajaran->save();
                            
                            \Log::info("Auto-sync tahun ajaran: Memperbarui mata pelajaran {$mapelId} dari tahun ajaran {$mataPelajaran->tahun_ajaran_id} ke {$validTahunAjaran->id}");
                            
                            session(['page_tahun_ajaran_id' => $validTahunAjaran->id]);
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Auto-sync tahun ajaran error: " . $e->getMessage());
            }
        }
        
        return $next($request);
    }
    
    /**
     * Cek apakah ID tahun ajaran valid (ada di database)
     */
    private function isValidTahunAjaranId($id)
    {
        return TahunAjaran::where('id', $id)->exists();
    }
}