<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\TahunAjaran;
use App\Models\ProfilSekolah;

class CheckBasicSetup
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
        // Skip middleware for these routes
        $excludedRoutes = [
            'profile',
            'profile.edit',
            'profile.submit',
            'tahun.ajaran.index',
            'tahun.ajaran.create',
            'tahun.ajaran.store',
            'login',
            'logout'
        ];

        if (in_array($request->route()->getName(), $excludedRoutes)) {
            return $next($request);
        }

        // Check if Profil Sekolah exists
        $profilSekolah = ProfilSekolah::first();
        if (!$profilSekolah) {
            return redirect()->route('profile.edit')
                ->with('warning', 'Silakan lengkapi data Profil Sekolah terlebih dahulu sebelum menggunakan fitur lain.');
        }

        // Check if Tahun Ajaran exists
        $tahunAjaran = TahunAjaran::first();
        if (!$tahunAjaran) {
            return redirect()->route('tahun.ajaran.create')
                ->with('warning', 'Silakan buat Tahun Ajaran terlebih dahulu sebelum menggunakan fitur lain.');
        }

        return $next($request);
    }
}