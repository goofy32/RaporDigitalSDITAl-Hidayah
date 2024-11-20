<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswas';

    protected $fillable = [
        'nis',
        'nisn',
        'nama',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'alamat',
        'kelas_id',
        'nama_ayah',
        'nama_ibu',
        'pekerjaan_ayah',
        'pekerjaan_ibu',
        'alamat_orangtua',
        'photo',
        'wali_siswa',
        'pekerjaan_wali',
    ];
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function prestasi()
    {
        return $this->hasMany(Prestasi::class);
    }
}