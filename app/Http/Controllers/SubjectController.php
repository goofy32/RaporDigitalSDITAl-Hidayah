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
        
        // Ambil semua mata pelajaran untuk validasi JavaScript
        $mataPelajaranList = MataPelajaran::select('id', 'nama_pelajaran', 'kelas_id', 'semester')->get();
        
        // Kode ini akan dimanfaatkan oleh JavaScript
        $waliKelasMap = Kelas::getWaliKelasMap();
        
        return view('data.add_subject', compact('classes', 'teachers', 'waliKelasMap', 'mataPelajaranList'));
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
    
        // Ambil data kelas
        $kelas = Kelas::find($validated['kelas']);
        $guru = Guru::find($validated['guru_pengampu']);
        
        // Validasi:
        // 1. Jika kelas memiliki wali kelas, maka guru pengampu harus wali kelas tersebut
        if ($kelas && $kelas->hasWaliKelas()) {
            $waliKelasId = $kelas->getWaliKelasId();
            
            if ($waliKelasId && $waliKelasId != $validated['guru_pengampu']) {
                return back()->withErrors([
                    'guru_pengampu' => 'Kelas ini memiliki wali kelas. Guru pengampu harus wali kelas tersebut.'
                ])->withInput();
            }
        } 
        // 2. Jika guru adalah wali kelas dari kelas lain, maka tidak bisa mengajar di kelas selain kelasnya
        else if ($guru) {
            // Cek apakah guru ini adalah wali kelas
            $kelasWali = Kelas::whereHas('waliKelas', function($query) use ($guru) {
                $query->where('guru_id', $guru->id);
            })->first();
            
            if ($kelasWali && $kelasWali->id != $validated['kelas']) {
                return back()->withErrors([
                    'guru_pengampu' => 'Guru ini adalah wali kelas dari kelas lain dan hanya dapat mengajar di kelasnya sendiri.'
                ])->withInput();
            }
        }
        
        // 3. Cek duplikasi nama mata pelajaran dalam satu kelas untuk semester yang sama
        $exists = MataPelajaran::where('kelas_id', $validated['kelas'])
            ->where('nama_pelajaran', $validated['mata_pelajaran'])
            ->where('semester', $validated['semester'])
            ->exists();
            
        if ($exists) {
            return back()->withErrors([
                'mata_pelajaran' => 'Mata pelajaran dengan nama yang sama sudah ada di kelas ini untuk semester yang sama.'
            ])->withInput();
        }
    
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
            
            // Validate user has permission (either admin or the assigned teacher)
            if (auth()->guard('guru')->check()) {
                $guru = auth()->guard('guru')->user();
                $mataPelajaran = $lingkupMateri->mataPelajaran;
                
                if ($mataPelajaran->guru_id != $guru->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak memiliki izin untuk menghapus data ini'
                    ], 403);
                }
            }
            
            // Check if there are any TPs associated with this Lingkup Materi
            if ($lingkupMateri->tujuanPembelajarans()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lingkup materi ini memiliki tujuan pembelajaran dan tidak dapat dihapus. Hapus tujuan pembelajaran terlebih dahulu.'
                ], 400);
            }
            
            $lingkupMateri->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Lingkup materi berhasil dihapus'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function edit($id)
    {
        $subject = MataPelajaran::with('lingkupMateris')->findOrFail($id);
        $classes = Kelas::all();
        $teachers = Guru::all();
    
        // Ambil semua mata pelajaran untuk validasi JavaScript
        $mataPelajaranList = MataPelajaran::select('id', 'nama_pelajaran', 'kelas_id', 'semester')->get();
        
        // Kode ini akan dimanfaatkan oleh JavaScript
        $waliKelasMap = Kelas::getWaliKelasMap();
        
        return view('data.edit_subject', compact('subject', 'classes', 'teachers', 'waliKelasMap', 'mataPelajaranList'));
    }
    
 
    public function updateLingkupMateri(Request $request, $id)
    {
        try {
            $lingkupMateri = LingkupMateri::findOrFail($id);
            
            // Validate user has permission (either admin or the assigned teacher)
            if (auth()->guard('guru')->check()) {
                $guru = auth()->guard('guru')->user();
                $mataPelajaran = $lingkupMateri->mataPelajaran;
                
                if ($mataPelajaran->guru_id != $guru->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak memiliki izin untuk mengubah data ini'
                    ], 403);
                }
            }
            
            $request->validate([
                'judul_lingkup_materi' => 'required|string|max:255'
            ]);
            
            $lingkupMateri->update([
                'judul_lingkup_materi' => $request->judul_lingkup_materi
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Lingkup materi berhasil diperbarui',
                'data' => $lingkupMateri
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
    
            $subject = MataPelajaran::findOrFail($id);
    
            $validated = $request->validate([
                'mata_pelajaran' => 'required|string|max:255',
                'kelas' => 'required|exists:kelas,id',
                'guru_pengampu' => 'required|exists:gurus,id',
                'semester' => 'required|integer|min:1|max:2',
                'lingkup_materi' => 'required|array',
                'lingkup_materi.*' => 'required|string|max:255',
            ]);
            
            // Ambil data kelas dan guru
            $kelas = Kelas::find($validated['kelas']);
            $guru = Guru::find($validated['guru_pengampu']);
            
            // Validasi:
            // 1. Jika kelas memiliki wali kelas, maka guru pengampu harus wali kelas tersebut
            if ($kelas && $kelas->hasWaliKelas()) {
                $waliKelasId = $kelas->getWaliKelasId();
                
                if ($waliKelasId && $waliKelasId != $validated['guru_pengampu']) {
                    return back()->withErrors([
                        'guru_pengampu' => 'Kelas ini memiliki wali kelas. Guru pengampu harus wali kelas tersebut.'
                    ])->withInput();
                }
            } 
            // 2. Jika guru adalah wali kelas dari kelas lain, maka tidak bisa mengajar di kelas selain kelasnya
            else if ($guru) {
                // Cek apakah guru ini adalah wali kelas
                $kelasWali = Kelas::whereHas('waliKelas', function($query) use ($guru) {
                    $query->where('guru_id', $guru->id);
                })->first();
                
                if ($kelasWali && $kelasWali->id != $validated['kelas']) {
                    return back()->withErrors([
                        'guru_pengampu' => 'Guru ini adalah wali kelas dari kelas lain dan hanya dapat mengajar di kelasnya sendiri.'
                    ])->withInput();
                }
            }
            
            // 3. Cek duplikasi nama mata pelajaran dalam satu kelas untuk semester yang sama
            // Kecuali mata pelajaran yang sedang diedit
            $exists = MataPelajaran::where('kelas_id', $validated['kelas'])
                ->where('nama_pelajaran', $validated['mata_pelajaran'])
                ->where('semester', $validated['semester'])
                ->where('id', '!=', $id) // Kecuali mata pelajaran yang sedang diedit
                ->exists();
                
            if ($exists) {
                return back()->withErrors([
                    'mata_pelajaran' => 'Mata pelajaran dengan nama yang sama sudah ada di kelas ini untuk semester yang sama.'
                ])->withInput();
            }
    
            // Update data mata pelajaran
            $subject->update([
                'nama_pelajaran' => $validated['mata_pelajaran'],
                'kelas_id' => $validated['kelas'],
                'guru_id' => $validated['guru_pengampu'],
                'semester' => $validated['semester'],
            ]);
    
            // Dapatkan lingkup materi yang sudah ada
            $existingLingkupMateriIds = $subject->lingkupMateris()->pluck('id')->toArray();
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
            return redirect()->route('subject.index')->with('success', 'Mata Pelajaran berhasil diperbarui!');
    
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
        $guru = Auth::guard('guru')->user();
        
        // Query untuk mendapatkan kelas
        $classesQuery = Kelas::query();
        
        // Jika guru ini adalah wali kelas, tambahkan kelas walinya ke dalam daftar
        if ($guru->isWaliKelas()) {
            $kelasWali = $guru->kelasWali()->first();
            
            // Ambil kelas yang diajar oleh guru (sebagai pengajar) atau kelas wali
            $classesQuery->where(function($query) use ($guruId, $kelasWali) {
                $query->whereHas('guru', function($q) use ($guruId) {
                    $q->where('guru_id', $guruId)
                      ->where('role', 'pengajar');
                });
                
                // Jika punya kelas wali, tambahkan sebagai OR condition
                if ($kelasWali) {
                    $query->orWhere('id', $kelasWali->id);
                }
            });
        } else {
            // Jika bukan wali kelas, hanya ambil kelas yang diajar sebagai pengajar biasa
            $classesQuery->whereHas('guru', function($query) use ($guruId) {
                $query->where('guru_id', $guruId)
                      ->where('role', 'pengajar');
            });
        }
        
        // Ambil hasil query dan urutkan
        $classes = $classesQuery->orderBy('nomor_kelas')
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
        $guru = Auth::guard('guru')->user();
        
        // Verifikasi guru adalah pemilik mata pelajaran
        if ($subject->guru_id != $guruId) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit mata pelajaran ini.');
        }
        
        // Query untuk mendapatkan kelas
        $classesQuery = Kelas::query();
        
        // Jika guru ini adalah wali kelas, tambahkan kelas walinya ke dalam daftar
        if ($guru->isWaliKelas()) {
            $kelasWali = $guru->kelasWali()->first();
            
            // Ambil kelas yang diajar oleh guru (sebagai pengajar) atau kelas wali
            $classesQuery->where(function($query) use ($guruId, $kelasWali) {
                $query->whereHas('guru', function($q) use ($guruId) {
                    $q->where('guru_id', $guruId)
                      ->where('role', 'pengajar');
                });
                
                // Jika punya kelas wali, tambahkan sebagai OR condition
                if ($kelasWali) {
                    $query->orWhere('id', $kelasWali->id);
                }
            });
        } else {
            // Jika bukan wali kelas, hanya ambil kelas yang diajar sebagai pengajar biasa
            $classesQuery->whereHas('guru', function($query) use ($guruId) {
                $query->where('guru_id', $guruId)
                      ->where('role', 'pengajar');
            });
        }
        
        // Ambil hasil query dan urutkan
        $classes = $classesQuery->orderBy('nomor_kelas')
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
    
        // Verifikasi guru bisa mengajar di kelas yang dipilih
        $kelasId = $validated['kelas'];
        if (!$guru->canTeachClass($kelasId)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengajar di kelas ini.'
                ], 403);
            }
            return back()->withErrors(['kelas' => 'Anda tidak memiliki akses untuk mengajar di kelas ini.'])->withInput();
        }
    
        try {
            DB::beginTransaction();
    
            // Update the subject's main data
            $subject->update([
                'nama_pelajaran' => $validated['mata_pelajaran'],
                'kelas_id' => $validated['kelas'],
                'semester' => $validated['semester'],
            ]);
    
            // Get existing lingkup materi items
            $existingLingkupMateris = $subject->lingkupMateris()->get();
            $existingTitles = $existingLingkupMateris->pluck('judul_lingkup_materi')->toArray();
            $newTitles = $validated['lingkup_materi'];
            
            // 1. Update existing items if they're still in the new list
            foreach ($existingLingkupMateris as $existingLM) {
                // Find this item's position in the new array, if it exists
                $newTitleIndex = array_search($existingLM->judul_lingkup_materi, $newTitles);
                
                if ($newTitleIndex !== false) {
                    // If the title exists in the new array, remove it from the new titles list
                    // (so we know which ones to create later)
                    unset($newTitles[$newTitleIndex]);
                } else {
                    // If it doesn't exist in the new array, delete it
                    $existingLM->delete();
                }
            }
            
            // 2. Add new items that weren't in the original list
            foreach ($newTitles as $newTitle) {
                LingkupMateri::create([
                    'mata_pelajaran_id' => $subject->id,
                    'judul_lingkup_materi' => $newTitle,
                ]);
            }
    
            DB::commit();
            
            // Periksa tipe request dan kembalikan respons yang sesuai
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mata Pelajaran berhasil diperbarui!'
                ]);
            }
            
            // Untuk request normal, kembalikan redirect
            return redirect()->route('pengajar.subject.index')
                ->with('success', 'Mata Pelajaran berhasil diperbarui!');
    
        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            
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