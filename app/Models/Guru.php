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
 
    /**
     * Kelas yang diwali
     */
    public function kelasWali()
    {
        return $this->belongsToMany(Kelas::class, 'guru_kelas')
            ->select('kelas.*') // Tambahkan ini untuk menghindari ambiguitas
            ->wherePivot('is_wali_kelas', true)
            ->wherePivot('role', 'wali_kelas');
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

    /**
     * Get kelas wali if exists
     */
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


   public function canTeachClass($kelasId)
   {
       // Jika guru ini adalah wali kelas dari kelas tersebut
       $waliKelasId = $this->getWaliKelasId();
       if ($waliKelasId && $waliKelasId == $kelasId) {
           return true;
       }
       
       // Jika guru ini ditugaskan sebagai pengajar di kelas tersebut
       return $this->kelasAjar()
           ->where('kelas.id', $kelasId)
           ->exists();
   }

    /**
     * Get all teachable classes for this guru
     */
    public function getTeachableClasses()
    {
        $guruId = $this->id;
        
        // Query untuk mendapatkan kelas yang bisa diajar
        $classesQuery = Kelas::query();
        
        // Jika guru ini adalah wali kelas, sertakan kelas wali
        if ($this->isWaliKelas()) {
            $kelasWali = $this->kelasWali()->first();
            
            // Ambil kelas yang diajar oleh guru (sebagai pengajar) atau kelas wali
            $classesQuery->where(function($query) use ($guruId, $kelasWali) {
                $query->whereHas('guru', function($q) use ($guruId) {
                    $q->where('guru_id', $guruId)
                    ->where('guru_kelas.role', 'pengajar');
                });
                
                // Jika punya kelas wali, tambahkan sebagai OR condition
                if ($kelasWali) {
                    $query->orWhere('id', $kelasWali->id);
                }
            });
        } else {
            // Jika bukan wali kelas, hanya ambil kelas yang diajar sebagai pengajar biasa
            $classesQuery->whereHas('guru', function($query) use ($guruId) {
                $query->where('guru_id', $guruId)
                    ->where('guru_kelas.role', 'pengajar');
            });
        }
        
        // Ambil hasil query dan urutkan
        return $classesQuery->orderBy('nomor_kelas')
            ->orderBy('nama_kelas')
            ->get();
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
     * Get kelas wali ID jika guru adalah wali kelas
     */
    public function getWaliKelasId()
    {
        $kelasWali = $this->kelasWali()->first();
        return $kelasWali ? $kelasWali->id : null;
    }

    /**
     * Get path photo profile
     */
    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }
}