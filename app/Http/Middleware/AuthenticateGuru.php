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

        // Tambahan pengecekan session
        if (!session()->has('selected_role')) {
            Auth::guard('guru')->logout();
            return redirect('login')
                ->with('error', 'Sesi telah berakhir. Silakan login kembali.');
        }

        return $next($request);
    }
}