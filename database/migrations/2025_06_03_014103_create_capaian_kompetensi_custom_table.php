<?php
// database/migrations/2024_XX_XX_create_capaian_kompetensi_custom_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('capaian_kompetensi_custom', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('mata_pelajaran_id');
            $table->text('custom_capaian')->nullable(); // Kustomisasi capaian oleh wali kelas
            $table->unsignedBigInteger('tahun_ajaran_id');
            $table->tinyInteger('semester');
            $table->timestamps();
            
            $table->foreign('siswa_id')->references('id')->on('siswas')->onDelete('cascade');
            $table->foreign('mata_pelajaran_id')->references('id')->on('mata_pelajarans')->onDelete('cascade');
            $table->foreign('tahun_ajaran_id')->references('id')->on('tahun_ajarans')->onDelete('cascade');
            
            $table->unique(['siswa_id', 'mata_pelajaran_id', 'tahun_ajaran_id', 'semester'], 'unique_capaian_custom');
        });
    }

    public function down()
    {
        Schema::dropIfExists('capaian_kompetensi_custom');
    }
};