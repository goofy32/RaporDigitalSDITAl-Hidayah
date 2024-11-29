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

    // Relasi yang sudah ada
    public function lingkupMateri()
    {
        return $this->belongsTo(LingkupMateri::class, 'lingkup_materi_id');
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class, 'tujuan_pembelajaran_id');
    }

    // Tambahkan relasi ke MataPelajaran melalui LingkupMateri
    public function mataPelajaran()
    {
        return $this->hasOneThrough(
            MataPelajaran::class,
            LingkupMateri::class,
            'id', // Foreign key di lingkup_materis
            'id', // Foreign key di mata_pelajarans
            'lingkup_materi_id', // Local key di tujuan_pembelajarans
            'mata_pelajaran_id' // Local key di lingkup_materis
        );
    }

    // Helper method untuk mengecek kepemilikan
    public function belongsToGuru($guruId)
    {
        return $this->lingkupMateri->mataPelajaran->guru_id == $guruId;
    }

    // Helper method untuk mendapatkan nomor urut TP dalam satu lingkup materi
    public function getOrderNumber()
    {
        return TujuanPembelajaran::where('lingkup_materi_id', $this->lingkup_materi_id)
            ->where('created_at', '<=', $this->created_at)
            ->count();
    }

    // Event deleting yang sudah ada
    protected static function booted()
    {
        static::deleting(function ($tujuanPembelajaran) {
            // Hapus nilai terkait
            $tujuanPembelajaran->nilais()->delete();
        });
    }
}