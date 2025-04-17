<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTahunAjaran;

class KkmSetting extends Model
{
    use HasFactory, HasTahunAjaran;

    protected $table = 'kkm_settings';

    protected $fillable = [
        'mata_pelajaran_id',
        'nilai_kkm',
        'bobot_tp',
        'bobot_lm',
        'bobot_as',
        'keterangan',
        'tahun_ajaran_id'
    ];

    protected $casts = [
        'nilai_kkm' => 'float',
        'bobot_tp' => 'float',
        'bobot_lm' => 'float',
        'bobot_as' => 'float'
    ];

    /**
     * Relasi dengan MataPelajaran
     */
    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    /**
     * Relasi dengan TahunAjaran
     */
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    /**
     * Get default KKM and weights if not set
     */
    public static function getDefault()
    {
        return [
            'nilai_kkm' => 70.00,
            'bobot_tp' => 1.00,
            'bobot_lm' => 1.00,
            'bobot_as' => 2.00
        ];
    }

    /**
     * Mendapatkan setting KKM untuk mata pelajaran tertentu
     * 
     * @param int $mataPelajaranId
     * @param int|null $tahunAjaranId
     * @return \App\Models\KkmSetting
     */
    public static function getForMataPelajaran($mataPelajaranId, $tahunAjaranId = null)
    {
        if ($tahunAjaranId === null) {
            $tahunAjaranId = session('tahun_ajaran_id');
        }
        
        return self::firstOrCreate(
            [
                'mata_pelajaran_id' => $mataPelajaranId,
                'tahun_ajaran_id' => $tahunAjaranId
            ],
            self::getDefault()
        );
    }

    /**
     * Menghitung nilai akhir rapor dengan bobot yang sudah ditentukan
     */
    public function hitungNilaiAkhir($naTP, $naLM, $nilaiAkhirSemester)
    {
        $totalBobot = $this->bobot_tp + $this->bobot_lm + $this->bobot_as;
        
        if ($totalBobot <= 0) {
            return 0;
        }
        
        return (
            ($naTP * $this->bobot_tp) +
            ($naLM * $this->bobot_lm) +
            ($nilaiAkhirSemester * $this->bobot_as)
        ) / $totalBobot;
    }

    /**
     * Cek apakah nilai memenuhi KKM
     */
    public function memenuhiKKM($nilai)
    {
        return $nilai >= $this->nilai_kkm;
    }
}