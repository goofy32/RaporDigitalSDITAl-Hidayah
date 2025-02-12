<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportMapping extends Model
{
    protected $fillable = [
        'report_template_id',
        'placeholder_key',
        'data_source',
        'description'
    ];

    public function template()
    {
        return $this->belongsTo(ReportTemplate::class, 'report_template_id');
    }
}