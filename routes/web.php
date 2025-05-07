<?php

use App\Http\Controllers\AbsensiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolProfileController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\AchievementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\TujuanPembelajaranController;
use App\Http\Controllers\EkstrakurikulerController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\GeminiChatController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\KkmController;
use App\Http\Controllers\BobotNilaiController;
use App\Http\Controllers\KenaikanKelasController;
use App\Models\FormatRapor;
use Illuminate\Support\Facades\Auth;

// Root route with role-based redirection
Route::get('/', function () {
    if (Auth::guard('web')->check()) {
        return redirect()->route('admin.dashboard');
    } elseif (Auth::guard('guru')->check()) {
        $selectedRole = session('selected_role');
        
        // Tambahkan pengecekan role yang valid
        if (!in_array($selectedRole, ['guru', 'wali_kelas'])) {
            Auth::guard('guru')->logout();
            return redirect()->route('login')
                ->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }
        
        return $selectedRole === 'wali_kelas' 
            ? redirect()->route('wali_kelas.dashboard')
            : redirect()->route('pengajar.dashboard');
    }
    return redirect()->route('login');
});

Route::fallback(function () {
    if (Auth::guard('web')->check()) {
        return redirect()->route('admin.dashboard');
    }

    if (Auth::guard('guru')->check()) {
        $selectedRole = session('selected_role');
        
        if ($selectedRole === 'wali_kelas') {
            return redirect()->route('wali_kelas.dashboard');
        } else if ($selectedRole === 'guru') {
            return redirect()->route('pengajar.dashboard');
        }
    }
    
    return redirect()->route('login');
});
// Login Routes
Route::middleware(['web', 'guest'])->group(function () {
    Route::get('login', function () {
        // Cek jika user sudah login
        if (Auth::guard('web')->check()) {
            return redirect()->route('admin.dashboard');
        }
        
        if (Auth::guard('guru')->check()) {
            $selectedRole = session('selected_role');
            return $selectedRole === 'wali_kelas' 
                ? redirect()->route('wali_kelas.dashboard')
                : redirect()->route('pengajar.dashboard');
        }
        
        return view('login');
    })->name('login');

    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.post');
});


Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/admin/check-password-format/{id}', function($id) {
    $guru = \App\Models\Guru::find($id);
    if (!$guru) return 'Guru tidak ditemukan';
    
    // Cek apakah password disimpan dengan format bcrypt (dimulai dengan $2y$)
    $passwordFormat = substr($guru->password, 0, 4);
    
    return "Format password: {$passwordFormat}. " . 
           "Benar jika dimulai dengan \$2y\$ atau \$2a\$. " .
           "Password length: " . strlen($guru->password);
})->middleware(['auth:web']);

