<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\TahunAjaran;

class TahunAjaranMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Cek jika ada tahun ajaran yang dipilih di session
        $tahunAjaranId = session('tahun_ajaran_id');
        
        // Jika tidak ada di session, gunakan tahun ajaran aktif
        if (!$tahunAjaranId) {
            $activeTahunAjaran = TahunAjaran::where('is_active', true)->first();
            
            if ($activeTahunAjaran) {
                session(['tahun_ajaran_id' => $activeTahunAjaran->id]);
                $tahunAjaranId = $activeTahunAjaran->id;
            } else {
                // Jika tidak ada tahun ajaran aktif, gunakan tahun ajaran terbaru
                $latestTahunAjaran = TahunAjaran::orderBy('tanggal_mulai', 'desc')->first();
                if ($latestTahunAjaran) {
                    session(['tahun_ajaran_id' => $latestTahunAjaran->id]);
                    $tahunAjaranId = $latestTahunAjaran->id;
                }
            }
        }
        
        // Share tahun ajaran ke semua view
        $tahunAjaran = null;
        if ($tahunAjaranId) {
            $tahunAjaran = TahunAjaran::find($tahunAjaranId);
            view()->share('activeTahunAjaran', $tahunAjaran);
        }
        
        // Ambil daftar semua tahun ajaran (untuk dropdown selector)
        $tahunAjarans = TahunAjaran::orderBy('is_active', 'desc')
                                   ->orderBy('tanggal_mulai', 'desc')
                                   ->get();
        
        view()->share('tahunAjarans', $tahunAjarans);
        
        return $next($request);
    }
}