<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Guru extends Authenticatable
{
    use HasFactory;

    protected $table = 'gurus';

    protected $fillable = [
        'nuptk',
        'nama',
        'jenis_kelamin',
        'tanggal_lahir',
        'no_handphone',
        'email',
        'alamat',
        'jabatan',
        'kelas_pengajar_id',
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function kelasPengajar()
    {
        return $this->belongsTo(Kelas::class, 'kelas_pengajar_id');
    }

    public function mataPelajarans()
    {
        return $this->hasMany(MataPelajaran::class, 'guru_id');
    }
}

