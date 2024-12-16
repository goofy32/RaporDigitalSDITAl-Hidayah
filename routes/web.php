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
use App\Http\Controllers\ReportFormatController;
use App\Http\Controllers\UserController;
use App\Models\FormatRapor;
use Illuminate\Support\Facades\Auth; // Tambahkan baris ini


Route::get('/', function () {
    if (Auth::guard('web')->check()) {
        return redirect()->route('admin.dashboard');
    } elseif (Auth::guard('guru')->check()) {
        $guru = Auth::guard('guru')->user();
        if ($guru->jabatan === 'wali_kelas') {
            return redirect()->route('wali_kelas.dashboard');
        } else {
            return redirect()->route('pengajar.dashboard');
        }
    }
    
    return redirect()->route('login');
});

Route::get('login', function () {
    return view('login');
})->name('login')->middleware('guest');

Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
    
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });
    // Existing admin routes...
    
    Route::get('profile', [SchoolProfileController::class, 'show'])->name('profile');
    Route::get('profile/edit', [SchoolProfileController::class, 'edit'])->name('profile.edit');
    Route::post('profile', [SchoolProfileController::class, 'store'])->name('profile.submit');
    
    Route::resource('students', StudentController::class)->names([
        'index' => 'student',
        'create' => 'student.create',
        'store' => 'student.store',
        'show' => 'student.show',
        'edit' => 'student.edit',
        'update' => 'student.update',
        'destroy' => 'student.destroy',
    ]);
    
    Route::get('students/template', [StudentController::class, 'downloadTemplate'])
        ->name('student.template');
    Route::get('students/upload', [StudentController::class, 'uploadPage'])
        ->name('student.upload');
    Route::post('students/import', [StudentController::class, 'importExcel'])
        ->name('student.import');

    Route::resource('subject', SubjectController::class);

    Route::get('kelas', [ClassController::class, 'index'])->name('kelas.index');
    Route::get('kelas/create', [ClassController::class, 'create'])->name('kelas.create');
    Route::post('kelas', [ClassController::class, 'store'])->name('kelas.store');
    Route::get('kelas/{id}', [ClassController::class, 'show'])->name('kelas.show');
    Route::get('kelas/{id}/edit', [ClassController::class, 'edit'])->name('kelas.edit');
    Route::put('kelas/{id}', [ClassController::class, 'update'])->name('kelas.update');
    Route::delete('kelas/{id}', [ClassController::class, 'destroy'])->name('kelas.destroy');
    
    Route::prefix('pengajar')->group(function () {
        Route::get('/', [TeacherController::class, 'index'])->name('teacher');
        Route::get('/create', [TeacherController::class, 'create'])->name('teacher.create');
        Route::post('/store', [TeacherController::class, 'store'])->name('teacher.store');
        Route::get('/{id}', [TeacherController::class, 'show'])->name('teacher.show');
        Route::get('/{id}/edit', [TeacherController::class, 'edit'])->name('teacher.edit');
        Route::put('/{id}', [TeacherController::class, 'update'])->name('teacher.update');
        Route::delete('/{id}', [TeacherController::class, 'destroy'])->name('teacher.destroy');
    });
    Route::get('pelajaran', function () {
        return view('admin.subject');
    })->name('subject');
    
    Route::resource('achievement', AchievementController::class)->names([
        'index' => 'achievement.index',
        'create' => 'achievement.create',
        'store' => 'achievement.store',
        'edit' => 'achievement.edit',
        'update' => 'achievement.update',
        'destroy' => 'achievement.destroy',
    ]);
    
    Route::get('tujuan-pembelajaran/create/{mata_pelajaran_id}', [TujuanPembelajaranController::class, 'create'])
    ->name('tujuan_pembelajaran.create');
    Route::post('tujuan-pembelajaran/store', [TujuanPembelajaranController::class, 'store'])->name('tujuan_pembelajaran.store');
    Route::get('tujuan-pembelajaran/{mata_pelajaran_id}', [TujuanPembelajaranController::class, 'view'])
    ->name('tujuan_pembelajaran.view');
    
    
    Route::resource('ekstrakulikuler', EkstrakurikulerController::class)->names([
        'index' => 'ekstra.index',
        'create' => 'ekstra.create',
        'store' => 'ekstra.store',
        'edit' => 'ekstra.edit',
        'update' => 'ekstra.update',
        'destroy' => 'ekstra.destroy',
    ]);

    Route::get('template-preview/{format}', function (FormatRapor $format) {
        $path = storage_path('app/public/' . $format->template_path);
        
        if (!file_exists($path)) {
            abort(404);
        }
        
        return response()->file($path, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'inline; filename="' . basename($format->template_path) . '"'
        ]);
    })->name('template.preview')->middleware(['auth', 'role:admin']);

    Route::get('/preview-doc/{id}', function($id) {
        $format = \App\Models\FormatRapor::findOrFail($id);
        $path = storage_path('app/public/' . $format->template_path);
        
        return response()->file($path, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ]);
    })->name('preview.doc')->middleware(['auth', 'role:admin']);
    
    Route::prefix('format-rapor')->name('report_format.')->group(function () {
        Route::get('/{type?}', [ReportFormatController::class, 'index'])->name('index');
        Route::post('/upload', [ReportFormatController::class, 'upload'])->name('upload');
        Route::post('/{format}/activate', [ReportFormatController::class, 'activate'])->name('activate');
        Route::delete('/{format}', [ReportFormatController::class, 'destroy'])->name('destroy');
        Route::post('/validate-placeholders', [ReportFormatController::class, 'validatePlaceholders'])
             ->name('validate_placeholders');
        Route::get('/preview/{format}', [ReportFormatController::class, 'preview'])->name('preview');

    });
});

