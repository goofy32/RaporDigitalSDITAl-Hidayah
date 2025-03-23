<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportTemplate extends Model
{
    protected $fillable = [
        'filename',
        'path',
        'type', 
        'is_active',
        'tahun_ajaran',
        'tahun_ajaran_text',
        'semester',
        'kelas_id',
        'tahun_ajaran_id' // Tambahkan field ini
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'semester' => 'integer'
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
    
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    // Get template aktif berdasarkan kelas dan tipe
    public static function getActiveTemplate($type, $kelasId)
    {
        // Cari template spesifik untuk kelas di tahun ajaran aktif
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        
        $query = self::where('type', $type)
                    ->where('kelas_id', $kelasId)
                    ->where('is_active', true);
        
        if ($tahunAjaranAktif) {
            $query->where('tahun_ajaran_id', $tahunAjaranAktif->id);
        }
        
        return $query->first();
    }
    
    // Accessor untuk mendapatkan label semester
    public function getSemesterLabelAttribute()
    {
        return $this->semester == 1 ? 'Ganjil' : 'Genap';
    }

    public function getReportStatus($siswaId) 
    {
        $siswa = Siswa::find($siswaId);
        if (!$siswa) return 'not_found';
        
        $semester = $this->type === 'UTS' ? 1 : 2;
        
        // Cek nilai untuk semester yang sesuai
        $hasNilai = $siswa->nilais()
            ->whereHas('mataPelajaran', function($q) use ($semester) {
                $q->where('semester', $semester);
            })
            ->where('nilai_akhir_rapor', '!=', null)
            ->exists();
            
        if (!$hasNilai) return 'incomplete';
        
        // Cek absensi untuk semester yang sesuai
        $hasAbsensi = $siswa->absensi()
            ->where('semester', $semester)
            ->exists();
            
        if (!$hasAbsensi) return 'incomplete';
        
        return 'ready';
    }
    
    // Mendapatkan tahun ajaran text, baik dari relasi maupun dari kolom langsung
    public function getTahunAjaranTextAttribute($value)
    {
        if ($this->tahunAjaran) {
            return $this->tahunAjaran->tahun_ajaran;
        }
        
        return $value ?? $this->tahun_ajaran;
    }
    
    public function mappings()
    {
        return $this->hasMany(ReportMapping::class);
    }

    public function generations()
    {
        return $this->hasMany(ReportGeneration::class);
    }
    
    /**
     * Scope untuk filter berdasarkan tahun ajaran
     */
    public function scopeTahunAjaran($query, $tahunAjaranId)
    {
        return $query->where('tahun_ajaran_id', $tahunAjaranId);
    }
    
    /**
     * Scope untuk filter berdasarkan tahun ajaran aktif
     */
    public function scopeAktif($query)
    {
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        if ($tahunAjaranAktif) {
            return $query->where('tahun_ajaran_id', $tahunAjaranAktif->id);
        }
        return $query;
    }
}