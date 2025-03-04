<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Siswa;
use Illuminate\Support\Facades\Log;

class CheckRaporAccess
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
        $siswaParam = $request->route('siswa');
        $guru = auth()->user();
        $guruId = $guru->id;
        
        // Ambil siswa dari parameter route
        if (is_numeric($siswaParam)) {
            $siswa = \App\Models\Siswa::find($siswaParam);
        } else {
            $siswa = $siswaParam;
        }
        
        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan'
            ], 404);
        }
        
        // Periksa akses wali kelas dengan query langsung ke tabel pivot
        $siswaKelasId = $siswa->kelas_id;
        $isWaliKelas = \DB::table('guru_kelas')
            ->where('guru_id', $guruId)
            ->where('kelas_id', $siswaKelasId)
            ->where('is_wali_kelas', 1)
            ->where('role', 'wali_kelas')
            ->exists();
        
        if ($isWaliKelas) {
            return $next($request);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Anda tidak memiliki akses untuk generate rapor siswa ini'
        ], 403);
    }
}