<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\ReportTemplate;
use App\Models\Siswa;
use App\Models\ProfilSekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class TahunAjaranController extends Controller
{
    /**
     * Display a listing of tahun ajaran.
     */
    public function index(Request $request)
    {
        $tampilkanArsip = $request->has('showArchived');
        
        // Jika tampilkanArsip bernilai true, sertakan rekaman yang sudah di-soft-delete
        $query = TahunAjaran::orderBy('is_active', 'desc')
                          ->orderBy('tanggal_mulai', 'desc');
                          
        if ($tampilkanArsip) {
            $query->withTrashed();
        }
        
        $tahunAjarans = $query->get();
        
        return view('admin.tahun_ajaran.index', compact('tahunAjarans', 'tampilkanArsip'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tahun_ajaran.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tahun_ajaran' => 'required|string|regex:/^\d{4}\/\d{4}$/',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'semester' => 'required|integer|in:1,2',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                         ->withErrors($validator)
                         ->withInput();
        }

        // Jika menandai sebagai aktif, nonaktifkan tahun ajaran lain
        if ($request->has('is_active') && $request->is_active) {
            TahunAjaran::where('is_active', true)
                   ->update(['is_active' => false]);
        }

        // Buat tahun ajaran baru
        $tahunAjaran = TahunAjaran::create($request->all());

        // Jika aktif, update profil sekolah
        if ($request->has('is_active') && $request->is_active) {
            $this->updateProfilSekolah($tahunAjaran);
        }

        return redirect()->route('tahun.ajaran.index')
                     ->with('success', 'Tahun ajaran berhasil dibuat!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Tambahkan withTrashed() untuk bisa mengakses tahun ajaran yang diarsipkan
        $tahunAjaran = TahunAjaran::withTrashed()->findOrFail($id);
        
        // Hitung statistik
        $totalKelas = Kelas::where('tahun_ajaran_id', $id)->count();
        $totalSiswa = Siswa::whereHas('kelas', function($query) use ($id) {
            $query->where('tahun_ajaran_id', $id);
        })->count();
        $totalMataPelajaran = MataPelajaran::where('tahun_ajaran_id', $id)->count();
        
        return view('admin.tahun_ajaran.show', compact(
            'tahunAjaran', 
            'totalKelas', 
            'totalSiswa', 
            'totalMataPelajaran'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Tambahkan withTrashed() untuk bisa mengedit tahun ajaran yang diarsipkan
        $tahunAjaran = TahunAjaran::withTrashed()->findOrFail($id);
        return view('admin.tahun_ajaran.edit', compact('tahunAjaran'));
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tahun_ajaran' => 'required|string|regex:/^\d{4}\/\d{4}$/',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'semester' => 'required|integer|in:1,2',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                         ->withErrors($validator)
                         ->withInput();
        }
    
        $tahunAjaran = TahunAjaran::withTrashed()->findOrFail($id);
        
        // Simpan semester lama untuk dibandingkan
        $oldSemester = $tahunAjaran->semester;
        $newSemester = $request->semester;
        
        // Cek jika tahun ajaran sedang aktif dan akan dinonaktifkan
        if ($tahunAjaran->is_active && !$request->has('is_active')) {
            // Hitung apakah ini tahun ajaran aktif satu-satunya
            $activeCount = TahunAjaran::where('is_active', true)->count();
            
            if ($activeCount <= 1) {
                return redirect()->back()
                         ->withInput()
                         ->with('error', 'Harus ada minimal satu tahun ajaran yang aktif. Aktifkan tahun ajaran lain terlebih dahulu sebelum menonaktifkan yang ini.');
            }
        }
    
        // Jika menandai sebagai aktif, nonaktifkan tahun ajaran lain
        if ($request->has('is_active') && $request->is_active && !$tahunAjaran->is_active) {
            TahunAjaran::where('is_active', true)
                   ->update(['is_active' => false]);
        }
    
        DB::beginTransaction();
        try {
            // Jika tahun ajaran di-softdelete, restore terlebih dahulu
            if ($tahunAjaran->trashed() && $request->has('is_active') && $request->is_active) {
                $tahunAjaran->restore();
            }
            
            // Update tahun ajaran
            $tahunAjaran->update($request->all());
            
            // Jika ini adalah tahun ajaran aktif, perbarui profil sekolah
            if ($tahunAjaran->is_active) {
                $this->updateProfilSekolah($tahunAjaran);
                
                // Jika semester berubah, update data terkait
                if ($oldSemester != $newSemester) {
                    $this->updateRelatedData($tahunAjaran->id, $newSemester);
                }
            }
            
            DB::commit();
            
            // Pesan sukses khusus untuk perubahan semester
            if ($oldSemester != $newSemester) {
                $semesterLabel = $newSemester == 1 ? 'Ganjil' : 'Genap';
                return redirect()->route('tahun.ajaran.index')
                            ->with('success', "Tahun ajaran berhasil diperbarui! Semester diubah menjadi {$semesterLabel}.");
            }
            
            return redirect()->route('tahun.ajaran.index')
                            ->with('success', 'Tahun ajaran berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                    ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                    ->withInput();
        }
    }
    
    /**
     * Update profil sekolah dengan informasi tahun ajaran
     */
    private function updateProfilSekolah(TahunAjaran $tahunAjaran)
    {
        $profil = ProfilSekolah::first();
        if ($profil) {
            $profil->update([
                'tahun_pelajaran' => $tahunAjaran->tahun_ajaran,
                'semester' => $tahunAjaran->semester
            ]);
            
            Log::info('Profil sekolah diperbarui dengan tahun ajaran aktif', [
                'tahun_ajaran' => $tahunAjaran->tahun_ajaran,
                'semester' => $tahunAjaran->semester
            ]);
        }
    }

    /**
     * Update data yang terkait dengan tahun ajaran saat semester berubah
     * 
     * @param int $tahunAjaranId
     * @param int $newSemester
     * @return void
     */
    private function updateRelatedData($tahunAjaranId, $newSemester)
    {
        try {
            Log::info("Memperbarui data terkait untuk tahun ajaran #{$tahunAjaranId} ke semester {$newSemester}");
            
            // Update absensi dengan semester baru if column exists
            if (Schema::hasColumn('absensis', 'semester')) {
                $absensiCount = DB::table('absensis')
                    ->where('tahun_ajaran_id', $tahunAjaranId)
                    ->update(['semester' => $newSemester]);
                
                Log::info("Updated {$absensiCount} absensi records to semester {$newSemester}");
            }
            
            // Update mata pelajaran dengan semester baru if column exists
            if (Schema::hasColumn('mata_pelajarans', 'semester')) {
                $mapelCount = DB::table('mata_pelajarans')
                    ->where('tahun_ajaran_id', $tahunAjaranId)
                    ->update(['semester' => $newSemester]);
                
                Log::info("Updated {$mapelCount} mata pelajaran records to semester {$newSemester}");
            }
            
            // Update template rapor dengan semester baru if column exists
            if (Schema::hasColumn('report_templates', 'semester')) {
                $templateCount = DB::table('report_templates')
                    ->where('tahun_ajaran_id', $tahunAjaranId)
                    ->update(['semester' => $newSemester]);
                
                Log::info("Updated {$templateCount} report template records to semester {$newSemester}");
            }
            
            // Tambahkan model lain yang memiliki field semester dan tahun_ajaran_id jika ada
            
            // Log perubahan untuk debugging
            \Log::info("Semester diperbarui untuk tahun ajaran #{$tahunAjaranId} ke semester {$newSemester}");
        } catch (\Exception $e) {
            \Log::error("Error updating related data: " . $e->getMessage());
            throw $e; // Re-throw to handle in the caller
        }
    }

    /**
     * Set a tahun ajaran as active.
     */
    public function setActive($id)
    {
        DB::beginTransaction();
        
        try {
            // Nonaktifkan semua tahun ajaran
            TahunAjaran::where('is_active', true)
                ->update(['is_active' => false]);
                
            // Aktifkan tahun ajaran yang dipilih
            $tahunAjaran = TahunAjaran::findOrFail($id);
            $tahunAjaran->update(['is_active' => true]);
            
            // Update juga di profil sekolah
            $this->updateProfilSekolah($tahunAjaran);
            
            // Set session untuk tampilan data
            session(['tahun_ajaran_id' => $id]);
            
            DB::commit();
            
            return redirect()->route('tahun.ajaran.index')
            ->with('success', 'Tahun ajaran ' . $tahunAjaran->tahun_ajaran . ' berhasil diaktifkan!');
        } catch (\Exception $e) {
            DB::rollback();
            
            // Coba ambil tahun ajaran yang sebelumnya aktif
            $oldActive = TahunAjaran::where('is_active', true)->first();
            
            // Jika tidak ada yang aktif, aktifkan yang terakhir
            if (!$oldActive) {
                $latest = TahunAjaran::latest('tanggal_mulai')->first();
                if ($latest) {
                    $latest->update(['is_active' => true]);
                    session(['tahun_ajaran_id' => $latest->id]);
                }
            }
            
            return redirect()->back()->with('error', 'Gagal mengaktifkan tahun ajaran: ' . $e->getMessage());
        }
    }
    
    /**
     * Menghapus tahun ajaran yang spesifik.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $tahunAjaran = TahunAjaran::findOrFail($id);
            
            // Cek apakah tahun ajaran sedang aktif
            if ($tahunAjaran->is_active) {
                return redirect()->back()
                    ->with('error', 'Tidak dapat mengarsipkan tahun ajaran yang sedang aktif. Aktifkan tahun ajaran lain terlebih dahulu.');
            }
            
            // Cek apakah ini adalah satu-satunya tahun ajaran
            $totalTahunAjaran = TahunAjaran::count();
            if ($totalTahunAjaran <= 1) {
                return redirect()->back()
                    ->with('error', 'Tidak dapat mengarsipkan tahun ajaran karena minimal harus ada satu tahun ajaran dalam sistem.');
            }
            
            // Soft delete tahun ajaran daripada menghapusnya permanen
            $tahunAjaran->delete();
            
            return redirect()->route('tahun.ajaran.index')
                ->with('success', 'Tahun ajaran berhasil diarsipkan. Data terkait masih dapat diakses dengan menampilkan tahun ajaran terarsip.');
                
        } catch (\Exception $e) {
            \Log::error('Error saat mengarsipkan tahun ajaran: ' . $e->getMessage(), [
                'tahun_ajaran_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengarsipkan tahun ajaran: ' . $e->getMessage());
        }
    }


    /**
     * Generate tahun ajaran baru berdasarkan tahun ajaran yang sudah ada.
     */
    public function copy($id)
    {
        $sourceTahunAjaran = TahunAjaran::withTrashed()->findOrFail($id);
        
        // Generate tahun ajaran baru
        $tahunParts = explode('/', $sourceTahunAjaran->tahun_ajaran);
        $newTahunAjaran = (intval($tahunParts[0]) + 1) . '/' . (intval($tahunParts[1]) + 1);
        
        // Cek apakah tahun ajaran baru sudah ada
        $exists = TahunAjaran::where('tahun_ajaran', $newTahunAjaran)->first();
        if ($exists) {
            return redirect()->back()->with('error', 'Tahun ajaran ' . $newTahunAjaran . ' sudah ada!');
        }
        
        return view('admin.tahun_ajaran.copy', compact('sourceTahunAjaran', 'newTahunAjaran'));
    }

    /**
     * Process copying data from one academic year to another.
     */
    public function processCopy(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tahun_ajaran' => 'required|string|regex:/^\d{4}\/\d{4}$/',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'semester' => 'required|integer|in:1,2',
            'copy_kelas' => 'boolean',
            'increment_kelas' => 'boolean',
            'create_kelas_one' => 'boolean',
            'copy_mata_pelajaran' => 'boolean',
            'copy_templates' => 'boolean',
            'copy_ekstrakurikuler' => 'boolean',
            'copy_kkm' => 'boolean',
            'copy_bobot_nilai' => 'boolean',
            'is_active' => 'boolean'
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }
    
        DB::beginTransaction();
        
        try {
            $sourceTahunAjaran = TahunAjaran::withTrashed()->findOrFail($id);
            
            // Jika akan diaktifkan, nonaktifkan yang lain dulu
            if ($request->is_active) {
                TahunAjaran::where('is_active', true)
                           ->update(['is_active' => false]);
            }
    
            // Buat tahun ajaran baru
            $newTahunAjaran = TahunAjaran::create([
                'tahun_ajaran' => $request->tahun_ajaran,
                'is_active' => $request->is_active ?? false,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'semester' => $request->semester,
                'deskripsi' => $request->deskripsi ?? ('Tahun Ajaran ' . $request->tahun_ajaran)
            ]);
    
            // Copy kelas jika diminta
            $kelasMapping = [];
            if ($request->copy_kelas) {
                $kelasMapping = $this->copyKelas(
                    $sourceTahunAjaran, 
                    $newTahunAjaran, 
                    $request->increment_kelas ?? false
                );
                
                // Buat kelas 1 baru jika diminta
                if ($request->create_kelas_one) {
                    $this->createKelasOne($newTahunAjaran);
                }
            }
    
            // Copy mata pelajaran jika diminta
            if ($request->copy_mata_pelajaran) {
                $this->copyMataPelajaran($sourceTahunAjaran, $newTahunAjaran, $request->semester, $kelasMapping);
            }
    
            // Copy template rapor jika diminta
            if ($request->copy_templates) {
                $this->copyReportTemplates($sourceTahunAjaran, $newTahunAjaran, $request->semester, $kelasMapping);
            }
            
            // Copy ekstrakurikuler jika diminta
            if ($request->copy_ekstrakurikuler) {
                $this->copyEkstrakurikuler($sourceTahunAjaran, $newTahunAjaran);
            }
            
            // Copy KKM jika diminta
            if ($request->copy_kkm) {
                $this->copyKkm($sourceTahunAjaran, $newTahunAjaran, $kelasMapping);
            }
            
            // Copy Bobot Nilai jika diminta
            if ($request->copy_bobot_nilai) {
                $this->copyBobotNilai($sourceTahunAjaran, $newTahunAjaran);
            }
            
            // Update profil sekolah jika tahun ajaran baru diaktifkan
            if ($newTahunAjaran->is_active) {
                $this->updateProfilSekolah($newTahunAjaran);
            }
            
            DB::commit();
            
            return redirect()->route('tahun.ajaran.index')
                             ->with('success', 'Tahun ajaran baru berhasil dibuat dan data telah disalin!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menyalin tahun ajaran: ' . $e->getMessage());
        }
    }
    
    // Metode helper untuk menyalin kelas dengan opsi peningkatan nomor kelas
    private function copyKelas($sourceTahunAjaran, $newTahunAjaran, $incrementKelasNumbers = false)
    {
        $sourceKelas = Kelas::where('tahun_ajaran_id', $sourceTahunAjaran->id)->get();
        $kelasMapping = [];
        
        foreach ($sourceKelas as $kelas) {
            $newKelas = $kelas->replicate();
            $newKelas->tahun_ajaran_id = $newTahunAjaran->id;
            
            // Tingkatkan nomor kelas jika diminta
            if ($incrementKelasNumbers) {
                $newKelas->nomor_kelas = $kelas->nomor_kelas + 1;
                
                // Skip kelas yang nomor kelasnya melebihi 6 (untuk SD)
                if ($newKelas->nomor_kelas > 6) {
                    continue;
                }
            }
            
            $newKelas->save();
            
            // Simpan mapping kelas lama ke kelas baru
            $kelasMapping[$kelas->id] = $newKelas->id;
            
            // Sync guru untuk kelas baru
            $guruRelations = DB::table('guru_kelas')
                              ->where('kelas_id', $kelas->id)
                              ->get();
            
            foreach ($guruRelations as $relation) {
                DB::table('guru_kelas')->insert([
                    'guru_id' => $relation->guru_id,
                    'kelas_id' => $newKelas->id,
                    'is_wali_kelas' => $relation->is_wali_kelas,
                    'role' => $relation->role,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
        
        return $kelasMapping;
    }
    
    // Metode baru untuk membuat kelas 1 di tahun ajaran baru
    private function createKelasOne($tahunAjaran)
    {
        // Ambil semua kelas 1 dari tahun ajaran sebelumnya sebagai referensi
        $lastYearKelas = Kelas::where('nomor_kelas', 1)
            ->where('tahun_ajaran_id', '!=', $tahunAjaran->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Jika tidak ada referensi, buat kelas 1A dan 1B
        if ($lastYearKelas->isEmpty()) {
            $kelasA = new Kelas([
                'nomor_kelas' => 1,
                'nama_kelas' => 'A',
                'tahun_ajaran_id' => $tahunAjaran->id
            ]);
            $kelasA->save();
            
            $kelasB = new Kelas([
                'nomor_kelas' => 1,
                'nama_kelas' => 'B',
                'tahun_ajaran_id' => $tahunAjaran->id
            ]);
            $kelasB->save();
        } else {
            // Salin struktur kelas 1 dari tahun sebelumnya
            foreach ($lastYearKelas as $kelas) {
                $newKelas = $kelas->replicate();
                $newKelas->tahun_ajaran_id = $tahunAjaran->id;
                $newKelas->nomor_kelas = 1; // Pastikan nomor kelas adalah 1
                $newKelas->save();
            }
        }
    }
        
    // Metode baru untuk menyalin KKM
    private function copyKkm($sourceTahunAjaran, $newTahunAjaran, $kelasMapping = [])
    {
        if (empty($kelasMapping)) {
            return;
        }
        
        $kkms = \App\Models\Kkm::where('tahun_ajaran_id', $sourceTahunAjaran->id)->get();
        
        foreach ($kkms as $kkm) {
            // Hanya salin jika kelas ada dalam mapping
            if (isset($kelasMapping[$kkm->kelas_id])) {
                $newKkm = $kkm->replicate();
                $newKkm->tahun_ajaran_id = $newTahunAjaran->id;
                $newKkm->kelas_id = $kelasMapping[$kkm->kelas_id];
                $newKkm->save();
            }
        }
    }
    
    // Metode baru untuk menyalin Bobot Nilai
    private function copyBobotNilai($sourceTahunAjaran, $newTahunAjaran)
    {
        $bobotNilai = \App\Models\BobotNilai::where('tahun_ajaran_id', $sourceTahunAjaran->id)->first();
        
        if ($bobotNilai) {
            $newBobotNilai = $bobotNilai->replicate();
            $newBobotNilai->tahun_ajaran_id = $newTahunAjaran->id;
            $newBobotNilai->save();
        }
    }
    
    // Metode baru untuk menyalin Ekstrakurikuler
    private function copyEkstrakurikuler($sourceTahunAjaran, $newTahunAjaran)
    {
        $ekstrakurikulers = \App\Models\Ekstrakurikuler::where('tahun_ajaran_id', $sourceTahunAjaran->id)->get();
        
        foreach ($ekstrakurikulers as $ekskul) {
            $newEkskul = $ekskul->replicate();
            $newEkskul->tahun_ajaran_id = $newTahunAjaran->id;
            $newEkskul->save();
        }
    }

    /**
     * Helper method untuk copy mata pelajaran dari satu tahun ajaran ke tahun ajaran lain.
     */
    private function copyMataPelajaran($sourceTahunAjaran, $newTahunAjaran, $newSemester = null)
    {
        $sourceMataPelajaran = MataPelajaran::where('tahun_ajaran_id', $sourceTahunAjaran->id)->get();
        
        // Ambil mapping kelas lama ke kelas baru
        $kelasMapping = [];
        $oldKelasIds = Kelas::where('tahun_ajaran_id', $sourceTahunAjaran->id)->pluck('id')->toArray();
        $newKelasIds = Kelas::where('tahun_ajaran_id', $newTahunAjaran->id)->pluck('id')->toArray();
        
        // Jika jumlah kelas sama, asumsikan mereka berkorespondensi 1-1
        if (count($oldKelasIds) === count($newKelasIds)) {
            $kelasMapping = array_combine($oldKelasIds, $newKelasIds);
        }
        
        foreach ($sourceMataPelajaran as $mapel) {
            $newMapel = $mapel->replicate();
            $newMapel->tahun_ajaran_id = $newTahunAjaran->id;
            
            // Set semester baru jika disediakan dan kolom semester ada
            if ($newSemester !== null && Schema::hasColumn('mata_pelajarans', 'semester')) {
                $newMapel->semester = $newSemester;
            }
            
            // Jika ada mapping kelas, gunakan kelas baru
            if (isset($kelasMapping[$mapel->kelas_id])) {
                $newMapel->kelas_id = $kelasMapping[$mapel->kelas_id];
            }
            
            $newMapel->save();
            
            // Copy lingkup materi dan tujuan pembelajaran
            foreach ($mapel->lingkupMateris as $lm) {
                $newLM = $lm->replicate();
                $newLM->mata_pelajaran_id = $newMapel->id;
                $newLM->save();
                
                foreach ($lm->tujuanPembelajarans as $tp) {
                    $newTP = $tp->replicate();
                    $newTP->lingkup_materi_id = $newLM->id;
                    $newTP->save();
                }
            }
        }
    }

    /**
     * Helper method untuk copy template rapor dari satu tahun ajaran ke tahun ajaran lain.
     */
    private function copyReportTemplates($sourceTahunAjaran, $newTahunAjaran, $newSemester = null)
    {
        $sourceTemplates = ReportTemplate::where('tahun_ajaran_id', $sourceTahunAjaran->id)->get();
        
        // Ambil mapping kelas lama ke kelas baru jika perlu
        $kelasMapping = [];
        $oldKelasIds = Kelas::where('tahun_ajaran_id', $sourceTahunAjaran->id)->pluck('id')->toArray();
        $newKelasIds = Kelas::where('tahun_ajaran_id', $newTahunAjaran->id)->pluck('id')->toArray();
        
        // Jika jumlah kelas sama, asumsikan mereka berkorespondensi 1-1
        if (count($oldKelasIds) === count($newKelasIds)) {
            $kelasMapping = array_combine($oldKelasIds, $newKelasIds);
        }
        
        foreach ($sourceTemplates as $template) {
            // Salin file template
            $newPath = str_replace(
                basename($template->path),
                'copy_' . $newTahunAjaran->tahun_ajaran . '_' . basename($template->path),
                $template->path
            );
            
            \Storage::copy('public/' . $template->path, 'public/' . $newPath);
            
            $newTemplate = $template->replicate();
            $newTemplate->tahun_ajaran_id = $newTahunAjaran->id;
            $newTemplate->tahun_ajaran_text = $newTahunAjaran->tahun_ajaran;
            $newTemplate->path = $newPath;
            $newTemplate->is_active = false; // Default tidak aktif
            
            // Set semester baru jika disediakan dan kolom semester ada
            if ($newSemester !== null && Schema::hasColumn('report_templates', 'semester')) {
                $newTemplate->semester = $newSemester;
            }
            
            // Jika ada mapping kelas, gunakan kelas baru
            if ($template->kelas_id && isset($kelasMapping[$template->kelas_id])) {
                $newTemplate->kelas_id = $kelasMapping[$template->kelas_id];
            }
            
            $newTemplate->save();
            
            // Copy mappings jika ada
            foreach ($template->mappings as $mapping) {
                $newMapping = $mapping->replicate();
                $newMapping->report_template_id = $newTemplate->id;
                $newMapping->save();
            }
        }
    }

    public function setSessionTahunAjaran($id)
    {
        try {
            $tahunAjaran = TahunAjaran::findOrFail($id);
            
            // Set session untuk digunakan di seluruh aplikasi
            session(['tahun_ajaran_id' => $id]);
            
            return redirect()->back()->with('success', 'Tampilan data diubah ke tahun ajaran ' . $tahunAjaran->tahun_ajaran);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $tahunAjaran = TahunAjaran::withTrashed()->findOrFail($id);
            
            if (!$tahunAjaran->trashed()) {
                return redirect()->back()
                    ->with('error', 'Tahun ajaran ini tidak dalam status diarsipkan.');
            }
            
            $tahunAjaran->restore();
            
            return redirect()->route('tahun.ajaran.index', ['showArchived' => true])
                ->with('success', 'Tahun ajaran ' . $tahunAjaran->tahun_ajaran . ' berhasil dipulihkan!');
                
        } catch (\Exception $e) {
            \Log::error('Error saat memulihkan tahun ajaran: ' . $e->getMessage(), [
                'tahun_ajaran_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memulihkan tahun ajaran: ' . $e->getMessage());
        }
    }
}