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
    
    public function siswas()
    {
        return $this->hasMany(Siswa::class);
    }

    public function mataPelajarans()
    {
        return $this->hasMany(MataPelajaran::class, 'kelas_id');
    }
    
    // Tambahkan method untuk debugging
    public function toArray()
    {
        $array = parent::toArray();
        $array['mata_pelajarans'] = $this->mataPelajarans;
        return $array;
    }
}