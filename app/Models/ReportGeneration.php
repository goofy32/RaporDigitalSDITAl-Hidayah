<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportGeneration extends Model
{
    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'report_template_id',
        'generated_file',
        'type',
        'tahun_ajaran',
        'semester',
        'generated_at',
        'generated_by'
    ];

    protected $casts = [
        'generated_at' => 'datetime'
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function template()
    {
        return $this->belongsTo(ReportTemplate::class, 'report_template_id');
    }

    public function generator()
    {
        return $this->belongsTo(Guru::class, 'generated_by');
    }
}