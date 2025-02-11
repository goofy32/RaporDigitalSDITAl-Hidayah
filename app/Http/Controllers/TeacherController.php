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

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Guru::select([
                'gurus.*',
                DB::raw('MIN(kelas.nomor_kelas) as nomor_kelas')
            ])
            ->leftJoin('guru_kelas', 'gurus.id', '=', 'guru_kelas.guru_id')
            ->leftJoin('kelas', 'guru_kelas.kelas_id', '=', 'kelas.id')
            ->with(['kelas' => function($q) {
                $q->withPivot('is_wali_kelas', 'role');
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
            ])
            ->orderBy('nomor_kelas', 'asc');
                
        if ($request->has('search')) {
            $search = strtolower($request->search);
            $terms = explode(' ', trim($search));
            
            if (count($terms) > 0 && $terms[0] === 'kelas') {
                $query->whereHas('kelas', function($kelasQ) use ($terms) {
                    if (count($terms) > 1 && is_numeric($terms[1])) {
                        $kelasQ->where('nomor_kelas', $terms[1]);
                    }
                });
            } else {
                $query->where(function($q) use ($search) {
                    $q->where('gurus.nama', 'LIKE', "%{$search}%")
                    ->orWhere('gurus.nuptk', 'LIKE', "%{$search}%")
                    ->orWhere('gurus.email', 'LIKE', "%{$search}%");
                });
            }
        }

        $teachers = $query->paginate(10);
        return view('admin.teacher', compact('teachers'));
    }
    public function create()
    {
        // Ambil semua kelas untuk kelas yang diajar
        $kelasForMengajar = Kelas::orderBy('nomor_kelas')
            ->orderBy('nama_kelas')
            ->get();

        // Ambil kelas yang belum memiliki wali kelas untuk opsi wali kelas
        $kelasForWali = Kelas::whereDoesntHave('guru', function($query) {
                $query->where('guru_kelas.is_wali_kelas', true)
                    ->where('guru_kelas.role', 'wali_kelas');
            })
            ->orderBy('nomor_kelas')
            ->orderBy('nama_kelas')
            ->get();

        return view('data.create_teacher', compact('kelasForMengajar', 'kelasForWali'));
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
                'kelas_ids' => 'required|array',
                'username' => 'required|string|max:255|unique:gurus,username',
                'password' => 'required|string|min:6|confirmed',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ];
            // Tambah validasi wali_kelas_id jika jabatan guru_wali
            if ($request->jabatan === 'guru_wali') {
                $rules['wali_kelas_id'] = 'required|exists:kelas,id';
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


            $validated = $request->validate($rules);

            // Handle password dan photo seperti sebelumnya
            $validated['password'] = Hash::make($validated['password']);
            $validated['password_plain'] = $request->password;

            if ($request->hasFile('photo')) {
                $validated['photo'] = $request->file('photo')->store('teacher-photos', 'public');
            }

            // Buat guru baru
            $guru = Guru::create($validated);

            // Array untuk menyimpan kelas yang sudah di-attach
            $attachedKelas = [];

            // Proses kelas yang diajar
            if (!empty($validated['kelas_ids'])) {
                foreach ($validated['kelas_ids'] as $kelasId) {
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
            }

            // Proses wali kelas jika perlu
            if ($request->jabatan === 'guru_wali' && 
                !in_array($request->wali_kelas_id, $attachedKelas)) {
                
                $guru->kelas()->attach($request->wali_kelas_id, [
                    'is_wali_kelas' => true,
                    'role' => 'wali_kelas'
                ]);
            }

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
        // Load guru dengan relasi yang dibutuhkan
        $teacher = Guru::with([
            'kelas' => function($query) {
                $query->withPivot('is_wali_kelas', 'role');
            },
            'kelasWali'
        ])->findOrFail($id);
    
        if ($teacher->tanggal_lahir) {
            $teacher->tanggal_lahir = date('Y-m-d', strtotime($teacher->tanggal_lahir));
        }
        // Ambil semua kelas untuk opsi mengajar
        $kelasList = Kelas::orderBy('nomor_kelas')
                          ->orderBy('nama_kelas')
                          ->get();
    
        // Ambil kelas yang tersedia untuk wali kelas
        $availableKelas = Kelas::whereDoesntHave('guru', function($query) use ($id) {
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
                'kelas_ids' => 'required|array',
                'username' => 'required|string|max:255|unique:gurus,username,'.$id,
            ];
    
            if ($request->jabatan === 'guru_wali') {
                $rules['wali_kelas_id'] = 'required|exists:kelas,id';
            }
    
            $validated = $request->validate($rules);
    
            // Update guru basic info
            $teacher->update($validated);
    
            // Hapus semua relasi kelas yang ada
            $teacher->kelas()->detach();
    
            // Array untuk tracking kelas yang sudah di-attach
            $attachedClasses = [];
    
            // Attach kelas mengajar terlebih dahulu
            foreach ($validated['kelas_ids'] as $kelasId) {
                // Skip jika kelas sudah di-attach
                if (in_array($kelasId, $attachedClasses)) {
                    continue;
                }
    
                $teacher->kelas()->attach($kelasId, [
                    'is_wali_kelas' => false,
                    'role' => 'pengajar'
                ]);
                $attachedClasses[] = $kelasId;
            }
    
            // Jika guru_wali, attach kelas wali secara terpisah
            if ($request->jabatan === 'guru_wali' && $request->filled('wali_kelas_id')) {
                $waliKelasId = $request->wali_kelas_id;
                
                // Jika kelas wali belum di-attach sebagai pengajar
                if (!in_array($waliKelasId, $attachedClasses)) {
                    $teacher->kelas()->attach($waliKelasId, [
                        'is_wali_kelas' => true,
                        'role' => 'wali_kelas'
                    ]);
                } else {
                    // Jika sudah ada sebagai pengajar, update role-nya
                    DB::table('guru_kelas')
                        ->where('guru_id', $teacher->id)
                        ->where('kelas_id', $waliKelasId)
                        ->update([
                            'is_wali_kelas' => true,
                            'role' => 'wali_kelas'
                        ]);
                }
            }
    
            DB::commit();
            return redirect()->route('teacher')
                ->with('success', 'Data guru berhasil diperbarui');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
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