<?php

namespace App\Observers;

use App\Models\TahunAjaran;
use App\Models\ProfilSekolah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TahunAjaranObserver
{
    /**
     * Handle the TahunAjaran "updated" event.
     */
    public function updated(TahunAjaran $tahunAjaran): void
    {
        // Jika semester berubah dan tahun ajaran ini aktif
        if ($tahunAjaran->is_active && $tahunAjaran->isDirty('semester')) {
            $oldSemester = $tahunAjaran->getOriginal('semester');
            $newSemester = $tahunAjaran->semester;
            
            Log::info("TahunAjaran semester changed from {$oldSemester} to {$newSemester}");
            
            $this->updateRelatedData($tahunAjaran->id, $newSemester);
        }

        // Jika status aktif berubah menjadi aktif, update profil sekolah
        if ($tahunAjaran->is_active && $tahunAjaran->isDirty('is_active') && $tahunAjaran->getOriginal('is_active') == false) {
            $this->updateProfilSekolah($tahunAjaran);
        }
    }

    /**
     * Handle the TahunAjaran "created" event.
     */
    public function created(TahunAjaran $tahunAjaran): void
    {
        // Jika tahun ajaran baru dibuat dengan status aktif
        if ($tahunAjaran->is_active) {
            $this->updateProfilSekolah($tahunAjaran);
        }
    }

    /**
     * Update profil sekolah dengan informasi tahun ajaran aktif
     */
    private function updateProfilSekolah(TahunAjaran $tahunAjaran): void
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
     */
    private function updateRelatedData($tahunAjaranId, $newSemester): void
    {
        // Gunakan transactions untuk memastikan semua update berhasil atau tidak sama sekali
        DB::beginTransaction();
        
        try {
            // Update absensi dengan semester baru (if column exists)
            $absensiCount = 0;
            if (Schema::hasColumn('absensis', 'semester')) {
                $absensiCount = DB::table('absensis')
                    ->where('tahun_ajaran_id', $tahunAjaranId)
                    ->update(['semester' => $newSemester]);
            }
            
            // Update mata pelajaran dengan semester baru (if column exists)
            $mapelCount = 0;
            if (Schema::hasColumn('mata_pelajarans', 'semester')) {
                $mapelCount = DB::table('mata_pelajarans')
                    ->where('tahun_ajaran_id', $tahunAjaranId)
                    ->update(['semester' => $newSemester]);
            }
            
            // Update nilai-nilai dengan semester baru - DO NOT UPDATE, NO COLUMN
            $nilaiCount = 0;
            
            // Update report templates dengan semester baru (if column exists)
            $templateCount = 0;
            if (Schema::hasColumn('report_templates', 'semester')) {
                $templateCount = DB::table('report_templates')
                    ->where('tahun_ajaran_id', $tahunAjaranId)
                    ->update(['semester' => $newSemester]);
            }
                
            // Jika ada tabel lain yang memiliki field semester dan tahun_ajaran_id,
            // tambahkan update di sini
            
            DB::commit();
            
            Log::info('Berhasil memperbarui data terkait semester', [
                'tahun_ajaran_id' => $tahunAjaranId,
                'new_semester' => $newSemester,
                'absensi_count' => $absensiCount,
                'mapel_count' => $mapelCount,
                'nilai_count' => $nilaiCount,
                'template_count' => $templateCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Gagal memperbarui data terkait semester', [
                'tahun_ajaran_id' => $tahunAjaranId,
                'new_semester' => $newSemester,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}