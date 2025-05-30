<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        // Ambil tahun ajaran dari session
        $tahunAjaranId = session('tahun_ajaran_id');
        
        // Log incoming request data for debugging
        Log::info('Teacher search request', [
            'search' => $request->search,
            'tahun_ajaran_id' => $tahunAjaranId,
            'page' => $request->page
        ]);
        
        // Jika pencarian aktif, gunakan pendekatan 2-step untuk prioritaskan hasil yang persis sama 
        if ($request->has('search') && !empty($request->search)) {
            $search = trim(strtolower($request->search));
            Log::info('Processing advanced search', ['term' => $search]);
            
            // Step 1: Query untuk exact match terlebih dahulu
            $exactMatches = Guru::select('gurus.*')
                ->where(function($q) use ($search) {
                    $q->whereRaw('LOWER(gurus.nama) = ?', [$search])
                      ->orWhereRaw('LOWER(gurus.nuptk) = ?', [$search])
                      ->orWhereRaw('LOWER(gurus.username) = ?', [$search])
                      ->orWhereRaw('LOWER(gurus.email) = ?', [$search]);
                })
                ->get();
                
            Log::info('Exact matches', [
                'count' => $exactMatches->count(),
                'names' => $exactMatches->pluck('nama')->toArray()
            ]);
            
            // Step 2: Query untuk partial match (mengandung kata pencarian)
            $partialMatches = Guru::select('gurus.*')
                ->where(function($q) use ($search) {
                    $q->whereRaw('LOWER(gurus.nama) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(gurus.nuptk) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(gurus.username) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(gurus.email) LIKE ?', ["%{$search}%"]);
                })
                ->whereNotIn('id', $exactMatches->pluck('id')->toArray()) // Exclude exact matches
                ->get();
                
            Log::info('Partial matches', [
                'count' => $partialMatches->count(),
                'first_5_names' => $partialMatches->take(5)->pluck('nama')->toArray()
            ]);
            
            // Step 3: Gabungkan hasil dengan exact match di awal
            $combinedResults = $exactMatches->concat($partialMatches);
            
            // Step 4: Load relations
            $guruIds = $combinedResults->pluck('id')->toArray();
            $relatedData = Guru::with(['kelas' => function($q) use ($tahunAjaranId) {
                    $q->withPivot('is_wali_kelas', 'role');
                    if ($tahunAjaranId) {
                        $q->where('kelas.tahun_ajaran_id', $tahunAjaranId);
                    }
                }])
                ->whereIn('id', $guruIds)
                ->get()
                ->keyBy('id');
                
            // Step 5: Integrasi data relational dengan urutan yang sama 
            $completeResults = $combinedResults->map(function($item) use ($relatedData) {
                return $relatedData->get($item->id);
            })->filter(); // Remove nulls
            
            // Step 6: Custom pagination
            $page = $request->input('page', 1);
            $perPage = 10;
            $total = $completeResults->count();
            
            $slice = $completeResults->slice(($page - 1) * $perPage, $perPage)->values();
            
            $teachers = new LengthAwarePaginator(
                $slice,
                $total,
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );
            
            Log::info('Search results (custom pagination)', [
                'total' => $total,
                'results_on_this_page' => $slice->count(),
                'page' => $page,
                'first_result_name' => $slice->first() ? $slice->first()->nama : 'no results'
            ]);
            
            return view('admin.teacher', compact('teachers'));
        }
        
        // Standard query tanpa pencarian (kode asli)
        $query = Guru::select([
                'gurus.*',
                DB::raw('MIN(kelas.nomor_kelas) as nomor_kelas')
            ])
            ->leftJoin('guru_kelas', 'gurus.id', '=', 'guru_kelas.guru_id')
            ->leftJoin('kelas', 'guru_kelas.kelas_id', '=', 'kelas.id')
            ->with(['kelas' => function($q) use ($tahunAjaranId) {
                $q->withPivot('is_wali_kelas', 'role');
                if ($tahunAjaranId) {
                    $q->where('kelas.tahun_ajaran_id', $tahunAjaranId);
                }
            }])
            ->groupBy([
                'gurus.id',
                'gurus.nuptk',
                'gurus.nama',
                'gurus.jenis_kelamin',
                'gurus.tanggal_lahir',
                'gurus.no_handphone',
                'gurus.email',
                'gurus.alamat',
                'gurus.jabatan',
                'gurus.username',
                'gurus.password',
                'gurus.password_plain',
                'gurus.photo',
                'gurus.created_at',
                'gurus.updated_at'
            ]);
            
        // Filter berdasarkan tahun ajaran aktif
        if ($tahunAjaranId) {
            $query->whereExists(function ($subquery) use ($tahunAjaranId) {
                $subquery->select(DB::raw(1))
                    ->from('guru_kelas')
                    ->join('kelas', 'guru_kelas.kelas_id', '=', 'kelas.id')
                    ->whereRaw('guru_kelas.guru_id = gurus.id')
                    ->where('kelas.tahun_ajaran_id', $tahunAjaranId);
            })
            ->orWhereNotExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('guru_kelas')
                    ->whereRaw('guru_kelas.guru_id = gurus.id');
            });
        }
        
        // Order by untuk tampilan yang konsisten
        $query->orderBy('gurus.nama', 'asc');

        $teachers = $query->paginate(10);
        
        Log::info('Standard query results', [
            'count' => $teachers->count(),
            'total' => $teachers->total(),
            'current_page' => $teachers->currentPage()
        ]);
        
        return view('admin.teacher', compact('teachers'));
    }


    public function create()
    {
        $tahunAjaranId = session('tahun_ajaran_id');
        
        // Ambil semua kelas untuk kelas yang diajar
        $kelasForMengajar = Kelas::when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->orderBy('nomor_kelas')
            ->orderBy('nama_kelas')
            ->get();
    
        // Ambil kelas yang belum memiliki wali kelas untuk opsi wali kelas
        $kelasForWali = Kelas::when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->whereDoesntHave('guru', function($query) {
                $query->where('guru_kelas.is_wali_kelas', true)
                    ->where('guru_kelas.role', 'wali_kelas');
            })
            ->orderBy('nomor_kelas')
            ->orderBy('nama_kelas')
            ->get();
    
        // Periksa apakah ada kelas yang tersedia
        $hasKelas = $kelasForMengajar->count() > 0;
    
        // Tambahkan pesan error ke session jika tidak ada kelas
        if (!$hasKelas) {
            session()->flash('error', 'Tidak ada kelas yang tersedia. Harap buat kelas terlebih dahulu sebelum menambahkan guru.');
        }
    
        return view('data.create_teacher', compact('kelasForMengajar', 'kelasForWali', 'hasKelas'));
    }
    
    public function store(Request $request)
    {
        return DB::transaction(function() use ($request) {
            // Validasi dasar tetap sama
            $rules = [
                'nuptk' => 'required|numeric|digits_between:9,15|unique:gurus,nuptk',
                'nama' => 'required|string|max:255',
                'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
                'tanggal_lahir' => 'required|date',
                'no_handphone' => 'required|numeric|digits_between:10,15',
                'email' => 'required|email|max:255|unique:gurus,email',
                'alamat' => 'required|string|max:500',
                'jabatan' => 'required|in:guru,guru_wali',
                'username' => 'required|string|max:255|unique:gurus,username',
                'password' => 'required|string|min:6|confirmed',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ];
            
            // Tambah validasi kelas_ids dan wali_kelas_id berdasarkan jabatan
            if ($request->jabatan === 'guru') {
                $rules['kelas_ids'] = 'required|array';
            } else if ($request->jabatan === 'guru_wali') {
                $rules['wali_kelas_id'] = 'required|exists:kelas,id';
                // Untuk guru_wali, kelas_ids bisa hanya berisi wali_kelas_id saja
                $rules['kelas_ids'] = 'nullable|array';
            }
    
            $validated = $request->validate($rules, [
                'nuptk.required' => 'NUPTK wajib diisi',
                'nuptk.numeric' => 'NUPTK harus berupa angka',
                'nuptk.digits_between' => 'NUPTK harus antara 9-15 digit',
                'nuptk.unique' => 'NUPTK sudah digunakan',
                'nama.required' => 'Nama wajib diisi',
                'jenis_kelamin.required' => 'Jenis kelamin wajib diisi',
                'tanggal_lahir.required' => 'Tanggal lahir wajib diisi',
                'no_handphone.required' => 'Nomor handphone wajib diisi',
                'no_handphone.numeric' => 'Nomor handphone harus berupa angka',
                'no_handphone.digits_between' => 'Nomor handphone harus antara 10-15 digit',
                'email.required' => 'Email wajib diisi',
                'email.email' => 'Format email tidak valid',
                'email.unique' => 'Email sudah digunakan',
                'alamat.required' => 'Alamat wajib diisi',
                'jabatan.required' => 'Jabatan wajib diisi',
                'jabatan.in' => 'Jabatan harus guru atau guru dan wali kelas',
                'kelas_ids.required' => 'Kelas yang diajar wajib diisi',
                'wali_kelas_id.required' => 'Kelas yang diwalikan wajib diisi untuk wali kelas',
                'wali_kelas_id.exists' => 'Kelas yang dipilih tidak valid',
                'username.required' => 'Username wajib diisi',
                'username.unique' => 'Username sudah digunakan',
                'password.required' => 'Password wajib diisi',
                'password.min' => 'Password minimal 6 karakter',
                'password.confirmed' => 'Konfirmasi password tidak cocok',
                'photo.image' => 'File harus berupa gambar',
                'photo.mimes' => 'Format file harus JPG atau PNG',
                'photo.max' => 'Ukuran file maksimal 2MB',
            ]);
    
            // Handle password dan photo
            $validated['password'] = Hash::make($validated['password']);
            $validated['password_plain'] = $request->password;
    
            if ($request->hasFile('photo')) {
                $validated['photo'] = $request->file('photo')->store('teacher-photos', 'public');
            }
    
            // Buat guru baru
            $guru = Guru::create($validated);
    
            // Tentukan kelas yang akan diajar
            $kelas_ids = [];
            
            // Jika guru_wali, pastikan hanya mengajar di kelas wali
            if ($request->jabatan === 'guru_wali' && $request->filled('wali_kelas_id')) {
                $kelas_ids = [$request->wali_kelas_id];
            } 
            // Jika guru biasa, gunakan kelas_ids yang dipilih
            else if ($request->jabatan === 'guru') {
                $kelas_ids = $validated['kelas_ids'] ?? [];
            }
    
            // Array untuk menyimpan kelas yang sudah di-attach
            $attachedKelas = [];
    
            // Proses kelas yang diajar
            foreach ($kelas_ids as $kelasId) {
                // Skip jika kelas sudah di-attach
                if (in_array($kelasId, $attachedKelas)) {
                    continue;
                }
    
                $isWaliKelas = ($request->jabatan === 'guru_wali' && $request->wali_kelas_id == $kelasId);
                
                // Cek apakah sudah ada relasi yang sama
                $existingRelation = DB::table('guru_kelas')
                    ->where('guru_id', $guru->id)
                    ->where('kelas_id', $kelasId)
                    ->where('is_wali_kelas', $isWaliKelas)
                    ->where('role', $isWaliKelas ? 'wali_kelas' : 'pengajar')
                    ->exists();
    
                if (!$existingRelation) {
                    $guru->kelas()->attach($kelasId, [
                        'is_wali_kelas' => $isWaliKelas,
                        'role' => $isWaliKelas ? 'wali_kelas' : 'pengajar'
                    ]);
                    $attachedKelas[] = $kelasId;
                }
            }
    
            Log::info('Guru baru ditambahkan', [
                'id' => $guru->id,
                'nama' => $guru->nama,
                'jabatan' => $guru->jabatan,
                'wali_kelas' => $request->jabatan === 'guru_wali' ? $request->wali_kelas_id : 'tidak ada'
            ]);
    
            return redirect()->route('teacher')
                ->with('success', 'Data guru berhasil ditambahkan');
        });
    }
    
    public function showPassword($id)
    {
        try {
            $teacher = Guru::findOrFail($id);
            
            // Tambahkan kolom password_plain di migration jika belum ada
            $plainPassword = $teacher->password_plain ?? 'Password tidak tersedia';
            
            return response()->json([
                'status' => 'success',
                'password' => $plainPassword
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil password'
            ], 500);
        }
    }
    
    public function show($id)
    {
        $teacher = Guru::with('kelasPengajar')->findOrFail($id);
        return view('data.teacher_data', compact('teacher'));
    }


    public function edit($id)
    {
        $tahunAjaranId = session('tahun_ajaran_id');
        
        // Load guru dengan relasi yang dibutuhkan
        $teacher = Guru::with([
            'kelas' => function($query) use ($tahunAjaranId) {
                $query->withPivot('is_wali_kelas', 'role');
                if ($tahunAjaranId) {
                    $query->where('kelas.tahun_ajaran_id', $tahunAjaranId);
                }
            },
            'kelasWali' => function($query) use ($tahunAjaranId) {
                if ($tahunAjaranId) {
                    $query->where('kelas.tahun_ajaran_id', $tahunAjaranId);
                }
            }
        ])->findOrFail($id);
    
        if ($teacher->tanggal_lahir) {
            $teacher->tanggal_lahir = date('Y-m-d', strtotime($teacher->tanggal_lahir));
        }
        
        // Ambil semua kelas untuk opsi mengajar
        $kelasList = Kelas::when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->orderBy('nomor_kelas')
            ->orderBy('nama_kelas')
            ->get();
    
        // Ambil kelas yang tersedia untuk wali kelas
        $availableKelas = Kelas::when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->whereDoesntHave('guru', function($query) use ($id) {
                $query->where('guru_kelas.is_wali_kelas', true)
                      ->where('guru_kelas.role', 'wali_kelas')
                      ->where('guru_id', '!=', $id);
            })
            ->orWhereHas('guru', function($query) use ($id) {
                $query->where('guru_id', $id)
                      ->where('guru_kelas.is_wali_kelas', true)
                      ->where('guru_kelas.role', 'wali_kelas');
            })
            ->orderBy('nomor_kelas')
            ->orderBy('nama_kelas')
            ->get();
    
        // Ambil data kelas wali saat ini
        $currentWaliKelas = $teacher->kelasWali()->first();
    
        return view('data.edit_teacher', compact(
            'teacher', 
            'kelasList', 
            'availableKelas',
            'currentWaliKelas'
        ));
    }
    
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $tahunAjaranId = session('tahun_ajaran_id');
            $teacher = Guru::findOrFail($id);
        
            // Validasi dasar
            $rules = [
                'nuptk' => 'required|numeric|digits_between:9,15|unique:gurus,nuptk,'.$id,
                'nama' => 'required|string|max:255',
                'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
                'tanggal_lahir' => 'required|date',
                'no_handphone' => 'required|numeric|digits_between:10,15',
                'email' => 'required|email|max:255|unique:gurus,email,'.$id,
                'alamat' => 'required|string|max:500',
                'jabatan' => 'required|in:guru,guru_wali',
                'username' => 'required|string|max:255|unique:gurus,username,'.$id,
            ];
        
            // Tambah validasi berdasarkan jabatan
            if ($request->jabatan === 'guru') {
                $rules['kelas_ids'] = 'required|array';
            } else if ($request->jabatan === 'guru_wali') {
                $rules['wali_kelas_id'] = 'required|exists:kelas,id';
                // Untuk guru_wali, kelas_ids bisa hanya berisi wali_kelas_id saja
                $rules['kelas_ids'] = 'nullable|array';
            }
        
            // Validasi password jika diisi
            if ($request->filled('password')) {
                $rules['password'] = 'required|min:6|confirmed';
                $rules['current_password'] = 'required';
                
                // Verifikasi password saat ini hanya jika ingin mengubah password
                if (!$request->filled('current_password')) {
                    return back()
                        ->withErrors(['current_password' => 'Password saat ini wajib diisi untuk mengubah password'])
                        ->withInput($request->except(['password', 'password_confirmation', 'current_password']));
                }
                
                // Verifikasi password saat ini
                if (!Hash::check($request->current_password, $teacher->password)) {
                    return back()
                        ->withErrors(['current_password' => 'Password saat ini tidak sesuai'])
                        ->withInput($request->except(['password', 'password_confirmation', 'current_password']));
                }
            }
        
            $validated = $request->validate($rules);
    
            // Update data guru
            $dataToUpdate = collect($validated)
                ->except(['password', 'current_password', 'kelas_ids', 'wali_kelas_id'])
                ->toArray();
        
            // Update password jika ada
            if ($request->filled('password')) {
                $dataToUpdate['password'] = Hash::make($request->password);
                $dataToUpdate['password_plain'] = $request->password; // Simpan juga password plaintext untuk keperluan admin
            }
        
            // Update photo jika ada
            if ($request->hasFile('photo')) {
                // Hapus photo lama jika ada
                if ($teacher->photo) {
                    Storage::disk('public')->delete($teacher->photo);
                }
                $dataToUpdate['photo'] = $request->file('photo')->store('teacher-photos', 'public');
            }
        
            $teacher->update($dataToUpdate);
        
            // Hapus semua relasi kelas yang ada untuk tahun ajaran saat ini
            if ($tahunAjaranId) {
                DB::table('guru_kelas')
                    ->where('guru_id', $teacher->id)
                    ->whereExists(function($query) use ($tahunAjaranId) {
                        $query->select(DB::raw(1))
                            ->from('kelas')
                            ->whereRaw('kelas.id = guru_kelas.kelas_id')
                            ->where('kelas.tahun_ajaran_id', $tahunAjaranId);
                    })
                    ->delete();
            } else {
                $teacher->kelas()->detach();
            }
        
            // Array untuk tracking kelas yang sudah di-attach
            $attachedClasses = [];
        
            // Tentukan kelas yang akan diajar
            $kelas_ids = [];
            
            // Jika guru_wali, pastikan hanya mengajar di kelas wali
            if ($request->jabatan === 'guru_wali' && $request->filled('wali_kelas_id')) {
                $kelas_ids = [$request->wali_kelas_id];
            } 
            // Jika guru biasa, gunakan kelas_ids yang dipilih
            else if ($request->jabatan === 'guru') {
                $kelas_ids = $request->kelas_ids ?? [];
            }
        
            // Attach kelas
            foreach ($kelas_ids as $kelasId) {
                // Skip jika kelas sudah di-attach
                if (in_array($kelasId, $attachedClasses)) {
                    continue;
                }
        
                $isWaliKelas = ($request->jabatan === 'guru_wali' && 
                            $request->wali_kelas_id == $kelasId);
        
                $teacher->kelas()->attach($kelasId, [
                    'is_wali_kelas' => $isWaliKelas,
                    'role' => $isWaliKelas ? 'wali_kelas' : 'pengajar'
                ]);
                
                $attachedClasses[] = $kelasId;
            }
        
            // Logging untuk debugging
            \Log::info('Update guru berhasil', [
                'id' => $teacher->id,
                'nama' => $teacher->nama,
                'jabatan' => $request->jabatan,
                'kelas_ids' => $kelas_ids,
                'wali_kelas_id' => $request->wali_kelas_id ?? null,
                'tahun_ajaran_id' => $tahunAjaranId
            ]);
        
            DB::commit();
            return redirect()->route('teacher')
                ->with('success', 'Data guru berhasil diperbarui' . 
                    ($request->filled('password') ? ' beserta passwordnya' : ''));
        
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error update guru', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    
    public function verifyPassword(Request $request)
    {
        try {
            // Log request untuk debugging
            \Log::info('Password verification request received', [
                'teacher_id' => $request->teacher_id,
                'has_password' => !empty($request->current_password)
            ]);
            
            // Ambil data guru
            $teacher = Guru::findOrFail($request->teacher_id);
            
            // Verifikasi password
            $valid = Hash::check($request->current_password, $teacher->password);
            
            \Log::info('Password verification result', [
                'valid' => $valid
            ]);
            
            return response()->json([
                'valid' => $valid,
                'message' => $valid ? 'Password valid' : 'Password tidak sesuai'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error verifying teacher password', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'valid' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showProfile()
    {
        // Karena kita menggunakan auth guard 'guru',
        // data guru yang login sudah tersedia di view melalui Auth::guard('guru')->user()
        return view('pengajar.profile_show');
    }
    public function showWaliKelasProfile()
    {
        return view('wali_kelas.profile_show');
    }

    public function destroy($id)
    {
        $teacher = Guru::findOrFail($id);
        
        // Delete photo if exists
        if ($teacher->photo) {
            Storage::disk('public')->delete($teacher->photo);
        }
        
        $teacher->delete();

        return redirect()->route('teacher')->with('success', 'Data guru berhasil dihapus');
    }
}