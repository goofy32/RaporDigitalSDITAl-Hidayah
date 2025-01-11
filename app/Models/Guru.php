<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guru extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

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
        'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'tanggal_lahir' => 'date',
    ];
    
    public function getTanggalLahirAttribute($value)
    {
        return $value ? date('Y-m-d', strtotime($value)) : null;
    }
    
    public function kelasPengajar()
    {
        return $this->belongsTo(Kelas::class, 'kelas_pengajar_id');
    }

    public function mataPelajarans()
    {
        return $this->hasMany(MataPelajaran::class, 'guru_id');
    }

    public function nilais()
    {
        return $this->hasManyThrough(Nilai::class, MataPelajaran::class, 'guru_id', 'mata_pelajaran_id');
    }

    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'guru_kelas', 'guru_id', 'kelas_id');
    }
}