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
    use App\Models\Siswa;
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
            Route::get('/notification-settings', [KkmController::class, 'getNotificationSettings'])
            ->name('notification-settings.get');
            Route::post('/notification-settings', [KkmController::class, 'saveNotificationSettings'])
                ->name('notification-settings.save');
        });

        Route::get('/tujuan-pembelajaran/{id}/check-dependencies', [TujuanPembelajaranController::class, 'checkDependencies'])
        ->name('tujuan_pembelajaran.check_dependencies');
        
        Route::get('/set-semester/{tahunAjaranId}/{semester}', [TahunAjaranController::class, 'setSessionSemester'])
        ->name('tahun.ajaran.set-semester');
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
            Route::delete('/{id}/force-delete', [TahunAjaranController::class, 'forceDelete'])->name('force-delete');
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

        // Pengajar Routes - Guard: guru, Role: guru
        Route::middleware(['auth:guru', 'role:guru'])
            ->prefix('pengajar')
            ->name('pengajar.')  // Tambahkan ini untuk name prefix
            ->group(function () {

        Route::get('/check-access/{mapelId}', function($mapelId) {
            $guru = Auth::guard('guru')->user();
            $mapel = \App\Models\MataPelajaran::find($mapelId);
            
            return [
                'guru_id' => $guru->id,
                'guru_name' => $guru->nama,
                'guru_role' => $guru->jabatan,
                'is_wali_kelas' => $guru->isWaliKelas(),
                'mapel_id' => $mapel->id,
                'mapel_name' => $mapel->nama_pelajaran,
                'mapel_guru_id' => $mapel->guru_id,
                'tahun_ajaran_match' => $mapel->tahun_ajaran_id == session('tahun_ajaran_id'),
                'session_tahun_ajaran' => session('tahun_ajaran_id'),
                'mapel_tahun_ajaran' => $mapel->tahun_ajaran_id,
                'has_access' => $mapel->guru_id === $guru->id
            ];
        })->middleware(['auth:guru']);

        Route::get('/debug/subject-edit/{id}', function($id) {
            $subject = \App\Models\MataPelajaran::with(['kelas', 'guru', 'lingkupMateris'])->find($id);
            $guru = auth()->guard('guru')->user();
            $tahunAjaranId = session('tahun_ajaran_id');
            
            $classes = \App\Models\Kelas::when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                    return $query->where('tahun_ajaran_id', $tahunAjaranId);
                })
                ->orderBy('nomor_kelas')
                ->orderBy('nama_kelas')
                ->get();
            
            // Check if the user is wali kelas and get their class
            $isWaliKelas = $guru->isWaliKelas();
            $kelasWaliId = $isWaliKelas ? $guru->getWaliKelasId() : null;
            
            return response()->json([
                'subject' => $subject,
                'guru' => [
                    'id' => $guru->id,
                    'nama' => $guru->nama,
                    'is_wali_kelas' => $isWaliKelas,
                    'kelas_wali_id' => $kelasWaliId
                ],
                'classes' => $classes->map(function($class) use ($kelasWaliId) {
                    return [
                        'id' => $class->id,
                        'nama' => "Kelas {$class->nomor_kelas} {$class->nama_kelas}",
                        'is_wali_kelas' => $class->id == $kelasWaliId
                    ];
                }),
                'session' => [
                    'tahun_ajaran_id' => $tahunAjaranId,
                    'selected_semester' => session('selected_semester')
                ]
            ]);
        })->middleware(['auth:guru']);

        // Add this to your routes/web.php file inside the pengajar route group
        Route::get('/score/{id}/check-access', function($id) {
            $mapel = \App\Models\MataPelajaran::find($id);
            $guru = Auth::guard('guru')->user();
            
            if (!$mapel) {
                return response()->json([
                    'hasAccess' => false,
                    'message' => 'Mata pelajaran tidak ditemukan'
                ]);
            }
            
            // Check if guru has access to this mapel
            if ($mapel->guru_id !== $guru->id) {
                return response()->json([
                    'hasAccess' => false,
                    'message' => 'Anda tidak memiliki akses ke mata pelajaran ini'
                ]);
            }
            
            // Check if tahun_ajaran_id matches
            $tahunAjaranId = session('tahun_ajaran_id');
            if ($tahunAjaranId && $mapel->tahun_ajaran_id != $tahunAjaranId) {
                return response()->json([
                    'hasAccess' => false,
                    'message' => 'Mata pelajaran tidak berada dalam tahun ajaran yang aktif'
                ]);
            }
            
            return response()->json([
                'hasAccess' => true,
                'message' => 'Akses diizinkan',
                'mapel' => $mapel
            ]);
        })->name('pengajar.score.check_access');
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
        Route::get('/kelas-progress/{id}', [DashboardController::class, 'getKelasProgress'])
            ->name('kelas.progress');
        Route::get('/mata-pelajaran-progress/{mataPelajaranId}', [DashboardController::class, 'getMataPelajaranProgress'])
            ->name('mata_pelajaran.progress');

        Route::get('/profile', [TeacherController::class, 'showProfile'])->name('profile');
        
        // Score Management
        Route::prefix('score')->name('score.')->group(function () {
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

        Route::get('/debug/rapor-siswa', function() {
            $guru = auth()->guard('guru')->user();
            $tahunAjaranId = session('tahun_ajaran_id');
            
            if (!$guru) {
                return "Silakan login sebagai guru terlebih dahulu";
            }
            
            // Ambil kelas wali
            $kelasWali = $guru->kelasWali;
            
            if (!$kelasWali) {
                return "Guru ini tidak memiliki kelas wali";
            }
            
            // Query siswa dengan berbagai pendekatan
            $result = [
                'guru' => [
                    'id' => $guru->id,
                    'nama' => $guru->nama
                ],
                'kelas_wali' => [
                    'id' => $kelasWali->id,
                    'nomor_kelas' => $kelasWali->nomor_kelas,
                    'nama_kelas' => $kelasWali->nama_kelas,
                    'tahun_ajaran_id' => $kelasWali->tahun_ajaran_id
                ],
                'session' => [
                    'tahun_ajaran_id' => $tahunAjaranId
                ],
                'query_results' => []
            ];
            
            // Query 1: Hanya berdasarkan kelas_id
            $siswa1 = Siswa::where('kelas_id', $kelasWali->id)->get();
            $result['query_results']['kelas_id_only'] = [
                'count' => $siswa1->count(),
                'siswa' => $siswa1->map(function($s) {
                    return [
                        'id' => $s->id,
                        'nama' => $s->nama,
                        'kelas_id' => $s->kelas_id,
                        'tahun_ajaran_id' => $s->tahun_ajaran_id
                    ];
                })
            ];
            
            // Query 2: kelas_id + tahun_ajaran_id
            $siswa2 = Siswa::where('kelas_id', $kelasWali->id)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->get();
            $result['query_results']['kelas_id_and_tahun_ajaran'] = [
                'count' => $siswa2->count(),
                'siswa' => $siswa2->map(function($s) {
                    return [
                        'id' => $s->id,
                        'nama' => $s->nama,
                        'kelas_id' => $s->kelas_id,
                        'tahun_ajaran_id' => $s->tahun_ajaran_id
                    ];
                })
            ];
            
            // Query 3: Melalui relasi
            $siswa3 = $kelasWali->siswas()->get();
            $result['query_results']['via_relation'] = [
                'count' => $siswa3->count(),
                'siswa' => $siswa3->map(function($s) {
                    return [
                        'id' => $s->id,
                        'nama' => $s->nama,
                        'kelas_id' => $s->kelas_id,
                        'tahun_ajaran_id' => $s->tahun_ajaran_id
                    ];
                })
            ];
            
            // Periksa inconsistencies di data siswa
            $siswaWithMismatchedTahunAjaran = Siswa::where('kelas_id', $kelasWali->id)
                ->where(function($query) use ($kelasWali) {
                    $query->where('tahun_ajaran_id', '!=', $kelasWali->tahun_ajaran_id)
                        ->orWhereNull('tahun_ajaran_id');
                })
                ->get();
            
            $result['inconsistencies'] = [
                'count' => $siswaWithMismatchedTahunAjaran->count(),
                'siswa' => $siswaWithMismatchedTahunAjaran->map(function($s) {
                    return [
                        'id' => $s->id,
                        'nama' => $s->nama,
                        'kelas_id' => $s->kelas_id,
                        'siswa_tahun_ajaran_id' => $s->tahun_ajaran_id,
                        'kelas_tahun_ajaran_id' => $s->kelas->tahun_ajaran_id
                    ];
                })
            ];
            
            return response()->json($result);
        })->middleware(['auth:guru']);

        Route::get('/debug/guru-kelas', function() {
            if (!Auth::guard('guru')->check()) {
                return "Silakan login sebagai guru terlebih dahulu";
            }
            
            $guru = Auth::guard('guru')->user();
            $tahunAjaranId = session('tahun_ajaran_id');
            $selectedSemester = session('selected_semester', 1);
            
            // Ambil semua relasi guru-kelas untuk guru ini
            $guruKelas = DB::table('guru_kelas')
                ->join('kelas', 'guru_kelas.kelas_id', '=', 'kelas.id')
                ->join('tahun_ajarans', 'kelas.tahun_ajaran_id', '=', 'tahun_ajarans.id')
                ->where('guru_kelas.guru_id', $guru->id)
                ->select(
                    'guru_kelas.id',
                    'guru_kelas.guru_id',
                    'guru_kelas.kelas_id',
                    'guru_kelas.is_wali_kelas',
                    'guru_kelas.role',
                    'kelas.nomor_kelas',
                    'kelas.nama_kelas',
                    'kelas.tahun_ajaran_id',
                    'tahun_ajarans.tahun_ajaran',
                    'tahun_ajarans.semester'
                )
                ->get();
            
            // Periksa apakah guru ini adalah wali kelas untuk tahun ajaran dan semester terpilih
            $isWaliKelas = DB::table('guru_kelas')
                ->join('kelas', 'guru_kelas.kelas_id', '=', 'kelas.id')
                ->join('tahun_ajarans', 'kelas.tahun_ajaran_id', '=', 'tahun_ajarans.id')
                ->where('guru_kelas.guru_id', $guru->id)
                ->where('guru_kelas.is_wali_kelas', true)
                ->where('guru_kelas.role', 'wali_kelas')
                ->where('kelas.tahun_ajaran_id', $tahunAjaranId)
                ->exists();
            
            // Ambil kelas untuk tahun ajaran dan semester terpilih
            $currentKelas = DB::table('kelas')
                ->join('tahun_ajarans', 'kelas.tahun_ajaran_id', '=', 'tahun_ajarans.id')
                ->where('kelas.tahun_ajaran_id', $tahunAjaranId)
                ->where('tahun_ajarans.semester', $selectedSemester)
                ->select('kelas.*', 'tahun_ajarans.tahun_ajaran', 'tahun_ajarans.semester')
                ->get();
            
            return response()->json([
                'guru' => [
                    'id' => $guru->id,
                    'nama' => $guru->nama,
                    'is_wali_kelas' => $isWaliKelas
                ],
                'session' => [
                    'tahun_ajaran_id' => $tahunAjaranId,
                    'selected_semester' => $selectedSemester
                ],
                'guru_kelas_relations' => $guruKelas,
                'current_kelas' => $currentKelas
            ]);
        });

        Route::get('/test-log', function() {
            \Log::info('Test log entry');
            return 'Log test completed. Check storage/logs directory.';
        });

            // Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
            Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
        });
        

        Route::get('/rapor/diagnose/{siswa}', [ReportController::class, 'diagnoseSiswaData'])->name('diagnose');

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
// Tambahkan rute ini ke bagian bawah file web.php
Route::get('/debug/batch-report', function() {
    try {
        // Dapatkan informasi sesi tahun ajaran
        $tahunAjaranId = session('tahun_ajaran_id');
        $tahunAjaran = \App\Models\TahunAjaran::find($tahunAjaranId);
        $currentSemester = $tahunAjaran ? $tahunAjaran->semester : null;
        
        // Cek user dan permission
        $user = auth()->guard('guru')->user();
        $serverUser = function_exists('exec') ? exec('whoami') : 'unknown';
        $phpUser = function_exists('get_current_user') ? get_current_user() : 'unknown';
        
        // Cek direktori penyimpanan
        $storagePublicPath = storage_path('app/public');
        $downloadsPath = storage_path('app/public/downloads');
        $testBatchPath = storage_path('app/public/downloads/test_batch_' . time());
        
        // Coba buat direktori test
        if (!file_exists($downloadsPath)) {
            mkdir($downloadsPath, 0755, true);
        }
        
        $mkdirResult = false;
        $mkdirError = null;
        
        try {
            $mkdirResult = mkdir($testBatchPath, 0755, true);
        } catch (\Exception $e) {
            $mkdirError = $e->getMessage();
        }
        
        // Coba buat file ZIP test
        $zipTest = false;
        $zipError = null;
        $zipPath = $testBatchPath . '/test.zip';
        
        try {
            if ($mkdirResult) {
                $zip = new \ZipArchive();
                $zipResult = $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                
                if ($zipResult === true) {
                    // Buat file test
                    $testFilePath = $testBatchPath . '/test.txt';
                    file_put_contents($testFilePath, 'Test content ' . date('Y-m-d H:i:s'));
                    
                    // Tambahkan ke ZIP
                    $zip->addFile($testFilePath, 'test.txt');
                    $zip->addFromString('info.txt', 'Generated at ' . date('Y-m-d H:i:s'));
                    $zip->close();
                    
                    $zipTest = file_exists($zipPath);
                } else {
                    $zipError = "ZipArchive open failed with code: " . $zipResult;
                }
            } else {
                $zipError = "Cannot create test directory";
            }
        } catch (\Exception $e) {
            $zipError = $e->getMessage();
        }
        
        // Cek disk space
        $diskTotal = function_exists('disk_total_space') ? disk_total_space('/') : 'unknown';
        $diskFree = function_exists('disk_free_space') ? disk_free_space('/') : 'unknown';
        
        // Cek template rapor
        $templatesUTS = \App\Models\ReportTemplate::where('type', 'UTS')
            ->where('is_active', true)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->get();
            
        $templatesUAS = \App\Models\ReportTemplate::where('type', 'UAS')
            ->where('is_active', true)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->get();
        
        // Cek data rapor semester 1 vs semester 2
        $semesterStats = [];
        
        if ($user) {
            $kelas = $user->kelasWali;
            
            if ($kelas) {
                $siswa = \App\Models\Siswa::where('kelas_id', $kelas->id)->first();
                
                if ($siswa) {
                    for ($sem = 1; $sem <= 2; $sem++) {
                        $nilai = $siswa->nilais()
                            ->whereHas('mataPelajaran', function($q) use ($sem) {
                                $q->where('semester', $sem);
                            })
                            ->where('tahun_ajaran_id', $tahunAjaranId)
                            ->whereNotNull('nilai_akhir_rapor')
                            ->count();
                            
                        $absensi = $siswa->absensi()
                            ->where('semester', $sem)
                            ->where('tahun_ajaran_id', $tahunAjaranId)
                            ->exists();
                            
                        $semesterStats[$sem] = [
                            'nilai_count' => $nilai,
                            'has_absensi' => $absensi
                        ];
                    }
                }
            }
        }
        
        // Kumpulkan semua informasi
        $result = [
            'server_info' => [
                'php_version' => phpversion(),
                'server_user' => $serverUser,
                'php_user' => $phpUser,
                'disk_total' => is_numeric($diskTotal) ? formatBytes($diskTotal) : $diskTotal,
                'disk_free' => is_numeric($diskFree) ? formatBytes($diskFree) : $diskFree,
                'memory_limit' => ini_get('memory_limit'),
                'time' => date('Y-m-d H:i:s'),
                'extensions' => [
                    'zip' => extension_loaded('zip'),
                    'fileinfo' => extension_loaded('fileinfo'),
                    'openssl' => extension_loaded('openssl')
                ]
            ],
            'session_info' => [
                'tahun_ajaran_id' => $tahunAjaranId,
                'tahun_ajaran' => $tahunAjaran ? $tahunAjaran->tahun_ajaran : 'tidak ditemukan',
                'semester' => $currentSemester,
            ],
            'directory_permissions' => [
                'storage_public' => [
                    'path' => $storagePublicPath,
                    'exists' => file_exists($storagePublicPath),
                    'writable' => is_writable($storagePublicPath),
                    'permissions' => file_exists($storagePublicPath) ? substr(sprintf('%o', fileperms($storagePublicPath)), -4) : 'N/A',
                    'owner' => function_exists('posix_getpwuid') && file_exists($storagePublicPath) ? 
                        posix_getpwuid(fileowner($storagePublicPath))['name'] : 'unknown'
                ],
                'downloads' => [
                    'path' => $downloadsPath,
                    'exists' => file_exists($downloadsPath),
                    'writable' => file_exists($downloadsPath) ? is_writable($downloadsPath) : false,
                    'permissions' => file_exists($downloadsPath) ? substr(sprintf('%o', fileperms($downloadsPath)), -4) : 'N/A',
                    'owner' => function_exists('posix_getpwuid') && file_exists($downloadsPath) ? 
                        posix_getpwuid(fileowner($downloadsPath))['name'] : 'unknown'
                ],
                'test_batch' => [
                    'path' => $testBatchPath,
                    'mkdir_result' => $mkdirResult,
                    'mkdir_error' => $mkdirError,
                    'exists' => file_exists($testBatchPath),
                    'writable' => file_exists($testBatchPath) ? is_writable($testBatchPath) : false,
                    'permissions' => file_exists($testBatchPath) ? substr(sprintf('%o', fileperms($testBatchPath)), -4) : 'N/A'
                ],
            ],
            'zip_test' => [
                'success' => $zipTest,
                'error' => $zipError,
                'zip_path' => $zipPath,
                'zip_exists' => file_exists($zipPath),
                'zip_size' => file_exists($zipPath) ? filesize($zipPath) : 0
            ],
            'templates' => [
                'uts' => $templatesUTS->map(function($t) {
                    return [
                        'id' => $t->id,
                        'filename' => $t->filename,
                        'semester' => $t->semester,
                        'is_active' => $t->is_active
                    ];
                }),
                'uas' => $templatesUAS->map(function($t) {
                    return [
                        'id' => $t->id,
                        'filename' => $t->filename,
                        'semester' => $t->semester,
                        'is_active' => $t->is_active
                    ];
                })
            ],
            'semester_data' => $semesterStats
        ];
        
        return response()->json($result);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => array_slice($e->getTrace(), 0, 5)
        ], 500);
    }
});

