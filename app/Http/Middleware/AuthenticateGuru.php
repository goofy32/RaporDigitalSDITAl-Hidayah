<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateGuru
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('guru')->check()) {
            return redirect('login');
        }
    
        $guru = Auth::guard('guru')->user();
    
        // Jika user adalah wali kelas, pastikan dia benar-benar wali kelas dari kelasnya
        if(session('selected_role') === 'wali_kelas') {
            $kelas = Kelas::where('wali_kelas_id', $guru->id)->first();
            if(!$kelas) {
                Auth::guard('guru')->logout();
                return redirect('login')->with('error', 'Anda tidak terdaftar sebagai wali kelas');
            }
        }
    
        return $next($request);
    }
}