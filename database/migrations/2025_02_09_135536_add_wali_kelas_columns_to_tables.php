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
        // Tambah kolom di tabel gurus
        Schema::table('gurus', function (Blueprint $table) {
            $table->boolean('is_wali_kelas')->default(false);
        });
    
        // Ubah kolom di tabel kelas
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn('wali_kelas'); // hapus kolom lama
            $table->foreignId('wali_kelas_id')->nullable()->constrained('gurus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};