/**
 * Format bytes to human readable format
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Debugging route khusus untuk ZipArchive di semester Genap
Route::get('/debug/zip-semester-genap', function() {
    try {
        // Set timezone
        date_default_timezone_set('Asia/Jakarta');
        
        // Dapatkan info tahun ajaran
        $tahunAjaranId = session('tahun_ajaran_id');
        $tahunAjaran = \App\Models\TahunAjaran::find($tahunAjaranId);
        $currentSemester = $tahunAjaran ? $tahunAjaran->semester : null;
        
        // Informasi dasar
        $info = [
            'time' => date('Y-m-d H:i:s'),
            'tahun_ajaran' => $tahunAjaran ? [
                'id' => $tahunAjaran->id,
                'tahun_ajaran' => $tahunAjaran->tahun_ajaran,
                'semester' => $tahunAjaran->semester,
                'is_active' => $tahunAjaran->is_active,
            ] : null
        ];
        
        // Cek apakah ini semester genap
        if (!$tahunAjaran || $tahunAjaran->semester != 2) {
            return response()->json([
                'info' => $info,
                'message' => 'Ini bukan semester genap. Silakan ubah ke tahun ajaran dengan semester genap untuk melakukan test.'
            ]);
        }
        
        // Buat test directory untuk ZIP
        $timestamp = time();
        $testDirStorage = storage_path('app/public/zip_test_' . $timestamp);
        $testDirPublic = public_path('zip_test_' . $timestamp);
        
        // Test kedua lokasi: storage dan public
        $locations = [
            'storage' => $testDirStorage,
            'public' => $testDirPublic
        ];
        
        $results = [];
        
        foreach ($locations as $name => $dir) {
            // Buat direktori
            $mkdirResult = false;
            $mkdirError = null;
            
            try {
                if (!file_exists($dir)) {
                    $mkdirResult = mkdir($dir, 0755, true);
                } else {
                    $mkdirResult = true;
                }
            } catch (\Exception $e) {
                $mkdirError = $e->getMessage();
            }
            
            // Buat test file untuk ZIP
            $testFile = $dir . '/test.txt';
            $fileWriteResult = false;
            $fileError = null;
            
            try {
                if ($mkdirResult) {
                    $fileWriteResult = file_put_contents($testFile, 'Test content ' . date('Y-m-d H:i:s'));
                }
            } catch (\Exception $e) {
                $fileError = $e->getMessage();
            }
            
            // Coba buat ZIP file
            $zipPath = $dir . '/test.zip';
            $zipResult = null;
            $zipSuccess = false;
            $zipError = null;
            $zipCloseSuccess = false;
            
            try {
                if ($mkdirResult && $fileWriteResult) {
                    $zip = new \ZipArchive();
                    $zipResult = $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                    
                    if ($zipResult === true) {
                        // Tambahkan file ke ZIP
                        $zip->addFile($testFile, 'test.txt');
                        $zip->addFromString('info.txt', "Dibuat pada: " . date('Y-m-d H:i:s') . "\nLokasi: " . $name);
                        
                        // Coba tutup ZIP
                        try {
                            $zipCloseSuccess = $zip->close();
                        } catch (\Exception $e) {
                            $zipError = "ZIP close failed: " . $e->getMessage();
                        }
                        
                        $zipSuccess = $zipCloseSuccess && file_exists($zipPath);
                    } else {
                        $zipError = "ZipArchive open failed with code: " . $zipResult;
                    }
                }
            } catch (\Exception $e) {
                $zipError = "ZIP processing failed: " . $e->getMessage();
            }
            
            // Cek hasil
            $results[$name] = [
                'directory' => [
                    'path' => $dir,
                    'created' => $mkdirResult,
                    'error' => $mkdirError,
                    'exists' => file_exists($dir),
                    'writable' => file_exists($dir) ? is_writable($dir) : false,
                    'permissions' => file_exists($dir) ? substr(sprintf('%o', fileperms($dir)), -4) : 'N/A'
                ],
                'file' => [
                    'path' => $testFile,
                    'created' => $fileWriteResult !== false,
                    'size' => file_exists($testFile) ? filesize($testFile) : 0,
                    'error' => $fileError
                ],
                'zip' => [
                    'path' => $zipPath,
                    'open_result' => $zipResult,
                    'close_success' => $zipCloseSuccess,
                    'success' => $zipSuccess,
                    'size' => file_exists($zipPath) ? filesize($zipPath) : 0,
                    'error' => $zipError
                ]
            ];
        }
        
        // Perbandingan dengan eksekusi langsung dari controller
        $controllerTest = testZipArchiveDirectly($tahunAjaranId);
        
        return response()->json([
            'info' => $info,
            'locations_test' => $results,
            'controller_test' => $controllerTest
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => array_slice($e->getTrace(), 0, 5)
        ], 500);
    }
});

/**
 * Test ZipArchive directly from a controller-like function
 */
