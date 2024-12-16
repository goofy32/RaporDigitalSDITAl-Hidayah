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
        Schema::create('format_rapor', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // UTS atau UAS
            $table->string('title');
            $table->string('template_path');
            $table->string('pdf_path');
            $table->json('placeholders')->nullable();
            $table->string('tahun_ajaran');
            $table->boolean('is_active')->default(false);
            // Data rapor
            $table->json('subjects_data')->nullable();
            $table->json('extracurricular_data')->nullable();
            $table->json('attendance_data')->nullable();
            $table->text('teacher_note')->nullable();
            $table->string('student_name')->nullable();
            $table->string('student_id')->nullable();
            $table->string('class_name')->nullable();
            $table->string('academic_year')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('format_rapor');
    }
};
