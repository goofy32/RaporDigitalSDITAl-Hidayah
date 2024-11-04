<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $students = [
            ['id' => 1, 'nisn' => '1234567890', 'nama' => 'John Doe', 'kelas' => '1 A', 'jenis_kelamin' => 'Laki-laki'],
            ['id' => 2, 'nisn' => '1234567891', 'nama' => 'Jane Doe', 'kelas' => '1 B', 'jenis_kelamin' => 'Perempuan'],
        ];

        return view('admin.student', compact('students'));
    }

    public function create()
    {
        // Hapus dd() dan langsung return view
        return view('data.add_student');
    }

    public function show($id)
    {
        $student = [
            'id' => $id,
            'nisn' => '1234567890',
            'nama' => 'John Doe',
            'kelas' => '1 A',
            'tanggal_lahir' => '01/01/2000',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'alamat' => 'Jl. Mawar No. 10'
        ];

        return view('data.siswa_data', compact('student'));
    }
}