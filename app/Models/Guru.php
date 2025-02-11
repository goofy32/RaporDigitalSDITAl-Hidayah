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
        'username',
        'password',
        'password_plain',
        'photo',
    ];

    // Relasi dengan kelas
    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'guru_kelas')
            ->withPivot('is_wali_kelas', 'role')
            ->withTimestamps();
    }
    // Kelas yang diwali
    public function kelasWali()
    {
        return $this->belongsToMany(Kelas::class, 'guru_kelas')
            ->wherePivot('is_wali_kelas', true)
            ->where('role', 'wali_kelas')  // Tambahkan ini
            ->withTimestamps();
    }
    // Kelas yang diajar
    public function kelasAjar()
    {
        return $this->belongsToMany(Kelas::class, 'guru_kelas')
            ->wherePivot('role', 'pengajar')
            ->withTimestamps();
    }

    // Check if guru is wali kelas
    public function isWaliKelas()
    {
        return $this->kelasWali()->exists();
    }

    // Get kelas wali if exists
    public function getKelasWaliAttribute()
    {
        return $this->kelasWali()->first();
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'tanggal_lahir' => 'date',
    ];

    /**
     * Format tanggal lahir
     */
    public function getTanggalLahirAttribute($value)
    {
        return $value ? date('Y-m-d', strtotime($value)) : null;
    }

    /**
     * Cek role berdasarkan session
     */
    public function hasRole($role)
    {
        return session('selected_role') === $role;
    }

    /**
     * Relasi dengan kelas sebagai pengajar
     */
    public function kelasPengajar()
    {
        return $this->belongsTo(Kelas::class, 'kelas_pengajar_id');
    }

    /**
     * Relasi dengan mata pelajaran yang diajar
     */
    public function mataPelajarans()
    {
        return $this->hasMany(MataPelajaran::class, 'guru_id');
    }

    /**
     * Relasi dengan nilai melalui mata pelajaran
     */
    public function nilais()
    {
        return $this->hasManyThrough(
            Nilai::class, 
            MataPelajaran::class,
            'guru_id',
            'mata_pelajaran_id'
        );
    }

    /**
     * Get kelas yang sedang diajar
     */
    public function getKelasYangDiajarAttribute()
    {
        return Kelas::whereHas('mataPelajarans', function($query) {
            $query->where('guru_id', $this->id);
        })->get();
    }

    /**
     * Get daftar siswa yang diajar
     */
    public function getSiswaYangDiajarAttribute()
    {
        return Siswa::whereIn('kelas_id', 
            $this->mataPelajarans()
                ->select('kelas_id')
                ->distinct()
                ->pluck('kelas_id')
        )->get();
    }

    /**
     * Get jumlah kelas yang diajar
     */
    public function getJumlahKelasAttribute()
    {
        return $this->mataPelajarans()
            ->select('kelas_id')
            ->distinct()
            ->count();
    }

    /**
     * Get jumlah mata pelajaran yang diajar
     */
    public function getJumlahMapelAttribute()
    {
        return $this->mataPelajarans()->count();
    }

    /**
     * Get jumlah siswa yang diajar
     */
    public function getJumlahSiswaAttribute()
    {
        return $this->getSiswaYangDiajarAttribute()->count();
    }

    /**
     * Scope untuk mencari guru berdasarkan kriteria
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nama', 'like', "%{$search}%")
              ->orWhere('nuptk', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Scope untuk filter berdasarkan status aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Get inisial nama untuk avatar
     */
    public function getInisialAttribute()
    {
        $nama = explode(' ', $this->nama);
        return strtoupper(substr($nama[0], 0, 1) . (isset($nama[1]) ? substr($nama[1], 0, 1) : ''));
    }

    /**
     * Get path photo profile
     */
    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }
}