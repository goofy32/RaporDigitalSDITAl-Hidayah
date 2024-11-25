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
    ];

    // Menambahkan eager loading default
    protected $with = ['lingkupMateris'];

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
            // Hapus nilai terkait
            $mataPelajaran->nilais()->delete();
            
            // Hapus Lingkup Materi terkait
            $mataPelajaran->lingkupMateris()->each(function ($lingkupMateri) {
                $lingkupMateri->delete();
            });
        });
    }
}