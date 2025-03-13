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
        'guru_id',
        'semester',
        'is_muatan_lokal',
        'allow_non_wali',
    ];
    
    protected $casts = [
        'is_muatan_lokal' => 'boolean',
        'allow_non_wali' => 'boolean', 
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
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
}