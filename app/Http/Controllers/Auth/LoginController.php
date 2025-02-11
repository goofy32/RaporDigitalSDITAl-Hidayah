<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Guru;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'role' => 'required|in:admin,guru,wali_kelas'
        ]);
    
        if ($credentials['role'] === 'admin') {
            if (Auth::guard('web')->attempt([
                'username' => $credentials['username'],
                'password' => $credentials['password']
            ])) {
                return redirect()->route('admin.dashboard');
            }
        } else {
            if (Auth::guard('guru')->attempt([
                'username' => $credentials['username'],
                'password' => $credentials['password']
            ])) {
                $guru = Auth::guard('guru')->user();
                
                // Cek role wali kelas
                if ($credentials['role'] === 'wali_kelas') {
                    $isWaliKelas = $guru->kelas()
                        ->wherePivot('is_wali_kelas', true)
                        ->exists();
                        
                    if (!$isWaliKelas) {
                        Auth::guard('guru')->logout();
                        return back()->with('error', 'Anda tidak terdaftar sebagai wali kelas');
                    }
                }
                
                session(['selected_role' => $credentials['role']]);
                return redirect()->route($credentials['role'] === 'wali_kelas' ? 
                    'wali_kelas.dashboard' : 'pengajar.dashboard');
            }
        }
    
        return back()->withErrors([
            'username' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ])->withInput($request->except('password'));
    }
    public function logout(Request $request)
    {
        $message = 'Anda telah berhasil logout.';
        
        Auth::guard('web')->logout();
        Auth::guard('guru')->logout();
    
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    
        return redirect('/login')->with('success', $message);
    }
}