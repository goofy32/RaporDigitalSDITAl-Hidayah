<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplyTahunAjaranFilter
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
        // Ambil tahun ajaran dari session
        $tahunAjaranId = session('tahun_ajaran_id');
        
        if ($tahunAjaranId) {
            // Simpan tahun ajaran ID sebagai variabel global dalam request
            $request->merge(['tahun_ajaran_id' => $tahunAjaranId]);
            
            // Share tahun ajaran ID ke semua view
            view()->share('selectedTahunAjaranId', $tahunAjaranId);
        }

        // Proses request
        $response = $next($request);

        return $response;
    }
}