<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProfilSekolah;
use Illuminate\Support\Facades\Storage;

class SchoolProfileController extends Controller
{
    // Menampilkan data profil sekolah
    public function show()
    {
        $profil = ProfilSekolah::first(); // Ambil data profil pertama

        if (!$profil) {
            // Jika data profil belum ada, arahkan ke form untuk menambah data
            return redirect()->route('profile.edit')->with('warning', 'Silakan isi profil sekolah terlebih dahulu.');
        }

        return view('data.school_data', compact('profil'));
    }

    // Menampilkan form untuk menambah atau mengedit data profil sekolah
    public function edit()
    {
        $profil = ProfilSekolah::first(); // Ambil data profil pertama
        $tahunAjarans = \App\Models\TahunAjaran::orderBy('tanggal_mulai', 'desc')->get();
        return view('admin.profile', compact('profil', 'tahunAjarans'));
    }

    // Menyimpan atau memperbarui data profil sekolah
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            // ... validasi lainnya ...
            'tahun_pelajaran' => 'required|string|max:255',
            'semester' => 'required|integer',
        ]);
    
        // Cek apakah data profil sudah ada
        $profil = ProfilSekolah::first();
    
        // Jika ada file logo yang diupload
        if ($request->hasFile('logo')) {
            // ... kode untuk upload logo ...
        }
    
        // Cari tahun ajaran aktif berdasarkan tahun dan semester yang dipilih
        $tahunAjaran = \App\Models\TahunAjaran::where('tahun_ajaran', $validated['tahun_pelajaran'])
                                             ->where('semester', $validated['semester'])
                                             ->first();
    
        // Jika tahun ajaran ditemukan, atur sebagai aktif
        if ($tahunAjaran) {
            // Nonaktifkan semua tahun ajaran dulu
            \App\Models\TahunAjaran::where('is_active', true)
                                  ->update(['is_active' => false]);
            
            // Aktifkan tahun ajaran yang dipilih
            $tahunAjaran->update(['is_active' => true]);
            
            // Set session untuk tahun ajaran
            session(['tahun_ajaran_id' => $tahunAjaran->id]);
        }
    
        if ($profil) {
            // Jika data profil sudah ada, lakukan update
            $profil->update($validated);
        } else {
            // Jika data profil belum ada, buat baru
            $profil = ProfilSekolah::create($validated);
        }
    
        // Setelah menyimpan data, arahkan ke halaman data profil sekolah
        return redirect()->route('profile')->with('success', 'Profil sekolah berhasil disimpan.');
    }
}