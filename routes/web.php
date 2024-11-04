<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolProfileController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\StudentController;

Route::get('/', function () {
    return view('admin.dashboard');
})->name('dashboard');

Route::get('profile', function () {
    return view('admin.profile');
})->name('profile');

Route::post('profile/submit', [SchoolProfileController::class, 'submit'])->name('profile.submit');
Route::get('profile/data', [SchoolProfileController::class, 'showData'])->name('profile.data');

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


Route::get('pelajaran', function () {
    return view('admin.subject');
})->name('subject');

Route::get('prestasi', function () {
    return view('admin.achievement');
})->name('achievement');

Route::get('ekstrakulikuler', function () {
    return view('admin.ekstrakulikuler');
})->name('ekstra');

Route::get('pengajar', function () {
    return view('admin.teacher');
})->name('teacher');

Route::get('format-rapot', function () {
    return view('admin.report_format');
})->name('report_format');