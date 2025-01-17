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
use App\Http\Controllers\NotificationController;
use App\Models\FormatRapor;
use Illuminate\Support\Facades\Auth;

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
   Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
   Route::get('/kelas-progress/{id}', [DashboardController::class, 'getKelasProgressAdmin'])
       ->name('admin.kelas.progress');
   
   Route::prefix('information')->name('information.')->group(function () {
       Route::post('/', [NotificationController::class, 'store'])->name('store');
       Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
       Route::get('/list', [NotificationController::class, 'list'])->name('list');
   });

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
   
   Route::resource('achievement', AchievementController::class)->names([
       'index' => 'achievement.index',
       'create' => 'achievement.create',
       'store' => 'achievement.store',
       'edit' => 'achievement.edit',
       'update' => 'achievement.update',
       'destroy' => 'achievement.destroy',
   ]);
   
   Route::prefix('tujuan-pembelajaran')->name('tujuan_pembelajaran.')->group(function () {
       Route::get('/create/{mata_pelajaran_id}', [TujuanPembelajaranController::class, 'create'])->name('create');
       Route::post('/store', [TujuanPembelajaranController::class, 'store'])->name('store');
       Route::get('/{mata_pelajaran_id}', [TujuanPembelajaranController::class, 'view'])->name('view');
   });
   
   Route::resource('ekstrakulikuler', EkstrakurikulerController::class)->names([
       'index' => 'ekstra.index',
       'create' => 'ekstra.create',
       'store' => 'ekstra.store',
       'edit' => 'ekstra.edit',
       'update' => 'ekstra.update',
       'destroy' => 'ekstra.destroy',
   ]);

   Route::prefix('format-rapor')->name('report_format.')->group(function () {
       Route::get('/{type?}', [ReportFormatController::class, 'index'])->name('index');
       Route::post('/upload', [ReportFormatController::class, 'upload'])->name('upload');
       Route::get('/preview/{format}', [ReportFormatController::class, 'preview'])->name('preview');
       Route::post('/save/{format}', [ReportFormatController::class, 'saveReportData'])->name('save');
       Route::post('/{format}/activate', [ReportFormatController::class, 'activate'])->name('activate');
       Route::delete('/{format}', [ReportFormatController::class, 'destroy'])->name('destroy');
       Route::get('/edit/{format}', [ReportFormatController::class, 'edit'])->name('edit');
   });
});

// Pengajar routes
Route::middleware(['auth.guru', 'role:guru'])->prefix('pengajar')->name('pengajar.')->group(function () {
   Route::prefix('notifications')->name('notifications.')->group(function () {
       Route::get('/', [NotificationController::class, 'index'])->name('index');
       Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
       Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
   });
   
   Route::get('/dashboard', [DashboardController::class, 'pengajarDashboard'])->name('dashboard');
   Route::get('/kelas-progress/{kelasId}', [DashboardController::class, 'getKelasProgress'])->name('kelas.progress');

   Route::get('/profile', [TeacherController::class, 'showProfile'])->name('profile');
   Route::get('profile/{id}', [UserController::class, 'show'])->name('profile.show');

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

   Route::prefix('subject')->name('subject.')->group(function () {
       Route::get('/', [SubjectController::class, 'teacherIndex'])->name('index');
       Route::get('/create', [SubjectController::class, 'teacherCreate'])->name('create');
       Route::post('/', [SubjectController::class, 'teacherStore'])->name('store');
       Route::get('/{id}/edit', [SubjectController::class, 'teacherEdit'])->name('edit');
       Route::put('/{id}', [SubjectController::class, 'teacherUpdate'])->name('update');
       Route::delete('/{id}', [SubjectController::class, 'teacherDestroy'])->name('destroy');
       Route::delete('/lingkup-materi/{id}', [SubjectController::class, 'deleteLingkupMateri'])->name('lingkup_materi.destroy');
   });

   Route::prefix('tujuan-pembelajaran')->name('tujuan_pembelajaran.')->group(function () {
       Route::get('/{mata_pelajaran_id}', [TujuanPembelajaranController::class, 'view'])->name('view');
       Route::get('/create/{mata_pelajaran_id}', [TujuanPembelajaranController::class, 'teacherCreate'])->name('create');
       Route::post('/store', [TujuanPembelajaranController::class, 'teacherStore'])->name('store');
   });
});

// Wali Kelas routes
Route::middleware(['auth.guru', 'role:wali_kelas'])->prefix('wali-kelas')->name('wali_kelas.')->group(function () {
   Route::prefix('notifications')->name('notifications.')->group(function () {
       Route::get('/', [NotificationController::class, 'index'])->name('index');
       Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
       Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
   });
   
   Route::get('/dashboard', [DashboardController::class, 'waliKelasDashboard'])->name('dashboard');
   Route::get('/profile', [TeacherController::class, 'showWaliKelasProfile'])->name('profile');

   Route::prefix('siswa')->name('student.')->group(function () {
       Route::get('/', [StudentController::class, 'waliKelasIndex'])->name('index');
       Route::get('/create', [StudentController::class, 'waliKelasCreate'])->name('create'); 
       Route::post('/', [StudentController::class, 'waliKelasStore'])->name('store');
       Route::get('/{id}', [StudentController::class, 'waliKelasShow'])->name('show');
       Route::get('/{id}/edit', [StudentController::class, 'waliKelasEdit'])->name('edit');
       Route::put('/{id}', [StudentController::class, 'waliKelasUpdate'])->name('update');
       Route::delete('/{id}', [StudentController::class, 'waliKelasDestroy'])->name('destroy');
   });

   Route::prefix('ekstrakurikuler')->name('ekstrakurikuler.')->group(function () {
       Route::get('/', [EkstrakurikulerController::class, 'waliKelasIndex'])->name('index');
       Route::get('/create', [EkstrakurikulerController::class, 'waliKelasCreate'])->name('create');
       Route::post('/', [EkstrakurikulerController::class, 'waliKelasStore'])->name('store');
       Route::get('/{id}/edit', [EkstrakurikulerController::class, 'waliKelasEdit'])->name('edit');
       Route::put('/{id}', [EkstrakurikulerController::class, 'waliKelasUpdate'])->name('update');
       Route::delete('/{id}', [EkstrakurikulerController::class, 'waliKelasDestroy'])->name('destroy');
   });

   Route::resource('absensi', AbsensiController::class)->names([
       'index' => 'absence.index',
       'create' => 'absence.create', 
       'store' => 'absence.store',
       'edit' => 'absence.edit',
       'update' => 'absence.update',
       'destroy' => 'absence.destroy',
   ]);

   Route::prefix('rapor')->name('rapor.')->group(function () {
       Route::get('/', function () {
           return view('wali_kelas.rapor');
       })->name('index');
       Route::get('/print/{id}', function () {
           return view('wali_kelas.print');
       })->name('print');
   });
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout');