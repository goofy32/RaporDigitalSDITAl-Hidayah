<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportTemplate extends Model
{
    protected $fillable = [
        'filename',
        'path',
        'type',
        'is_active',
        'tahun_ajaran',
        'semester'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'semester' => 'integer'
    ];

    // Accessor untuk mendapatkan label semester
    public function getSemesterLabelAttribute()
    {
        return $this->semester == 1 ? 'Ganjil' : 'Genap';
    }

    public function mappings()
    {
        return $this->hasMany(ReportMapping::class);
    }

    public function generations()
    {
        return $this->hasMany(ReportGeneration::class);
    }
}