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
        return $this->belongsTo(Kelas::class)->orderBy('nomor_kelas', 'asc');        
    }
    

    public function prestasi()
    {
        return $this->hasMany(Prestasi::class);
    }
    public function nilais()
    {
        return $this->hasMany(Nilai::class);
    }

    public function nilaiEkstrakurikuler()
    {
        return $this->hasMany(NilaiEkstrakurikuler::class);
    }

    public function absensi()
    {
        return $this->hasOne(Absensi::class);
    }

    public function getKelasLengkapAttribute()
    {
        return $this->kelas ? $this->kelas->full_kelas : '-';
    }

    // Tambahkan scope untuk filter berdasarkan wali kelas
    public function scopeWaliKelas($query, $guruId)
    {
        return $query->whereHas('kelas', function($q) use ($guruId) {
            $q->where('wali_kelas', $guruId);
        });
    }

    // Tambahkan method untuk mengecek apakah siswa ada di kelas yang diwalikan
    public function isInKelasWali($guruId)
    {
        return $this->kelas && $this->kelas->wali_kelas == $guruId;
    }

    public function hasCompleteData($type = 'UTS')
    {
        $semester = $type === 'UTS' ? 1 : 2;
        
        // Cek nilai berdasarkan semester
        $hasNilai = $this->nilais()
            ->whereHas('mataPelajaran', function($q) use ($semester) {
                $q->where('semester', $semester);
            })
            ->where('nilai_akhir_rapor', '!=', null)
            ->exists();
        
        // Cek kehadiran semester yang sesuai
        $hasAbsensi = $this->absensi()
            ->where('semester', $semester)
            ->exists();
        
        return $hasNilai && $hasAbsensi;
    }
    
}