<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormatRapor extends Model
{
    use HasFactory;

    protected $table = 'format_rapor';

    protected $fillable = [
        'type',
        'title',
        'template_path',
        'pdf_path',
        'is_active',
        'placeholders',
        'tahun_ajaran',
        'subjects_data',
        'extracurricular_data',
        'attendance_data',
        'teacher_note',
        'student_name',
        'student_id',
        'class_name',
        'academic_year'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'placeholders' => 'array',
        'subjects_data' => 'array',
        'extracurricular_data' => 'array',
        'attendance_data' => 'array'
    ];

    public function getSubjectsDataAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function getExtracurricularDataAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function getAttendanceDataAttribute($value)
    {
        return json_decode($value, true) ?? [
            'sick' => 0,
            'permitted' => 0,
            'noPermission' => 0
        ];
    }
}