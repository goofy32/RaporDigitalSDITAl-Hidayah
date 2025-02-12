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
        'is_active' => 'boolean'
    ];

    public function mappings()
    {
        return $this->hasMany(ReportMapping::class);
    }

    public function generations()
    {
        return $this->hasMany(ReportGeneration::class);
    }
}