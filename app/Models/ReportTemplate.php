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
        'semester',
        'kelas_id' // Tambahkan kelas_id
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'semester' => 'integer'
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    // Get template aktif berdasarkan kelas dan tipe
    public static function getActiveTemplate($type, $kelasId)
    {
        // Cari template spesifik untuk kelas
        $template = self::where('type', $type)
                       ->where('kelas_id', $kelasId)
                       ->where('is_active', true)
                       ->first();
        
        return $template;
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
    

    public function mappings()
    {
        return $this->hasMany(ReportMapping::class);
    }

    public function generations()
    {
        return $this->hasMany(ReportGeneration::class);
    }
}