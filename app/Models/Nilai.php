<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    use HasFactory;

    protected $table = 'nilais';

    protected $fillable = [
        'id',  // Menambahkan 'id' ke dalam fillable
        'siswa_id',
        'mata_pelajaran_id',
        'tujuan_pembelajaran_id',
        'lingkup_materi_id',
        'nilai_tp',
        'nilai_lm',
        'nilai_akhir_semester',
        'na_tp',
        'na_lm',
        'tp_number',
        'nilai_tes',
        'nilai_non_tes',
        'nilai_akhir_rapor'
    ];

    protected $casts = [
        'nilai_tp' => 'float',
        'nilai_lm' => 'float',
        'nilai_akhir_semester' => 'float',
        'na_tp' => 'float',
        'na_lm' => 'float',
        'nilai_tes' => 'float',
        'nilai_non_tes' => 'float',
        'nilai_akhir_rapor' => 'float'
    ];

    public static $rules = [
        'siswa_id' => 'required|exists:siswas,id',
        'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
        'tujuan_pembelajaran_id' => 'nullable|exists:tujuan_pembelajarans,id',
        'lingkup_materi_id' => 'nullable|exists:lingkup_materis,id',
        'nilai_tp' => 'nullable|numeric|min:0|max:100',
        'nilai_lm' => 'nullable|numeric|min:0|max:100',
        'nilai_akhir_semester' => 'nullable|numeric|min:0|max:100',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function tujuanPembelajaran()
    {
        return $this->belongsTo(TujuanPembelajaran::class, 'tujuan_pembelajaran_id');
    }

    public function lingkupMateri()
    {
        return $this->belongsTo(LingkupMateri::class, 'lingkup_materi_id');
    }
}