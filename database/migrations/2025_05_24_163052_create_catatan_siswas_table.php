<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('catatan_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->text('catatan');
            $table->integer('semester');
            $table->foreignId('tahun_ajaran_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Unique constraint untuk mencegah duplikasi
            $table->unique(['siswa_id', 'semester', 'tahun_ajaran_id'], 'unique_catatan_siswa');
            
            // Index untuk performa query
            $table->index(['siswa_id', 'tahun_ajaran_id']);
            $table->index(['semester', 'tahun_ajaran_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('catatan_siswas');
    }
};