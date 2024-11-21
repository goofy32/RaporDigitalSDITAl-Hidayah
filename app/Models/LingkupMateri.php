<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LingkupMateri extends Model
{
    use HasFactory;

    protected $fillable = [
        'mata_pelajaran_id',
        'judul_lingkup_materi',
    ];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function tujuanPembelajarans(): HasMany
    {
        return $this->hasMany(TujuanPembelajaran::class, 'lingkup_materi_id');
    }

    protected static function booted()
    {
        static::deleting(function ($lingkupMateri) {
            // Hapus Tujuan Pembelajaran terkait
            $lingkupMateri->tujuanPembelajarans()->delete();
        });
    }
}
