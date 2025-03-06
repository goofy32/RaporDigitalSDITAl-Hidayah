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
            // Cari guru berdasarkan username
            $guru = Guru::where('username', $credentials['username'])->first();
            
            // Jika guru ditemukan
            if ($guru && Hash::check($credentials['password'], $guru->password)) {
                
                // Jika mencoba login sebagai wali kelas
                if ($credentials['role'] === 'wali_kelas') {
                    // Cek apakah guru memiliki jabatan guru_wali
                    if ($guru->jabatan !== 'guru_wali') {
                        return back()->withErrors([
                            'role' => 'Akun ini tidak memiliki akses sebagai wali kelas. Silakan pilih role lain.'
                        ])->withInput($request->except('password'));
                    }
                    
                    // Cek apakah guru benar-benar terdaftar sebagai wali kelas di suatu kelas
                    $isWaliKelas = $guru->kelas()
                        ->wherePivot('is_wali_kelas', true)
                        ->wherePivot('role', 'wali_kelas')
                        ->exists();
                        
                    if (!$isWaliKelas) {
                        return back()->withErrors([
                            'role' => 'Akun ini belum ditugaskan sebagai wali kelas.'
                        ])->withInput($request->except('password'));
                    }
                }
                
                // Login berhasil
                Auth::guard('guru')->login($guru);
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

    public function showLoginForm()
    {
        // Cek jika user sudah login
        if (Auth::guard('web')->check()) {
            return redirect()->route('admin.dashboard');
        }
        
        if (Auth::guard('guru')->check()) {
            $selectedRole = session('selected_role');
            return $selectedRole === 'wali_kelas' 
                ? redirect()->route('wali_kelas.dashboard')
                : redirect()->route('pengajar.dashboard');
        }
        
        try {
            // Coba render view dengan path eksplisit
            $viewPath = resource_path('views/login.blade.php');
            if (file_exists($viewPath)) {
                return view('login');
            } else {
                \Log::error('Login view file does not exist at: ' . $viewPath);
                // Fallback ke welcome page jika login tidak ditemukan
                return view('welcome');
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load login view: ' . $e->getMessage());
            // Fallback ke welcome page
            return view('welcome');
        }
    }
}