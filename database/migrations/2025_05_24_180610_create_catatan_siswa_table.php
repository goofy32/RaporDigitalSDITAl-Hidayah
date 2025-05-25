<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('catatan_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->text('catatan');
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->onDelete('cascade');
            $table->integer('semester');
            $table->string('type')->default('umum'); // umum, uts, uas
            $table->foreignId('created_by')->constrained('gurus')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['siswa_id', 'tahun_ajaran_id', 'semester', 'type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('catatan_siswa');
    }
};

// database/migrations/2024_create_catatan_mata_pelajaran_table.php
class CreateCatatanMataPelajaranTable extends Migration
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
            
            $table->index(['mata_pelajaran_id', 'siswa_id', 'tahun_ajaran_id', 'semester', 'type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('catatan_mata_pelajaran');
    }
}