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
        $kelasWaliId = $waliKelas->getWaliKelasId(); // Gunakan metode dari model
        
        if (!$kelasWaliId) {
            return redirect()->back()->with('error', 'Anda belum ditugaskan sebagai wali kelas untuk kelas manapun.');
        }
        
        $query = Absensi::with('siswa')
            ->whereHas('siswa', function($query) use ($kelasWaliId) {
                $query->where('kelas_id', $kelasWaliId);
            });
    
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('siswa', function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nis', 'LIKE', "%{$search}%");
            });
        }

        // Tambah filter semester
        if ($request->has('semester')) {
            $query->where('semester', $request->semester);
        }
    
        $absensis = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('wali_kelas.absence', compact('absensis'));
    }

    public function create()
    {
        $waliKelas = auth()->guard('guru')->user();
        $kelasWaliId = $waliKelas->getWaliKelasId();
        
        if (!$kelasWaliId) {
            return redirect()->back()->with('error', 'Anda belum ditugaskan sebagai wali kelas untuk kelas manapun.');
        }
        
        $siswa = Siswa::where('kelas_id', $kelasWaliId)
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
            'semester' => 'required|in:1,2'
        ]);

        // Cek apakah sudah ada data absensi untuk siswa dan semester ini
        $existingAbsensi = Absensi::where('siswa_id', $request->siswa_id)
                                 ->where('semester', $request->semester)
                                 ->first();

        if ($existingAbsensi) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Data absensi untuk siswa ini di semester yang sama sudah ada');
        }

        Absensi::create($request->all());

        return redirect()->route('wali_kelas.absence.index')
                        ->with('success', 'Data absensi berhasil ditambahkan');
    }

    public function edit($id)
    {
        try {
            $waliKelas = auth()->guard('guru')->user();
            $kelasWaliId = $waliKelas->getWaliKelasId();
            
            \Log::info('Editing absensi', [
                'id' => $id,
                'kelasWaliId' => $kelasWaliId
            ]);
            
            $absensi = Absensi::with('siswa')
                ->whereHas('siswa', function($query) use ($kelasWaliId) {
                    $query->where('kelas_id', $kelasWaliId);
                })
                ->findOrFail($id);
            
            return view('wali_kelas.edit_absence', compact('absensi'));
        } catch (\Exception $e) {
            \Log::error('Error editing absensi: ' . $e->getMessage());
            return redirect()->route('wali_kelas.absence.index')
                ->with('error', 'Data absensi tidak ditemukan atau Anda tidak memiliki akses.');
        }
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'sakit' => 'required|integer|min:0',
            'izin' => 'required|integer|min:0',
            'tanpa_keterangan' => 'required|integer|min:0',
            'semester' => 'required|in:1,2'
        ]);

        $absensi = Absensi::findOrFail($id);
        
        // Cek duplikasi kecuali untuk record yang sedang diedit
        $existingAbsensi = Absensi::where('siswa_id', $absensi->siswa_id)
                                 ->where('semester', $request->semester)
                                 ->where('id', '!=', $id)
                                 ->first();

        if ($existingAbsensi) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Data absensi untuk siswa ini di semester yang sama sudah ada');
        }

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