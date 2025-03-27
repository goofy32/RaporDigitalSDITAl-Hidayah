<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTahunAjaran;

class Absensi extends Model
{
    use HasTahunAjaran;
    
    protected $fillable = [
        'siswa_id',
        'sakit',
        'izin',
        'tanpa_keterangan',
        'semester',
        'tahun_ajaran_id' // Tambahkan ini
    ];
    

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
    
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }
    
    public function scopeTahunAjaran($query, $tahunAjaranId)
    {
        return $query->where('tahun_ajaran_id', $tahunAjaranId);
    }
    
    public function scopeAktif($query)
    {
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        if ($tahunAjaranAktif) {
            return $query->where('tahun_ajaran_id', $tahunAjaranAktif->id);
        }
        return $query;
    }
}