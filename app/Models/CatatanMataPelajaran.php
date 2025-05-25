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
        'catatan',
        'tahun_ajaran_id',
        'semester',
        'type',
        'created_by'
    ];
    
    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }
    
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
    
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(Guru::class, 'created_by');
    }
    
    public function scopeForCurrentContext($query)
    {
        $tahunAjaranId = session('tahun_ajaran_id');
        $selectedSemester = session('selected_semester', 1);
        
        return $query->where('tahun_ajaran_id', $tahunAjaranId)
                    ->where('semester', $selectedSemester);
    }
    
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}