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
        Schema::create('profil_sekolah', function (Blueprint $table) {
            $table->id();
            $table->string('logo')->nullable();
            $table->string('nama_instansi');
            $table->string('nama_sekolah');
            $table->string('tahun_pelajaran');
            $table->integer('semester');
            $table->string('npsn');
            $table->string('kepala_sekolah');
            $table->text('alamat');
            $table->integer('guru_kelas');
            $table->string('kode_pos');
            $table->integer('kelas');
            $table->string('telepon');
            $table->integer('jumlah_siswa');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_sekolahs');
    }
};
