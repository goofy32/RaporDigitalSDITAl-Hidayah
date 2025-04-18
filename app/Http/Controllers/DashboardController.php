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
        
        $totalSubjects = MataPelajaran::when($tahunAjaranId, function($query) use ($tahunAjaranId) {
            return $query->where('tahun_ajaran_id', $tahunAjaranId);
        })->count();
        
        $totalTeachers = Guru::count(); // Guru tetap dihitung semua
        
        $totalExtracurriculars = Ekstrakurikuler::when($tahunAjaranId && Schema::hasColumn('ekstrakurikulers', 'tahun_ajaran_id'), function($query) use ($tahunAjaranId) {
            return $query->where('tahun_ajaran_id', $tahunAjaranId);
        })->count();
        
        $overallProgress = $this->calculateOverallProgressForAdmin($tahunAjaranId);
        
        $kelas = Kelas::when($tahunAjaranId, function($query) use ($tahunAjaranId) {
            return $query->where('tahun_ajaran_id', $tahunAjaranId);
        })->get();
        
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
            $query = DB::table('nilais')->whereNotNull('nilai_tp');
            
            if ($tahunAjaranId) {
                $query->where('tahun_ajaran_id', $tahunAjaranId);
            }
            
            $totalNilai = $query->count();
            
            $tpQuery = DB::table('tujuan_pembelajarans');
            if ($tahunAjaranId) {
                $tpQuery->whereHas('lingkupMateri.mataPelajaran', function($q) use ($tahunAjaranId) {
                    $q->where('tahun_ajaran_id', $tahunAjaranId);
                });
            }
            
            $totalTP = $tpQuery->count();
            
            if ($totalTP === 0) return 0;
            
            return ($totalNilai / $totalTP) * 100;
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
                
            // Hitung jumlah mata pelajaran yang diajar
            $mapelCount = MataPelajaran::where('guru_id', $guru->id)
                ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                    return $query->where('tahun_ajaran_id', $tahunAjaranId);
                })
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
            
            // Ambil daftar kelas
            $kelas = Kelas::whereIn('id', function($query) use ($guru, $tahunAjaranId) {
                $query->select('kelas_id')
                    ->from('mata_pelajarans')
                    ->where('guru_id', $guru->id)
                    ->when($tahunAjaranId, function($q) use ($tahunAjaranId) {
                        return $q->where('tahun_ajaran_id', $tahunAjaranId);
                    })
                    ->distinct();
            })->get();
            
            // Hitung progress keseluruhan
            $overallProgress = $this->calculateOverallProgress($guru->id, $tahunAjaranId);
            
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
            $waliKelas = auth()->guard('guru')->user();
            $tahunAjaranId = session('tahun_ajaran_id');
            
            // Hanya wali kelas yang boleh akses
            if (!$waliKelas || session('selected_role') !== 'wali_kelas') {
                return redirect()->route('login');
            }
            
            // Ambil kelas yang diwalikan oleh guru
            $kelas = $waliKelas->kelasWali;
            
            if (!$kelas) {
                return view('wali_kelas.dashboard', [
                    'totalSiswa' => 0,
                    'totalAbsensi' => 0,
                    'totalEkskul' => 0,
                    'kelas' => null,
                    'notifications' => collect(),
                    'recentActivities' => collect(),
                    'schoolProfile' => ProfilSekolah::first()
                ]);
            }
            
            // Get stats data
            $totalSiswa = Siswa::where('kelas_id', $kelas->id)
                ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                    return $query->whereHas('kelas', function($q) use ($tahunAjaranId) {
                        $q->where('tahun_ajaran_id', $tahunAjaranId);
                    });
                })
                ->count();
            
            // Perbaikan cara menghitung absensi
            $totalAbsensi = DB::table('absensis')
                ->join('siswas', 'absensis.siswa_id', '=', 'siswas.id')
                ->where('siswas.kelas_id', $kelas->id)
                ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                    return $query->where('absensis.tahun_ajaran_id', $tahunAjaranId);
                })
                ->count();
                
            $totalMapel = MataPelajaran::where('kelas_id', $kelas->id)
                ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                    return $query->where('tahun_ajaran_id', $tahunAjaranId);
                })
                ->count();
            
            try {
                $totalEkskul = DB::table('nilai_ekstrakurikuler')
                    ->join('siswas', 'nilai_ekstrakurikuler.siswa_id', '=', 'siswas.id')
                    ->where('siswas.kelas_id', $kelas->id)
                    ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                        return $query->where('nilai_ekstrakurikuler.tahun_ajaran_id', $tahunAjaranId);
                    })
                    ->distinct('ekstrakurikuler_id')
                    ->count('ekstrakurikuler_id');
            } catch (\Exception $e) {
                \Log::warning('Tabel nilai_ekstrakurikuler belum tersedia: ' . $e->getMessage());
                $totalEkskul = 0;
            }
            
            // Get kelas info
            $kelas = Kelas::where('id', $kelas->id)->first();
            
            // Get notifications
            $notifications = Notification::where(function($query) use ($waliKelas) {
                $query->where('target', 'all')
                      ->orWhere('target', 'wali_kelas')
                      ->orWhere(function($q) use ($waliKelas) {
                          $q->where('target', 'specific')
                            ->whereRaw('JSON_CONTAINS(specific_users, ?)', [json_encode($waliKelas->id)]);
                      });
            })
            ->latest()
            ->get();
            
            // Get recent activities
            $recentActivities = DB::table('nilais')
                ->join('siswas', 'nilais.siswa_id', '=', 'siswas.id')
                ->join('mata_pelajarans', 'nilais.mata_pelajaran_id', '=', 'mata_pelajarans.id')
                ->where('siswas.kelas_id', $kelas->id)
                ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                    return $query->where('nilais.tahun_ajaran_id', $tahunAjaranId);
                })
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
            $schoolProfile = ProfilSekolah::first();
    
            return view('wali_kelas.dashboard', compact(
                'totalSiswa',
                'totalMapel', 
                'totalEkskul',
                'totalAbsensi',
                'kelas',
                'notifications',
                'recentActivities',
                'schoolProfile'
            ));
    
        } catch (\Exception $e) {
            \Log::error('Error in waliKelasDashboard: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat dashboard.');
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
        $totalTP = DB::table('mata_pelajarans')
            ->join('lingkup_materis', 'mata_pelajarans.id', '=', 'lingkup_materis.mata_pelajaran_id')
            ->join('tujuan_pembelajarans', 'lingkup_materis.id', '=', 'tujuan_pembelajarans.lingkup_materi_id')
            ->where('mata_pelajarans.kelas_id', $kelasId)
            ->count();

        if ($totalTP === 0) {
            return response()->json(['success' => true, 'progress' => 0]);
        }

        $completedTP = DB::table('mata_pelajarans')
            ->join('lingkup_materis', 'mata_pelajarans.id', '=', 'lingkup_materis.mata_pelajaran_id')
            ->join('tujuan_pembelajarans', 'lingkup_materis.id', '=', 'tujuan_pembelajarans.lingkup_materi_id')
            ->join('nilais', function($join) {
                $join->on('tujuan_pembelajarans.id', '=', 'nilais.tujuan_pembelajaran_id')
                    ->whereNotNull('nilais.nilai_tp');
            })
            ->where('mata_pelajarans.kelas_id', $kelasId)
            ->count();

        $progress = ($completedTP / $totalTP) * 100;

        return response()->json(['success' => true, 'progress' => $progress]);
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
