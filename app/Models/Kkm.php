<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasTahunAjaran;

class Kkm extends Model
{
    use HasFactory, HasTahunAjaran;
    
    protected $table = 'kkms';
    
    protected $fillable = [
        'mata_pelajaran_id',
        'kelas_id',
        'nilai',
        'tahun_ajaran_id'
    ];
    
    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }
    
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
    
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}