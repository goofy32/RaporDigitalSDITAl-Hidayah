<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajarans';

    protected $fillable = [
        'nama_pelajaran',
        'kelas_id',
        'semester',
        'guru_id',
        'lingkup_materi',
    ];

    protected $casts = [
        'lingkup_materi' => 'array',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }
}