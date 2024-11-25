<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TujuanPembelajaran extends Model
{
    use HasFactory;

    protected $table = 'tujuan_pembelajarans';

    protected $fillable = [
        'lingkup_materi_id',
        'kode_tp',
        'deskripsi_tp',
    ];

    public function lingkupMateri()
    {
        return $this->belongsTo(LingkupMateri::class, 'lingkup_materi_id');
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class, 'tujuan_pembelajaran_id');
    }

    protected static function booted()
    {
        static::deleting(function ($tujuanPembelajaran) {
            // Hapus nilai terkait
            $tujuanPembelajaran->nilais()->delete();
        });
    }
}