<?php
// app/Models/CapaianKompetensiTemplate.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTahunAjaran;

class CapaianKompetensiTemplate extends Model
{
    use HasTahunAjaran;
    
    protected $table = 'capaian_templates';
    
    protected $fillable = [
        'mata_pelajaran',
        'nilai_min',
        'nilai_max',
        'template_text',
        'tahun_ajaran_id'
    ];

    protected $casts = [
        'nilai_min' => 'decimal:2',
        'nilai_max' => 'decimal:2',
    ];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    /**
     * Mendapatkan template capaian berdasarkan mata pelajaran dan nilai
     */
    public static function getTemplateByNilai($mataPelajaran, $nilai, $tahunAjaranId = null)
    {
        $tahunAjaranId = $tahunAjaranId ?: session('tahun_ajaran_id');
        
        return self::where('mata_pelajaran', $mataPelajaran)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('nilai_min', '<=', $nilai)
            ->where('nilai_max', '>=', $nilai)
            ->first();
    }

    /**
     * Generate capaian text berdasarkan template dan nama siswa
     */
    public function generateCapaianText($namaSiswa)
    {
        return str_replace('{nama_siswa}', $namaSiswa, $this->template_text);
    }

    /**
     * Scope untuk filter berdasarkan mata pelajaran
     */
    public function scopeByMataPelajaran($query, $mataPelajaran)
    {
        return $query->where('mata_pelajaran', $mataPelajaran);
    }

    /**
     * Scope untuk order berdasarkan nilai minimum
     */
    public function scopeOrderByNilai($query)
    {
        return $query->orderBy('nilai_min', 'desc');
    }
}