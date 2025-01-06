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
    
    // Tambahkan accessor jika diperlukan
    public function getTanggalLahirAttribute($value)
    {
        return $value ? date('Y-m-d', strtotime($value)) : null;
    }
    
    public function kelasPengajar()
    {
        return $this->belongsTo(Kelas::class, 'kelas_pengajar_id');
    }
}