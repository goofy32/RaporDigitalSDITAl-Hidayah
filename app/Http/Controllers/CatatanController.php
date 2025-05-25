<?php
// app/Http/Controllers/CatatanController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CatatanSiswa;
use App\Models\CatatanMataPelajaran;
use App\Models\Siswa;
use App\Models\MataPelajaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CatatanController extends Controller
{
    // =================== CATATAN SISWA ===================
    
    /**
     * Show form for adding/editing student notes
     */
    public function showCatatanSiswa(Siswa $siswa)
    {
        $guru = Auth::guard('guru')->user();
        $tahunAjaranId = session('tahun_ajaran_id');
        $selectedSemester = session('selected_semester', 1);
        
        // Check if this teacher is the wali kelas for this student
        if (!$siswa->isInKelasWali($guru->id)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengedit catatan siswa ini.');
        }
        
        // Get existing notes for current context
        $catatanList = CatatanSiswa::where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('semester', $selectedSemester)
            ->orderBy('type')
            ->get()
            ->keyBy('type');
        
        return view('wali_kelas.catatan.siswa', compact('siswa', 'catatanList'));
    }
    
    /**
     * Store or update student notes
     */
    public function storeCatatanSiswa(Request $request, Siswa $siswa)
    {
        $request->validate([
            'catatan_umum' => 'nullable|string|max:1000',
            'catatan_uts' => 'nullable|string|max:1000',
            'catatan_uas' => 'nullable|string|max:1000',
        ]);
        
        $guru = Auth::guard('guru')->user();
        $tahunAjaranId = session('tahun_ajaran_id');
        $selectedSemester = session('selected_semester', 1);
        
        // Check access
        if (!$siswa->isInKelasWali($guru->id)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengedit catatan siswa ini.');
        }
        
        DB::beginTransaction();
        
        try {
            $types = ['umum', 'uts', 'uas'];
            
            foreach ($types as $type) {
                $fieldName = "catatan_{$type}";
                $catatanText = $request->input($fieldName);
                
                if (!empty($catatanText)) {
                    CatatanSiswa::updateOrCreate(
                        [
                            'siswa_id' => $siswa->id,
                            'tahun_ajaran_id' => $tahunAjaranId,
                            'semester' => $selectedSemester,
                            'type' => $type,
                        ],
                        [
                            'catatan' => $catatanText,
                            'created_by' => $guru->id,
                        ]
                    );
                } else {
                    // Delete if empty
                    CatatanSiswa::where([
                        'siswa_id' => $siswa->id,
                        'tahun_ajaran_id' => $tahunAjaranId,
                        'semester' => $selectedSemester,
                        'type' => $type,
                    ])->delete();
                }
            }
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Catatan siswa berhasil disimpan.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan catatan: ' . $e->getMessage());
        }
    }
    
    // =================== CATATAN MATA PELAJARAN ===================
    
    /**
     * Show list of subjects for adding notes
     */
    public function indexCatatanMataPelajaran()
    {
        $guru = Auth::guard('guru')->user();
        $tahunAjaranId = session('tahun_ajaran_id');
        $selectedSemester = session('selected_semester', 1);
        
        // Get subjects taught by this teacher (including as wali kelas)
        $kelas = $guru->kelasWali;
        
        if (!$kelas) {
            return redirect()->back()->with('error', 'Anda tidak memiliki kelas yang diwalikan.');
        }
        
        // Get subjects in the class that this teacher can manage
        $mataPelajarans = MataPelajaran::where('kelas_id', $kelas->id)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('semester', $selectedSemester)
            ->with(['guru'])
            ->orderBy('nama_pelajaran')
            ->get();
        
        return view('wali_kelas.catatan.mata_pelajaran.index', compact('mataPelajarans', 'kelas'));
    }
    
    /**
     * Show form for adding notes to a specific subject for all students
     */
    public function showCatatanMataPelajaran(MataPelajaran $mataPelajaran)
    {
        $guru = Auth::guard('guru')->user();
        $tahunAjaranId = session('tahun_ajaran_id');
        $selectedSemester = session('selected_semester', 1);
        
        // Check if this teacher can manage this subject
        $kelas = $guru->kelasWali;
        if (!$kelas || $mataPelajaran->kelas_id !== $kelas->id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengedit catatan mata pelajaran ini.');
        }
        
        // Get all students in the class
        $siswaList = Siswa::where('kelas_id', $kelas->id)
            ->orderBy('nama')
            ->get();
        
        // Get existing notes for this subject and all students
        $existingCatatan = CatatanMataPelajaran::where('mata_pelajaran_id', $mataPelajaran->id)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('semester', $selectedSemester)
            ->get()
            ->groupBy(['siswa_id', 'type']);
        
        return view('wali_kelas.catatan.mata_pelajaran.form', compact(
            'mataPelajaran', 
            'siswaList', 
            'existingCatatan'
        ));
    }
    
    /**
     * Store subject notes for all students
     */
    public function storeCatatanMataPelajaran(Request $request, MataPelajaran $mataPelajaran)
    {
        $guru = Auth::guard('guru')->user();
        $tahunAjaranId = session('tahun_ajaran_id');
        $selectedSemester = session('selected_semester', 1);
        
        // Check access
        $kelas = $guru->kelasWali;
        if (!$kelas || $mataPelajaran->kelas_id !== $kelas->id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengedit catatan mata pelajaran ini.');
        }
        
        $request->validate([
            'catatan' => 'required|array',
            'catatan.*.umum' => 'nullable|string|max:1000',
            'catatan.*.uts' => 'nullable|string|max:1000',
            'catatan.*.uas' => 'nullable|string|max:1000',
        ]);
        
        DB::beginTransaction();
        
        try {
            $catatanData = $request->input('catatan', []);
            
            foreach ($catatanData as $siswaId => $catatan) {
                $types = ['umum', 'uts', 'uas'];
                
                foreach ($types as $type) {
                    $catatanText = $catatan[$type] ?? '';
                    
                    if (!empty($catatanText)) {
                        CatatanMataPelajaran::updateOrCreate(
                            [
                                'mata_pelajaran_id' => $mataPelajaran->id,
                                'siswa_id' => $siswaId,
                                'tahun_ajaran_id' => $tahunAjaranId,
                                'semester' => $selectedSemester,
                                'type' => $type,
                            ],
                            [
                                'catatan' => $catatanText,
                                'created_by' => $guru->id,
                            ]
                        );
                    } else {
                        // Delete if empty
                        CatatanMataPelajaran::where([
                            'mata_pelajaran_id' => $mataPelajaran->id,
                            'siswa_id' => $siswaId,
                            'tahun_ajaran_id' => $tahunAjaranId,
                            'semester' => $selectedSemester,
                            'type' => $type,
                        ])->delete();
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('wali_kelas.catatan.mata_pelajaran.index')
                ->with('success', 'Catatan mata pelajaran berhasil disimpan.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan catatan: ' . $e->getMessage());
        }
    }
    
    /**
     * Get notes for a specific student and subject (for AJAX)
     */
    public function getCatatanForSiswa(Request $request)
    {
        $siswaId = $request->input('siswa_id');
        $mataPelajaranId = $request->input('mata_pelajaran_id');
        $type = $request->input('type', 'umum');
        
        $tahunAjaranId = session('tahun_ajaran_id');
        $selectedSemester = session('selected_semester', 1);
        
        $catatan = CatatanMataPelajaran::where([
            'siswa_id' => $siswaId,
            'mata_pelajaran_id' => $mataPelajaranId,
            'tahun_ajaran_id' => $tahunAjaranId,
            'semester' => $selectedSemester,
            'type' => $type,
        ])->first();
        
        return response()->json([
            'success' => true,
            'catatan' => $catatan ? $catatan->catatan : ''
        ]);
    }
}