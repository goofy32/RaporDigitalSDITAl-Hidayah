<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LingkupMateri extends Model
{
    use HasFactory;

    protected $table = 'lingkup_materis';

    protected $fillable = [
        'mata_pelajaran_id',
        'judul_lingkup_materi',
    ];

    // Menambahkan eager loading default
    protected $with = ['tujuanPembelajarans'];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function tujuanPembelajarans()
    {
        return $this->hasMany(TujuanPembelajaran::class, 'lingkup_materi_id');
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class, 'lingkup_materi_id');
    }

    protected static function booted()
    {
        static::deleting(function ($lingkupMateri) {
            // Hapus nilai terkait
            $lingkupMateri->nilais()->delete();
            
            // Hapus Tujuan Pembelajaran terkait
            $lingkupMateri->tujuanPembelajarans()->delete();
        });
    }
}