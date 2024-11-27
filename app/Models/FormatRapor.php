<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormatRapor extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'template_path',
        'pdf_path',      // Tambahkan ini
        'is_active',
        'placeholders',
        'tahun_ajaran'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'placeholders' => 'array'  // Ini akan otomatis handle JSON encode/decode
    ];
    
    // Helper method untuk mendapatkan placeholders yang aman
    public function getPlaceholdersAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }
        return $value ?? [];
    }
}