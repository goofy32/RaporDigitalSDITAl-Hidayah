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
        return view('admin.profile', compact('profil'));
    }

    // Menyimpan atau memperbarui data profil sekolah
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'nama_instansi' => 'required|string|max:255',
            'nama_sekolah' => 'required|string|max:255',
            'npsn' => 'required|string|max:255',
            'alamat' => 'required|string',
            'kode_pos' => 'required|string|max:10',
            'telepon' => 'required|string|max:20',
            'email_sekolah' => 'required|email|max:255',
            'tahun_pelajaran' => 'required|string|max:255',
            'semester' => 'required|integer',
            'kepala_sekolah' => 'required|string|max:255',
            'tempat_terbit' => 'required|string|max:255',
            'tanggal_terbit' => 'required|date',
            'kelas' => 'nullable|integer',
            'guru_kelas' => 'nullable|integer',
            'jumlah_siswa' => 'nullable|integer',
        ]);

        // Cek apakah data profil sudah ada
        $profil = ProfilSekolah::first();

        // Jika ada file logo yang diupload
        if ($request->hasFile('logo')) {
            // Simpan file logo
            $logoPath = $request->file('logo')->store('logos', 'public');

            // Hapus logo lama jika ada
            if ($profil && $profil->logo) {
                Storage::disk('public')->delete($profil->logo);
            }

            // Simpan path logo ke dalam data yang akan disimpan
            $validated['logo'] = $logoPath;
        } else {
            // Jika tidak ada file logo yang diupload
            unset($validated['logo']);
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