<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('catatan_mata_pelajarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_pelajaran_id')->constrained()->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->text('catatan');
            $table->foreignId('tahun_ajaran_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Unique constraint untuk mencegah duplikasi
            $table->unique(['mata_pelajaran_id', 'siswa_id', 'tahun_ajaran_id'], 'unique_catatan_mapel');
            
            // Index untuk performa query
            $table->index(['siswa_id', 'tahun_ajaran_id']);
            $table->index(['mata_pelajaran_id', 'tahun_ajaran_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('catatan_mata_pelajarans');
    }
};