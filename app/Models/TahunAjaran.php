<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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