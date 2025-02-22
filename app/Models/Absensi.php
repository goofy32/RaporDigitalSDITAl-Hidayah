<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $fillable = [
        'siswa_id',
        'sakit',
        'izin',
        'tanpa_keterangan',
        'semester', 
    ];
    

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}