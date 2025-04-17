<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TahunAjaran extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajarans';

    protected $fillable = [
        'tahun_ajaran', // Format: "2024/2025"
        'is_active',
        'tanggal_mulai',
        'tanggal_selesai',
        'semester', // 1: Ganjil, 2: Genap
        'deskripsi'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'semester' => 'integer'
    ];

    // Boot method untuk setup model events
    protected static function boot()
    {
        parent::boot();

        // Event ketika model diupdate
        static::updated(function ($tahunAjaran) {
            // Jika semester berubah dan tahun ajaran ini aktif
            if ($tahunAjaran->is_active && $tahunAjaran->isDirty('semester')) {
                $oldSemester = $tahunAjaran->getOriginal('semester');
                $newSemester = $tahunAjaran->semester;
                static::updateRelatedData($tahunAjaran->id, $newSemester, $oldSemester);
            }

            // Jika status aktif berubah menjadi true
            if ($tahunAjaran->is_active && $tahunAjaran->isDirty('is_active')) {
                // Update data profil sekolah
                $profil = ProfilSekolah::first();
                if ($profil) {
                    $profil->update([
                        'tahun_pelajaran' => $tahunAjaran->tahun_ajaran,
                        'semester' => $tahunAjaran->semester
                    ]);
                }
            }
        });
    }

    /**
     * Update data terkait ketika semester berubah
     */
    protected static function updateRelatedData($tahunAjaranId, $newSemester, $oldSemester)
    {
        \Log::info("Memperbarui data terkait untuk tahun ajaran #{$tahunAjaranId} dari semester {$oldSemester} ke {$newSemester}");

        // Update absensi dengan semester baru
        DB::table('absensis')
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->update(['semester' => $newSemester]);
        
        // Update mata pelajaran dengan semester baru
        DB::table('mata_pelajarans')
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->update(['semester' => $newSemester]);
        
        // Update nilai-nilai dengan semester baru
        DB::table('nilais')
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->update(['semester' => $newSemester]);
        
        // Update template rapor dengan semester baru
        DB::table('report_templates')
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->update(['semester' => $newSemester]);
        
        // Tambahkan model lain yang memiliki semester dan tahun_ajaran_id jika ada
    }


    // Relasi dengan kelas (bisa ada banyak kelas dalam satu tahun ajaran)
    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }

    // Relasi dengan siswa (semua siswa dalam tahun ajaran ini)
    public function siswas()
    {
        return $this->hasManyThrough(Siswa::class, Kelas::class);
    }

    // Relasi dengan mata pelajaran
    public function mataPelajarans()
    {
        return $this->hasMany(MataPelajaran::class);
    }

    // Relasi dengan template rapor
    public function reportTemplates()
    {
        return $this->hasMany(ReportTemplate::class);
    }
    
    // Get tahun ajaran aktif
    public static function getActive()
    {
        return self::where('is_active', true)->first();
    }
    
    // Format label semester
    public function getSemesterLabelAttribute()
    {
        return $this->semester == 1 ? 'Ganjil' : 'Genap';
    }
    
    // Cek apakah tahun ajaran sedang aktif berdasarkan tanggal
    public function isCurrentlyActive()
    {
        $today = now();
        return $this->is_active && 
               $today->between($this->tanggal_mulai, $this->tanggal_selesai);
    }
}