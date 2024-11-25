<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelajaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'kelas_id',
        'mata_pelajaran_id',
        'guru_id',
        'tahun_ajaran',
        'semester'
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function siswas()
    {
        return $this->belongsToMany(Siswa::class, 'pembelajaran_siswa')
            ->withTimestamps();
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class);
    }
}