<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ReportTemplate;

class CheckReportTemplate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $type = $request->route('type') ?? 'UTS';
        $kelasId = null;
        
        // Jika ada siswa yang dimaksud, ambil kelas_id dari siswa
        if ($request->route('siswa')) {
            $siswa = $request->route('siswa');
            if (is_numeric($siswa)) {
                $siswa = \App\Models\Siswa::find($siswa);
            }
            
            if ($siswa) {
                $kelasId = $siswa->kelas_id;
            }
        }
        
        // Periksa template aktif berdasarkan kelas
        $templateExistsForClass = false;
        
        if ($kelasId) {
            // Cek template spesifik untuk kelas
            $templateExistsForClass = ReportTemplate::where('type', $type)
                ->where('is_active', true)
                ->where(function($query) use ($kelasId) {
                    $query->where('kelas_id', $kelasId)
                        ->orWhereHas('kelasList', function($q) use ($kelasId) {
                            $q->where('kelas_id', $kelasId);
                        });
                })
                ->exists();
        }
        
        // Jika tidak ada template spesifik untuk kelas, cek template global
        $templateExistsGlobal = ReportTemplate::where('type', $type)
            ->where('is_active', true)
            ->whereNull('kelas_id')
            ->exists();
        
        if (!$templateExistsForClass && !$templateExistsGlobal) {
            return redirect()->back()->with('error', 'Template rapor belum tersedia');
        }

        return $next($request);
    }
}