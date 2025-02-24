<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Ekstrakurikuler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClassController extends Controller
{
    // Menampilkan daftar kelas
    public function index(Request $request)
    {
        $query = Kelas::query();
        
        if ($request->has('search')) {
            $search = strtolower($request->search);
            $terms = explode(' ', trim($search));
            
            $query->where(function($q) use ($terms, $search) {
                // Jika kata pertama adalah "kelas"
                if (count($terms) > 0 && $terms[0] === 'kelas') {
                    if (count($terms) > 1 && is_numeric($terms[1])) {
                        // Jika ada nomor kelas yang dispecifikkan (kelas 1, kelas 2, dst)
                        $q->where('nomor_kelas', $terms[1]);
                    } else {
                        // Jika hanya "kelas", urutkan berdasarkan nomor_kelas
                        $q->orderBy('nomor_kelas', 'asc');
                    }
                } else {
                    // Pencarian normal untuk term lainnya
                    $q->where('nama_kelas', 'like', '%' . $search . '%')
                      ->orWhere('nomor_kelas', 'like', '%' . $search . '%')
                      ->orWhere('wali_kelas', 'like', '%' . $search . '%');
                }
            });
        }
    
        // Default ordering jika tidak ada pencarian
        if (!$request->has('search') || 
            (isset($terms) && count($terms) === 1 && $terms[0] === 'kelas')) {
            $query->orderBy('nomor_kelas', 'asc')
                  ->orderBy('nama_kelas', 'asc');
        }
        
        $kelasList = $query->paginate(10);
        
        return view('admin.class', compact('kelasList'));
    }

    // Menampilkan form tambah data kelas
    public function create()
    {
        // Dapatkan guru yang hanya memiliki jabatan 'guru' saja (bukan guru_wali)
        // dan belum menjadi wali kelas di kelas manapun
        $guruList = Guru::where('jabatan', 'guru')
            ->whereDoesntHave('kelas', function($query) {
                $query->where('guru_kelas.is_wali_kelas', true)
                      ->where('guru_kelas.role', 'wali_kelas');
            })
            ->get();

        return view('data.create_class', compact('guruList'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_kelas' => 'required|integer|min:1|max:99',
            'nama_kelas' => 'required|string|max:255',
        ], [
            'nomor_kelas.required' => 'Nomor kelas harus diisi',
            'nomor_kelas.integer' => 'Nomor kelas harus berupa angka',
            'nomor_kelas.min' => 'Nomor kelas minimal 1',
            'nomor_kelas.max' => 'Nomor kelas maksimal 99',
            'nama_kelas.required' => 'Nama kelas harus diisi',
            'nama_kelas.max' => 'Nama kelas maksimal 255 karakter'
        ]);
    
        DB::beginTransaction();
        try {
            // Buat kelas baru
            $kelas = Kelas::create([
                'nomor_kelas' => $request->nomor_kelas,
                'nama_kelas' => $request->nama_kelas
            ]);
    
            // Jika ada wali kelas yang dipilih
            if ($request->filled('wali_kelas_id')) {
                // Ambil data guru
                $guru = Guru::find($request->wali_kelas_id);
                
                if (!$guru) {
                    throw new \Exception('Guru tidak ditemukan');
                }
                
                // Attach guru sebagai wali kelas
                $kelas->guru()->attach($request->wali_kelas_id, [
                    'is_wali_kelas' => true,
                    'role' => 'wali_kelas'
                ]);
    
                // Update jabatan guru menjadi guru_wali
                $guru->jabatan = 'guru_wali';
                $guru->save();
                
                // Tambahkan logging untuk debugging
                \Log::info('Mengubah jabatan guru', [
                    'guru_id' => $guru->id,
                    'nama' => $guru->nama,
                    'jabatan_baru' => 'guru_wali'
                ]);
            }
    
            DB::commit();
            return redirect()->route('kelas.index')
                ->with('success', 'Data kelas berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saat membuat kelas: ' . $e->getMessage(), [
                'stacktrace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
    
    // Menampilkan form edit data kelas
    public function edit($id)
    {
        $kelas = Kelas::findOrFail($id);
        $waliKelas = $kelas->guru()
                          ->whereRaw('guru_kelas.is_wali_kelas = 1')
                          ->whereRaw("guru_kelas.role = 'wali_kelas'")
                          ->first();
        
        return view('data.edit_class', compact('kelas', 'waliKelas'));
    }
    
    // Mengupdate data kelas
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nomor_kelas' => 'required|integer|min:1|max:99',
            'nama_kelas' => 'required|string|max:255',
        ], [
            'nomor_kelas.required' => 'Nomor kelas harus diisi',
            'nomor_kelas.integer' => 'Nomor kelas harus berupa angka',
            'nomor_kelas.min' => 'Nomor kelas minimal 1',
            'nomor_kelas.max' => 'Nomor kelas maksimal 99',
            'nama_kelas.required' => 'Nama kelas harus diisi',
            'nama_kelas.max' => 'Nama kelas maksimal 255 karakter'
        ]);
    
        DB::beginTransaction();
        try {
            $kelas = Kelas::findOrFail($id);
    
            // Cek apakah kombinasi nomor_kelas dan nama_kelas sudah ada di kelas lain
            $existingClass = Kelas::where('id', '!=', $id)
                                 ->where('nomor_kelas', $validated['nomor_kelas'])
                                 ->where('nama_kelas', $validated['nama_kelas'])
                                 ->first();
            
            if ($existingClass) {
                return back()
                    ->withInput()
                    ->with('error', 'Kelas ' . $validated['nomor_kelas'] . ' ' . $validated['nama_kelas'] . ' sudah ada. Silakan gunakan nama kelas yang berbeda.');
            }
            
            $kelas->update([
                'nomor_kelas' => $validated['nomor_kelas'],
                'nama_kelas' => $validated['nama_kelas']
            ]);
    
            DB::commit();
            return redirect()->route('kelas.index')
                ->with('success', 'Data kelas berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Menghapus data kelas
    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        
        // Ambil wali kelas jika ada
        $waliKelas = $kelas->guru()
            ->wherePivot('is_wali_kelas', true)
            ->wherePivot('role', 'wali_kelas')
            ->first();
        
        DB::beginTransaction();
        try {
            // Jika ada wali kelas, update jabatannya kembali menjadi 'guru'
            if ($waliKelas) {
                // Hitung apakah guru masih menjadi wali kelas di kelas lain
                $otherWaliKelasCount = DB::table('guru_kelas')
                    ->where('guru_id', $waliKelas->id)
                    ->where('kelas_id', '!=', $id)
                    ->where('is_wali_kelas', true)
                    ->where('role', 'wali_kelas')
                    ->count();
                
                // Jika tidak menjadi wali kelas di kelas lain, ubah jabatan menjadi 'guru'
                if ($otherWaliKelasCount == 0) {
                    $waliKelas->jabatan = 'guru';
                    $waliKelas->save();
                }
            }
            
            $kelas->delete();
            
            DB::commit();
            return redirect()->route('kelas.index')
                ->with('success', 'Data kelas berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('kelas.index')
                ->with('error', 'Terjadi kesalahan saat menghapus kelas: ' . $e->getMessage());
        }
    }

    // Fungsi-fungsi lainnya tetap seperti sebelumnya
    public function adminDashboard()
    {
        $totalStudents = Siswa::count();
        $totalTeachers = Guru::count();
        $totalSubjects = MataPelajaran::count();
        $totalClasses = Kelas::count();
        $totalExtracurriculars = Ekstrakurikuler::count();
        $overallProgress = $this->calculateOverallProgressForAdmin();
        $informationItems = $this->getInformationItems();
        $kelas = Kelas::all();
    
        return view('admin.dashboard', compact(
            'totalStudents',
            'totalTeachers',
            'totalSubjects',
            'totalClasses',
            'totalExtracurriculars',
            'overallProgress',
            'kelas',
            'informationItems'
        ));
    }
    
    private function calculateOverallProgressForAdmin()
    {
        $totalNilai = Nilai::count();
        $completedNilai = Nilai::whereNotNull('nilai_akhir_rapor')->count();

        return $totalNilai > 0 ? ($completedNilai / $totalNilai) * 100 : 0;
    }

    private function calculateClassProgress()
    {
        $classes = Kelas::with('mataPelajarans')->get();
        $progress = [];

        foreach ($classes as $class) {
            $totalScores = 0;
            $completedScores = 0;

            foreach ($class->mataPelajarans as $subject) {
                $totalStudents = $class->siswas()->count();
                $totalScores += $totalStudents;
                $completedScores += Nilai::where('mata_pelajaran_id', $subject->id)
                    ->whereNotNull('nilai_akhir_rapor')
                    ->count();
            }

            $progress[$class->nama_kelas] = $totalScores > 0 ? ($completedScores / $totalScores) * 100 : 0;
        }

        return $progress;
    }

    public function getKelasProgressAdmin($kelasId)
    {
        try {
            $kelas = Kelas::with(['siswas', 'mataPelajarans'])->findOrFail($kelasId);
            $progress = $this->calculateClassProgressForSingleClass($kelas);
            
            \Log::info('Admin Kelas progress calculated:', [
                'kelas_id' => $kelasId,
                'progress' => $progress
            ]);
            
            return response()->json([
                'success' => true,
                'progress' => $progress
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error calculating class progress:', [
                'error' => $e->getMessage(),
                'kelas_id' => $kelasId
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error calculating progress'
            ], 500);
        }
    }

    private function calculateOverallProgress()
    {
        $totalScores = DB::table('siswas')
            ->join('kelas', 'siswas.kelas_id', '=', 'kelas.id')
            ->join('mata_pelajarans', 'kelas.id', '=', 'mata_pelajarans.kelas_id')
            ->count();

        $completedScores = Nilai::whereNotNull('nilai_akhir_rapor')->count();

        return $totalScores > 0 ? ($completedScores / $totalScores) * 100 : 0;
    }

    private function getInformationItems()
    {
        // Implement this method to fetch information items
        // For now, we'll return an empty array
        return [];
    }

    
    public function getKelasProgress($kelasId)
    {
        try {
            $kelas = Kelas::findOrFail($kelasId);
            $progress = $this->calculateClassProgressForSingleClass($kelas);
            
            Log::info('Kelas progress calculated:', [
                'kelas_id' => $kelasId,
                'progress' => $progress
            ]);
            
            return response()->json([
                'success' => true,
                'progress' => $progress,
                'details' => [
                    'kelas' => $kelas->nama_kelas,
                    'total_students' => $kelas->siswas()->count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error calculating kelas progress:', [
                'kelas_id' => $kelasId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error calculating progress',
                'progress' => 0
            ]);
        }
    }
    

    private function calculateClassProgressForSingleClass($kelas)
    {
        $totalPossibleNilai = 0;
        $completedNilai = 0;
    
        // Hitung untuk setiap siswa dan mata pelajaran
        $kelas->siswas->each(function($siswa) use (&$totalPossibleNilai, &$completedNilai) {
            $siswa->mataPelajarans->each(function($mapel) use (&$totalPossibleNilai, &$completedNilai, $siswa) {
                $totalPossibleNilai++;
                
                $nilai = Nilai::where([
                    'siswa_id' => $siswa->id,
                    'mata_pelajaran_id' => $mapel->id
                ])->whereNotNull('nilai_akhir_rapor')->first();
                
                if ($nilai) {
                    $completedNilai++;
                }
            });
        });
    
        return $totalPossibleNilai > 0 ? round(($completedNilai / $totalPossibleNilai) * 100, 2) : 0;
    }
}