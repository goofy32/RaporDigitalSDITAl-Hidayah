<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolProfileController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\SubjectController;


Route::get('/', function () {
    return view('admin.dashboard');
})->name('dashboard');

// Route untuk menampilkan data profil sekolah
Route::get('profile', [SchoolProfileController::class, 'show'])->name('profile');
// Route untuk menampilkan form profil sekolah (tambah/edit)
Route::get('profile/edit', [SchoolProfileController::class, 'edit'])->name('profile.edit');
// Route untuk menyimpan data profil sekolah
Route::post('profile', [SchoolProfileController::class, 'store'])->name('profile.submit');

Route::get('kelas', [ClassController::class, 'index'])->name('kelas.index');
Route::get('kelas/create', [ClassController::class, 'create'])->name('kelas.create');
Route::post('kelas', [ClassController::class, 'store'])->name('kelas.store');
Route::get('kelas/{id}', [ClassController::class, 'show'])->name('kelas.show');
Route::get('kelas/{id}/edit', [ClassController::class, 'edit'])->name('kelas.edit');
Route::put('kelas/{id}', [ClassController::class, 'update'])->name('kelas.update');
Route::delete('kelas/{id}', [ClassController::class, 'destroy'])->name('kelas.destroy');

Route::prefix('admin')->group(function () {
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

    Route::resource('subject', SubjectController::class)->names([
    'index' => 'subject.index',
    'create' => 'subject.create',
    'store' => 'subject.store',
    'show' => 'subject.show',
    'edit' => 'subject.edit',
    'update' => 'subject.update',
    'destroy' => 'subject.destroy',
    ]);
});

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

Route::get('login', function () {
    return view('login');
})->name('login');

Route::get('prestasi', function () {
    return view('admin.achievement');
})->name('achievement');

Route::get('ekstrakulikuler', function () {
    return view('admin.ekstrakulikuler');
})->name('ekstra');


Route::get('format-rapot/{type?}', function ($type = 'UTS') {
    return view('admin.report_format', ['type' => $type]);
})->name('report_format');