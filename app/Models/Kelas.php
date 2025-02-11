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
    ];

    protected $appends = ['full_kelas'];
    
    protected $casts = [
        'nomor_kelas' => 'integer'
    ];

    // Relasi dengan guru
    public function guru()
    {
        return $this->belongsToMany(Guru::class, 'guru_kelas')
            ->withPivot('is_wali_kelas', 'role')
            ->withTimestamps();
    }
    // Wali kelas
    public function waliKelas()
    {
        return $this->belongsToMany(Guru::class, 'guru_kelas')
            ->where('guru_kelas.is_wali_kelas', true)
            ->withPivot('role');
    }

    
    
    // Guru pengajar
    public function guruPengajar()
    {
        return $this->belongsToMany(Guru::class, 'guru_kelas')
            ->wherePivot('role', 'pengajar')
            ->withTimestamps();
    }

    public function getWaliKelas()
    {
        // Ambil data wali kelas untuk kelas ini
        return $this->belongsToMany(Guru::class, 'guru_kelas')
            ->where('guru_kelas.is_wali_kelas', true)
            ->where('guru_kelas.role', 'wali_kelas')
            ->first();
    }

    // Get wali kelas name
    public function getWaliKelasNameAttribute()
    {
        $waliKelas = $this->waliKelas()->first();
        return $waliKelas ? $waliKelas->nama : '-';
    }

    // Get full kelas name
    public function getFullKelasAttribute()
    {
        return "Kelas {$this->nomor_kelas} {$this->nama_kelas}";
    }
    
    public function siswas()
    {
        return $this->hasMany(Siswa::class);
    }

    public function mataPelajarans()
    {
        return $this->hasMany(MataPelajaran::class, 'kelas_id');
    }
    
    
    public function toArray()
    {
        $array = parent::toArray();
        $array['mata_pelajarans'] = $this->mataPelajarans;
        return $array;
    }

    public function hasWaliKelas()
    {
        // Periksa apakah kelas sudah memiliki wali kelas
        return $this->belongsToMany(Guru::class, 'guru_kelas')
            ->where('guru_kelas.is_wali_kelas', true)
            ->where('guru_kelas.role', 'wali_kelas')
            ->exists();
    }
}