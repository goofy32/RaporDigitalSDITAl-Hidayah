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
            // Log creating event
            \Log::info("HasTahunAjaran: creating {$model->getTable()} record", [
                'model_data' => $model->getAttributes(),
                'has_tahun_ajaran_id' => isset($model->tahun_ajaran_id),
                'tahun_ajaran_id_value' => $model->tahun_ajaran_id ?? null,
                'session_tahun_ajaran' => session('tahun_ajaran_id')
            ]);
            
            // Otomatis isi tahun_ajaran_id saat create jika tidak ada
            if (!$model->tahun_ajaran_id && session('tahun_ajaran_id')) {
                $model->tahun_ajaran_id = session('tahun_ajaran_id');
                \Log::info("HasTahunAjaran: auto-filling tahun_ajaran_id", [
                    'model' => get_class($model),
                    'tahun_ajaran_id' => $model->tahun_ajaran_id
                ]);
            }
        });
        
        // Tambahkan hook untuk updating
        static::updating(function ($model) {
            // Log updating event
            \Log::info("HasTahunAjaran: updating {$model->getTable()} record", [
                'model_id' => $model->id,
                'dirty_attributes' => $model->getDirty(),
                'has_tahun_ajaran_id' => isset($model->tahun_ajaran_id),
                'tahun_ajaran_id_value' => $model->tahun_ajaran_id,
                'is_dirty_tahun_ajaran' => $model->isDirty('tahun_ajaran_id'),
                'session_tahun_ajaran' => session('tahun_ajaran_id')
            ]);
            
            // Jika tahun_ajaran_id dihapus atau diubah menjadi null, kembalikan ke nilai session
            if ($model->isDirty('tahun_ajaran_id') && $model->tahun_ajaran_id === null && session('tahun_ajaran_id')) {
                $model->tahun_ajaran_id = session('tahun_ajaran_id');
                \Log::info("HasTahunAjaran: restored tahun_ajaran_id during update", [
                    'model' => get_class($model),
                    'tahun_ajaran_id' => $model->tahun_ajaran_id
                ]);
            }
        });
        
        static::created(function ($model) {
            // Log after creation
            \Log::info("HasTahunAjaran: created {$model->getTable()} record", [
                'model_id' => $model->id,
                'final_tahun_ajaran_id' => $model->tahun_ajaran_id
            ]);
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