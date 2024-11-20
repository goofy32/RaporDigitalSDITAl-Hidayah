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
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
            'role' => 'required'
        ]);

        $role = $request->role;

        switch ($role) {
            case 'guru':
                $guru = Guru::where('username', $request->username)->first();
                
                if ($guru && Hash::check($request->password, $guru->password)) {
                    Auth::guard('guru')->login($guru);
                    return redirect()->intended('/pengajar/dashboard');
                }
                break;

            case 'admin':
                if (Auth::attempt($credentials)) {
                    return redirect()->intended('/admin/dashboard');
                }
                break;

            // Tambahkan case lain sesuai role yang ada
        }

        return back()->withErrors([
            'login' => 'Username atau password salah.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}