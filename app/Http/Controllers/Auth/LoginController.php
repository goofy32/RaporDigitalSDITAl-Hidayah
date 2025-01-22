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
        $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string',
            'role' => 'required|in:admin,guru,wali_kelas'
        ]);

        // Clear existing sessions
        Auth::guard('web')->logout();
        Auth::guard('guru')->logout();

        if ($request->role === 'admin') {
            $admin = User::where('username', $request->username)->first();
            
            if ($admin && Hash::check($request->password, $admin->password)) {
                Auth::guard('web')->login($admin);
                return redirect()->route('admin.dashboard');
            }
        } else {
            // Handle guru login (both pengajar and wali kelas)
            $guru = Guru::where('username', $request->username)->first();
            
            if ($guru && Hash::check($request->password, $guru->password)) {
                Auth::guard('guru')->login($guru);
                session(['selected_role' => $request->role]);
                
                return redirect()->route(
                    $request->role === 'wali_kelas' 
                        ? 'wali_kelas.dashboard' 
                        : 'pengajar.dashboard'
                );
            }
        }

        return back()
            ->withInput($request->only('username', 'role'))
            ->withErrors(['login' => 'Username atau password salah']);
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