// Pengajar routes
Route::middleware(['auth.guru', 'role:guru'])->prefix('pengajar')->group(function () {
    Route::get('/dashboard', function () {
        return view('pengajar.dashboard');
    })->name('pengajar.dashboard');
    
    // Profile route - hanya untuk menampilkan
    Route::get('/profile', [TeacherController::class, 'showProfile'])->name('pengajar.profile');
    Route::get('profile/{id}', [UserController::class, 'show'])->name('profile.show');

    Route::get('/dashboard', [DashboardController::class, 'pengajarDashboard'])->name('pengajar.dashboard');
    Route::get('/kelas-progress/{kelasId}', [DashboardController::class, 'getKelasProgress'])->name('pengajar.kelas.progress');
    
    Route::get('/score', [ScoreController::class, 'index'])->name('pengajar.score');
    Route::get('/score/{id}/input', [ScoreController::class, 'inputScore'])->name('pengajar.input_score');
    Route::post('/score/{id}/save', [ScoreController::class, 'saveScore'])->name('pengajar.save_scores');
    Route::get('/score/{id}/preview', [ScoreController::class, 'previewScore'])->name('pengajar.preview_score');
    Route::post('/nilai/delete', [ScoreController::class, 'deleteNilai'])->name('pengajar.nilai.delete');

    Route::get('/subject', [SubjectController::class, 'teacherIndex'])->name('pengajar.subject.index');
    Route::get('/subject/create', [SubjectController::class, 'teacherCreate'])->name('pengajar.subject.create');
    Route::post('/subject', [SubjectController::class, 'teacherStore'])->name('pengajar.subject.store');
    Route::get('/subject/{id}/edit', [SubjectController::class, 'teacherEdit'])->name('pengajar.subject.edit');
    Route::put('/subject/{id}', [SubjectController::class, 'teacherUpdate'])->name('pengajar.subject.update');
    Route::delete('/subject/{id}', [SubjectController::class, 'teacherDestroy'])->name('pengajar.subject.destroy');

    Route::middleware(['check.matapelajaran.ownership'])->group(function () {
        Route::get('/tujuan-pembelajaran/{mata_pelajaran_id}', [TujuanPembelajaranController::class, 'view'])
            ->name('pengajar.tujuan_pembelajaran.view');
        Route::get('/tujuan-pembelajaran/create/{mata_pelajaran_id}', [TujuanPembelajaranController::class, 'teacherCreate'])
            ->name('pengajar.tujuan_pembelajaran.create');
        Route::post('/tujuan-pembelajaran/store', [TujuanPembelajaranController::class, 'teacherStore'])
            ->name('pengajar.tujuan_pembelajaran.store');
    });

        
});
// Wali Kelas routes
Route::middleware(['auth.guru', 'role:wali_kelas'])->prefix('wali-kelas')->name('wali_kelas.')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('wali_kelas.dashboard');
    })->name('dashboard');
    
    // Profile
    Route::get('/profile', [TeacherController::class, 'showWaliKelasProfile'])
        ->name('profile');

    // Student Management 
    Route::get('/siswa', [StudentController::class, 'waliKelasIndex'])->name('student.index');
    Route::get('/siswa/create', [StudentController::class, 'waliKelasCreate'])->name('student.create');
    Route::post('/siswa', [StudentController::class, 'waliKelasStore'])->name('student.store');
    Route::get('/siswa/{id}', [StudentController::class, 'waliKelasShow'])->name('student.show');
    Route::get('/siswa/{id}/edit', [StudentController::class, 'waliKelasEdit'])->name('student.edit');
    Route::put('/siswa/{id}', [StudentController::class, 'waliKelasUpdate'])->name('student.update');
    Route::delete('/siswa/{id}', [StudentController::class, 'waliKelasDestroy'])->name('student.destroy');

    // Ekstrakurikuler Management
    Route::get('/ekstrakurikuler', [EkstrakurikulerController::class, 'waliKelasIndex'])->name('ekstrakurikuler.index');
    Route::get('/ekstrakurikuler/create', [EkstrakurikulerController::class, 'waliKelasCreate'])->name('ekstrakurikuler.create');
    Route::post('/ekstrakurikuler', [EkstrakurikulerController::class, 'waliKelasStore'])->name('ekstrakurikuler.store');
    Route::get('/ekstrakurikuler/{id}/edit', [EkstrakurikulerController::class, 'waliKelasEdit'])->name('ekstrakurikuler.edit');
    Route::put('/ekstrakurikuler/{id}', [EkstrakurikulerController::class, 'waliKelasUpdate'])->name('ekstrakurikuler.update');
    Route::delete('/ekstrakurikuler/{id}', [EkstrakurikulerController::class, 'waliKelasDestroy'])->name('ekstrakurikuler.destroy');

    // Absence Management
    Route::resource('absensi', AbsensiController::class)->names([
        'index' => 'absence.index',
        'create' => 'absence.create',
        'store' => 'absence.store',
        'edit' => 'absence.edit',
        'update' => 'absence.update',
        'destroy' => 'absence.destroy',
    ]);

    // Rapor Management
    Route::get('/rapor', function () {
        return view('wali_kelas.rapor');
    })->name('rapor.index');

    Route::get('/rapor/print/{id}', function () {
        return view('wali_kelas.print');
    })->name('rapor.print');
});

// Logout route
Route::post('logout', [LoginController::class, 'logout'])->name('logout');





