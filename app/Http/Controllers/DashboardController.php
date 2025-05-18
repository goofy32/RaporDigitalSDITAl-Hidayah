<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\Nilai;
use App\Models\Guru;
use App\Models\Ekstrakurikuler;
use App\Models\Notification;
use App\Models\TahunAjaran;
use App\Models\ProfilSekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function adminDashboard()
    {
        $tahunAjaranId = session('tahun_ajaran_id');

        $totalStudents = Siswa::when($tahunAjaranId, function($query) use ($tahunAjaranId) {
            return $query->whereHas('kelas', function($q) use ($tahunAjaranId) {
                $q->where('tahun_ajaran_id', $tahunAjaranId);
            });
        })->count();
        
        $totalClasses = Kelas::when($tahunAjaranId, function($query) use ($tahunAjaranId) {
            return $query->where('tahun_ajaran_id', $tahunAjaranId);
        })->count();
        
       $semester = null;
        if ($tahunAjaranId) {
            $currentTahunAjaran = TahunAjaran::find($tahunAjaranId);
            if ($currentTahunAjaran) {
                $semester = $currentTahunAjaran->semester;
            }
        }

        $totalSubjects = MataPelajaran::when($tahunAjaranId, function($query) use ($tahunAjaranId) {
            return $query->where('tahun_ajaran_id', $tahunAjaranId);
        })->when($semester, function($query) use ($semester) {
            return $query->where('semester', $semester);
        })->count();
        
        $totalTeachers = Guru::count(); // Guru tetap dihitung semua
        
        $totalExtracurriculars = Ekstrakurikuler::when($tahunAjaranId && Schema::hasColumn('ekstrakurikulers', 'tahun_ajaran_id'), function($query) use ($tahunAjaranId) {
            return $query->where('tahun_ajaran_id', $tahunAjaranId);
        })->count();
        
        $overallProgress = $this->calculateOverallProgressForAdmin($tahunAjaranId) ?? 0;
        
        $kelas = Kelas::when($tahunAjaranId, function($query) use ($tahunAjaranId) {
            return $query->where('tahun_ajaran_id', $tahunAjaranId);
        })
        ->select('id', 'nomor_kelas', 'nama_kelas')
        ->orderBy('nomor_kelas')
        ->orderBy('nama_kelas')
        ->get()
        ->unique(function($item) {
            // Create a unique key combining class number and name
            return $item->nomor_kelas . '-' . $item->nama_kelas;
        });
        
        $guru = Guru::with(['kelasPengajar', 'mataPelajarans'])->get();
        $informationItems = Notification::latest()->get();

        return view('admin.dashboard', compact(
            'totalStudents',
            'totalTeachers',
            'totalSubjects',
            'totalClasses',
            'totalExtracurriculars',
            'overallProgress',
            'kelas',
            'guru',
            'informationItems'
        ));
    }

    private function calculateOverallProgressForAdmin($tahunAjaranId = null)
    {
        try {
            $tahunAjaranId = $tahunAjaranId ?: session('tahun_ajaran_id');
            
            // Get all active classes for the current tahun ajaran
            $kelasIds = \App\Models\Kelas::where('tahun_ajaran_id', $tahunAjaranId)
                ->pluck('id');
                
            if ($kelasIds->isEmpty()) {
                \Log::info("No classes found for tahun ajaran: {$tahunAjaranId}");
                return 0;
            }
            
            // Get all students in these classes
            $totalStudents = \App\Models\Siswa::whereIn('kelas_id', $kelasIds)->count();
            
            // Get all mata pelajaran for these classes
            $mataPelajarans = \App\Models\MataPelajaran::whereIn('kelas_id', $kelasIds)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->get();
                
            if ($mataPelajarans->isEmpty() || $totalStudents === 0) {
                \Log::info("No subjects or students found");
                return 0;
            }
            
            // Calculate the total number of scores needed
            $totalScoresNeeded = 0;
            $totalScoresCompleted = 0;
            
            foreach ($mataPelajarans as $mapel) {
                // Each student needs a final score for each subject
                $totalScoresNeeded += $totalStudents;
                
                // Count how many students have completed scores for this subject
                $completedScores = \App\Models\Nilai::where('mata_pelajaran_id', $mapel->id)
                    ->whereNotNull('nilai_akhir_rapor')
                    ->where('tahun_ajaran_id', $tahunAjaranId)
                    ->count();
                    
                $totalScoresCompleted += $completedScores;
            }
            
            \Log::info("Total scores needed: {$totalScoresNeeded}, completed: {$totalScoresCompleted}");
            
            // Calculate percentage
            $progress = $totalScoresNeeded > 0 ? 
                min(100, ($totalScoresCompleted / $totalScoresNeeded) * 100) : 0;
                
            \Log::info("Calculated overall progress: {$progress}%");
            
            return $progress;
        } catch (\Exception $e) {
            \Log::error('Error calculating admin overall progress: ' . $e->getMessage());
            return 0;
        }
    }

    public function pengajarDashboard()
    {
        try {
            $guru = Auth::guard('guru')->user();
            $tahunAjaranId = session('tahun_ajaran_id');
            
            if (!$guru) {
                return redirect()->route('login');
            }
            
            // Hitung jumlah kelas yang diajar
            $kelasCount = MataPelajaran::where('guru_id', $guru->id)
                ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                    return $query->where('tahun_ajaran_id', $tahunAjaranId);
                })
                ->distinct('kelas_id')
                ->count('kelas_id');
                
            // Hitung jumlah mata pelajaran yang diajar - dengan penghapusan duplikat
            $mapelCount = MataPelajaran::where('guru_id', $guru->id)
                ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                    return $query->where('tahun_ajaran_id', $tahunAjaranId);
                })
                ->distinct('nama_pelajaran', 'kelas_id') // Pastikan unik berdasarkan nama dan kelas
                ->count();
                
            // Hitung jumlah siswa yang diajar (unique)
            $siswaCount = Siswa::whereIn('kelas_id', function($query) use ($guru, $tahunAjaranId) {
                $query->select('kelas_id')
                    ->from('mata_pelajarans')
                    ->where('guru_id', $guru->id)
                    ->when($tahunAjaranId, function($q) use ($tahunAjaranId) {
                        return $q->where('tahun_ajaran_id', $tahunAjaranId);
                    })
                    ->distinct();
            })->count();
            
            // Ambil daftar kelas dengan mata pelajaran yang sudah difilter untuk menghindari duplikasi
            $kelas = Kelas::whereIn('id', function($query) use ($guru, $tahunAjaranId) {
                $query->select('kelas_id')
                    ->from('mata_pelajarans')
                    ->where('guru_id', $guru->id)
                    ->when($tahunAjaranId, function($q) use ($tahunAjaranId) {
                        return $q->where('tahun_ajaran_id', $tahunAjaranId);
                    })
                    ->distinct();
            })->get();
            
            // Praproseskan mata pelajaran untuk menghindari duplikasi dalam dropdown
            foreach($kelas as $kelasItem) {
                // Dapatkan mata pelajaran unik berdasarkan nama untuk kelas ini
                $uniqueSubjects = $kelasItem->mataPelajarans
                    ->where('guru_id', $guru->id)
                    ->unique(function ($item) {
                        return $item->nama_pelajaran;
                    });
                    
                // Ganti koleksi mata pelajaran dengan yang unik
                $kelasItem->setRelation('mataPelajarans', $uniqueSubjects);
            }
            
            // Hitung progress keseluruhan
            $totalStudentSubjects = 0;  // Total siswa * mata pelajaran
            $completedStudentSubjects = 0;  // Total nilai yang telah diinput
                        
            // Get all mata pelajaran taught by this teacher
            $mataPelajarans = MataPelajaran::where('guru_id', $guru->id)
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->get();

            foreach ($mataPelajarans as $mataPelajaran) {
            // Count students in this class
            $studentsInClass = Siswa::where('kelas_id', $mataPelajaran->kelas_id)->count();

            // Total yang harus dinilai: jumlah siswa di kelas ini
            $totalStudentSubjects += $studentsInClass;

            // Count students with completed scores
            $completedCount = Nilai::where('mata_pelajaran_id', $mataPelajaran->id)
                ->whereNotNull('nilai_akhir_rapor')
                ->count();
                
            $completedStudentSubjects += $completedCount;
            }

            // Cek apakah totalStudentSubjects > 0 untuk menghindari division by zero
            $overallProgress = ($totalStudentSubjects > 0) 
            ? min(100, ($completedStudentSubjects / $totalStudentSubjects) * 100) 
            : 0;

            // Log untuk debug
            \Log::info("Overall progress calculation:", [
            'total_student_subjects' => $totalStudentSubjects,
            'completed_student_subjects' => $completedStudentSubjects,
            'progress_percentage' => $overallProgress
            ]);
            // Ambil notifikasi dengan proper error handling
            try {
                $notifications = Notification::where(function($query) use ($guru) {
                    $query->where('target', 'all')
                          ->orWhere('target', 'guru')
                          ->orWhere(function($q) use ($guru) {
                              $q->where('target', 'specific')
                                ->whereRaw('JSON_CONTAINS(specific_users, ?)', [json_encode($guru->id)]);
                          });
                })
                ->latest()
                ->get()
                ->map(function ($notification) use ($guru) {
                    $notification->is_read = $notification->isReadBy($guru->id);
                    return $notification;
                });
            } catch (\Exception $e) {
                \Log::error('Error fetching notifications: ' . $e->getMessage());
                $notifications = collect();
            }
            
            // Cache data stats untuk performa
            $cacheKey = "guru_{$guru->id}_dashboard_stats";
            $cacheDuration = now()->addMinutes(5);
            
            $stats = Cache::remember($cacheKey, $cacheDuration, function () use (
                $kelasCount, 
                $mapelCount, 
                $siswaCount, 
                $overallProgress
            ) {
                return [
                    'kelasCount' => $kelasCount,
                    'mapelCount' => $mapelCount,
                    'siswaCount' => $siswaCount,
                    'overallProgress' => $overallProgress
                ];
            });
            
            return view('pengajar.dashboard', [
                'kelas' => $kelas,
                'overallProgress' => $stats['overallProgress'],
                'kelasCount' => $stats['kelasCount'],
                'mapelCount' => $stats['mapelCount'],
                'siswaCount' => $stats['siswaCount'],
                'notifications' => $notifications
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in pengajarDashboard: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat dashboard');
        }
    }

    public function waliKelasDashboard() 
    {
        try {
            $guru = auth()->guard('guru')->user();
            $tahunAjaranId = session('tahun_ajaran_id');
            $selectedSemester = session('selected_semester', 1); // Default ke semester 1
            
            \Log::info("Wali Kelas Dashboard", [
                'guru_id' => $guru->id,
                'tahun_ajaran_id' => $tahunAjaranId,
                'selected_semester' => $selectedSemester
            ]);
            
            // Get kelas yang diwalikan oleh guru ini untuk tahun ajaran yang dipilih
            $kelasWali = DB::table('guru_kelas')
                ->join('kelas', 'guru_kelas.kelas_id', '=', 'kelas.id')
                ->where('guru_kelas.guru_id', $guru->id)
                ->where('guru_kelas.is_wali_kelas', true)
                ->where('guru_kelas.role', 'wali_kelas')
                ->where('kelas.tahun_ajaran_id', $tahunAjaranId)
                ->select('kelas.id', 'kelas.nomor_kelas', 'kelas.nama_kelas')
                ->first();
                
            \Log::info("Kelas wali yang ditemukan", [
                'kelas_wali' => $kelasWali ?? 'Tidak ditemukan'
            ]);
            
            if (!$kelasWali) {
                // Jika kelasWali tidak ditemukan, coba tampilkan semua relasi guru-kelas untuk debugging
                $guruKelasRelations = DB::table('guru_kelas')
                    ->join('kelas', 'guru_kelas.kelas_id', '=', 'kelas.id')
                    ->where('guru_kelas.guru_id', $guru->id)
                    ->select('guru_kelas.*', 'kelas.tahun_ajaran_id', 'kelas.nomor_kelas', 'kelas.nama_kelas')
                    ->get();
                    
                \Log::info("Semua relasi guru-kelas", [
                    'relations' => $guruKelasRelations
                ]);
                
                return view('wali_kelas.dashboard', [
                    'totalSiswa' => 0,
                    'totalMapel' => 0,
                    'totalEkskul' => 0,
                    'totalAbsensi' => 0,
                    'kelas' => null,
                    'notifications' => collect(),
                    'recentActivities' => collect(),
                    'schoolProfile' => \App\Models\ProfilSekolah::first()
                ]);
            }
            
            // Get stats data
            $totalSiswa = \App\Models\Siswa::where('kelas_id', $kelasWali->id)->count();
            
            \Log::info("Total siswa di kelas", [
                'kelas_id' => $kelasWali->id,
                'total_siswa' => $totalSiswa
            ]);
            
            // Get mata pelajaran count
            $totalMapel = \App\Models\MataPelajaran::where('kelas_id', $kelasWali->id)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->count();
            
            // Get absensi count
            $totalAbsensi = DB::table('absensis')
                ->join('siswas', 'absensis.siswa_id', '=', 'siswas.id')
                ->where('siswas.kelas_id', $kelasWali->id)
                ->where('absensis.tahun_ajaran_id', $tahunAjaranId)
                ->count();
                
            // Get ekstrakurikuler count
            try {
                $totalEkskul = DB::table('nilai_ekstrakurikuler')
                    ->join('siswas', 'nilai_ekstrakurikuler.siswa_id', '=', 'siswas.id')
                    ->where('siswas.kelas_id', $kelasWali->id)
                    ->where('nilai_ekstrakurikuler.tahun_ajaran_id', $tahunAjaranId)
                    ->distinct('ekstrakurikuler_id')
                    ->count('ekstrakurikuler_id');
            } catch (\Exception $e) {
                \Log::warning('Tabel nilai_ekstrakurikuler error: ' . $e->getMessage());
                $totalEkskul = 0;
            }
            
            // Get kelas info
            $kelas = \App\Models\Kelas::find($kelasWali->id);
            
            // Get notifications
            $notifications = \App\Models\Notification::where(function($query) use ($guru) {
                $query->where('target', 'all')
                    ->orWhere('target', 'wali_kelas')
                    ->orWhere(function($q) use ($guru) {
                        $q->where('target', 'specific')
                            ->whereRaw('JSON_CONTAINS(specific_users, ?)', [json_encode($guru->id)]);
                    });
            })
            ->latest()
            ->get();
            
            // Get recent activities
            $recentActivities = DB::table('nilais')
                ->join('siswas', 'nilais.siswa_id', '=', 'siswas.id')
                ->join('mata_pelajarans', 'nilais.mata_pelajaran_id', '=', 'mata_pelajarans.id')
                ->where('siswas.kelas_id', $kelasWali->id)
                ->where('nilais.tahun_ajaran_id', $tahunAjaranId)
                ->whereNotNull('nilais.nilai_tp')
                ->select(
                    'siswas.nama',
                    'mata_pelajarans.nama_pelajaran',
                    'nilais.created_at'
                )
                ->orderBy('nilais.created_at', 'desc')
                ->limit(5)
                ->get();
                
            // Get school profile  
            $schoolProfile = \App\Models\ProfilSekolah::first();
            
            // Tambahkan data debugging untuk troubleshooting
            $debugData = [
                'tahunAjaranId' => $tahunAjaranId,
                'selectedSemester' => $selectedSemester,
                'kelasWaliId' => $kelasWali->id,
                'guruId' => $guru->id
            ];

            return view('wali_kelas.dashboard', compact(
                'totalSiswa',
                'totalMapel', 
                'totalEkskul',
                'totalAbsensi',
                'kelas',
                'notifications',
                'recentActivities',
                'schoolProfile',
                'debugData' // Tambahkan data debugging ke view
            ));

        } catch (\Exception $e) {
            \Log::error('Error in waliKelasDashboard: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->with('error', 'Terjadi kesalahan saat memuat dashboard: ' . $e->getMessage());
        }
    }
    
    // Method untuk mengambil progress keseluruhan kelas wali
    public function getOverallProgressWaliKelas()
    {
        try {
            $waliKelas = auth()->guard('guru')->user();
            $tahunAjaranId = session('tahun_ajaran_id');
        
            if (!$waliKelas || session('selected_role') !== 'wali_kelas') {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            // Ambil kelas yang diwalikan oleh guru
            $kelas = $waliKelas->kelasWali;
            
            if (!$kelas) {
                return response()->json(['progress' => 0]);
            }
            
            $totalTPQuery = DB::table('mata_pelajarans')
                ->join('lingkup_materis', 'mata_pelajarans.id', '=', 'lingkup_materis.mata_pelajaran_id')
                ->join('tujuan_pembelajarans', 'lingkup_materis.id', '=', 'tujuan_pembelajarans.lingkup_materi_id')
                ->where('mata_pelajarans.kelas_id', $kelas->id);
                
            if ($tahunAjaranId) {
                $totalTPQuery->where('mata_pelajarans.tahun_ajaran_id', $tahunAjaranId);
            }
            
            $totalTP = $totalTPQuery->count();
    
            if ($totalTP === 0) {
                return response()->json(['progress' => 0]);
            }
    
            $completedTPQuery = DB::table('mata_pelajarans')
                ->join('lingkup_materis', 'mata_pelajarans.id', '=', 'lingkup_materis.mata_pelajaran_id')
                ->join('tujuan_pembelajarans', 'lingkup_materis.id', '=', 'tujuan_pembelajarans.lingkup_materi_id')
                ->join('nilais', function($join) {
                    $join->on('tujuan_pembelajarans.id', '=', 'nilais.tujuan_pembelajaran_id')
                        ->whereNotNull('nilais.nilai_tp');
                })
                ->where('mata_pelajarans.kelas_id', $kelas->id);
                
            if ($tahunAjaranId) {
                $completedTPQuery->where('mata_pelajarans.tahun_ajaran_id', $tahunAjaranId);
                $completedTPQuery->where('nilais.tahun_ajaran_id', $tahunAjaranId);
            }
            
            $completedTP = $completedTPQuery->count();
    
            $progress = ($completedTP / $totalTP) * 100;
    
            return response()->json(['progress' => round($progress, 2)]);
    
        } catch (\Exception $e) {
            \Log::error('Error calculating overall progress: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan'], 500);
        }
    }
    
    /**
     * Get progress for a specific mata pelajaran
     * 
     * @param int $mataPelajaranId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMataPelajaranProgress($mataPelajaranId)
    {
        try {
            $guru = Auth::guard('guru')->user();
            if (!$guru) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $mataPelajaran = MataPelajaran::findOrFail($mataPelajaranId);
            
            // Check if guru teaches this subject
            if ($mataPelajaran->guru_id !== $guru->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Get students in this class
            $siswaCount = Siswa::where('kelas_id', $mataPelajaran->kelas_id)->count();
            
            if ($siswaCount === 0) {
                return response()->json(['progress' => 0]);
            }

            // Count completed scores for this subject
            $completedCount = Nilai::where('mata_pelajaran_id', $mataPelajaranId)
                ->whereNotNull('nilai_akhir_rapor')
                ->count();

            // Calculate progress percentage (handle division by zero)
            $progress = $siswaCount > 0 ? ($completedCount / $siswaCount) * 100 : 0;

            return response()->json([
                'progress' => round($progress, 2),
                'completed' => $completedCount,
                'total' => $siswaCount
            ]);
        } catch (\Exception $e) {
            \Log::error('Error calculating mata pelajaran progress: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan'], 500);
        }
    }
    // Method untuk mengambil progress per mata pelajaran untuk kelas wali
    public function getKelasProgressWaliKelas() 
    {
        try {
            $waliKelas = auth()->guard('guru')->user();
            $tahunAjaranId = session('tahun_ajaran_id');
            
            // Tambahkan pengecekan role
            if (!$waliKelas || session('selected_role') !== 'wali_kelas') {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            // Ambil kelas yang diwalikan oleh guru
            $kelas = $waliKelas->kelasWali;
            
            if (!$kelas) {
                return response()->json(['progress' => 0]);
            }
            
            // Cache hasil untuk performa
            $cacheKey = "wali_kelas_progress_{$waliKelas->id}";
            $cacheDuration = now()->addMinutes(5);
            
            return Cache::remember($cacheKey, $cacheDuration, function() use ($kelas, $tahunAjaranId) {
                $mataPelajarans = MataPelajaran::where('kelas_id', $kelas->id)
                    ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                        return $query->where('tahun_ajaran_id', $tahunAjaranId);
                    })
                    ->get();
                                            
                $totalProgress = 0;
                $mapelCount = $mataPelajarans->count();
        
                foreach ($mataPelajarans as $mapel) {
                    $totalTP = DB::table('lingkup_materis')
                        ->join('tujuan_pembelajarans', 'lingkup_materis.id', '=', 'tujuan_pembelajarans.lingkup_materi_id')
                        ->where('lingkup_materis.mata_pelajaran_id', $mapel->id)
                        ->count();
        
                    if ($totalTP > 0) {
                        $completedTP = DB::table('lingkup_materis')
                            ->join('tujuan_pembelajarans', 'lingkup_materis.id', '=', 'tujuan_pembelajarans.lingkup_materi_id')
                            ->join('nilais', function($join) use ($tahunAjaranId) {
                                $join->on('tujuan_pembelajarans.id', '=', 'nilais.tujuan_pembelajaran_id')
                                    ->whereNotNull('nilais.nilai_tp');
                                    
                                if ($tahunAjaranId) {
                                    $join->where('nilais.tahun_ajaran_id', $tahunAjaranId);
                                }
                            })
                            ->where('lingkup_materis.mata_pelajaran_id', $mapel->id)
                            ->count();
        
                        $totalProgress += ($completedTP / $totalTP) * 100;
                    }
                }
        
                $averageProgress = $mapelCount > 0 ? $totalProgress / $mapelCount : 0;
        
                return response()->json([
                    'progress' => round($averageProgress, 2),
                    'details' => [
                        'kelas_id' => $kelas->id,
                        'total_mapel' => $mapelCount
                    ]
                ]);
            });
    
        } catch (\Exception $e) {
            \Log::error('Error calculating class progress: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan'], 500);
        }
    }

    
    private function getNotifications($guru)
    {
        try {
            return Notification::where(function($query) use ($guru) {
                $query->where('target', 'all')
                      ->orWhere('target', 'wali_kelas')
                      ->orWhere(function($q) use ($guru) {
                          $q->where('target', 'specific')
                            ->whereRaw("JSON_CONTAINS(specific_users, ?)", [json_encode($guru->id)]);
                      });
            })
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($notification) use ($guru) {
                $notification->is_read = $notification->isReadBy($guru->id);
                return $notification;
            });
        } catch (\Exception $e) {
            \Log::error('Error fetching notifications: ' . $e->getMessage());
            return collect();
        }
    }

    public function getKelasProgressAdmin($kelasId)
    {
        try {
            // Get the current tahun ajaran ID from session
            $tahunAjaranId = session('tahun_ajaran_id');
            
            // Get all students in this class
            $studentsInClass = \App\Models\Siswa::where('kelas_id', $kelasId)->count();
            
            if ($studentsInClass === 0) {
                return response()->json(['success' => true, 'progress' => 0]);
            }
            
            // Get all mata pelajaran for this class
            $mataPelajarans = \App\Models\MataPelajaran::where('kelas_id', $kelasId)
                ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                    return $query->where('tahun_ajaran_id', $tahunAjaranId);
                })
                ->get();
                
            if ($mataPelajarans->isEmpty()) {
                return response()->json(['success' => true, 'progress' => 0]);
            }
            
            // For each mata pelajaran, check if all students have completed scores
            $totalScoreNeeded = 0;
            $totalScoreCompleted = 0;
            
            foreach ($mataPelajarans as $mapel) {
                // Total required scores for this subject = number of students
                $totalScoreNeeded += $studentsInClass;
                
                // Count students with completed nilai_akhir_rapor for this subject
                $completedScores = \App\Models\Nilai::where('mata_pelajaran_id', $mapel->id)
                    ->whereNotNull('nilai_akhir_rapor')
                    ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                        return $query->where('tahun_ajaran_id', $tahunAjaranId);
                    })
                    ->count();
                    
                $totalScoreCompleted += $completedScores;
            }
            
            // Log the calculation for debugging
            \Log::info("Class {$kelasId} progress calculation:", [
                'students' => $studentsInClass,
                'subjects' => $mataPelajarans->count(),
                'total_needed' => $totalScoreNeeded,
                'total_completed' => $totalScoreCompleted
            ]);
            
            // Calculate progress percentage
            $progress = $totalScoreNeeded > 0 ? ($totalScoreCompleted / $totalScoreNeeded) * 100 : 0;
            
            return response()->json([
                'success' => true, 
                'progress' => $progress,
                'details' => [
                    'total_needed' => $totalScoreNeeded,
                    'total_completed' => $totalScoreCompleted
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error calculating class progress: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function getNotificationStatus($notification, $userId)
    {
        return DB::table('notification_reads')
            ->where('notification_id', $notification->id)
            ->where('guru_id', $userId)
            ->exists();
    }

    private function calculateOverallProgress($guruId, $tahunAjaranId = null)
    {
        try {
            $kelasIds = MataPelajaran::where('guru_id', $guruId)
                ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                    return $query->where('tahun_ajaran_id', $tahunAjaranId);
                })
                ->distinct()
                ->pluck('kelas_id');
    
            if ($kelasIds->isEmpty()) {
                return 0;
            }
    
            $totalProgress = 0;
            foreach ($kelasIds as $kelasId) {
                $totalProgress += $this->calculateProgressByClass($guruId, $kelasId, $tahunAjaranId);
            }
    
            return $totalProgress / $kelasIds->count();
            
        } catch (\Exception $e) {
            \Log::error('Error calculating overall progress: ' . $e->getMessage());
            return 0;
        }
    }
    
    private function calculateProgressByClass($guruId, $kelasId, $tahunAjaranId = null)
    {
        try {
            // Hitung total TP
            $tpQuery = DB::table('mata_pelajarans')
                ->join('lingkup_materis', 'mata_pelajarans.id', '=', 'lingkup_materis.mata_pelajaran_id')
                ->join('tujuan_pembelajarans', 'lingkup_materis.id', '=', 'tujuan_pembelajarans.lingkup_materi_id')
                ->where('mata_pelajarans.guru_id', $guruId)
                ->where('mata_pelajarans.kelas_id', $kelasId);
                
            if ($tahunAjaranId) {
                $tpQuery->where('mata_pelajarans.tahun_ajaran_id', $tahunAjaranId);
            }
            
            $totalTP = $tpQuery->count();

            if ($totalTP === 0) {
                return 0;
            }

            // Hitung TP yang sudah ada nilainya
            $completedTPQuery = DB::table('mata_pelajarans')
                ->join('lingkup_materis', 'mata_pelajarans.id', '=', 'lingkup_materis.mata_pelajaran_id')
                ->join('tujuan_pembelajarans', 'lingkup_materis.id', '=', 'tujuan_pembelajarans.lingkup_materi_id')
                ->join('nilais', function($join) {
                    $join->on('tujuan_pembelajarans.id', '=', 'nilais.tujuan_pembelajaran_id')
                        ->whereNotNull('nilais.nilai_tp');
                })
                ->where('mata_pelajarans.guru_id', $guruId)
                ->where('mata_pelajarans.kelas_id', $kelasId);
                
            if ($tahunAjaranId) {
                $completedTPQuery->where('mata_pelajarans.tahun_ajaran_id', $tahunAjaranId);
                $completedTPQuery->where('nilais.tahun_ajaran_id', $tahunAjaranId);
            }
            
            $completedTP = $completedTPQuery->count();

            return ($completedTP / $totalTP) * 100;
            
        } catch (\Exception $e) {
            \Log::error('Error calculating class progress: ' . $e->getMessage());
            return 0;
        }
    }

    public function getKelasProgressPengajar($kelasId)
    {
        try {
            $guru = Auth::guard('guru')->user();
            if (!$guru) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $totalTP = DB::table('mata_pelajarans')
                ->join('lingkup_materis', 'mata_pelajarans.id', '=', 'lingkup_materis.mata_pelajaran_id')
                ->join('tujuan_pembelajarans', 'lingkup_materis.id', '=', 'tujuan_pembelajarans.lingkup_materi_id')
                ->where('mata_pelajarans.guru_id', $guru->id)
                ->where('mata_pelajarans.kelas_id', $kelasId)
                ->count();

            if ($totalTP === 0) {
                return response()->json(['progress' => 0]);
            }

            $completedTP = DB::table('mata_pelajarans')
                ->join('lingkup_materis', 'mata_pelajarans.id', '=', 'lingkup_materis.mata_pelajaran_id')
                ->join('tujuan_pembelajarans', 'lingkup_materis.id', '=', 'tujuan_pembelajarans.lingkup_materi_id')
                ->join('nilais', function($join) {
                    $join->on('tujuan_pembelajarans.id', '=', 'nilais.tujuan_pembelajaran_id')
                        ->whereNotNull('nilais.nilai_tp');
                })
                ->where('mata_pelajarans.guru_id', $guru->id)
                ->where('mata_pelajarans.kelas_id', $kelasId)
                ->count();

            $progress = ($completedTP / $totalTP) * 100;

            return response()->json(['progress' => round($progress, 2)]);
        } catch (\Exception $e) {
            \Log::error('Error calculating class progress: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan'], 500);
        }
    }
}
