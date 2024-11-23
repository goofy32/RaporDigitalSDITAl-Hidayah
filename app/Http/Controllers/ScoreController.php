<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScoreController extends Controller
{
    // Menampilkan halaman daftar pembelajaran
    public function index()
    {
        $subjects = [
            ['id' => 1, 'class' => 'Kelas 1A', 'name' => 'Pendidikan Agama Islam'],
            ['id' => 2, 'class' => 'Kelas 1B', 'name' => 'Matematika'],
        ];
    
        return view('pengajar.score', compact('subjects'));
    }

    // Menampilkan halaman input nilai
    public function inputScore($id)
    {
        // Data statis siswa
        $students = [
            ['id' => 1, 'name' => 'Ahmad Fauzan'],
            ['id' => 2, 'name' => 'Rahma Sari'],
            ['id' => 3, 'name' => 'Dewi Anggraini'],
        ];
    
        // Data statis mata pelajaran
        $subject = [
            'id' => $id,
            'class' => 'Kelas 1A',
            'name' => 'Pendidikan Agama Islam',
        ];
    
        return view('pengajar.input_score', compact('students', 'subject'));
    }

    // Menangani penyimpanan nilai (tanpa database)
    public function saveScore(Request $request, $id)
    {
        // Hanya log data untuk sementara
        $data = $request->all();
        return back()->with('success', 'Nilai berhasil disimpan!')->with('data', $data);
    }
}