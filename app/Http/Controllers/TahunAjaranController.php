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

class TahunAjaranController extends Controller
{
    /**
     * Display a listing of tahun ajaran.
     */
    public function index()
    {
        $tahunAjarans = TahunAjaran::orderBy('is_active', 'desc')
                                   ->orderBy('tanggal_mulai', 'desc')
                                   ->get();
        
        return view('admin.tahun_ajaran.index', compact('tahunAjarans'));
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
        TahunAjaran::create($request->all());

        return redirect()->route('tahun.ajaran.index')
                         ->with('success', 'Tahun ajaran berhasil dibuat!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $tahunAjaran = TahunAjaran::findOrFail($id);
        
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
        $tahunAjaran = TahunAjaran::findOrFail($id);
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
    
        $tahunAjaran = TahunAjaran::findOrFail($id);
        
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
            // Update tahun ajaran
            $tahunAjaran->update($request->all());
            
            // Jika ini adalah tahun ajaran aktif, perbarui profil sekolah
            if ($tahunAjaran->is_active) {
                $profil = ProfilSekolah::first();
                if ($profil) {
                    $profil->update([
                        'tahun_pelajaran' => $tahunAjaran->tahun_ajaran,
                        'semester' => $tahunAjaran->semester
                    ]);
                }
                
                // Jika semester berubah, update data terkait
                if ($oldSemester != $newSemester) {
                    $this->updateRelatedData($tahunAjaran->id, $newSemester);
                }
            }
            
            DB::commit();
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
     * Update data yang terkait dengan tahun ajaran saat semester berubah
     * 
     * @param int $tahunAjaranId
     * @param int $newSemester
     * @return void
     */
    private function updateRelatedData($tahunAjaranId, $newSemester)
    {
        try {
            // Update absensi dengan semester baru if column exists
            if (Schema::hasColumn('absensis', 'semester')) {
                DB::table('absensis')
                    ->where('tahun_ajaran_id', $tahunAjaranId)
                    ->update(['semester' => $newSemester]);
            }
            
            // Update mata pelajaran dengan semester baru if column exists
            if (Schema::hasColumn('mata_pelajarans', 'semester')) {
                DB::table('mata_pelajarans')
                    ->where('tahun_ajaran_id', $tahunAjaranId)
                    ->update(['semester' => $newSemester]);
            }
            
            // Update nilai-nilai dengan semester baru - skip this as the column doesn't exist
            // Update report templates dengan semester baru if column exists
            if (Schema::hasColumn('report_templates', 'semester')) {
                DB::table('report_templates')
                    ->where('tahun_ajaran_id', $tahunAjaranId)
                    ->update(['semester' => $newSemester]);
            }
            
            // Log perubahan untuk debugging
            \Log::info("Semester diperbarui untuk tahun ajaran #{$tahunAjaranId} ke semester {$newSemester}");
        } catch (\Exception $e) {
            \Log::error("Error updating related data: " . $e->getMessage());
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
            ProfilSekolah::syncWithTahunAjaran();
            
            // Set session
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
                    ->with('error', 'Tidak dapat menghapus tahun ajaran yang sedang aktif. Aktifkan tahun ajaran lain terlebih dahulu.');
            }
            
            // Cek apakah ini adalah satu-satunya tahun ajaran
            $totalTahunAjaran = TahunAjaran::count();
            if ($totalTahunAjaran <= 1) {
                return redirect()->back()
                    ->with('error', 'Tidak dapat menghapus tahun ajaran karena minimal harus ada satu tahun ajaran dalam sistem.');
            }
            
            // Jika aman, hapus tahun ajaran
            $tahunAjaran->delete();
            
            return redirect()->route('tahun.ajaran.index')
                ->with('success', 'Tahun ajaran berhasil dihapus.');
                
        } catch (\Exception $e) {
            \Log::error('Error saat menghapus tahun ajaran: ' . $e->getMessage(), [
                'tahun_ajaran_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus tahun ajaran: ' . $e->getMessage());
        }
    }

    /**
     * Generate tahun ajaran baru berdasarkan tahun ajaran yang sudah ada.
     */
    public function copy($id)
    {
        $sourceTahunAjaran = TahunAjaran::findOrFail($id);
        
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
            'copy_mata_pelajaran' => 'boolean',
            'copy_templates' => 'boolean',
            'is_active' => 'boolean'
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }
    
        DB::beginTransaction();
        
        try {
            $sourceTahunAjaran = TahunAjaran::findOrFail($id);
            
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
            if ($request->copy_kelas) {
                $this->copyKelas($sourceTahunAjaran, $newTahunAjaran);
            }
    
            // Copy mata pelajaran jika diminta
            if ($request->copy_mata_pelajaran) {
                $this->copyMataPelajaran($sourceTahunAjaran, $newTahunAjaran, $request->semester);
            }
    
            // Copy template rapor jika diminta
            if ($request->copy_templates) {
                $this->copyReportTemplates($sourceTahunAjaran, $newTahunAjaran, $request->semester);
            }
            
            // Update profil sekolah jika tahun ajaran baru diaktifkan
            if ($newTahunAjaran->is_active) {
                $profil = ProfilSekolah::first();
                if ($profil) {
                    $profil->update([
                        'tahun_pelajaran' => $newTahunAjaran->tahun_ajaran,
                        'semester' => $newTahunAjaran->semester
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('tahun.ajaran.index')
                             ->with('success', 'Tahun ajaran baru berhasil dibuat dan data telah disalin!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menyalin tahun ajaran: ' . $e->getMessage());
        }
    }

    /**
     * Helper method untuk copy kelas dari satu tahun ajaran ke tahun ajaran lain.
     */
    private function copyKelas($sourceTahunAjaran, $newTahunAjaran)
    {
        $sourceKelas = Kelas::where('tahun_ajaran_id', $sourceTahunAjaran->id)->get();
        
        foreach ($sourceKelas as $kelas) {
            $newKelas = $kelas->replicate();
            $newKelas->tahun_ajaran_id = $newTahunAjaran->id;
            $newKelas->save();
            
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

}