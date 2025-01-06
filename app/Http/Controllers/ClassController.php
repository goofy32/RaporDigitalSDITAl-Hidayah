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
        return view('data.create_class');
    }

    // Menyimpan data kelas baru
    public function store(Request $request)
    {
        $request->validate([
            'nomor_kelas' => 'required|integer',
            'nama_kelas' => 'required|string|max:255',
            'wali_kelas' => 'required|string|max:255',
        ]);
    
        Kelas::create([
            'nomor_kelas' => $request->input('nomor_kelas'),
            'nama_kelas' => $request->input('nama_kelas'),
            'wali_kelas' => $request->input('wali_kelas'),
        ]);
    
        return redirect()->route('kelas.index')->with('success', 'Data kelas berhasil ditambahkan.');
    }

    // Menampilkan form edit data kelas
    public function edit($id)
    {
        $kelas = Kelas::findOrFail($id);
    
        // Pisahkan nomor kelas dan nama kelas dari nama_kelas
        $pattern = '/Kelas (\d+) - (.+)/';
        preg_match($pattern, $kelas->nama_kelas, $matches);
    
        if ($matches) {
            $kelas->nomor_kelas = $matches[1];
            $kelas->nama_kelas = $matches[2];
        } else {
            // Jika format tidak sesuai, set nilai default
            $kelas->nomor_kelas = '';
            $kelas->nama_kelas = $kelas->nama_kelas;
        }
    
        return view('data.edit_class', compact('kelas'));
    }

    // Mengupdate data kelas
    public function update(Request $request, $id)
    {
        $request->validate([
            'nomor_kelas' => 'required|integer',
            'nama_kelas' => 'required|string|max:255',
            'wali_kelas' => 'required|string|max:255',
        ]);
    
        $kelas = Kelas::findOrFail($id);
    
        $kelas->update([
            'nomor_kelas' => $request->input('nomor_kelas'),
            'nama_kelas' => $request->input('nama_kelas'),
            'wali_kelas' => $request->input('wali_kelas'),
        ]);
    
        return redirect()->route('kelas.index')->with('success', 'Data kelas berhasil diupdate.');
    }

    // Menghapus data kelas
    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->delete();

        return redirect()->route('kelas.index')->with('success', 'Data kelas berhasil dihapus.');
    }

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
        $kelas = Kelas::findOrFail($kelasId);
        $progress = $this->calculateClassProgressForSingleClass($kelas);
        
        \Log::info('Kelas progress for ID ' . $kelasId . ': ' . $progress); // Tambahkan log ini
        
        return response()->json(['progress' => $progress]);
    }
    
    private function calculateClassProgressForSingleClass($kelas)
    {
        $totalNilai = Nilai::whereHas('siswa', function($query) use ($kelas) {
            $query->where('kelas_id', $kelas->id);
        })->count();
    
        $completedNilai = Nilai::whereHas('siswa', function($query) use ($kelas) {
            $query->where('kelas_id', $kelas->id);
        })->whereNotNull('nilai_akhir_rapor')->count();
    
        return $totalNilai > 0 ? ($completedNilai / $totalNilai) * 100 : 0;
    }
}
