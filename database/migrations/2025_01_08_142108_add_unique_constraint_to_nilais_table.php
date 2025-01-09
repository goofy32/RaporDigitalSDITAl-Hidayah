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
        Schema::table('nilais', function (Blueprint $table) {
            // Drop unique key jika sudah ada untuk menghindari konflik
            if(Schema::hasTable('nilais') && Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('nilais')) {
                $table->dropIndex(['siswa_id', 'mata_pelajaran_id', 'tujuan_pembelajaran_id', 'lingkup_materi_id']);
            }

            // Tambahkan unique constraint baru
            $table->unique(
                ['siswa_id', 'mata_pelajaran_id', 'tujuan_pembelajaran_id', 'lingkup_materi_id'],
                'nilais_unique_scores'
            );

            // Pastikan kolom dapat bernilai null
            $table->float('nilai_tp')->nullable()->change();
            $table->float('nilai_lm')->nullable()->change();
            $table->float('na_tp')->nullable()->change();
            $table->float('na_lm')->nullable()->change();
            $table->float('nilai_tes')->nullable()->change();
            $table->float('nilai_non_tes')->nullable()->change();
            $table->float('nilai_akhir_semester')->nullable()->change();
            $table->float('nilai_akhir_rapor')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            // Hapus unique constraint
            $table->dropUnique('nilais_unique_scores');
        });
    }
};
