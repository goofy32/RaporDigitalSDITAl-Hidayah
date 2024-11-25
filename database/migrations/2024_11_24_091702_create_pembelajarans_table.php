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
        Schema::create('pembelajarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->onDelete('cascade');
            $table->foreignId('guru_id')->constrained('gurus')->onDelete('cascade');
            $table->string('tahun_ajaran');
            $table->enum('semester', ['ganjil', 'genap']);
            $table->timestamps();
        });

        // Tabel pivot untuk menghubungkan siswa dengan pembelajaran
        Schema::create('pembelajaran_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembelajaran_id')->constrained('pembelajarans')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pembelajaran_siswa');
        Schema::dropIfExists('pembelajarans');
    }
};
