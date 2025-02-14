<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Siswa;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $waliKelas = auth()->guard('guru')->user();
        $query = Absensi::with('siswa')
            ->whereHas('siswa', function($query) use ($waliKelas) {
                $query->where('kelas_id', $waliKelas->kelas_pengajar_id);
            });
    
        // Tambah fitur pencarian
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('siswa', function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nis', 'LIKE', "%{$search}%");
            });
        }
    
        $absensis = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('wali_kelas.absence', compact('absensis'));
    }

    public function create()
    {
        $waliKelas = auth()->guard('guru')->user();
        $siswa = Siswa::where('kelas_id', $waliKelas->kelas_pengajar_id)
                     ->orderBy('nama')
                     ->get();

        return view('wali_kelas.add_absence', compact('siswa'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'sakit' => 'required|integer|min:0',
            'izin' => 'required|integer|min:0',
            'tanpa_keterangan' => 'required|integer|min:0',
        ]);

        Absensi::create($request->all());

        return redirect()->route('wali_kelas.absence.index')
                        ->with('success', 'Data absensi berhasil ditambahkan');
    }

    public function edit($id)
    {
        $waliKelas = auth()->guard('guru')->user();
        $absensi = Absensi::with('siswa')
            ->whereHas('siswa', function($query) use ($waliKelas) {
                $query->where('kelas_id', $waliKelas->kelas_pengajar_id);
            })
            ->findOrFail($id);

        return view('wali_kelas.edit_absence', compact('absensi'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'sakit' => 'required|integer|min:0',
            'izin' => 'required|integer|min:0',
            'tanpa_keterangan' => 'required|integer|min:0',
        ]);

        $absensi = Absensi::findOrFail($id);
        $absensi->update($request->all());

        return redirect()->route('wali_kelas.absence.index')
                        ->with('success', 'Data absensi berhasil diperbarui');
    }

    public function destroy($id)
    {
        $absensi = Absensi::findOrFail($id);
        $absensi->delete();

        return redirect()->route('wali_kelas.absence.index')
                        ->with('success', 'Data absensi berhasil dihapus');
    }
}