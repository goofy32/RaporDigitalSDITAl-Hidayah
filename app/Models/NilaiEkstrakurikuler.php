<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NilaiEkstrakurikuler extends Model
{
    protected $table = 'nilai_ekstrakurikuler';
    
    protected $fillable = [
        'siswa_id',
        'ekstrakurikuler_id',
        'predikat',
        'deskripsi',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }
}