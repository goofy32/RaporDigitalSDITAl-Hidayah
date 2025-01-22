<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'nomor_kelas',
        'nama_kelas',
        'wali_kelas',
    ];
    
    protected $casts = [
        'nomor_kelas' => 'integer'
    ];
    
    public function siswas()
    {
        return $this->hasMany(Siswa::class);
    }

    public function mataPelajarans()
    {
        return $this->hasMany(MataPelajaran::class, 'kelas_id');
    }

    public function waliKelas()
    {
        return $this->belongsTo(Guru::class, 'wali_kelas', 'id');
    }
    
    // Tambahkan accessor untuk mendapatkan nama wali kelas
    public function getWaliKelasNameAttribute()
    {
        return $this->waliKelas ? $this->waliKelas->nama : '-';
    }
    
    
    // Tambahkan method untuk debugging
    public function toArray()
    {
        $array = parent::toArray();
        $array['mata_pelajarans'] = $this->mataPelajarans;
        return $array;
    }
}