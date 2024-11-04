<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClassController extends Controller
{
        // Menampilkan form tambah data kelas
        public function create()
        {
            return view('data.create_class');
        }
    
        // Menyimpan data kelas baru
        public function store(Request $request)
        {
            // Validasi dan simpan data
            $request->validate([
                'kelas' => 'required|string|max:255',
                'wali_kelas' => 'required|string|max:255',
            ]);
    
            // Lakukan penyimpanan ke database (contoh tabel 'kelas')
            //DB::table('kelas')->insert([
            //    'kelas' => $request->input('kelas'),
            //    'wali_kelas' => $request->input('wali_kelas'),
            //]);
    
            // Redirect ke halaman kelas dengan pesan sukses
            return redirect()->route('class')->with('success', 'Data kelas berhasil ditambahkan.');
        }
}
