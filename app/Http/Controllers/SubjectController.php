<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = MataPelajaran::with(['kelas', 'guru'])->get(); 
        return view('admin.subject', compact('subjects'));
    }

    public function create()
    {
        $classes = Kelas::all();
        $teachers = Guru::all();
        $students = Siswa::all();
        return view('data.add_subject', compact('classes', 'teachers', 'students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mata_pelajaran' => 'required|string|max:255',
            'kelas' => 'required|exists:kelas,id',
            'guru_pengampu' => 'required|exists:gurus,id',
            'semester' => 'required|integer|min:1|max:2',
            'lingkup_materi' => 'required|array'
        ]);
    
        MataPelajaran::create([
            'nama_pelajaran' => $validated['mata_pelajaran'],
            'kelas_id' => $validated['kelas'],
            'guru_id' => $validated['guru_pengampu'],
            'semester' => $validated['semester'],
            'lingkup_materi' => $validated['lingkup_materi']
        ]);
    
        return redirect()->route('subject.index')
            ->with('success', 'Mata Pelajaran berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $subject = MataPelajaran::findOrFail($id);
        $classes = Kelas::all();
        $teachers = Guru::all();
        $students = Siswa::all();

        return view('data.edit_subject', compact('subject', 'classes', 'teachers', 'students'));
    }

    public function update(Request $request, $id)
    {
        $subject = MataPelajaran::findOrFail($id);
    
        $validated = $request->validate([
            'mata_pelajaran' => 'required|string|max:255',
            'kelas' => 'required|exists:kelas,id',
            'guru_pengampu' => 'required|exists:gurus,id', // Perbaiki menjadi gurus,id
            'semester' => 'required|integer|min:1|max:2',
            'lingkup_materi' => 'nullable|array',
        ]);
    
        $subject->update([
            'nama_pelajaran' => $validated['mata_pelajaran'],
            'kelas_id' => $validated['kelas'],
            'guru_id' => $validated['guru_pengampu'],
            'semester' => $validated['semester'],
            'lingkup_materi' => $validated['lingkup_materi'],
        ]);
    
        return redirect()->route('subject.index')->with('success', 'Mata Pelajaran berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $subject = MataPelajaran::findOrFail($id);
        $subject->delete();

        return redirect()->route('subject.index')->with('success', 'Mata Pelajaran berhasil dihapus!');
    }
}
