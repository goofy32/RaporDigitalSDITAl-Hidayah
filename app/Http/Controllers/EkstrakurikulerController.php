<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use App\Models\NilaiEkstrakurikuler;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EkstrakurikulerController extends Controller
{
    public function index(Request $request)
    {
        $query = Ekstrakurikuler::query();
        
        // Handle search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_ekstrakurikuler', 'LIKE', "%{$search}%")
                  ->orWhere('pembina', 'LIKE', "%{$search}%");
            });
        }
        
        $ekstrakurikulers = $query->paginate(10);
        return view('admin.ekstrakulikuler', compact('ekstrakurikulers'));
    }
    

    public function create()
    {
        return view('data.add_data_extracurriculer');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_ekstrakurikuler' => 'required|string|max:255',
                'pembina' => 'required|string|max:255',
            ], [
                'nama_ekstrakurikuler.required' => 'Nama ekstrakurikuler wajib diisi',
                'pembina.required' => 'Nama pembina wajib diisi',
            ]);

            Ekstrakurikuler::create($validated);

            return redirect()->route('ekstra.index')
                ->with('success', 'Data ekstrakurikuler berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Error creating ekstrakurikuler: ' . $e->getMessage());
            return back()
                ->with('error', 'Terjadi kesalahan sistem')
                ->withInput();
        }
    }

    public function edit($id)
    {
        $ekstrakurikuler = Ekstrakurikuler::findOrFail($id);
        return view('data.edit_data_extracurriculer', compact('ekstrakurikuler'));
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nama_ekstrakurikuler' => 'required|string|max:255',
                'pembina' => 'required|string|max:255',
            ], [
                'nama_ekstrakurikuler.required' => 'Nama ekstrakurikuler wajib diisi',
                'pembina.required' => 'Nama pembina wajib diisi',
            ]);

            $ekstrakurikuler = Ekstrakurikuler::findOrFail($id);
            $ekstrakurikuler->update($validated);

            return redirect()->route('ekstra.index')
                ->with('success', 'Data ekstrakurikuler berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error updating ekstrakurikuler: ' . $e->getMessage());
            return back()
                ->with('error', 'Terjadi kesalahan sistem')
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $ekstrakurikuler = Ekstrakurikuler::findOrFail($id);
            $ekstrakurikuler->delete();

            return redirect()->route('ekstra.index')
                ->with('success', 'Data ekstrakurikuler berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting ekstrakurikuler: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem');
        }
    }
    public function waliKelasIndex()
    {
        // Ambil data wali kelas yang sedang login
        $waliKelas = auth()->guard('guru')->user();
        
        // Query nilai ekstrakulikuler hanya untuk siswa di kelas yang diajar
        $nilaiEkstrakurikuler = NilaiEkstrakurikuler::with(['siswa', 'ekstrakurikuler'])
            ->whereHas('siswa', function($query) use ($waliKelas) {
                $query->where('kelas_id', $waliKelas->kelas_pengajar_id);
            })
            ->paginate(10);
    
        return view('wali_kelas.ekstrakurikuler', compact('nilaiEkstrakurikuler'));
    }

    public function waliKelasCreate()
    {
        // Ambil data wali kelas yang sedang login
        $waliKelas = auth()->guard('guru')->user();
        
        // Ambil daftar ekstrakurikuler
        $ekstrakurikuler = Ekstrakurikuler::all();
        
        // Ambil hanya siswa dari kelas yang diajar
        $siswa = Siswa::where('kelas_id', $waliKelas->kelas_pengajar_id)
                      ->orderBy('nama')
                      ->get();
        
        return view('wali_kelas.add_ekstrakurikuler', compact('ekstrakurikuler', 'siswa'));
    }

    public function waliKelasStore(Request $request)
    {
        $waliKelas = auth()->guard('guru')->user();
        
        // Validasi bahwa siswa berada di kelas yang diajar
        $validated = $request->validate([
            'siswa_id' => [
                'required',
                'exists:siswas,id',
                function ($attribute, $value, $fail) use ($waliKelas) {
                    $siswa = Siswa::find($value);
                    if ($siswa->kelas_id !== $waliKelas->kelas_pengajar_id) {
                        $fail('Siswa tidak terdaftar di kelas Anda.');
                    }
                },
            ],
            'ekstrakurikuler_id' => 'required|exists:ekstrakurikulers,id',
            'predikat' => 'required|string',
            'deskripsi' => 'nullable|string',
        ]);
    
        // Cek apakah siswa sudah memiliki nilai untuk ekstrakurikuler ini
        $exists = NilaiEkstrakurikuler::where('siswa_id', $validated['siswa_id'])
            ->where('ekstrakurikuler_id', $validated['ekstrakurikuler_id'])
            ->exists();
            
        if ($exists) {
            return back()->with('error', 'Siswa sudah memiliki nilai untuk ekstrakurikuler ini.');
        }
    
        NilaiEkstrakurikuler::create($validated);
    
        return redirect()->route('wali_kelas.ekstrakurikuler.index')
            ->with('success', 'Data ekstrakurikuler berhasil ditambahkan');
    }
    public function waliKelasEdit($id)
    {
        $waliKelas = auth()->guard('guru')->user();
        
        // Query nilai ekstrakulikuler dan pastikan siswa berada di kelas yang diajar
        $nilaiEkstrakurikuler = NilaiEkstrakurikuler::with(['siswa', 'ekstrakurikuler'])
            ->whereHas('siswa', function($query) use ($waliKelas) {
                $query->where('kelas_id', $waliKelas->kelas_pengajar_id);
            })
            ->findOrFail($id);
        
        return view('wali_kelas.edit_ekstrakurikuler', compact('nilaiEkstrakurikuler'));
    }

    public function waliKelasUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'predikat' => 'required|string',
            'deskripsi' => 'nullable|string',
        ]);

        $nilaiEkstrakurikuler = NilaiEkstrakurikuler::findOrFail($id);
        $nilaiEkstrakurikuler->update($validated);

        return redirect()->route('wali_kelas.ekstrakurikuler.index')
            ->with('success', 'Data ekstrakurikuler berhasil diperbarui');
    }
}