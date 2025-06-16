<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTahunAjaran;

class CapaianKompetensiRangeTemplate extends Model
{
    use HasTahunAjaran;
    
    protected $table = 'capaian_range';
    
    protected $fillable = [
        'nama_range',
        'nilai_min',
        'nilai_max',
        'template_text',
        'color_class',
        'urutan',
        'is_active',
        'tahun_ajaran_id'
    ];

    protected $casts = [
        'nilai_min' => 'integer',
        'nilai_max' => 'integer',
        'urutan' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    /**
     * Get template berdasarkan nilai
     */
    public static function getTemplateByNilai($nilai, $tahunAjaranId = null)
    {
        $tahunAjaranId = $tahunAjaranId ?: session('tahun_ajaran_id');
        
        return self::where('tahun_ajaran_id', $tahunAjaranId)
            ->where('is_active', true)
            ->where('nilai_min', '<=', $nilai)
            ->where('nilai_max', '>=', $nilai)
            ->orderBy('urutan')
            ->first();
    }

    /**
     * Generate text capaian berdasarkan template
     */
    public function generateCapaianText($namaSiswa, $namaMataPelajaran)
    {
        $text = str_replace(['{nama_siswa}', '{mata_pelajaran}'], [$namaSiswa, $namaMataPelajaran], $this->template_text);
        return $text;
    }

    /**
     * Scope untuk filter berdasarkan tahun ajaran aktif
     */
    public function scopeAktif($query)
    {
        $tahunAjaranId = session('tahun_ajaran_id');
        return $query->where('tahun_ajaran_id', $tahunAjaranId)
                    ->where('is_active', true);
    }

    /**
     * Scope untuk ordering berdasarkan urutan
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan')->orderBy('nilai_min', 'desc');
    }

    /**
     * Create default templates untuk tahun ajaran baru
     */
    public static function createDefaultTemplates($tahunAjaranId)
    {
        $defaultTemplates = [
            [
                'nama_range' => 'Sangat Baik',
                'nilai_min' => 88,
                'nilai_max' => 100,
                'template_text' => 'Menunjukkan penguasaan yang sangat baik dalam {mata_pelajaran}. {nama_siswa} mampu memahami konsep, menerapkan, dan menganalisis dengan sangat baik.',
                'color_class' => 'text-green-600',
                'urutan' => 1,
            ],
            [
                'nama_range' => 'Baik',
                'nilai_min' => 74,
                'nilai_max' => 87,
                'template_text' => 'Menunjukkan penguasaan yang baik dalam {mata_pelajaran}. {nama_siswa} mampu memahami konsep dan menerapkannya dengan baik.',
                'color_class' => 'text-green-400',
                'urutan' => 2,
            ],
            [
                'nama_range' => 'Cukup',
                'nilai_min' => 60,
                'nilai_max' => 73,
                'template_text' => 'Menunjukkan penguasaan yang cukup dalam {mata_pelajaran}. {nama_siswa} sudah mampu memahami konsep dasar dengan baik.',
                'color_class' => 'text-yellow-600',
                'urutan' => 3,
            ],
            [
                'nama_range' => 'Perlu Bimbingan',
                'nilai_min' => 0,
                'nilai_max' => 59,
                'template_text' => 'Perlu bimbingan dalam {mata_pelajaran}. {nama_siswa} disarankan untuk mengulang pembelajaran materi dasar dengan bimbingan guru.',
                'color_class' => 'text-red-600',
                'urutan' => 4,
            ],
        ];

        foreach ($defaultTemplates as $template) {
            self::create(array_merge($template, ['tahun_ajaran_id' => $tahunAjaranId]));
        }
    }
}