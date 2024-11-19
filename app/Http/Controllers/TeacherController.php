<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
        $validated = $request->validate([
            'nuptk' => 'required|numeric|digits_between:9,15|unique:gurus,nuptk,' . ($teacher->id ?? 'NULL'),
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tanggal_lahir' => 'required|date',
            'no_handphone' => 'required|numeric|digits_between:10,15',
            'email' => 'required|email|max:255|unique:gurus,email,' . ($teacher->id ?? 'NULL'),
            'alamat' => 'required|string|max:500',
            'jabatan' => 'required|string|max:100',
            'kelas_pengajar_id' => 'required|exists:kelas,id',
            'username' => 'required|string|max:255|unique:gurus,username,' . ($teacher->id ?? 'NULL'),
            'password' => 'required|nullable|string|min:6|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Handle password
        $validated['password'] = Hash::make($request->password);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('teacher-photos', 'public');
            $validated['photo'] = $path;
        }

        Guru::create($validated);

        return redirect()->route('teacher')->with('success', 'Data guru berhasil ditambahkan');
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
        $teacher = Guru::findOrFail($id);

        $validated = $request->validate([
            'nuptk' => 'required|numeric|digits_between:9,15|unique:gurus,nuptk,' . ($teacher->id ?? 'NULL'),
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tanggal_lahir' => 'required|date',
            'no_handphone' => 'required|numeric|digits_between:10,15',
            'email' => 'required|email|max:255|unique:gurus,email,' . ($teacher->id ?? 'NULL'),
            'alamat' => 'required|string|max:500',
            'jabatan' => 'required|string|max:100',
            'kelas_pengajar_id' => 'required|exists:kelas,id',
            'username' => 'required|string|max:255|unique:gurus,username,' . ($teacher->id ?? 'NULL'),
            'password' => 'nullable|string|min:6|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Handle password update
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