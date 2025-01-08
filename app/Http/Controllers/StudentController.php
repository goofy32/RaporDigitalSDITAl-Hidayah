<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\StudentImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Siswa::with('kelas')->orderBy('kelas_id', 'asc');
     
        if ($request->has('search')) {
            $search = strtolower($request->search);
            $terms = explode(' ', trim($search));
            
            $query->where(function($q) use ($terms, $search) { // Tambahkan $search ke use
                // Jika kata pertama adalah "kelas"
                if (count($terms) > 0 && $terms[0] === 'kelas') {
                    $q->whereHas('kelas', function($kelasQ) use ($terms) {
                        // Jika ada nomor kelas yang dispecifikkan (kelas 1, kelas 2, dst)
                        if (count($terms) > 1 && is_numeric($terms[1])) {
                            $kelasQ->where('nomor_kelas', $terms[1]);
                        } else {
                            // Jika hanya "kelas", urutkan berdasarkan nomor_kelas
                            $kelasQ->orderBy('nomor_kelas', 'asc');
                        }
                    });
                } else {
                    // Pencarian normal untuk term lainnya menggunakan $search
                    $q->where(function($subQ) use ($search) {
                        $subQ->where('nama', 'LIKE', "%{$search}%")
                            ->orWhere('nis', 'LIKE', "%{$search}%")
                            ->orWhere('nisn', 'LIKE', "%{$search}%")
                            ->orWhereHas('kelas', function($kelasQ) use ($search) {
                                $kelasQ->where('nama_kelas', 'LIKE', "%{$search}%")
                                      ->orWhere('nomor_kelas', 'LIKE', "%{$search}%");
                            });
                    });
                }
            });
            
            // Jika pencarian dimulai dengan "kelas" tapi tidak ada nomor spesifik
            if (str_starts_with($search, 'kelas') && count($terms) === 1) {
                $query->join('kelas', 'siswas.kelas_id', '=', 'kelas.id')
                      ->orderBy('kelas.nomor_kelas', 'asc')
                      ->select('siswas.*');
            }
        }
        
        $students = $query->paginate(10);
        return view('admin.student', compact('students'));
    }

    public function create()
    {
        $kelas = Kelas::all();
        return view('data.add_student', compact('kelas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis' => 'required|string|max:20|unique:siswas,nis', // Batasi panjang NIS
            'nisn' => 'required|string|max:20|unique:siswas,nisn', // Batasi panjang NISN
            'nama' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date|before:today', // Pastikan tanggal lahir sebelum hari ini
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required|string|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu',
            'alamat' => 'required|string|max:500',
            'kelas_id' => 'required|exists:kelas,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'nama_ayah' => 'required|string|max:255',
            'nama_ibu' => 'required|string|max:255',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'pekerjaan_ibu' => 'nullable|string|max:100',
            'alamat_orangtua' => 'nullable|string|max:500',
            'wali_siswa' => 'nullable|string|max:255',
            'pekerjaan_wali' => 'nullable|string|max:100',
        ]);
    
        // Set default empty string untuk field nullable
        $validated['alamat_orangtua'] = $validated['alamat_orangtua'] ?? '';
        $validated['pekerjaan_ayah'] = $validated['pekerjaan_ayah'] ?? '';
        $validated['pekerjaan_ibu'] = $validated['pekerjaan_ibu'] ?? '';
        $validated['wali_siswa'] = $validated['wali_siswa'] ?? '';
        $validated['pekerjaan_wali'] = $validated['pekerjaan_wali'] ?? '';
    
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }
    
        try {
            Siswa::create($validated);
            return redirect()->route('student')->with('success', 'Data siswa berhasil ditambahkan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                        ->withInput();
        }
    }

    public function show($id)
    {
        $student = Siswa::with('kelas')->findOrFail($id);
        return view('data.siswa_data', compact('student'));
    }
    public function edit($id)
    {
        $student = Siswa::findOrFail($id);
        $kelas = Kelas::all();
        return view('data.edit_student', compact('student', 'kelas'));
    }

    public function update(Request $request, $id)
    {
        $student = Siswa::findOrFail($id);
        $validated = $request->validate([
            'nis' => 'required|string|max:20|unique:siswas,nis,'.$id,
            'nisn' => 'required|string|max:20|unique:siswas,nisn,'.$id,
            'nama' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date|before:today',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required|string|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu',
            'alamat' => 'required|string|max:500',
            'kelas_id' => 'required|exists:kelas,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'nama_ayah' => 'required|string|max:255',
            'nama_ibu' => 'required|string|max:255',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'pekerjaan_ibu' => 'nullable|string|max:100',
            'alamat_orangtua' => 'nullable|string|max:500',
            'wali_siswa' => 'nullable|string|max:255',
            'pekerjaan_wali' => 'nullable|string|max:100',
        ]);
    
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($student->photo) {
                Storage::delete($student->photo);
            }
            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }
    
        $student->update($validated);
        return redirect()->route('student')->with('success', 'Data siswa berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $student = Siswa::findOrFail($id);
        
        // Hapus foto jika ada
        if ($student->photo) {
            Storage::delete($student->photo);
        }
        
        $student->delete();
        return redirect()->route('student')->with('success', 'Data siswa berhasil dihapus!');
    }

    public function waliKelasIndex(Request $request)
    {
        $waliKelas = auth()->guard('guru')->user();
        $query = Siswa::with('kelas')
            ->where('kelas_id', $waliKelas->kelas_pengajar_id);
        
        if($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nis', 'LIKE', "%{$search}%")
                  ->orWhere('nisn', 'LIKE', "%{$search}%");
            });
        }
        
        $students = $query->paginate($request->get('per_page', 10));
        
        return view('wali_kelas.student', compact('students'));
    }

    public function waliKelasShow($id)
    {
        $waliKelas = auth()->guard('guru')->user();
        $student = Siswa::with('kelas')
            ->where('kelas_id', $waliKelas->kelas_pengajar_id)
            ->findOrFail($id);
            
        return view('wali_kelas.detail_student', compact('student'));
    }

    public function waliKelasCreate()
    {
        $waliKelas = auth()->guard('guru')->user();
        $kelas = Kelas::where('id', $waliKelas->kelas_pengajar_id)->first();
        
        return view('wali_kelas.add_student', compact('kelas'));
    }

    public function waliKelasStore(Request $request)
    {
        $waliKelas = auth()->guard('guru')->user();
        
        $validated = $request->validate([
            'nis' => 'required|unique:siswas',
            'nisn' => 'required|unique:siswas',
            'nama' => 'required',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required',
            'agama' => 'required',
            'alamat' => 'required',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'nama_ayah' => 'required|string',
            'nama_ibu' => 'required|string',
            'pekerjaan_ayah' => 'nullable|string',
            'pekerjaan_ibu' => 'nullable|string',
            'alamat_orangtua' => 'nullable|string',
            'wali_siswa' => 'nullable|string',
            'pekerjaan_wali' => 'nullable|string',
        ]);

        // Set kelas_id sesuai kelas wali kelas
        $validated['kelas_id'] = $waliKelas->kelas_pengajar_id;
        
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }

        try {
            Siswa::create($validated);
            return redirect()->route('wali_kelas.student.index')
                ->with('success', 'Data siswa berhasil ditambahkan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function waliKelasEdit($id)
    {
        $waliKelas = auth()->guard('guru')->user();
        $student = Siswa::where('kelas_id', $waliKelas->kelas_pengajar_id)
            ->findOrFail($id);
        $kelas = Kelas::where('id', $waliKelas->kelas_pengajar_id)->first();

        return view('wali_kelas.edit_student', compact('student', 'kelas'));
    }

    public function waliKelasUpdate(Request $request, $id)
    {
        $waliKelas = auth()->guard('guru')->user();
        $student = Siswa::where('kelas_id', $waliKelas->kelas_pengajar_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'nis' => 'required|unique:siswas,nis,' . $id,
            'nisn' => 'required|unique:siswas,nisn,' . $id,
            'nama' => 'required',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required',
            'agama' => 'required',
            'alamat' => 'required',
            'photo' => 'nullable|image|max:2048',
            'nama_ayah' => 'nullable',
            'nama_ibu' => 'nullable',
            'pekerjaan_ayah' => 'nullable',
            'pekerjaan_ibu' => 'nullable',
            'alamat_orangtua' => 'nullable',
        ]);

        if ($request->hasFile('photo')) {
            if ($student->photo) {
                Storage::delete('public/' . $student->photo);
            }
            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }

        $student->update($validated);
        return redirect()->route('wali_kelas.student.index')
            ->with('success', 'Data siswa berhasil diperbarui!');
    }

    public function waliKelasDestroy($id)
    {
        $waliKelas = auth()->guard('guru')->user();
        $student = Siswa::where('kelas_id', $waliKelas->kelas_pengajar_id)
            ->findOrFail($id);
            
        if ($student->photo) {
            Storage::delete('public/' . $student->photo);
        }
            
        $student->delete();
        return redirect()->route('wali_kelas.student.index')
            ->with('success', 'Data siswa berhasil dihapus!');
    }
    public function uploadPage()
    {
        return view('data.upload_student');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);
    
        try {
            DB::beginTransaction();
    
            $import = new StudentImport();
            Excel::import($import, $request->file('file'));
    
            // Ambil error dari import
            $importErrors = $import->getErrors();
    
            if (!empty($importErrors)) {
                DB::rollBack();
                Log::error('Import Errors:', $importErrors);
                return back()->with('error', $importErrors);
            }
    
            DB::commit();
    
            return redirect()->route('student')
                ->with('success', 'Data siswa berhasil diimpor!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import Exception: ' . $e->getMessage());
            Log::error('Import Trace: ' . $e->getTraceAsString());
    
            return back()->with('error', [
                'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage()
            ]);
        }
    }



    public function downloadTemplate()
    {
        $filePath = public_path('templates/Student_Template_with_Data.xlsx');

        if (!file_exists($filePath)) {
            abort(404, 'File template tidak ditemukan.');
        }

        return response()->download($filePath, 'Student_Template.xlsx');
    }
}