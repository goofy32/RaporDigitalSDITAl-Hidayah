<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Guru;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\AuditService;

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
                // Set initial session activity
                session(['last_activity' => time()]);
                
                // Log successful admin login
                AuditService::logLogin('success', $credentials['username']);
                
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
                        AuditService::logLogin('failed', $credentials['username']);
                        
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
                        AuditService::logLogin('failed', $credentials['username']);
                        
                        return back()->withErrors([
                            'role' => 'Akun ini belum ditugaskan sebagai wali kelas.'
                        ])->withInput($request->except('password'));
                    }
                }
                
                // Login berhasil
                Auth::guard('guru')->login($guru);
                session(['selected_role' => $credentials['role']]);
                
                // Set initial session activity
                session(['last_activity' => time()]);
                
                // Log successful guru/wali_kelas login
                AuditService::logLogin('success', $credentials['username']);
                
                return redirect()->route($credentials['role'] === 'wali_kelas' ? 
                    'wali_kelas.dashboard' : 'pengajar.dashboard');
            }
        }
    
        // Log failed login attempt
        AuditService::logLogin('failed', $credentials['username']);
        
        return back()->withErrors([
            'username' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ])->withInput($request->except('password'));
    }

    public function logout(Request $request)
    {
        $message = 'Anda telah berhasil logout.';
        
        // Log logout event before actually logging out
        AuditService::logLogout();
        
        // Clear all possible auth guards
        Auth::guard('web')->logout();
        Auth::guard('guru')->logout();
    
        // Completely invalidate and regenerate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Clear all session data
        $request->session()->flush();
        
        // If it's an AJAX request (like from session timeout)
        if ($request->wantsJson() || $request->hasHeader('Turbo-Frame')) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('login')
            ]);
        }
    
        return redirect('/login')
            ->with('success', $message)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    protected function authenticated(Request $request, $user)
    {
        $profilSekolah = ProfilSekolah::first();
        $tahunAjaran = TahunAjaran::first();
        
        if (!$profilSekolah || !$tahunAjaran) {
            if (!$profilSekolah && !$tahunAjaran) {
                session()->flash('warning', 'Selamat datang! Silakan lengkapi Profil Sekolah dan buat Tahun Ajaran terlebih dahulu.');
            } elseif (!$profilSekolah) {
                session()->flash('warning', 'Selamat datang! Silakan lengkapi Profil Sekolah terlebih dahulu.');
            } else {
                session()->flash('warning', 'Selamat datang! Silakan buat Tahun Ajaran terlebih dahulu.');
            }
        }
    }
}