<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SchoolProfileController extends Controller
{
    public function submit(Request $request)
    {
        // Validasi data jika perlu
        $data = $request->all();

        // Redirect ke halaman data sekolah dengan data yang diisi
        return redirect()->route('profile.data')->with('data', $data);
    }

    public function showData()
    {
        // Ambil data dari session
        $data = session('data');

        // Pastikan untuk mengarahkan ke path tampilan baru di dalam folder school
        return view('data.school_data', compact('data'));
    }
}
