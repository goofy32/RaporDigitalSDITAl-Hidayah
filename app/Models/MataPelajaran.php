<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTahunAjaran;

class MataPelajaran extends Model
{
    use HasFactory, HasTahunAjaran;

    protected $table = 'mata_pelajarans';

    protected $fillable = [
        'nama_pelajaran',
        'kelas_id',
        'guru_id',
        'semester',
        'is_muatan_lokal',
        'allow_non_wali',
        'tahun_ajaran_id', // Tambahkan field ini
    ];
    
    protected $casts = [
        'is_muatan_lokal' => 'boolean',
        'allow_non_wali' => 'boolean',
        'guru_id' => 'integer'
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }
    
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function lingkupMateris()
    {
        return $this->hasMany(LingkupMateri::class, 'mata_pelajaran_id');
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class, 'mata_pelajaran_id');
    }

    protected static function booted()
    {
        static::deleting(function ($mataPelajaran) {
            $mataPelajaran->nilais()->delete();
            $mataPelajaran->lingkupMateris->each(function ($lingkupMateri) {
                $lingkupMateri->tujuanPembelajarans()->delete();
            });
            $mataPelajaran->lingkupMateris()->delete();
        });
    }
    
    public function catatanMataPelajaran()
    {
        return $this->hasMany(CatatanMataPelajaran::class);
    }

    // Get catatan for specific student and type
    public function getCatatanForSiswa($siswaId, $type = 'umum')
    {
        $tahunAjaranId = session('tahun_ajaran_id');
        $selectedSemester = session('selected_semester', 1);
        
        return $this->catatanMataPelajaran()
            ->where('siswa_id', $siswaId)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('semester', $selectedSemester)
            ->where('type', $type)
            ->first();
    }
    /**
     * Scope untuk filter berdasarkan tahun ajaran
     */
    public function scopeTahunAjaran($query, $tahunAjaranId)
    {
        return $query->where('tahun_ajaran_id', $tahunAjaranId);
    }
    
    /**
     * Scope untuk filter berdasarkan tahun ajaran aktif
     */
    public function scopeAktif($query)
    {
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        if ($tahunAjaranAktif) {
            return $query->where('tahun_ajaran_id', $tahunAjaranAktif->id);
        }
        return $query;
    }
}