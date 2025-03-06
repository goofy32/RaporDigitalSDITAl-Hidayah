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
            return view('login');
        } catch (\Exception $e) {
            \Log::error('Failed to load login view: ' . $e->getMessage());
            
            // Return full HTML form that mimics your actual login form
            return response()->make('
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Login - Rapor Digital SDIT Al-Hidayah Logam</title>
                    <style>
                        body { background-color: #f8fafc; font-family: Arial, sans-serif; }
                        .container { max-width: 400px; margin: 50px auto; padding: 20px; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                        h1 { text-align: center; color: #16a34a; }
                        input, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; }
                        button { width: 100%; padding: 10px; background: #16a34a; color: white; border: none; cursor: pointer; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>RAPOR DIGITAL<br>SDIT AL-HIDAYAH LOGAM</h1>
                        <form action="/login" method="POST">
                            <input type="hidden" name="_token" value="'.csrf_token().'">
                            <div>
                                <label for="username">Username</label>
                                <input type="text" name="username" id="username" required>
                            </div>
                            <div>
                                <label for="password">Password</label>
                                <input type="password" name="password" id="password" required>
                            </div>
                            <div>
                                <label for="role">Role</label>
                                <select name="role" id="role" required>
                                    <option value="" disabled selected>Pilih Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="guru">Guru</option>
                                    <option value="wali_kelas">Wali Kelas</option>
                                </select>
                            </div>
                            <button type="submit">Login</button>
                        </form>
                    </div>
                </body>
                </html>
            ');
        }
    }
    public function debug()
    {
        $viewPaths = config('view.paths');
        $compiledPath = config('view.compiled');
        $exists = file_exists(resource_path('views/login.blade.php'));
        
        return response()->json([
            'view_paths' => $viewPaths,
            'compiled_path' => $compiledPath,
            'login_file_exists' => $exists,
            'resource_path' => resource_path('views'),
            'base_path' => base_path(),
            'storage_path' => storage_path(),
        ]);
    }
}