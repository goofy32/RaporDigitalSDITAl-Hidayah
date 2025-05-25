<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTahunAjaran;

class CatatanMataPelajaran extends Model
{
    use HasTahunAjaran;
    
    protected $table = 'catatan_mata_pelajaran';
    
    protected $fillable = [
        'mata_pelajaran_id',
        'siswa_id',
        'tahun_ajaran_id',
        'semester',
        'type', // 'umum', 'uts', 'uas'
        'catatan',
        'created_by'
    ];

    protected $casts = [
        'semester' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function creator()
    {
        return $this->belongsTo(Guru::class, 'created_by');
    }

    /**
     * Scope untuk filter berdasarkan type catatan
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope untuk filter berdasarkan siswa dan mata pelajaran
     */
    public function scopeForSiswaMapel($query, $siswaId, $mataPelajaranId)
    {
        return $query->where('siswa_id', $siswaId)
                    ->where('mata_pelajaran_id', $mataPelajaranId);
    }
}