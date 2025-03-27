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

    // Relasi ke LingkupMateri
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
            'id', // foreign key on lingkup_materis
            'id', // foreign key on mata_pelajarans
            'lingkup_materi_id', // local key on tujuan_pembelajarans
            'mata_pelajaran_id' // local key on lingkup_materis
        );
    }

    public function getTahunAjaranIdAttribute()
    {
        return $this->lingkupMateri && $this->lingkupMateri->mataPelajaran 
            ? $this->lingkupMateri->mataPelajaran->tahun_ajaran_id 
            : null;
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

    // Event deleting untuk menghapus data terkait
    protected static function booted()
    {
        static::deleting(function ($tujuanPembelajaran) {
            // Hapus nilai terkait
            $tujuanPembelajaran->nilais()->delete();
        });
    }
}