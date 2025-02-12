<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportPlaceholder extends Model
{
    use HasFactory;

    protected $fillable = [
        'placeholder_key',
        'description',
        'category',
        'sample_value',
        'is_required'
    ];

    protected $casts = [
        'is_required' => 'boolean'
    ];
}