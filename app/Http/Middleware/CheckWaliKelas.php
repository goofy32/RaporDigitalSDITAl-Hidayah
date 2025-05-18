<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckWaliKelas
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $guru = auth()->guard('guru')->user();
        $tahunAjaranId = session('tahun_ajaran_id');
        
        if (!$guru) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu');
        }
        
        Log::info('Check Wali Kelas middleware', [
            'guru_id' => $guru->id,
            'tahun_ajaran_id' => $tahunAjaranId
        ]);
        
        // Periksa apakah guru ini adalah wali kelas untuk tahun ajaran terpilih
        $isWaliKelas = DB::table('guru_kelas')
            ->join('kelas', 'guru_kelas.kelas_id', '=', 'kelas.id')
            ->where('guru_kelas.guru_id', $guru->id)
            ->where('guru_kelas.is_wali_kelas', true)
            ->where('guru_kelas.role', 'wali_kelas')
            ->where('kelas.tahun_ajaran_id', $tahunAjaranId)
            ->exists();
            
        Log::info('Wali kelas check result', ['isWaliKelas' => $isWaliKelas]);
        
        if (!$isWaliKelas) {
            // Coba dapatkan semua relasi guru-kelas untuk debugging
            $allRelations = DB::table('guru_kelas')
                ->join('kelas', 'guru_kelas.kelas_id', '=', 'kelas.id')
                ->where('guru_kelas.guru_id', $guru->id)
                ->select('guru_kelas.*', 'kelas.tahun_ajaran_id')
                ->get();
                
            Log::info('All guru-kelas relations', ['relations' => $allRelations]);
            
            return redirect()->route('pengajar.dashboard')
                ->with('error', 'Anda tidak terdaftar sebagai wali kelas untuk tahun ajaran yang aktif.');
        }

        return $next($request);
    }
}
