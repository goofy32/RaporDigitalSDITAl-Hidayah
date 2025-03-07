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
        // Gunakan join untuk memastikan kita bisa mengurutkan berdasarkan kolom dari tabel kelas
        $query = MataPelajaran::join('kelas', 'mata_pelajarans.kelas_id', '=', 'kelas.id')
            ->select('mata_pelajarans.*') // Pastikan hanya mengambil kolom dari mata_pelajarans
            ->with(['kelas', 'guru']); // Load relasi kelas dan guru
            
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
                    $q->where('mata_pelajarans.nama_pelajaran', 'LIKE', "%{$search}%")
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
    
        // Default sorting: urutkan berdasarkan nomor kelas (ascending) lalu nama kelas
        $query->orderBy('kelas.nomor_kelas', 'asc')
              ->orderBy('kelas.nama_kelas', 'asc')
              ->orderBy('mata_pelajarans.nama_pelajaran', 'asc');
        
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
        \Log::info('Subject store method called with data:', $request->all());
    
        try {
            $validated = $request->validate([
                'mata_pelajaran' => 'required|string|max:255',
                'kelas' => 'required|exists:kelas,id',
                'guru_pengampu' => 'required|exists:gurus,id',
                'semester' => 'required|integer|min:1|max:2',
                'is_muatan_lokal' => 'sometimes',
                'lingkup_materi' => 'required|array',
                'lingkup_materi.*' => 'required|string|max:255',
            ]);
    
            // Convert checkbox value to boolean 
            $isMuatanLokal = $request->has('is_muatan_lokal');
    
            \Log::info('Validation passed, proceeding with save');
    
            // Ambil data kelas dan guru
            $kelas = Kelas::find($validated['kelas']);
            $guru = Guru::find($validated['guru_pengampu']);
            
            // Validasi sesuai status muatan lokal
            if ($isMuatanLokal) {
                // Jika ini adalah mata pelajaran muatan lokal: 
                // Hanya guru biasa (bukan wali kelas) yang dapat mengajar
                if ($guru && $guru->jabatan == 'guru_wali') {
                    return back()->withErrors([
                        'guru_pengampu' => 'Mata pelajaran muatan lokal hanya dapat diajar oleh guru dengan jabatan guru (bukan wali kelas).'
                    ])->withInput();
                }
            } else {
                // Ini adalah mata pelajaran biasa (bukan muatan lokal):
                // Jika kelas memiliki wali kelas, guru pengampu harus wali kelas tersebut
                if ($kelas && $kelas->hasWaliKelas()) {
                    $waliKelasId = $kelas->getWaliKelasId();
                    
                    if ($waliKelasId && $waliKelasId != $validated['guru_pengampu']) {
                        return back()->withErrors([
                            'guru_pengampu' => 'Untuk mata pelajaran wajib (bukan muatan lokal), guru pengampu harus wali kelas dari kelas ini.'
                        ])->withInput();
                    }
                }
                // Jika guru adalah wali kelas dari kelas lain, tidak bisa mengajar di kelas selain kelasnya
                else if ($guru && $guru->jabatan == 'guru_wali') {
                    // Cek apakah guru ini adalah wali kelas
                    $kelasWali = Kelas::whereHas('waliKelas', function($query) use ($guru) {
                        $query->where('guru_id', $guru->id);
                    })->first();
                    
                    if ($kelasWali && $kelasWali->id != $validated['kelas']) {
                        return back()->withErrors([
                            'guru_pengampu' => 'Guru ini adalah wali kelas dari kelas lain dan hanya dapat mengajar di kelasnya sendiri untuk mata pelajaran wajib.'
                        ])->withInput();
                    }
                }
            }
            
            // 3. Cek duplikasi nama mata pelajaran dalam satu kelas untuk semester yang sama
            $exists = MataPelajaran::where('kelas_id', $validated['kelas'])
                ->where('nama_pelajaran', $validated['mata_pelajaran'])
                ->where('semester', $validated['semester'])
                ->exists(); // Tidak perlu kecualikan ID karena ini method store
                
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
                'is_muatan_lokal' => $isMuatanLokal, // Use the boolean value
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
        } catch (\Exception $e) {
            \Log::error('Error in subject store method:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function checkLingkupMateriDependencies($id)
    {
        try {
            $lingkupMateri = LingkupMateri::findOrFail($id);
            
            // Verify permission
            if (auth()->guard('guru')->check()) {
                $guru = auth()->guard('guru')->user();
                $mataPelajaran = $lingkupMateri->mataPelajaran;
                
                if ($mataPelajaran->guru_id != $guru->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak memiliki izin untuk memeriksa data ini'
                    ], 403);
                }
            }
            
            // Cek apakah ada tujuan pembelajaran terkait
            $hasDependents = $lingkupMateri->tujuanPembelajarans()->exists();
            
            return response()->json([
                'success' => true,
                'hasDependents' => $hasDependents
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
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
            
            // Mulai transaksi database untuk memastikan semua operasi berhasil atau gagal bersama
            DB::beginTransaction();
            
            // Hapus semua tujuan pembelajaran terkait terlebih dahulu
            if ($lingkupMateri->tujuanPembelajarans()->exists()) {
                $lingkupMateri->tujuanPembelajarans()->delete();
            }
            
            // Kemudian hapus lingkup materi
            $lingkupMateri->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Lingkup materi dan semua tujuan pembelajaran terkait berhasil dihapus'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
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
    
            // Convert checkbox value to boolean
            $isMuatanLokal = $request->has('is_muatan_lokal');
            
            // Ambil data kelas dan guru
            $kelas = Kelas::find($validated['kelas']);
            $guru = Guru::find($validated['guru_pengampu']);
            
            // Validasi sesuai status muatan lokal
            if ($isMuatanLokal) {
                // Jika ini adalah mata pelajaran muatan lokal: 
                // Hanya guru biasa (bukan wali kelas) yang dapat mengajar
                if ($guru && $guru->jabatan == 'guru_wali') {
                    return back()->withErrors([
                        'guru_pengampu' => 'Mata pelajaran muatan lokal hanya dapat diajar oleh guru dengan jabatan guru (bukan wali kelas).'
                    ])->withInput();
                }
            } else {
                // Ini adalah mata pelajaran biasa (bukan muatan lokal):
                // Jika kelas memiliki wali kelas, guru pengampu harus wali kelas tersebut
                if ($kelas && $kelas->hasWaliKelas()) {
                    $waliKelasId = $kelas->getWaliKelasId();
                    
                    if ($waliKelasId && $waliKelasId != $validated['guru_pengampu']) {
                        return back()->withErrors([
                            'guru_pengampu' => 'Untuk mata pelajaran wajib (bukan muatan lokal), guru pengampu harus wali kelas dari kelas ini.'
                        ])->withInput();
                    }
                }
                // Jika guru adalah wali kelas dari kelas lain, tidak bisa mengajar di kelas selain kelasnya
                else if ($guru && $guru->jabatan == 'guru_wali') {
                    // Cek apakah guru ini adalah wali kelas
                    $kelasWali = Kelas::whereHas('waliKelas', function($query) use ($guru) {
                        $query->where('guru_id', $guru->id);
                    })->first();
                    
                    if ($kelasWali && $kelasWali->id != $validated['kelas']) {
                        return back()->withErrors([
                            'guru_pengampu' => 'Guru ini adalah wali kelas dari kelas lain dan hanya dapat mengajar di kelasnya sendiri untuk mata pelajaran wajib.'
                        ])->withInput();
                    }
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
                'is_muatan_lokal' => $isMuatanLokal, // Use the converted boolean value
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
        
        // Gunakan join dengan tabel kelas untuk memungkinkan pengurutan yang lebih baik
        $subjects = MataPelajaran::join('kelas', 'mata_pelajarans.kelas_id', '=', 'kelas.id')
            ->select('mata_pelajarans.*') // Pastikan hanya mengambil kolom dari mata pelajaran
            ->with(['kelas', 'guru', 'lingkupMateris'])
            ->where('mata_pelajarans.guru_id', $guru->id)
            ->orderBy('kelas.nomor_kelas', 'asc') // Urutkan berdasarkan nomor kelas
            ->orderBy('kelas.nama_kelas', 'asc')  // Kemudian nama kelas (A, B, C, dll)
            ->orderBy('mata_pelajarans.nama_pelajaran', 'asc') // Terakhir berdasarkan nama mata pelajaran
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
    
            // SECURITY FIX: Force is_muatan_lokal value based on teacher role
            // For regular teachers (jabatan = 'guru'), always set muatan_lokal = true
            // For homeroom teachers (jabatan = 'guru_wali'), set muatan_lokal = false
            $isMuatanLokal = ($guru->jabatan == 'guru');
    
            // Cek duplikasi mata pelajaran
            $exists = MataPelajaran::where('kelas_id', $validated['kelas'])
                ->where('nama_pelajaran', $validated['mata_pelajaran'])
                ->where('semester', $validated['semester'])
                ->exists();
                
            if ($exists) {
                return back()->withErrors([
                    'mata_pelajaran' => 'Mata pelajaran dengan nama yang sama sudah ada di kelas ini untuk semester yang sama.'
                ])->withInput();
            }
    
            $mataPelajaran = MataPelajaran::create([
                'nama_pelajaran' => $validated['mata_pelajaran'],
                'kelas_id' => $validated['kelas'],
                'guru_id' => $guru->id,
                'semester' => $validated['semester'],
                'is_muatan_lokal' => $isMuatanLokal, // Always use the server-determined value
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
                ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())
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
    
        // SECURITY FIX: Force is_muatan_lokal value based on teacher role
        // For regular teachers (jabatan = 'guru'), always set muatan_lokal = true
        // For homeroom teachers (jabatan = 'guru_wali'), set muatan_lokal = false
        $isMuatanLokal = ($guru->jabatan == 'guru');
    
        // Check for duplicates excluding current record
        $exists = MataPelajaran::where('kelas_id', $validated['kelas'])
            ->where('nama_pelajaran', $validated['mata_pelajaran'])
            ->where('semester', $validated['semester'])
            ->where('id', '!=', $id) // This is critical - exclude the current record
            ->exists();
            
        if ($exists) {
            return back()->withErrors([
                'mata_pelajaran' => 'Mata pelajaran dengan nama yang sama sudah ada di kelas ini untuk semester yang sama.'
            ])->withInput();
        }
    
        // Verify teacher can teach in selected class
        $kelasId = $validated['kelas'];
        if (!$guru->canTeachClass($kelasId)) {
            return back()->withErrors([
                'kelas' => 'Anda tidak memiliki akses untuk mengajar di kelas ini.'
            ])->withInput();
        }
    
        try {
            DB::beginTransaction();
    
            // Update the subject with the server-determined is_muatan_lokal value
            $subject->update([
                'nama_pelajaran' => $validated['mata_pelajaran'],
                'kelas_id' => $validated['kelas'],
                'semester' => $validated['semester'],
                'is_muatan_lokal' => $isMuatanLokal, // Always use the server-determined value
            ]);
            
            // Handle lingkup materi updates
            $existingLingkupMateris = $subject->lingkupMateris()->get();
            $existingTitles = $existingLingkupMateris->pluck('judul_lingkup_materi')->toArray();
            $newTitles = $validated['lingkup_materi'];
            
            // Process existing entries
            foreach ($existingLingkupMateris as $existingLM) {
                $newTitleIndex = array_search($existingLM->judul_lingkup_materi, $newTitles);
                
                if ($newTitleIndex !== false) {
                    // Keep and remove from new titles list
                    unset($newTitles[$newTitleIndex]);
                } else {
                    // Delete if not in new list
                    $existingLM->delete();
                }
            }
            
            // Add new entries
            foreach ($newTitles as $newTitle) {
                LingkupMateri::create([
                    'mata_pelajaran_id' => $subject->id,
                    'judul_lingkup_materi' => $newTitle,
                ]);
            }
    
            DB::commit();
            return redirect()->route('pengajar.subject.index')
                ->with('success', 'Mata Pelajaran berhasil diperbarui!');
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