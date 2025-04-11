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
use Illuminate\Support\Facades\Schema;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        // Ambil tahun ajaran dari session
        $tahunAjaranId = session('tahun_ajaran_id');
        
        // Buat query dasar dengan eager loading kelas
        $query = Siswa::with(['kelas' => function($query) {
            $query->orderBy('nomor_kelas', 'asc')
                  ->orderBy('nama_kelas', 'asc');
        }]);
        
        // Filter berdasarkan tahun ajaran jika ada
        if ($tahunAjaranId) {
            $query->whereHas('kelas', function($q) use ($tahunAjaranId) {
                $q->where('tahun_ajaran_id', $tahunAjaranId);
            });
        }
         
        // Handle pencarian
        if ($request->has('search')) {
            $search = strtolower($request->search);
            $terms = explode(' ', trim($search));
            
            $query->where(function($q) use ($terms, $search) {
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
        }
        
        // Default sorting: mengurutkan berdasarkan kelas (nomor kelas ASC) kemudian nama siswa
        $query->join('kelas', 'siswas.kelas_id', '=', 'kelas.id')
              ->orderBy('kelas.nomor_kelas', 'asc')
              ->orderBy('kelas.nama_kelas', 'asc')
              ->orderBy('siswas.nama', 'asc')
              ->select('siswas.*');
        
        $students = $query->paginate(10);
        
        // Pass data tahun ajaran ke view untuk menampilkan informasi
        $activeTahunAjaran = null;
        if ($tahunAjaranId) {
            $activeTahunAjaran = \App\Models\TahunAjaran::find($tahunAjaranId);
        }
        
        return view('admin.student', compact('students', 'activeTahunAjaran'));
    }

    public function create()
    {
        $tahunAjaranId = session('tahun_ajaran_id');
        
        $kelas = Kelas::when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->orderBy('nomor_kelas')
            ->orderBy('nama_kelas')
            ->get();
            
        return view('data.add_student', compact('kelas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis' => [
                'required',
                'numeric',          // Memastikan hanya angka
                'digits_between:5,10', // Minimal 5 digit, maksimal 10 digit
                'unique:siswas,nis'
            ],
            'nisn' => [
                'required',
                'numeric',         // Memastikan hanya angka
                'digits:10',       // Harus 10 digit
                'unique:siswas,nisn'
            ],
            'nama' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s]*$/'  // Hanya huruf dan spasi
            ],
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
        $tahunAjaranId = session('tahun_ajaran_id');
        $student = Siswa::findOrFail($id);
        
        $kelas = Kelas::when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->orderBy('nomor_kelas')
            ->orderBy('nama_kelas')
            ->get();
            
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
        $kelasWaliId = $waliKelas->getWaliKelasId();
        $tahunAjaranId = session('tahun_ajaran_id');
        
        if (!$kelasWaliId) {
            return back()->with('error', 'Anda belum ditugaskan sebagai wali kelas.');
        }
        
        // Pastikan relasi kelas selalu di-load
        $query = Siswa::with(['kelas' => function($query) {
            $query->select('id', 'nomor_kelas', 'nama_kelas');
        }])->where('kelas_id', $kelasWaliId);
        
        // Filter berdasarkan tahun ajaran
        if ($tahunAjaranId) {
            $query->whereHas('kelas', function($q) use ($tahunAjaranId) {
                $q->where('tahun_ajaran_id', $tahunAjaranId);
            });
        }
        
        if($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                ->orWhere('nis', 'LIKE', "%{$search}%")
                ->orWhere('nisn', 'LIKE', "%{$search}%")
                ->orWhere('jenis_kelamin', 'LIKE', "%{$search}%");
            });
        }
        
        // Default sorting
        $query->orderBy('nama', 'asc');
        
        $students = $query->paginate(10);
        
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
        
        // Cek wali kelas melalui relasi guru_kelas
        $kelas = $waliKelas->kelasWali()->first();
        
        if (!$kelas) {
            return redirect()->route('wali_kelas.student.index')
                ->with('error', 'Anda belum ditugaskan sebagai wali kelas.');
        }
        
        return view('wali_kelas.add_student', compact('kelas'));
    }
    

    public function waliKelasStore(Request $request)
    {
        try {
            // Get wali kelas
            $waliKelas = auth()->guard('guru')->user();
            
            // Cek wali kelas melalui relasi guru_kelas
            $kelas = $waliKelas->kelasWali()->first();
            
            if (!$kelas) {
                return redirect()->route('wali_kelas.student.index')
                    ->with('error', 'Anda belum ditugaskan sebagai wali kelas.');
            }
            
            // Get tahun ajaran from session or from kelas
            $tahunAjaranId = session('tahun_ajaran_id') ?? $kelas->tahun_ajaran_id;
            
            if (!$tahunAjaranId) {
                return redirect()->route('wali_kelas.student.index')
                    ->with('error', 'Tahun ajaran belum dipilih. Silakan pilih tahun ajaran terlebih dahulu.');
            }
            
            // Validate the request
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
            ], [
                'nis.required' => 'NIS wajib diisi.',
                'nis.unique' => 'NIS sudah digunakan oleh siswa lain.',
                'nisn.required' => 'NISN wajib diisi.',
                'nisn.unique' => 'NISN sudah digunakan oleh siswa lain.',
                'nama.required' => 'Nama siswa wajib diisi.',
                'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
                'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
                'agama.required' => 'Agama wajib dipilih.',
                'alamat.required' => 'Alamat wajib diisi.',
                'nama_ayah.required' => 'Nama ayah wajib diisi.',
                'nama_ibu.required' => 'Nama ibu wajib diisi.',
            ]);
    
            // Set kelas_id from the selected class
            $validated['kelas_id'] = $kelas->id;
            
            // Explicitly set the tahun_ajaran_id
            $validated['tahun_ajaran_id'] = $tahunAjaranId;
            
            // Handle photo upload
            if ($request->hasFile('photo')) {
                $validated['photo'] = $request->file('photo')->store('photos', 'public');
            }
    
            // Use database transaction
            DB::beginTransaction();
            
            // Create student
            $siswa = Siswa::create($validated);
            
            DB::commit();
            
            return redirect()->route('wali_kelas.student.index')
                ->with('success', 'Data siswa berhasil ditambahkan!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Ubah format error menjadi satu pesan dengan HTML untuk SweetAlert
            $errorMessages = collect($e->errors())->flatten()->implode('<br>');
            
            // Kembali dengan validation_errors dalam session
            \Log::info('Validation error: ' . $errorMessages);
            return back()->with('swal_validation_error', $errorMessages)->withInput();
        } catch (\Exception $e) {
            // Rollback transaction in case of error
            if (isset($e) && DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            
            // Log the error
            \Log::error('Error adding student: ' . $e->getMessage());
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function waliKelasEdit($id)
    {
        $waliKelas = auth()->guard('guru')->user();
        $kelasWaliId = $waliKelas->getWaliKelasId(); // Menggunakan method getWaliKelasId() bukan kelas_pengajar_id
        
        $student = Siswa::where('kelas_id', $kelasWaliId)
            ->findOrFail($id);
        $kelas = Kelas::where('id', $kelasWaliId)->first();
    
        return view('wali_kelas.edit_student', compact('student', 'kelas'));
    }

    public function waliKelasUpdate(Request $request, $id)
    {
        $waliKelas = auth()->guard('guru')->user();
        $kelasWaliId = $waliKelas->getWaliKelasId(); // Menggunakan method getWaliKelasId() bukan kelas_pengajar_id
        
        $student = Siswa::where('kelas_id', $kelasWaliId)
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
        $kelasWaliId = $waliKelas->getWaliKelasId(); // Menggunakan method getWaliKelasId() bukan kelas_pengajar_id
        
        $student = Siswa::where('kelas_id', $kelasWaliId)
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
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ]);
    
        try {
            DB::beginTransaction();
    
            // 1. Log start import
            \Log::info('Starting import process', [
                'file' => $request->file('file')->getClientOriginalName()
            ]);
    
            // 2. Baca file terlebih dahulu
            $data = Excel::toArray(new StudentImport, $request->file('file'));
            
            // 3. Log data yang dibaca
            \Log::info('Excel data read:', [
                'sheets' => count($data),
                'rows' => isset($data[0]) ? count($data[0]) : 0
            ]);
    
            // 4. Lakukan import
            $import = new StudentImport();
            Excel::import($import, $request->file('file'));
    
            // 5. Cek jumlah row yang diproses
            \Log::info('Rows processed:', [
                'count' => $import->getRowCount()
            ]);
    
            // 6. Cek error
            $errors = $import->getErrors();
            if (!empty($errors)) {
                \Log::error('Import errors found:', $errors);
                DB::rollBack();
                return back()->with('error', $errors);
            }
    
            // 7. Commit jika berhasil
            DB::commit();
            \Log::info('Import completed and committed');
    
            return redirect()->route('student')
                ->with('success', 'Data siswa berhasil diimpor!');
    
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Import failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
    public function downloadTemplate()
    {
        try {
            // Ubah path ke public_path() untuk mengakses folder public langsung
            $filePath = public_path('templates/Student_Template_with_Data.xlsx');
    
            if (!file_exists($filePath)) {
                return back()->with('error', 'File template tidak ditemukan di ' . $filePath);
            }
    
            // Gunakan nama file yang lebih user-friendly saat didownload
            return response()->download($filePath, 'Template_Import_Siswa.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
    
        } catch (\Exception $e) {
            \Log::error('Error downloading template: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengunduh template.');
        }
    }
}