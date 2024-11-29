<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Guru::query();
        
        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nuptk', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }
        
        // Get paginated results
        $teachers = $query->with('kelasPengajar')->paginate(10);
        
        return view('admin.teacher', compact('teachers'));
    }

    public function create()
    {
        $classes = Kelas::all();
        return view('data.create_teacher', compact('classes'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nuptk' => 'required|numeric|digits_between:9,15|unique:gurus,nuptk',
                'nama' => 'required|string|max:255',
                'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
                'tanggal_lahir' => 'required|date',
                'no_handphone' => 'required|numeric|digits_between:10,15',
                'email' => 'required|email|max:255|unique:gurus,email',
                'alamat' => 'required|string|max:500',
                'jabatan' => 'required|string|max:100',
                'kelas_pengajar_id' => 'required|exists:kelas,id',
                'username' => 'required|string|max:255|unique:gurus,username',
                'password' => 'required|string|min:6|confirmed',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ], [
                // Custom error messages
                'nuptk.required' => 'NIP wajib diisi',
                'nuptk.numeric' => 'NIP harus berupa angka',
                'nuptk.digits_between' => 'NIP harus antara 9-15 digit',
                'nama.required' => 'Nama wajib diisi',
                'jenis_kelamin.required' => 'Jenis kelamin wajib diisi',
                'tanggal_lahir.required' => 'Tanggal lahir wajib diisi',
                'no_handphone.required' => 'Nomor handphone wajib diisi',
                'email.required' => 'Email wajib diisi',
                'email.email' => 'Format email tidak valid',
                'alamat.required' => 'Alamat wajib diisi',
                'jabatan.required' => 'Jabatan wajib diisi',
                'kelas_pengajar_id.required' => 'Kelas mengajar wajib diisi',
                'username.required' => 'Username wajib diisi',
                'password.required' => 'Password wajib diisi',
                'password.min' => 'Password minimal 6 karakter',
                'password.confirmed' => 'Konfirmasi password tidak cocok',
            ]);
    
            // Handle password
            $validated['password'] = Hash::make($request->password);
    
            // Handle photo upload
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('teacher-photos', 'public');
                $validated['photo'] = $path;
            }
    
            Guru::create($validated);
    
            return redirect()->route('teacher')
                ->with('success', 'Data guru berhasil ditambahkan');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error creating teacher: ' . $e->getMessage());
            return back()
                ->with('error', 'Terjadi kesalahan sistem')
                ->withInput();
        }
    }

    public function show($id)
    {
        $teacher = Guru::with('kelasPengajar')->findOrFail($id);
        return view('data.teacher_data', compact('teacher'));
    }

    public function edit($id)
    {
        $teacher = Guru::findOrFail($id);
        $classes = Kelas::all();
        return view('data.edit_teacher', compact('teacher', 'classes'));
    }

    public function update(Request $request, $id)
    {
        try {
            $teacher = Guru::findOrFail($id);

            $validated = $request->validate([
                'nuptk' => 'required|numeric|digits_between:9,15|unique:gurus,nuptk,'.$id,
                'nama' => 'required|string|max:255',
                'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
                'tanggal_lahir' => 'required|date',
                'no_handphone' => 'required|numeric|digits_between:10,15',
                'email' => 'required|email|max:255|unique:gurus,email,'.$id,
                'alamat' => 'required|string|max:500',
                'jabatan' => 'required|string|max:100',
                'kelas_pengajar_id' => 'required|exists:kelas,id',
                'username' => 'required|string|max:255|unique:gurus,username,'.$id,
                'password' => 'nullable|string|min:6|confirmed',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ], [
                // Custom error messages
                'nuptk.required' => 'NIP wajib diisi',
                'nuptk.numeric' => 'NIP harus berupa angka',
                'nuptk.digits_between' => 'NIP harus antara 9-15 digit',
                'nama.required' => 'Nama wajib diisi',
                'jenis_kelamin.required' => 'Jenis kelamin wajib diisi',
                'tanggal_lahir.required' => 'Tanggal lahir wajib diisi',
                'no_handphone.required' => 'Nomor handphone wajib diisi',
                'email.required' => 'Email wajib diisi',
                'email.email' => 'Format email tidak valid',
                'alamat.required' => 'Alamat wajib diisi',
                'jabatan.required' => 'Jabatan wajib diisi',
                'kelas_pengajar_id.required' => 'Kelas mengajar wajib diisi',
                'username.required' => 'Username wajib diisi',
                'password.required' => 'Password wajib diisi',
                'password.min' => 'Password minimal 6 karakter',
                'password.confirmed' => 'Konfirmasi password tidak cocok',
            ]);
    
            // Handle password
            if ($request->filled('password')) {
                $validated['password'] = Hash::make($request->password);
            } else {
                unset($validated['password']);
            }
        
            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo
                if ($teacher->photo) {
                    Storage::disk('public')->delete($teacher->photo);
                }
                $path = $request->file('photo')->store('teacher-photos', 'public');
                $validated['photo'] = $path;
            }
    
    
            $teacher->update($validated);

    
            return redirect()->route('teacher')->with('success', 'Data guru berhasil diperbarui');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Mohon periksa kembali input Anda');
        } catch (\Exception $e) {
            Log::error('Error updating teacher: ' . $e->getMessage());
            return back()
                ->with('error', 'Terjadi kesalahan sistem')
                ->withInput();
        }
    }

    public function showProfile()
    {
        // Karena kita menggunakan auth guard 'guru',
        // data guru yang login sudah tersedia di view melalui Auth::guard('guru')->user()
        return view('pengajar.profile_show');
    }
    public function showWaliKelasProfile()
    {
        return view('wali_kelas.profile_show');
    }

    public function destroy($id)
    {
        $teacher = Guru::findOrFail($id);
        
        // Delete photo if exists
        if ($teacher->photo) {
            Storage::disk('public')->delete($teacher->photo);
        }
        
        $teacher->delete();

        return redirect()->route('teacher')->with('success', 'Data guru berhasil dihapus');
    }
}