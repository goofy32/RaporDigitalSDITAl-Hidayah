<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prestasi;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Facades\Log;


class AchievementController extends Controller
{
    // Menampilkan semua data prestasi
    public function index()
    {
        $prestasis = Prestasi::with('kelas', 'siswa')->paginate(10);
        return view('admin.achievement', compact('prestasis'));
    }

    // Menampilkan form tambah prestasi
    public function create()
    {
        $kelas = Kelas::all();
        $siswa = Siswa::all();
    
        // Tambahkan log untuk memeriksa data kelas
        Log::info('Data Kelas:', $kelas->toArray());
    
        return view('data.add_prestasi', compact('kelas', 'siswa'));
    }
    // Menyimpan data prestasi
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'siswa_id' => 'required|exists:siswas,id',
            'jenis_prestasi' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:500',
        ]);

        Prestasi::create($validated);

        return redirect()->route('achievement.index')->with('success', 'Data Prestasi berhasil ditambahkan');
    }

    // Menampilkan form edit
    public function edit($id)
    {
        $prestasi = Prestasi::findOrFail($id);
        $kelas = Kelas::all();
        $siswa = Siswa::all();
        return view('data.edit_prestasi', compact('prestasi', 'kelas', 'siswa'));
    }
    
    // Memperbarui data prestasi
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'siswa_id' => 'required|exists:siswa,id',
            'jenis_prestasi' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:500',
        ]);
    
        $prestasi = Prestasi::findOrFail($id);
        $prestasi->update($validated);
    
        return redirect()->route('achievement.index')->with('success', 'Data Prestasi berhasil diperbarui');
    }
    // Menghapus data prestasi
    public function destroy($id)
    {
        $prestasi = Prestasi::findOrFail($id);
        $prestasi->delete();

        return redirect()->route('achievement.index')->with('success', 'Data Prestasi berhasil dihapus');
    }
}