// Admin Routes - Guard: web, Role: admin only
Route::middleware(['auth:web', 'role:admin', 'check.basic.setup'])->prefix('admin')->group(function () {

    Route::prefix('kkm')->name('admin.kkm.')->group(function() {
        Route::get('/', [KkmController::class, 'index'])->name('index');
        Route::post('/', [KkmController::class, 'store'])->name('store');
        Route::get('/list', [KkmController::class, 'getKkmList'])->name('list');
        // Route baru untuk KKM massal
        Route::post('/global', [KkmController::class, 'applyGlobalKkm'])->name('global');
        Route::delete('/{id}', [KkmController::class, 'destroy'])->name('destroy');
    });

    // Bobot Nilai Routes
    Route::prefix('bobot-nilai')->name('admin.bobot_nilai.')->group(function() {
        Route::get('/', [BobotNilaiController::class, 'index'])->name('index');
        Route::post('/', [BobotNilaiController::class, 'update'])->name('update');
        Route::get('/data', [BobotNilaiController::class, 'getBobot'])->name('data');
    });
    Route::post('/report-history/regenerate/{report}', [ReportController::class, 'regenerateHistoryRapor'])
    ->name('admin.report.history.regenerate');

    Route::get('/report-history/preview/{report}', [ReportController::class, 'previewHistoryRapor'])
    ->name('admin.report.history.preview');

    // Endpoint untuk mendapatkan data kelas
    Route::get('/kelas/data', function() {
        $tahunAjaranId = session('tahun_ajaran_id');
        $kelas = App\Models\Kelas::with(['mataPelajarans' => function($query) use ($tahunAjaranId) {
            $query->where('tahun_ajaran_id', $tahunAjaranId);
        }])->where('tahun_ajaran_id', $tahunAjaranId)->get();
        
        return response()->json(['kelas' => $kelas]);
    })->name('kelas.data');

    Route::post('/gemini/send-message', [GeminiChatController::class, 'sendMessage'])->name('gemini.send');
    Route::get('/gemini/history', [GeminiChatController::class, 'getHistory'])->name('gemini.history');

    Route::get('/set-tahun-ajaran/{id}', function($id) {
        session(['tahun_ajaran_id' => $id]);
        return redirect()->back()->with('success', 'Tahun ajaran berhasil diubah');
    })->name('tahun.ajaran.set-session');
        // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/kelas-progress/{id}', [DashboardController::class, 'getKelasProgressAdmin'])
        ->name('admin.kelas.progress');
    
    // Information/Notifications
    Route::prefix('information')->name('information.')->group(function () {
        Route::post('/', [NotificationController::class, 'store'])->name('store');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::get('/list', [NotificationController::class, 'list'])->name('list');
    });

    Route::prefix('audit')->name('admin.audit.')->group(function () {
        Route::get('/', [AuditController::class, 'index'])->name('index');
        Route::get('/export', [AuditController::class, 'export'])->name('export');
        Route::post('/clear', [AuditController::class, 'clear'])->name('clear');
        Route::get('/{auditLog}', [AuditController::class, 'show'])->name('show');
    });
    
    // Profile Routes
    Route::get('profile', [SchoolProfileController::class, 'show'])->name('profile');
    Route::get('profile/edit', [SchoolProfileController::class, 'edit'])->name('profile.edit');
    Route::post('profile', [SchoolProfileController::class, 'store'])->name('profile.submit');
    
    // Student Management
    Route::resource('students', StudentController::class)->names([
        'index' => 'student',
        'create' => 'student.create',
        'store' => 'student.store',
        'show' => 'student.show',
        'edit' => 'student.edit',
        'update' => 'student.update',
        'destroy' => 'student.destroy',
    ]);
    
    Route::get('template/student', [StudentController::class, 'downloadTemplate'])->name('student.template');
    Route::get('students/upload', [StudentController::class, 'uploadPage'])->name('student.upload');
    Route::post('students/import', [StudentController::class, 'importExcel'])->name('student.import');

    // Subject Routes
    Route::resource('subject', SubjectController::class);

    // Class Management
    Route::get('kelas', [ClassController::class, 'index'])->name('kelas.index');
    Route::get('kelas/create', [ClassController::class, 'create'])->name('kelas.create');
    Route::post('kelas', [ClassController::class, 'store'])->name('kelas.store');
    Route::get('kelas/{id}', [ClassController::class, 'show'])->name('kelas.show');
    Route::get('kelas/{id}/edit', [ClassController::class, 'edit'])->name('kelas.edit');
    Route::put('kelas/{id}', [ClassController::class, 'update'])->name('kelas.update');
    Route::delete('kelas/{id}', [ClassController::class, 'destroy'])->name('kelas.destroy');
    
    Route::prefix('tahun-ajaran')->name('tahun.ajaran.')->group(function () {
        Route::post('/{id}/restore', [TahunAjaranController::class, 'restore'])->name('restore'); 
        Route::get('/', [TahunAjaranController::class, 'index'])->name('index');
        Route::get('/create', [TahunAjaranController::class, 'create'])->name('create');
        Route::post('/', [TahunAjaranController::class, 'store'])->name('store');
        Route::get('/{id}', [TahunAjaranController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [TahunAjaranController::class, 'edit'])->name('edit');
        Route::put('/{id}', [TahunAjaranController::class, 'update'])->name('update');
        Route::post('{id}/set-active', [TahunAjaranController::class, 'setActive'])->name('set-active');
        Route::get('/{id}/copy', [TahunAjaranController::class, 'copy'])->name('copy');
        Route::post('/{id}/copy', [TahunAjaranController::class, 'processCopy'])->name('process-copy');
        Route::delete('/{id}', [TahunAjaranController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/advance-semester', [TahunAjaranController::class, 'advanceToNextSemester'])
        ->name('advance-semester');
    });
    
    // Teacher Management
    Route::prefix('pengajar')->group(function () {
        Route::get('/', [TeacherController::class, 'index'])->name('teacher');
        Route::get('/create', [TeacherController::class, 'create'])->name('teacher.create');
        Route::post('/store', [TeacherController::class, 'store'])->name('teacher.store');
        Route::get('/{id}', [TeacherController::class, 'show'])->name('teacher.show');
        Route::get('/{id}/edit', [TeacherController::class, 'edit'])->name('teacher.edit');
        Route::put('/{id}', [TeacherController::class, 'update'])->name('teacher.update');
        Route::delete('/{id}', [TeacherController::class, 'destroy'])->name('teacher.destroy');
        Route::get('/{id}/password', [TeacherController::class, 'showPassword'])
        ->name('teacher.show_password');
        
        // Tambahkan rute baru di sini
        Route::post('/verify-password', [TeacherController::class, 'verifyPassword'])
            ->name('teacher.verify-password');
    });
    
    Route::prefix('kenaikan-kelas')->name('admin.kenaikan-kelas.')->group(function () {
        Route::get('/', [KenaikanKelasController::class, 'index'])->name('index');
        Route::get('/kelas/{id}', [KenaikanKelasController::class, 'showKelasSiswa'])->name('show-siswa');
        Route::post('/kenaikan', [KenaikanKelasController::class, 'processKenaikanKelas'])->name('process-kenaikan');
        Route::post('/tinggal', [KenaikanKelasController::class, 'processTinggalKelas'])->name('process-tinggal');
        Route::post('/kelulusan', [KenaikanKelasController::class, 'processKelulusan'])->name('process-kelulusan');
        Route::post('/mass-promotion', [KenaikanKelasController::class, 'processMassPromotion'])->name('process-mass');
    });

    // Achievement Routes
    Route::resource('achievement', AchievementController::class)->names([
        'index' => 'achievement.index',
        'create' => 'achievement.create',
        'store' => 'achievement.store',
        'edit' => 'achievement.edit',
        'update' => 'achievement.update',
        'destroy' => 'achievement.destroy',
    ]);
    
    // Learning Objectives
    Route::prefix('tujuan-pembelajaran')->name('tujuan_pembelajaran.')->group(function () {
        Route::get('/create/{mata_pelajaran_id}', [TujuanPembelajaranController::class, 'create'])->name('create');
        Route::post('/store', [TujuanPembelajaranController::class, 'store'])->name('store');
        Route::get('/{mata_pelajaran_id}', [TujuanPembelajaranController::class, 'view'])->name('view');
        Route::get('/{mata_pelajaran_id}/list', [TujuanPembelajaranController::class, 'listByMataPelajaran'])->name('list');
        Route::delete('/{id}', [TujuanPembelajaranController::class, 'destroy'])->name('destroy');
    });
    
    // Extracurricular
    Route::resource('ekstrakulikuler', EkstrakurikulerController::class)->names([
        'index' => 'ekstra.index',
        'create' => 'ekstra.create',
        'store' => 'ekstra.store',
        'edit' => 'ekstra.edit',
        'update' => 'ekstra.update',
        'destroy' => 'ekstra.destroy',
    ]);

    Route::get('/report-history', [ReportController::class, 'history'])->name('admin.report.history');
    Route::get('/report-history/download/{report}', [ReportController::class, 'downloadHistory'])->name('admin.report.history.download');
    
    Route::get('/report-template/tutorial', [ReportController::class, 'tutorialView'])
    ->name('report.template.tutorial');
    // Report Format
    Route::prefix('report-template')->name('report.template.')->group(function () {
        // Sample template download route
        Route::get('/sample', [ReportController::class, 'downloadSampleTemplate'])
        ->name('sample');
        Route::get('/placeholder-guide', [ReportController::class, 'placeholderGuide'])
            ->name('placeholder.guide');
        Route::get('/current', [ReportController::class, 'getCurrentTemplate'])
            ->name('current');
        Route::post('/upload', [ReportController::class, 'upload'])
            ->name('upload');
        // Route yang sudah ada - kita akan memodifikasi controller method-nya
        Route::get('/{template}/preview', [ReportController::class, 'preview'])
            ->name('preview');
        // Tambahkan route baru untuk preview dengan docx.js
        Route::get('/{template}/preview-data', [ReportController::class, 'previewData'])
            ->name('preview-data');
        Route::post('/{template}/activate', [ReportController::class, 'activate'])
            ->name('activate');
        Route::delete('/{template}', [ReportController::class, 'destroy'])
            ->name('destroy');
        Route::get('/{type?}', [ReportController::class, 'index'])
            ->name('index');
    });
});

// Get students for a given class
Route::get('/debug/check-students/{kelasId}', function($kelasId) {
        $kelas = \App\Models\Kelas::findOrFail($kelasId);
        $tahunAjaranId = session('tahun_ajaran_id');
        
        // Log basic info
        \Log::info("Debug check for kelas ID: {$kelasId}");
        \Log::info("Current tahun_ajaran_id: {$tahunAjaranId}");
        \Log::info("Kelas tahun_ajaran_id: {$kelas->tahun_ajaran_id}");
        
        $directStudents = $kelas->siswas;
        // Get students directly from the class
        \Log::info("Direct kelas->siswas count: " . $directStudents->count());
        
        // Get students with the query builder approach
        $queryStudents = \App\Models\Siswa::where('kelas_id', $kelasId)
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->whereHas('kelas', function($q) use ($tahunAjaranId) {
                    $q->where('tahun_ajaran_id', $tahunAjaranId);
                });
            })
            ->get();
        \Log::info("Query builder students count: " . $queryStudents->count());
        
        // Return the debug info
        return [
            'kelas_id' => $kelasId,
            'tahun_ajaran_id' => $tahunAjaranId,
            'kelas_tahun_ajaran_id' => $kelas->tahun_ajaran_id,
            'direct_students_count' => $directStudents->count(),
            'direct_students' => $directStudents->map(function($s) {
                return ['id' => $s->id, 'nama' => $s->nama];
            }),
            'query_students_count' => $queryStudents->count(),
            'query_students' => $queryStudents->map(function($s) {
                return ['id' => $s->id, 'nama' => $s->nama];
            })
        ];
    })->middleware(['auth:guru', 'role:guru']);

// Pengajar Routes - Guard: guru, Role: guru
Route::middleware(['auth:guru', 'role:guru'])
    ->prefix('pengajar')
    ->name('pengajar.')  // Tambahkan ini untuk name prefix
    ->group(function () {
    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
    });

    Route::get('/nilai/kkm/{mapelId}', [KkmController::class, 'getKkm'])->name('nilai.kkm');
    Route::get('/nilai/bobot', [BobotNilaiController::class, 'getBobot'])->name('nilai.bobot');
    
    Route::get('/dashboard', [DashboardController::class, 'pengajarDashboard'])->name('dashboard');
    Route::get('/kelas-progress/overall', [DashboardController::class, 'getOverallClassProgress'])
        ->name('kelas.progress.overall');
    
    // Route baru untuk mata pelajaran
    Route::get('/mata-pelajaran/kelas/{kelasId}', [SubjectController::class, 'getSubjectsByClass'])
        ->name('mata_pelajaran.by_kelas');
    
    Route::get('/mata-pelajaran/progress/{kelasId}', [SubjectController::class, 'getSubjectsProgress'])
        ->name('mata_pelajaran.progress');

    Route::get('/profile', [TeacherController::class, 'showProfile'])->name('profile');
    
    // Score Management
    Route::prefix('score')->name('score.')->middleware(['auto.sync.tahun.ajaran'])->group(function () {
        Route::get('/', [ScoreController::class, 'index'])->name('index');
        Route::get('/{id}/input', [ScoreController::class, 'inputScore'])->name('input_score');
        Route::post('/{id}/save', [ScoreController::class, 'saveScore'])->name('save_scores');
        Route::get('/{id}/preview', [ScoreController::class, 'previewScore'])->name('preview_score');
        Route::delete('/{id}', [ScoreController::class, 'deleteScores'])->name('delete');
        Route::post('/score/nilai/delete', [ScoreController::class, 'deleteNilai'])->name('nilai.delete');
        Route::post('/validate', [ScoreController::class, 'validateScores'])->name('validate');
        Route::post('/get-class-subjects', [ScoreController::class, 'getClassSubjects'])->name('get_class_subjects');
    });

    // Subject Management
    Route::prefix('subject')->name('subject.')->group(function () {
        Route::get('/', [SubjectController::class, 'teacherIndex'])->name('index');
        Route::get('/create', [SubjectController::class, 'teacherCreate'])->name('create');
        Route::post('/', [SubjectController::class, 'teacherStore'])->name('store');
        Route::get('/{id}/edit', [SubjectController::class, 'teacherEdit'])->name('edit');
        Route::put('/{id}', [SubjectController::class, 'teacherUpdate'])->name('update');
        Route::delete('/{id}', [SubjectController::class, 'teacherDestroy'])->name('destroy');
        Route::delete('/lingkup-materi/{id}', [SubjectController::class, 'deleteLingkupMateri'])->name('lingkup_materi.destroy');
        Route::get('/lingkup-materi/{id}/check-dependencies', [SubjectController::class, 'checkLingkupMateriDependencies'])
        ->name('lingkup_materi.check_dependencies');
    });

    // Learning Objectives
    Route::prefix('tujuan-pembelajaran')->name('tujuan_pembelajaran.')->group(function () {
        Route::get('/create/{mata_pelajaran_id}', [TujuanPembelajaranController::class, 'teacherCreate'])->name('create');
        Route::post('/store', [TujuanPembelajaranController::class, 'teacherStore'])->name('store');
        Route::get('/{mata_pelajaran_id}', [TujuanPembelajaranController::class, 'teacherView'])->name('view');
        Route::get('/{mata_pelajaran_id}/list', [TujuanPembelajaranController::class, 'listByMataPelajaran'])->name('list');
        Route::delete('/{id}', [TujuanPembelajaranController::class, 'teacherDestroy'])->name('destroy');
    });
});

