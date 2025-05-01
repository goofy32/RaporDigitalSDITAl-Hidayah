<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SemesterSnapshot extends Model
{
    protected $fillable = [
        'tahun_ajaran_id',
        'semester',
        'snapshot_date',
        'data'
    ];

    protected $casts = [
        'snapshot_date' => 'datetime',
        'data' => 'array',
    ];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}