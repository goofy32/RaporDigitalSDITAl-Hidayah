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
    
    // Tambahkan accessor untuk nama kelas lengkap
    public function getFullKelasAttribute()
    {
        return "Kelas {$this->nomor_kelas} {$this->nama_kelas}";
    }

    // Tambahkan ke appends agar bisa diakses langsung
    protected $appends = ['full_kelas'];
    
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
    
    public function getWaliKelasNameAttribute()
    {
        return $this->waliKelas ? $this->waliKelas->nama : '-';
    }
    
    public function toArray()
    {
        $array = parent::toArray();
        $array['mata_pelajarans'] = $this->mataPelajarans;
        return $array;
    }
}