// Wali Kelas Routes - Guard: guru, Role: wali_kelas
Route::middleware(['auth:guru', 'role:wali_kelas'])
    ->prefix('wali-kelas')
    ->name('wali_kelas.')
    ->group(function () {
    
        // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
    });
    

    Route::get('/dashboard', [DashboardController::class, 'waliKelasDashboard'])
    ->middleware('check.wali.kelas')  // Tambah middleware baru
    ->name('dashboard');
    Route::get('/profile', [TeacherController::class, 'showWaliKelasProfile'])->name('profile');
    Route::get('/overall-progress', [DashboardController::class, 'getOverallProgressWaliKelas'])
        ->name('overall.progress');
        
    Route::get('/kelas-progress', [DashboardController::class, 'getKelasProgressWaliKelas'])
        ->name('kelas.progress');
    // Student Management
    Route::prefix('siswa')->name('student.')->group(function () {
        Route::get('/', [StudentController::class, 'waliKelasIndex'])->name('index');
        Route::get('/create', [StudentController::class, 'waliKelasCreate'])->name('create'); 
        Route::post('/', [StudentController::class, 'waliKelasStore'])->name('store');
        Route::get('/{id}', [StudentController::class, 'waliKelasShow'])->name('show');
        Route::get('/{id}/edit', [StudentController::class, 'waliKelasEdit'])->name('edit');
        Route::put('/{id}', [StudentController::class, 'waliKelasUpdate'])->name('update');
        Route::delete('/{id}', [StudentController::class, 'waliKelasDestroy'])->name('destroy');
    });

    // Extracurricular
    Route::prefix('ekstrakurikuler')->name('ekstrakurikuler.')->group(function () {
        Route::get('/', [EkstrakurikulerController::class, 'waliKelasIndex'])->name('index');
        Route::get('/create', [EkstrakurikulerController::class, 'waliKelasCreate'])->name('create');
        Route::post('/', [EkstrakurikulerController::class, 'waliKelasStore'])->name('store');
        Route::get('/{id}/edit', [EkstrakurikulerController::class, 'waliKelasEdit'])->name('edit');
        Route::put('/{id}', [EkstrakurikulerController::class, 'waliKelasUpdate'])->name('update');
        Route::delete('/{id}', [EkstrakurikulerController::class, 'waliKelasDestroy'])->name('destroy');
    });

    // Absence Management
    Route::resource('absensi', AbsensiController::class)->names([
        'index' => 'absence.index',
        'create' => 'absence.create', 
        'store' => 'absence.store',
        'edit' => 'absence.edit',
        'update' => 'absence.update',
        'destroy' => 'absence.destroy',
    ]);

    Route::get('/lingkup-materi/{id}/check-dependencies', [TujuanPembelajaranController::class, 'checkLingkupMateriDependencies'])
    ->name('lingkup_materi.check_dependencies');
    
    // Add route for updating lingkup materi (if needed)
    Route::post('/lingkup-materi/{id}/update', [SubjectController::class, 'updateLingkupMateri'])
        ->name('lingkup_materi.update');
        
    // Ensure this route exists for tujuan pembelajaran view
    Route::get('/tujuan-pembelajaran/{mata_pelajaran_id}/view', [TujuanPembelajaranController::class, 'teacherView'])
        ->name('tujuan_pembelajaran.view');
        
    Route::prefix('rapor')->name('rapor.')->middleware(['auto.sync.tahun.ajaran'])->group(function () {
        Route::get('/', [ReportController::class, 'indexWaliKelas'])->name('index');
        
        // Gunakan model binding dan middleware
        Route::middleware('check.rapor.access')->group(function () {
            Route::post('/generate/{siswa}', [ReportController::class, 'generateReport'])->name('generate');
            Route::get('/download/{siswa}/{type}', [ReportController::class, 'downloadReport'])->name('download');
        });

        Route::get('/preview/{siswa}', [ReportController::class, 'previewRapor'])->name('preview');


        Route::get('/check-templates', [ReportController::class, 'checkActiveTemplates'])
        ->name('check-templates');
        
        Route::post('/batch-generate', [ReportController::class, 'generateBatchReport'])->name('batch.generate');
        Route::get('download-pdf/{siswa}', [ReportController::class, 'downloadPdf']) ->name('rapor.download-pdf');
        Route::get('/preview-pdf/{siswa}', [ReportController::class, 'previewPdf'])->name('preview-pdf');
        Route::get('/download-pdf/{siswa}', [ReportController::class, 'downloadPdf'])->name('download-pdf');
    });
});