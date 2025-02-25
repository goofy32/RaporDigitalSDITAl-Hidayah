<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\LingkupMateri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = MataPelajaran::with(['kelas', 'guru']);
        
        if ($request->has('search')) {
            $search = strtolower($request->search);
            $terms = explode(' ', trim($search));
            
            $query->where(function($q) use ($terms, $search) {
                // Jika kata pertama adalah "kelas"
                if (count($terms) > 0 && $terms[0] === 'kelas') {
                    $q->whereHas('kelas', function($kelasQ) use ($terms) {
                        if (count($terms) > 1 && is_numeric($terms[1])) {
                            // Jika ada nomor kelas yang dispecifikkan
                            $kelasQ->where('nomor_kelas', $terms[1]);
                        }
                    });
                } else {
                    // Pencarian normal
                    $q->where('nama_pelajaran', 'LIKE', "%{$search}%")
                      ->orWhereHas('kelas', function($kelasQ) use ($search) {
                          $kelasQ->where('nama_kelas', 'LIKE', "%{$search}%")
                                ->orWhere('nomor_kelas', 'LIKE', "%{$search}%");
                      })
                      ->orWhereHas('guru', function($guruQ) use ($search) {
                          $guruQ->where('nama', 'LIKE', "%{$search}%");
                      });
                }
            });
        }
    
        // Default ordering berdasarkan kelas
        if (!$request->has('search') || 
            (count($terms) === 1 && $terms[0] === 'kelas')) {
            $query->whereHas('kelas', function($q) {
                $q->orderBy('nomor_kelas', 'asc')
                  ->orderBy('nama_kelas', 'asc');
            });
        }
        
        $subjects = $query->paginate(10);
        return view('admin.subject', compact('subjects'));
    }

    public function create()
    {
        $classes = Kelas::orderBy('nomor_kelas')
                        ->orderBy('nama_kelas')
                        ->get();
        $teachers = Guru::orderBy('nama')->get();
        return view('data.add_subject', compact('classes', 'teachers'));
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
    public function deleteLingkupMateri($id)
    {
        try {
            $lingkupMateri = LingkupMateri::findOrFail($id);
            $lingkupMateri->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
        try {
            DB::beginTransaction();
    
            $subject = MataPelajaran::findOrFail($id);
    
            $validated = $request->validate([
                'mata_pelajaran' => 'required|string|max:255',
                'kelas' => 'required|exists:kelas,id',
                'semester' => 'required|integer|min:1|max:2',
                'lingkup_materi' => 'required|array',
                'lingkup_materi.*' => 'required|string|max:255',
            ]);
    
            $subject->update([
                'nama_pelajaran' => $validated['mata_pelajaran'],
                'kelas_id' => $validated['kelas'],
                'semester' => $validated['semester'],
            ]);
    
            // Dapatkan lingkup materi yang sudah ada
            $existingLingkupMateriTitles = $subject->lingkupMateris()->pluck('judul_lingkup_materi')->toArray();
            $newLingkupMateriTitles = $validated['lingkup_materi'];
            
            // Lingkup materi yang akan dihapus (ada di existing tapi tidak ada di input baru)
            $toBeDeletedTitles = array_diff($existingLingkupMateriTitles, $newLingkupMateriTitles);
            
            // Hapus lingkup materi yang tidak ada lagi
            if (!empty($toBeDeletedTitles)) {
                $subject->lingkupMateris()
                    ->whereIn('judul_lingkup_materi', $toBeDeletedTitles)
                    ->delete();
            }
            
            // Tambahkan lingkup materi baru yang belum ada
            foreach ($newLingkupMateriTitles as $judulLingkupMateri) {
                if (!in_array($judulLingkupMateri, $existingLingkupMateriTitles)) {
                    LingkupMateri::create([
                        'mata_pelajaran_id' => $subject->id,
                        'judul_lingkup_materi' => $judulLingkupMateri,
                    ]);
                }
            }
    
            DB::commit();
            return redirect()->route('pengajar.subject.index')->with('success', 'Mata Pelajaran berhasil diperbarui!');
    
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
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
            ->orderBy('kelas_id')
            ->paginate(10);
        
        return view('pengajar.subject', compact('subjects'));
    }

    public function teacherCreate()
    {
        // Ambil ID guru yang sedang login
        $guruId = Auth::guard('guru')->id();
        
        // Ambil hanya kelas yang diajar oleh guru tersebut
        $classes = Kelas::whereHas('guru', function($query) use ($guruId) {
            $query->where('guru_id', $guruId)
                  ->where('role', 'pengajar');
        })->orderBy('nomor_kelas')
          ->orderBy('nama_kelas')
          ->get();

        return view('pengajar.add_subject', compact('classes'));
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

        try {
            DB::beginTransaction();

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

            DB::commit();
            return redirect()->route('pengajar.subject.index')
                ->with('success', 'Mata Pelajaran berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data.')
                ->withInput();
        }
    }

    public function teacherEdit($id)
    {
        // Ambil data mata pelajaran yang akan diedit
        $subject = MataPelajaran::with('lingkupMateris')->findOrFail($id);
        
        // Ambil ID guru yang sedang login
        $guruId = Auth::guard('guru')->id();
        
        // Ambil hanya kelas yang diajar oleh guru tersebut
        $classes = Kelas::whereHas('guru', function($query) use ($guruId) {
            $query->where('guru_id', $guruId)
                  ->where('role', 'pengajar');
        })
        ->orderBy('nomor_kelas')
        ->orderBy('nama_kelas')
        ->get();
    
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
            'lingkup_materi' => 'required|array',
            'lingkup_materi.*' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $subject = MataPelajaran::findOrFail($id);

            $validated = $request->validate([
                'mata_pelajaran' => 'required|string|max:255',
                'kelas' => 'required|exists:kelas,id',
                'semester' => 'required|integer|min:1|max:2',
                'lingkup_materi' => 'required|array',
                'lingkup_materi.*' => 'required|string|max:255',
            ]);

            $subject->update([
                'nama_pelajaran' => $validated['mata_pelajaran'],
                'kelas_id' => $validated['kelas'],
                'semester' => $validated['semester'],
            ]);

            // Dapatkan lingkup materi yang sudah ada
            $existingLingkupMateriTitles = $subject->lingkupMateris()->pluck('judul_lingkup_materi')->toArray();
            $newLingkupMateriTitles = $validated['lingkup_materi'];
            
            // Lingkup materi yang akan dihapus (ada di existing tapi tidak ada di input baru)
            $toBeDeletedTitles = array_diff($existingLingkupMateriTitles, $newLingkupMateriTitles);
            
            // Hapus lingkup materi yang tidak ada lagi
            if (!empty($toBeDeletedTitles)) {
                $subject->lingkupMateris()
                    ->whereIn('judul_lingkup_materi', $toBeDeletedTitles)
                    ->delete();
            }
            
            // Tambahkan lingkup materi baru yang belum ada
            foreach ($newLingkupMateriTitles as $judulLingkupMateri) {
                if (!in_array($judulLingkupMateri, $existingLingkupMateriTitles)) {
                    LingkupMateri::create([
                        'mata_pelajaran_id' => $subject->id,
                        'judul_lingkup_materi' => $judulLingkupMateri,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('pengajar.subject.index')->with('success', 'Mata Pelajaran berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function teacherDestroy($id)
    {
        $guru = auth()->guard('guru')->user();
        $subject = MataPelajaran::where('guru_id', $guru->id)
            ->findOrFail($id);

        try {
            $subject->delete();
            return redirect()->route('pengajar.subject.index')
                ->with('success', 'Mata Pelajaran berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }
}