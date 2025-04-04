<?php

namespace App\Traits;

trait HasTahunAjaran
{
    /**
     * Boot the trait
     */
    public static function bootHasTahunAjaran()
    {
        // Saat membuat model baru
        static::creating(function ($model) {
            // Otomatis isi tahun_ajaran_id saat create jika tidak ada
            if (!$model->tahun_ajaran_id && session('tahun_ajaran_id')) {
                $model->tahun_ajaran_id = session('tahun_ajaran_id');
            }
        });
        
        // Tambahkan hook untuk updating
        static::updating(function ($model) {
            // Jika tahun_ajaran_id dihapus atau diubah menjadi null, kembalikan ke nilai session
            if ($model->isDirty('tahun_ajaran_id') && $model->tahun_ajaran_id === null && session('tahun_ajaran_id')) {
                $model->tahun_ajaran_id = session('tahun_ajaran_id');
            }
        });
    }

    /**
     * Scope untuk filter berdasarkan tahun ajaran
     */
    public function scopeTahunAjaran($query, $tahunAjaranId = null)
    {
        $tahunAjaranId = $tahunAjaranId ?: session('tahun_ajaran_id');
        
        if ($tahunAjaranId) {
            return $query->where('tahun_ajaran_id', $tahunAjaranId);
        }
        
        return $query;
    }

    /**
     * Scope untuk filter berdasarkan tahun ajaran aktif saja
     */
    public function scopeAktif($query)
    {
        return $this->scopeTahunAjaran($query);
    }
    
    /**
     * Relasi dengan model TahunAjaran
     */
    public function tahunAjaran()
    {
        return $this->belongsTo(\App\Models\TahunAjaran::class, 'tahun_ajaran_id');
    }
}