<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('catatan_mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->text('catatan');
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->onDelete('cascade');
            $table->integer('semester');
            $table->string('type')->default('umum'); // umum, uts, uas
            $table->foreignId('created_by')->constrained('gurus')->onDelete('cascade');
            $table->timestamps();
            
            // Index dengan nama pendek
            $table->index(['mata_pelajaran_id', 'siswa_id', 'tahun_ajaran_id'], 'idx_catatan_mapel_main');
            $table->index(['semester', 'type'], 'idx_catatan_mapel_filter');
        });
    }

    public function down()
    {
        Schema::dropIfExists('catatan_mata_pelajaran');
    }
};
