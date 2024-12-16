<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('format_rapors', function (Blueprint $table) {
            $table->json('subjects_data')->nullable();
            $table->json('extracurricular_data')->nullable();
            $table->json('attendance_data')->nullable();
            $table->text('teacher_note')->nullable();
            $table->string('student_name')->nullable();
            $table->string('student_id')->nullable();
            $table->string('class_name')->nullable();
            $table->string('academic_year')->nullable();
        });
    }

    public function down()
    {
        Schema::table('format_rapor', function (Blueprint $table) {
            $table->dropColumn([
                'subjects_data',
                'extracurricular_data',
                'attendance_data',
                'teacher_note',
                'student_name',
                'student_id',
                'class_name',
                'academic_year'
            ]);
        });
    }
};
