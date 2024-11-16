<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeacherController extends Controller
{
    /**
     * Menampilkan daftar pengajar
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $teachers = session('teachers', []);
        return view('admin.teacher', compact('teachers'));
    }

    /**
     * Tampilkan form tambah data pengajar.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('data.create_teacher');
    }

    /**
     * Simpan data pengajar ke session (tanpa database).
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'nip' => 'required|numeric|digits_between:6,20',
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|in:Laki-laki,Perempuan',
            'tanggal_lahir' => 'required|date',
            'no_handphone' => 'required|numeric|digits_between:10,15',
            'email' => 'required|email|max:255',
            'alamat' => 'required|string|max:500',
            'jabatan' => 'required|string|max:100',
            'kelas_mengajar' => 'required|string|max:50',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Simpan foto (jika ada)
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('uploads/photos', 'public');
            $validated['photo'] = $photoPath;
        } else {
            $validated['photo'] = null;
        }

        // Simpan ke session (data sementara)
        $teachers = session('teachers', []); // Ambil data dari session
        $validated['id'] = count($teachers) + 1; // Buat ID baru
        $teachers[] = $validated; // Tambah data baru
        session(['teachers' => $teachers]); // Simpan kembali ke session

        return redirect()->route('teacher')->with('success', 'Data pengajar berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail data pengajar berdasarkan ID.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $teachers = session('teachers', []);
        $teacher = collect($teachers)->firstWhere('id', (int)$id);

        if (!$teacher) {
            abort(404, 'Data pengajar tidak ditemukan.');
        }

        return view('data.teacher_data', compact('teacher'));
    }
}