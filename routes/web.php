<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolProfileController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;

Route::get('/', function () {
    return view('admin.dashboard');
})->name('dashboard');

// Route untuk menampilkan data profil sekolah
Route::get('profile', [SchoolProfileController::class, 'show'])->name('profile');
// Route untuk menampilkan form profil sekolah (tambah/edit)
Route::get('profile/edit', [SchoolProfileController::class, 'edit'])->name('profile.edit');
// Route untuk menyimpan data profil sekolah
Route::post('profile', [SchoolProfileController::class, 'store'])->name('profile.submit');

Route::get('kelas', function () {
    return view('admin.class');
})->name('class');


// Menampilkan form tambah data kelas
Route::get('class/create', [ClassController::class, 'create'])->name('class.create');

// Menyimpan data kelas baru
Route::post('class/store', [ClassController::class, 'store'])->name('class.store');

Route::prefix('admin')->group(function () {
    Route::get('/siswa', [StudentController::class, 'index'])->name('student');
    Route::get('/students/create', [StudentController::class, 'create'])->name('student.create');
    Route::get('/students/{id}', [StudentController::class, 'show'])->name('student.show');
});

Route::prefix('pengajar')->group(function () {
    Route::get('/', [TeacherController::class, 'index'])->name('teacher');
    Route::get('/create', [TeacherController::class, 'create'])->name('teacher.create');
    Route::post('/store', [TeacherController::class, 'store'])->name('teacher.store');
    Route::get('/{id}', [TeacherController::class, 'show'])->name('teacher.show');
});

Route::get('pelajaran', function () {
    return view('admin.subject');
})->name('subject');

Route::get('prestasi', function () {
    return view('admin.achievement');
})->name('achievement');

Route::get('ekstrakulikuler', function () {
    return view('admin.ekstrakulikuler');
})->name('ekstra');


Route::get('format-rapot/{type?}', function ($type = 'UTS') {
    return view('admin.report_format', ['type' => $type]);
})->name('report_format');