function testZipArchiveDirectly($tahunAjaranId) {
    try {
        // Dapatkan info guru dan kelas
        $guru = auth()->guard('guru')->user();
        if (!$guru) {
            return ['error' => 'User not authenticated as guru'];
        }
        
        $kelas = $guru->kelasWali;
        if (!$kelas) {
            return ['error' => 'Guru doesn\'t have a wali kelas'];
        }
        
        // Buat direktori di storage/app/public (mirip dengan generateBatchReport)
        $timestamp = date('Ymd_His');
        $publicDir = storage_path('app/public/direct_test_' . $timestamp);
        
        if (!file_exists($publicDir)) {
            mkdir($publicDir, 0755, true);
        }
        
        // Buat beberapa file dummy
        $files = [];
        for ($i = 1; $i <= 3; $i++) {
            $filename = "dummy_file_{$i}.txt";
            $path = $publicDir . '/' . $filename;
            file_put_contents($path, "Ini adalah file dummy {$i} dibuat pada " . date('Y-m-d H:i:s'));
            
            $files[] = [
                'path' => $path,
                'name' => $filename
            ];
        }
        
        // Buat file summary
        $summaryPath = $publicDir . "/RINGKASAN.md";
        file_put_contents($summaryPath, "# Test Summary\n\nCreated at: " . date('Y-m-d H:i:s'));
        
        // Buat ZIP
        $zipName = "Test_Zip_{$timestamp}.zip";
        $zipPath = $publicDir . '/' . $zipName;
        
        $zip = new \ZipArchive();
        $zipResult = $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        
        $addFileResults = [];
        $zipCloseSuccess = false;
        
        if ($zipResult === true) {
            // Tambahkan file summary
            $addSummaryResult = $zip->addFile($summaryPath, "RINGKASAN.md");
            $addFileResults[] = ['file' => 'RINGKASAN.md', 'result' => $addSummaryResult];
            
            // Tambahkan dummy files
            foreach ($files as $file) {
                $addResult = $zip->addFile($file['path'], $file['name']);
                $addFileResults[] = ['file' => $file['name'], 'result' => $addResult];
            }
            
            // Close ZIP
            try {
                $zipCloseSuccess = $zip->close();
            } catch (\Exception $e) {
                return [
                    'zipResult' => $zipResult,
                    'addFileResults' => $addFileResults,
                    'zipCloseError' => $e->getMessage(),
                    'zipCloseSuccess' => false
                ];
            }
        }
        
        return [
            'directory' => [
                'path' => $publicDir,
                'exists' => file_exists($publicDir),
                'writable' => is_writable($publicDir),
                'permissions' => substr(sprintf('%o', fileperms($publicDir)), -4)
            ],
            'zipResult' => $zipResult,
            'zipPath' => $zipPath,
            'zipExists' => file_exists($zipPath),
            'zipSize' => file_exists($zipPath) ? filesize($zipPath) : 0,
            'addFileResults' => $addFileResults,
            'zipCloseSuccess' => $zipCloseSuccess
        ];
        
    } catch (\Exception $e) {
        return [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
    }
}
    // Add this to your routes/web.php file inside the wali-kelas route group
    Route::get('/wali-kelas/debug/rapor-status', function() {
        $tahunAjaranId = session('tahun_ajaran_id');
        $tahunAjaran = \App\Models\TahunAjaran::find($tahunAjaranId);
        $currentSemester = $tahunAjaran ? $tahunAjaran->semester : null;
        
        $guru = auth()->guard('guru')->user();
        $kelasWaliId = $guru->getWaliKelasId();
        
        $templatesUTS = \App\Models\ReportTemplate::where('type', 'UTS')
            ->where('is_active', true)
            ->when($tahunAjaranId, function($q) use ($tahunAjaranId) {
                return $q->where('tahun_ajaran_id', $tahunAjaranId);
            })->get();
            
        $templatesUAS = \App\Models\ReportTemplate::where('type', 'UAS')
            ->where('is_active', true)
            ->when($tahunAjaranId, function($q) use ($tahunAjaranId) {
                return $q->where('tahun_ajaran_id', $tahunAjaranId);
            })->get();
        
        return [
            'current_semester' => $currentSemester,
            'tahun_ajaran' => $tahunAjaran,
            'kelas_wali_id' => $kelasWaliId,
            'templates_uts' => $templatesUTS->map(fn($t) => [
                'id' => $t->id,
                'semester_setting' => $t->semester,
                'is_active' => $t->is_active
            ]),
            'templates_uas' => $templatesUAS->map(fn($t) => [
                'id' => $t->id,
                'semester_setting' => $t->semester,
                'is_active' => $t->is_active
            ])
        ];
    })->name('debug.rapor.status');
    

// Tambahkan di web.php dalam grup route wali_kelas
Route::get('/debug/siswa/487', function() {
    try {
        // Ambil data siswa dengan ID 487
        $siswa = \App\Models\Siswa::with([
            'kelas',
            'kelas.tahunAjaran',
            'nilais' => function($query) {
                $tahunAjaranId = session('tahun_ajaran_id');
                $query->where('tahun_ajaran_id', $tahunAjaranId);
            },
            'nilais.mataPelajaran',
            'nilaiEkstrakurikuler' => function($query) {
                $tahunAjaranId = session('tahun_ajaran_id');
                $query->where('tahun_ajaran_id', $tahunAjaranId);
            },
            'nilaiEkstrakurikuler.ekstrakurikuler',
            'absensi' => function($query) {
                $tahunAjaranId = session('tahun_ajaran_id');
                $semester = 2; // Hardcode semester 2 untuk tahun ajaran genap
                $query->where('tahun_ajaran_id', $tahunAjaranId)
                      ->where('semester', $semester);
            }
        ])->find(487);
        
        if (!$siswa) {
            return response()->json([
                'error' => 'Siswa dengan ID 487 tidak ditemukan'
            ], 404);
        }
        
        // Ambil detail kelas
        $kelas = $siswa->kelas;
        $kelasDetail = $kelas ? [
            'id' => $kelas->id,
            'nomor_kelas' => $kelas->nomor_kelas,
            'nama_kelas' => $kelas->nama_kelas,
            'nama_lengkap' => "Kelas {$kelas->nomor_kelas} {$kelas->nama_kelas}",
            'tahun_ajaran_id' => $kelas->tahun_ajaran_id,
            'tahun_ajaran' => $kelas->tahunAjaran ? $kelas->tahunAjaran->tahun_ajaran : null,
            'semester' => $kelas->tahunAjaran ? $kelas->tahunAjaran->semester : null
        ] : null;
        
        // Cek template aktif untuk siswa ini
        $tahunAjaranId = session('tahun_ajaran_id');
        $templateUTS = null;
        $templateUAS = null;
        
        if ($kelas) {
            // Cek template UTS
            $templateUTS = \App\Models\ReportTemplate::where('type', 'UTS')
                ->where('is_active', true)
                ->where(function($query) use ($kelas) {
                    $query->where('kelas_id', $kelas->id)
                        ->orWhereHas('kelasList', function($q) use ($kelas) {
                            $q->where('kelas_id', $kelas->id);
                        })
                        ->orWhereNull('kelas_id'); // template global
                })
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->first();
                
            // Cek template UAS
            $templateUAS = \App\Models\ReportTemplate::where('type', 'UAS')
                ->where('is_active', true)
                ->where(function($query) use ($kelas) {
                    $query->where('kelas_id', $kelas->id)
                        ->orWhereHas('kelasList', function($q) use ($kelas) {
                            $q->where('kelas_id', $kelas->id);
                        })
                        ->orWhereNull('kelas_id'); // template global
                })
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->first();
        }
        
        // Cek status nilai
        $nilaiStatus = [];
        $mapelSemester1 = [];
        $mapelSemester2 = [];
        
        if ($kelas) {
            // Ambil mata pelajaran semester 1 (UTS)
            $mapelSemester1 = \App\Models\MataPelajaran::where('kelas_id', $kelas->id)
                ->where('semester', 1)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->get()
                ->map(function($mapel) {
                    return [
                        'id' => $mapel->id,
                        'nama_pelajaran' => $mapel->nama_pelajaran,
                        'guru_id' => $mapel->guru_id,
                        'is_muatan_lokal' => $mapel->is_muatan_lokal
                    ];
                });
                
            // Ambil mata pelajaran semester 2 (UAS)
            $mapelSemester2 = \App\Models\MataPelajaran::where('kelas_id', $kelas->id)
                ->where('semester', 2)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->get()
                ->map(function($mapel) {
                    return [
                        'id' => $mapel->id,
                        'nama_pelajaran' => $mapel->nama_pelajaran,
                        'guru_id' => $mapel->guru_id,
                        'is_muatan_lokal' => $mapel->is_muatan_lokal
                    ];
                });
        }
        
        // Cek nilai semester 1 (UTS)
        $nilaiUTS = $siswa->nilais()
            ->whereHas('mataPelajaran', function($q) {
                $q->where('semester', 1);
            })
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('nilai_akhir_rapor', '!=', null)
            ->get()
            ->map(function($nilai) {
                return [
                    'id' => $nilai->id,
                    'mata_pelajaran_id' => $nilai->mata_pelajaran_id,
                    'mata_pelajaran' => $nilai->mataPelajaran ? $nilai->mataPelajaran->nama_pelajaran : null,
                    'nilai_akhir_rapor' => $nilai->nilai_akhir_rapor
                ];
            });
            
        // Cek nilai semester 2 (UAS)
        $nilaiUAS = $siswa->nilais()
            ->whereHas('mataPelajaran', function($q) {
                $q->where('semester', 2);
            })
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('nilai_akhir_rapor', '!=', null)
            ->get()
            ->map(function($nilai) {
                return [
                    'id' => $nilai->id,
                    'mata_pelajaran_id' => $nilai->mata_pelajaran_id,
                    'mata_pelajaran' => $nilai->mataPelajaran ? $nilai->mataPelajaran->nama_pelajaran : null,
                    'nilai_akhir_rapor' => $nilai->nilai_akhir_rapor
                ];
            });
            
        // Cek absensi siswa
        $absensiSemester1 = \App\Models\Absensi::where('siswa_id', $siswa->id)
            ->where('semester', 1)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->first();
            
        $absensiSemester2 = \App\Models\Absensi::where('siswa_id', $siswa->id)
            ->where('semester', 2)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->first();
            
        // Compile semua data untuk debugging
        return response()->json([
            'siswa' => [
                'id' => $siswa->id,
                'nis' => $siswa->nis,
                'nisn' => $siswa->nisn,
                'nama' => $siswa->nama,
                'kelas_id' => $siswa->kelas_id,
                'tahun_ajaran_id' => $siswa->tahun_ajaran_id
            ],
            'kelas' => $kelasDetail,
            'templates' => [
                'UTS' => $templateUTS ? [
                    'id' => $templateUTS->id,
                    'filename' => $templateUTS->filename,
                    'path' => $templateUTS->path,
                    'is_active' => $templateUTS->is_active,
                    'tahun_ajaran_id' => $templateUTS->tahun_ajaran_id
                ] : null,
                'UAS' => $templateUAS ? [
                    'id' => $templateUAS->id,
                    'filename' => $templateUAS->filename,
                    'path' => $templateUAS->path,
                    'is_active' => $templateUAS->is_active,
                    'tahun_ajaran_id' => $templateUAS->tahun_ajaran_id
                ] : null
            ],
            'mata_pelajaran' => [
                'semester_1_count' => count($mapelSemester1),
                'semester_1' => $mapelSemester1,
                'semester_2_count' => count($mapelSemester2),
                'semester_2' => $mapelSemester2
            ],
            'nilai' => [
                'uts_count' => count($nilaiUTS),
                'uts' => $nilaiUTS,
                'uas_count' => count($nilaiUAS),
                'uas' => $nilaiUAS,
            ],
            'absensi' => [
                'semester_1' => $absensiSemester1 ? [
                    'id' => $absensiSemester1->id,
                    'sakit' => $absensiSemester1->sakit,
                    'izin' => $absensiSemester1->izin,
                    'tanpa_keterangan' => $absensiSemester1->tanpa_keterangan,
                    'tahun_ajaran_id' => $absensiSemester1->tahun_ajaran_id
                ] : null,
                'semester_2' => $absensiSemester2 ? [
                    'id' => $absensiSemester2->id,
                    'sakit' => $absensiSemester2->sakit,
                    'izin' => $absensiSemester2->izin,
                    'tanpa_keterangan' => $absensiSemester2->tanpa_keterangan,
                    'tahun_ajaran_id' => $absensiSemester2->tahun_ajaran_id
                ] : null
            ],
            'ekstrakurikuler' => $siswa->nilaiEkstrakurikuler->map(function($eks) {
                return [
                    'id' => $eks->id,
                    'ekstrakurikuler_id' => $eks->ekstrakurikuler_id,
                    'nama_ekstrakurikuler' => $eks->ekstrakurikuler ? $eks->ekstrakurikuler->nama_ekstrakurikuler : null,
                    'predikat' => $eks->predikat,
                    'deskripsi' => $eks->deskripsi,
                    'tahun_ajaran_id' => $eks->tahun_ajaran_id
                ];
            }),
            'session' => [
                'tahun_ajaran_id' => $tahunAjaranId
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => array_slice($e->getTrace(), 0, 5)
        ], 500);
    }
})->name('debug.siswa.487');

    // Add this to your routes/web.php file
    Route::get('/debug/siswa-wali-kelas', function() {
        $guru = auth()->guard('guru')->user();
        $tahunAjaranId = session('tahun_ajaran_id');
        
        if (!$guru) {
            return "You need to be logged in as a teacher";
        }
        
        // First approach - using Helper method
        $kelasWaliId = $guru->getWaliKelasId();
        
        // Second approach - direct query (more reliable for debugging)
        $kelasWali = DB::table('guru_kelas')
            ->join('kelas', 'guru_kelas.kelas_id', '=', 'kelas.id')
            ->where('guru_kelas.guru_id', $guru->id)
            ->where('guru_kelas.is_wali_kelas', true)
            ->where('guru_kelas.role', 'wali_kelas')
            ->where('kelas.tahun_ajaran_id', $tahunAjaranId)
            ->select('kelas.id as kelas_id', 'kelas.nomor_kelas', 'kelas.nama_kelas')
            ->first();
        
        // Let's try different approaches to query students
        
        // Approach 1: Using kelas_id directly
        $students1 = \App\Models\Siswa::where('kelas_id', $kelasWaliId)
            ->get();
        
        // Approach 2: Using kelas_id + tahun_ajaran_id filter
        $students2 = \App\Models\Siswa::where('kelas_id', $kelasWaliId)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->get();
        
        // Approach 3: Using direct DB query for maximum control
        $students3 = DB::table('siswas')
            ->where('kelas_id', $kelasWaliId)
            ->get();
        
        // Let's also check what columns exist in the siswas table
        $columns = Schema::getColumnListing('siswas');
        
        return response()->json([
            'guru_info' => [
                'id' => $guru->id,
                'nama' => $guru->nama
            ],
            'session' => [
                'tahun_ajaran_id' => $tahunAjaranId
            ],
            'kelas_wali_helper' => [
                'kelas_id' => $kelasWaliId
            ],
            'kelas_wali_direct' => $kelasWali,
            'student_counts' => [
                'approach1' => $students1->count(),
                'approach2' => $students2->count(),
                'approach3' => count($students3)
            ],
            'sample_students' => [
                'approach1' => $students1->take(3)->map(function($s) {
                    return ['id' => $s->id, 'nama' => $s->nama, 'kelas_id' => $s->kelas_id, 'tahun_ajaran_id' => $s->tahun_ajaran_id];
                }),
                'approach2' => $students2->take(3)->map(function($s) {
                    return ['id' => $s->id, 'nama' => $s->nama, 'kelas_id' => $s->kelas_id, 'tahun_ajaran_id' => $s->tahun_ajaran_id];
                }),
                'approach3' => collect(array_slice($students3->toArray(), 0, 3))
            ],
            'table_structure' => [
                'columns' => $columns,
                'has_tahun_ajaran_id' => in_array('tahun_ajaran_id', $columns)
            ]
        ]);
    })->middleware(['auth:guru']);

        Route::get('/lingkup-materi/{id}/check-dependencies', [TujuanPembelajaranController::class, 'checkLingkupMateriDependencies'])
        ->name('lingkup_materi.check_dependencies');
        
        // Add route for updating lingkup materi (if needed)
        Route::post('/lingkup-materi/{id}/update', [SubjectController::class, 'updateLingkupMateri'])
            ->name('lingkup_materi.update');
            
        // Ensure this route exists for tujuan pembelajaran view
        Route::get('/tujuan-pembelajaran/{mata_pelajaran_id}/view', [TujuanPembelajaranController::class, 'teacherView'])
            ->name('tujuan_pembelajaran.view');
            
        Route::prefix('rapor')->name('rapor.')->group(function () {
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