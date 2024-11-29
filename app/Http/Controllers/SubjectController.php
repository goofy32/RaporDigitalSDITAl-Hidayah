<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\LingkupMateri;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = MataPelajaran::with(['kelas', 'guru'])->paginate(10);
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
            'lingkup_materi' => 'required|array',
            'lingkup_materi.*' => 'required|string|max:255',
        ]);
    
        // Simpan Mata Pelajaran
        $mataPelajaran = MataPelajaran::create([
            'nama_pelajaran' => $validated['mata_pelajaran'],
            'kelas_id' => $validated['kelas'],
            'guru_id' => $validated['guru_pengampu'],
            'semester' => $validated['semester'],
        ]);
    
        // Simpan Lingkup Materi
        foreach ($validated['lingkup_materi'] as $judulLingkupMateri) {
            LingkupMateri::create([
                'mata_pelajaran_id' => $mataPelajaran->id,
                'judul_lingkup_materi' => $judulLingkupMateri,
            ]);
        }
    
        return redirect()->route('subject.index')
            ->with('success', 'Mata Pelajaran dan Lingkup Materi berhasil ditambahkan!');
    }
    public function teacherEdit($id)
    {
        $guru = auth()->guard('guru')->user();
        $subject = MataPelajaran::with('lingkupMateris')
            ->where('guru_id', $guru->id)
            ->findOrFail($id);
        $classes = Kelas::all();

        return view('pengajar.edit_subject', compact('subject', 'classes'));
    }

    public function teacherUpdate(Request $request, $id)
    {
        $guru = auth()->guard('guru')->user();
        $subject = MataPelajaran::where('guru_id', $guru->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'mata_pelajaran' => 'required|string|max:255',
            'kelas' => 'required|exists:kelas,id',
            'semester' => 'required|integer|min:1|max:2',
            'lingkup_materi' => 'nullable|array',
            'lingkup_materi.*' => 'required|string|max:255',
        ]);

        $subject->update([
            'nama_pelajaran' => $validated['mata_pelajaran'],
            'kelas_id' => $validated['kelas'],
            'semester' => $validated['semester'],
        ]);

        if (isset($validated['lingkup_materi'])) {
            $subject->lingkupMateris()->delete();
            foreach ($validated['lingkup_materi'] as $judulLingkupMateri) {
                LingkupMateri::create([
                    'mata_pelajaran_id' => $subject->id,
                    'judul_lingkup_materi' => $judulLingkupMateri,
                ]);
            }
        }

        return redirect()->route('pengajar.subject.index')
        ->with('success', 'Mata Pelajaran berhasil diperbarui!');
    }

    public function edit($id)
    {
        $subject = MataPelajaran::with('lingkupMateris')->findOrFail($id);
        $classes = Kelas::all();
        $teachers = Guru::all();
    
        return view('data.edit_subject', compact('subject', 'classes', 'teachers'));
    }

    public function update(Request $request, $id)
    {
        $subject = MataPelajaran::findOrFail($id);
    
        $validated = $request->validate([
            'mata_pelajaran' => 'required|string|max:255',
            'kelas' => 'required|exists:kelas,id',
            'guru_pengampu' => 'required|exists:gurus,id',
            'semester' => 'required|integer|min:1|max:2',
            'lingkup_materi' => 'nullable|array',
            'lingkup_materi.*' => 'required|string|max:255',
        ]);
    
        $subject->update([
            'nama_pelajaran' => $validated['mata_pelajaran'],
            'kelas_id' => $validated['kelas'],
            'guru_id' => $validated['guru_pengampu'],
            'semester' => $validated['semester'],
        ]);
    
        // Update Lingkup Materi
        if (isset($validated['lingkup_materi'])) {
            // Hapus Lingkup Materi lama
            $subject->lingkupMateris()->delete();
    
            // Tambahkan Lingkup Materi baru
            foreach ($validated['lingkup_materi'] as $judulLingkupMateri) {
                LingkupMateri::create([
                    'mata_pelajaran_id' => $subject->id,
                    'judul_lingkup_materi' => $judulLingkupMateri,
                ]);
            }
        }
    
        return redirect()->route('subject.index')->with('success', 'Mata Pelajaran dan Lingkup Materi berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $subject = MataPelajaran::findOrFail($id);
        $subject->delete();

        return redirect()->route('subject.index')->with('success', 'Mata Pelajaran berhasil dihapus!');
    }

    public function teacherIndex()
    {
        $guru = auth()->guard('guru')->user();
        $subjects = MataPelajaran::with(['kelas', 'guru', 'lingkupMateris'])
            ->where('guru_id', $guru->id)
            ->paginate(10);
        
        return view('pengajar.subject', compact('subjects'));
    }

    public function teacherCreate()
    {
        $guru = auth()->guard('guru')->user();
        $classes = Kelas::all();
        return view('pengajar.add_subject', compact('classes', 'guru'));
    }

    public function teacherStore(Request $request)
    {
        $guru = auth()->guard('guru')->user();
        
        $validated = $request->validate([
            'mata_pelajaran' => 'required|string|max:255',
            'kelas' => 'required|exists:kelas,id',
            'semester' => 'required|integer|min:1|max:2',
            'lingkup_materi' => 'required|array',
            'lingkup_materi.*' => 'required|string|max:255',
        ]);

        $mataPelajaran = MataPelajaran::create([
            'nama_pelajaran' => $validated['mata_pelajaran'],
            'kelas_id' => $validated['kelas'],
            'guru_id' => $guru->id,
            'semester' => $validated['semester'],
        ]);

        foreach ($validated['lingkup_materi'] as $judulLingkupMateri) {
            LingkupMateri::create([
                'mata_pelajaran_id' => $mataPelajaran->id,
                'judul_lingkup_materi' => $judulLingkupMateri,
            ]);
        }

        return redirect()->route('pengajar.subject.index')
        ->with('success', 'Mata Pelajaran berhasil ditambahkan!');
        }
    }
