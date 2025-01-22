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
}