<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    use HasFactory;
    
    protected $table = 'mata_pelajarans'; // Menentukan nama tabel

    protected $fillable = [
        'nama_pelajaran', // Sesuaikan dengan kolom di database
        'kelas_id',
        'semester',
        'guru_id',
        'lingkup_materi'
    ];

    protected $casts = [
        'lingkup_materi' => 'json'
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
    
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }
}