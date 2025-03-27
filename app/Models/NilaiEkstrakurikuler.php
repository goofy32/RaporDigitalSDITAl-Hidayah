<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTahunAjaran;

class NilaiEkstrakurikuler extends Model
{
    use HasTahunAjaran;

    protected $table = 'nilai_ekstrakurikuler';
    
    protected $fillable = [
        'siswa_id',
        'ekstrakurikuler_id',
        'predikat',
        'deskripsi',
        'tahun_ajaran_id' // Tambahkan ini
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
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