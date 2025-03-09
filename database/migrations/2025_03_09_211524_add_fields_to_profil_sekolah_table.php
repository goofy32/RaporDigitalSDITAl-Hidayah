<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profil_sekolah', function (Blueprint $table) {
            // Tambahkan kolom yang belum ada
            $table->string('nip_kepala_sekolah')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kabupaten')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('nip_wali_kelas')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profil_sekolah', function (Blueprint $table) {
            // Hapus kolom jika rollback
            $table->dropColumn([
                'nip_kepala_sekolah',
                'kelurahan',
                'kecamatan',
                'kabupaten',
                'provinsi',
                'nip_wali_kelas'
            ]);
        });
    }
};