<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Guru;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'role' => 'required|in:admin,guru,wali_kelas'
        ]);
    
        // Clear existing sessions
        Auth::guard('web')->logout();
        Auth::guard('guru')->logout();
    
        switch ($request->role) {
            case 'guru':
            case 'wali_kelas':
                $guru = Guru::where('username', $request->username)->first();
                
                if ($guru && Hash::check($request->password, $guru->password)) {
                    Auth::guard('guru')->login($guru);
                    session(['selected_role' => $request->role]);
                    
                    // Redirect berdasarkan pilihan role saat login
                    return $request->role === 'wali_kelas' 
                        ? redirect()->route('wali_kelas.dashboard')
                        : redirect()->route('pengajar.dashboard');
                }
                break;
    
            case 'admin':
                if (Auth::guard('web')->attempt([
                    'username' => $request->username,
                    'password' => $request->password
                ])) {
                    $request->session()->regenerate();
                    return redirect()->route('admin.dashboard');
                }
                break;
        }
    
        return back()
            ->withInput($request->only('username', 'role'))
            ->withErrors(['login' => 'Username atau password salah']);
    }
    public function logout(Request $request)
    {
        // Logout from all guards
        Auth::guard('web')->logout();
        Auth::guard('guru')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
