<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        // Check for admin role
        if ($role === 'admin' && Auth::guard('web')->check()) {
            return $next($request);
        }
        
        // Check for guru and wali_kelas roles
        if (in_array($role, ['guru', 'wali_kelas']) && Auth::guard('guru')->check()) {
            $guru = Auth::guard('guru')->user();
            
            if ($role === 'wali_kelas') {
                // Check if guru has wali_kelas role
                if ($guru->jabatan === 'wali_kelas') {
                    return $next($request);
                }
            } else if ($role === 'guru') {
                // Allow both regular guru and wali_kelas to access guru routes
                if (in_array($guru->jabatan, ['guru', 'wali_kelas'])) {
                    return $next($request);
                }
            }
        }
        
        return redirect('login')->with('error', 'Unauthorized access');
    }
}

