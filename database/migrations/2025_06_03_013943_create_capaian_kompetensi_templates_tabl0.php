<?php
// database/migrations/2024_XX_XX_create_capaian_kompetensi_templates_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('capaian_templates', function (Blueprint $table) {
            $table->id();
            $table->string('mata_pelajaran'); // Nama mata pelajaran (PAI, Matematika, dll)
            $table->decimal('nilai_min', 5, 2); // Nilai minimum untuk range
            $table->decimal('nilai_max', 5, 2); // Nilai maksimum untuk range
            $table->text('template_text'); // Template kalimat capaian
            $table->unsignedBigInteger('tahun_ajaran_id');
            $table->timestamps();
            
            $table->foreign('tahun_ajaran_id')->references('id')->on('tahun_ajarans')->onDelete('cascade');
            $table->index(['mata_pelajaran', 'tahun_ajaran_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('capaian_kompetensi_templates');
    }
};