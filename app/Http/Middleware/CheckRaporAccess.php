<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class CheckRaporAccess
{
    public function handle(Request $request, Closure $next)
    {
        // Validasi apakah route memiliki parameter siswa
        if (!$request->route('siswa')) {
            return redirect()->back()
                ->with('error', 'Data siswa tidak ditemukan');
        }

        $siswa = $request->route('siswa');

        // Validasi apakah data siswa lengkap
        if (!$siswa->hasCompleteData($request->type ?? 'UTS')) {
            return redirect()->back()
                ->with('error', 'Data siswa belum lengkap untuk generate rapor');
        }

        return $next($request);
    }
}