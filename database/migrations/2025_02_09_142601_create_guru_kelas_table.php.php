<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuruKelasTable extends Migration
{
    public function up()
    {
        // Hapus foreign key dan kolom di tabel gurus
        Schema::table('gurus', function (Blueprint $table) {
            if (Schema::hasColumn('gurus', 'kelas_pengajar_id')) {
                $table->dropForeign(['kelas_pengajar_id']);
                $table->dropColumn('kelas_pengajar_id');
            }
        });

        // Hapus kolom di tabel kelas
        Schema::table('kelas', function (Blueprint $table) {
            if (Schema::hasColumn('kelas', 'wali_kelas_id')) {
                $table->dropForeign(['wali_kelas_id']);
                $table->dropColumn('wali_kelas_id');
            }
        });

        // Buat tabel pivot baru
        Schema::create('guru_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained()->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained()->onDelete('cascade');
            $table->boolean('is_wali_kelas')->default(false);
            $table->string('role')->default('pengajar');
            $table->timestamps();
            
            // Satu guru hanya bisa jadi wali kelas di satu kelas
            $table->unique(['guru_id', 'is_wali_kelas', 'role']);
            // Satu kelas hanya bisa punya satu wali kelas
            $table->unique(['kelas_id', 'is_wali_kelas', 'role']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('guru_kelas');

        Schema::table('gurus', function (Blueprint $table) {
            $table->foreignId('kelas_pengajar_id')->nullable()->constrained('kelas');
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->foreignId('wali_kelas_id')->nullable()->constrained('gurus');
        });
    }
}