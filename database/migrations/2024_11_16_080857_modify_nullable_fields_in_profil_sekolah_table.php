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
        Schema::table('profil_sekolah', function (Blueprint $table) {
            $table->integer('kelas')->nullable()->change();
            $table->integer('guru_kelas')->nullable()->change();
            $table->integer('jumlah_siswa')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('profil_sekolah', function (Blueprint $table) {
            $table->integer('kelas')->nullable(false)->change();
            $table->integer('guru_kelas')->nullable(false)->change();
            $table->integer('jumlah_siswa')->nullable(false)->change();
        });
    }
};
