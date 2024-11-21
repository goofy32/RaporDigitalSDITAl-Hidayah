<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        // Redirect authenticated users based on their role
        if (Auth::guard('web')->check()) {
            return redirect()->route('admin.dashboard');
        }

        if (Auth::guard('guru')->check()) {
            $guru = Auth::guard('guru')->user();
            if ($guru->jabatan === 'wali_kelas') {
                return redirect()->route('wali_kelas.dashboard');
            } else {
                return redirect()->route('pengajar.dashboard');
            }
        }

        return $next($request);
    }
}