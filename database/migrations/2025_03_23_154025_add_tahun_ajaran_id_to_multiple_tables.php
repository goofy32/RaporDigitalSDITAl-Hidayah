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
        // 1. Menambahkan tahun_ajaran_id ke tabel absensis
        if (Schema::hasTable('absensis') && !Schema::hasColumn('absensis', 'tahun_ajaran_id')) {
            Schema::table('absensis', function (Blueprint $table) {
                $table->foreignId('tahun_ajaran_id')->nullable()->after('semester')
                      ->constrained('tahun_ajarans')->nullOnDelete();
            });
        }

        // 2. Menambahkan tahun_ajaran_id ke tabel nilai_ekstrakurikuler
        if (Schema::hasTable('nilai_ekstrakurikuler') && !Schema::hasColumn('nilai_ekstrakurikuler', 'tahun_ajaran_id')) {
            Schema::table('nilai_ekstrakurikuler', function (Blueprint $table) {
                $table->foreignId('tahun_ajaran_id')->nullable()->after('deskripsi')
                      ->constrained('tahun_ajarans')->nullOnDelete();
            });
        }

        // 3. Menambahkan tahun_ajaran_id ke tabel prestasis
        if (Schema::hasTable('prestasis') && !Schema::hasColumn('prestasis', 'tahun_ajaran_id')) {
            Schema::table('prestasis', function (Blueprint $table) {
                $table->foreignId('tahun_ajaran_id')->nullable()->after('keterangan')
                      ->constrained('tahun_ajarans')->nullOnDelete();
            });
        }

        // 4. Menambahkan tahun_ajaran_id ke tabel nilais jika belum ada
        if (Schema::hasTable('nilais') && !Schema::hasColumn('nilais', 'tahun_ajaran_id')) {
            Schema::table('nilais', function (Blueprint $table) {
                $table->foreignId('tahun_ajaran_id')->nullable()->after('nilai_akhir_rapor')
                      ->constrained('tahun_ajarans')->nullOnDelete();
            });
        }

        // 5. Menambahkan tahun_ajaran_id ke tabel ekstrakurikulers jika perlu
        if (Schema::hasTable('ekstrakurikulers') && !Schema::hasColumn('ekstrakurikulers', 'tahun_ajaran_id')) {
            Schema::table('ekstrakurikulers', function (Blueprint $table) {
                $table->foreignId('tahun_ajaran_id')->nullable()->after('pembina')
                      ->constrained('tahun_ajarans')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Menghapus tahun_ajaran_id dari tabel absensis
        if (Schema::hasTable('absensis') && Schema::hasColumn('absensis', 'tahun_ajaran_id')) {
            Schema::table('absensis', function (Blueprint $table) {
                $table->dropForeign(['tahun_ajaran_id']);
                $table->dropColumn('tahun_ajaran_id');
            });
        }

        // 2. Menghapus tahun_ajaran_id dari tabel nilai_ekstrakurikuler
        if (Schema::hasTable('nilai_ekstrakurikuler') && Schema::hasColumn('nilai_ekstrakurikuler', 'tahun_ajaran_id')) {
            Schema::table('nilai_ekstrakurikuler', function (Blueprint $table) {
                $table->dropForeign(['tahun_ajaran_id']);
                $table->dropColumn('tahun_ajaran_id');
            });
        }

        // 3. Menghapus tahun_ajaran_id dari tabel prestasis
        if (Schema::hasTable('prestasis') && Schema::hasColumn('prestasis', 'tahun_ajaran_id')) {
            Schema::table('prestasis', function (Blueprint $table) {
                $table->dropForeign(['tahun_ajaran_id']);
                $table->dropColumn('tahun_ajaran_id');
            });
        }

        // 4. Menghapus tahun_ajaran_id dari tabel nilais
        if (Schema::hasTable('nilais') && Schema::hasColumn('nilais', 'tahun_ajaran_id')) {
            Schema::table('nilais', function (Blueprint $table) {
                $table->dropForeign(['tahun_ajaran_id']);
                $table->dropColumn('tahun_ajaran_id');
            });
        }

        // 5. Menghapus tahun_ajaran_id dari tabel ekstrakurikulers
        if (Schema::hasTable('ekstrakurikulers') && Schema::hasColumn('ekstrakurikulers', 'tahun_ajaran_id')) {
            Schema::table('ekstrakurikulers', function (Blueprint $table) {
                $table->dropForeign(['tahun_ajaran_id']);
                $table->dropColumn('tahun_ajaran_id');
            });
        }
    }
};