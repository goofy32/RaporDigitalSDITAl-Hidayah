<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
        
        if (!$guru->kelas()->wherePivot('is_wali_kelas', true)->exists()) {
            auth()->guard('guru')->logout();
            return redirect('login')
                ->with('error', 'Akses ditolak. Anda bukan wali kelas.');
        }

        return $next($request);
    }
}
