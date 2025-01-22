<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        // Handle admin role
        if ($role === 'admin') {
            if (!Auth::guard('web')->check()) {
                return redirect()->route('login');
            }
            return $next($request);
        }
        
        // Handle guru dan wali_kelas roles
        if (in_array($role, ['guru', 'wali_kelas'])) {
            // Pastikan user login sebagai guru
            if (!Auth::guard('guru')->check()) {
                return redirect()->route('login');
            }

            $selectedRole = session('selected_role');
            
            // Pastikan ada role yang dipilih saat login
            if (!$selectedRole) {
                Auth::guard('guru')->logout();
                return redirect()->route('login')
                    ->with('error', 'Silakan login dan pilih role Anda');
            }

            // Pastikan role yang diminta sesuai dengan yang dipilih saat login
            if ($role === $selectedRole) {
                return $next($request);
            }
    
            // Jika mencoba akses role yang berbeda, tampilkan error
            return response()->view('errors.role-mismatch', [
                'current_role' => $selectedRole,
                'attempted_role' => $role
            ], 403);
        }
        
        // Jika role tidak dikenal
        return redirect()->route('login')
            ->with('error', 'Unauthorized access');
    }
}