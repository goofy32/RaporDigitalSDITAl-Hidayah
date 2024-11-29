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
        if ($role === 'admin' && Auth::guard('web')->check()) {
            return $next($request);
        }
        
        // Handle guru dan wali_kelas roles
        if (in_array($role, ['guru', 'wali_kelas']) && Auth::guard('guru')->check()) {
            $selectedRole = session('selected_role');
            
            if ($role === $selectedRole) {
                return $next($request);
            }

            // Jika guru mencoba akses route yang bukan rolenya saat ini
            if (in_array($role, ['guru', 'wali_kelas'])) {
                // Buat view baru untuk error
                return response()->view('errors.role-mismatch', [
                    'current_role' => $selectedRole,
                    'attempted_role' => $role
                ], 403);
            }
        }
        
        return redirect('login')->with('error', 'Unauthorized access');
    }
}