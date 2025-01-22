<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        // Jangan redirect jika mengakses route login
        if ($request->is('login')) {
            return $next($request);
        }

        // Redirect admin
        if (Auth::guard('web')->check()) {
            return redirect()->route('admin.dashboard');
        }

        // Redirect guru berdasarkan selected_role di session
        if (Auth::guard('guru')->check()) {
            $selectedRole = session('selected_role');

            if ($selectedRole === 'wali_kelas') {
                return redirect()->route('wali_kelas.dashboard');
            } else if ($selectedRole === 'guru') {
                return redirect()->route('pengajar.dashboard');
            }

            // Jika tidak ada selected_role yang valid, logout dan redirect ke login
            Auth::guard('guru')->logout();
            return redirect()->route('login')
                ->with('error', 'Silakan login kembali dan pilih role Anda');
        }

        return $next($request);
    }
}