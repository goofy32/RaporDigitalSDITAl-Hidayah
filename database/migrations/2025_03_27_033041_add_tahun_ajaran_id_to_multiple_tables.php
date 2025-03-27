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
        // 1. Tambahkan tahun_ajaran_id ke tabel mata_pelajarans jika belum ada
        if (!Schema::hasColumn('mata_pelajarans', 'tahun_ajaran_id')) {
            Schema::table('mata_pelajarans', function (Blueprint $table) {
                $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->onDelete('set null');
            });
        }
        
        // 2. Tambahkan tahun_ajaran_id ke tabel siswas jika belum ada
        if (!Schema::hasColumn('siswas', 'tahun_ajaran_id')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->onDelete('set null');
            });
        }
        
        // 3. Tambahkan tahun_ajaran_id ke tabel kelas jika belum ada
        if (!Schema::hasColumn('kelas', 'tahun_ajaran_id')) {
            Schema::table('kelas', function (Blueprint $table) {
                $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->onDelete('set null');
            });
        }
        
        // 4. Tambahkan tahun_ajaran_id ke tabel absensis jika belum ada
        if (!Schema::hasColumn('absensis', 'tahun_ajaran_id')) {
            Schema::table('absensis', function (Blueprint $table) {
                $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->onDelete('set null');
            });
        }
        
        // 5. Tambahkan tahun_ajaran_id ke tabel nilai_ekstrakurikuler jika belum ada
        if (!Schema::hasColumn('nilai_ekstrakurikuler', 'tahun_ajaran_id')) {
            Schema::table('nilai_ekstrakurikuler', function (Blueprint $table) {
                $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->onDelete('set null');
            });
        }
        
        // 6. Tambahkan tahun_ajaran_id ke tabel prestasis jika belum ada
        if (!Schema::hasColumn('prestasis', 'tahun_ajaran_id')) {
            Schema::table('prestasis', function (Blueprint $table) {
                $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->onDelete('set null');
            });
        }
        
        // 7. Tambahkan tahun_ajaran_id ke tabel nilais jika belum ada
        if (!Schema::hasColumn('nilais', 'tahun_ajaran_id')) {
            Schema::table('nilais', function (Blueprint $table) {
                $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->onDelete('set null');
            });
        }

        // 8. Tambahkan tahun_ajaran_id ke tabel ekstrakurikulers jika perlu
        if (!Schema::hasColumn('ekstrakurikulers', 'tahun_ajaran_id')) {
            Schema::table('ekstrakurikulers', function (Blueprint $table) {
                $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus foreign key dan kolom di masing-masing tabel
        if (Schema::hasColumn('ekstrakurikulers', 'tahun_ajaran_id')) {
            Schema::table('ekstrakurikulers', function (Blueprint $table) {
                $table->dropForeign(['tahun_ajaran_id']);
                $table->dropColumn('tahun_ajaran_id');
            });
        }

        if (Schema::hasColumn('nilais', 'tahun_ajaran_id')) {
            Schema::table('nilais', function (Blueprint $table) {
                $table->dropForeign(['tahun_ajaran_id']);
                $table->dropColumn('tahun_ajaran_id');
            });
        }

        if (Schema::hasColumn('prestasis', 'tahun_ajaran_id')) {
            Schema::table('prestasis', function (Blueprint $table) {
                $table->dropForeign(['tahun_ajaran_id']);
                $table->dropColumn('tahun_ajaran_id');
            });
        }

        if (Schema::hasColumn('nilai_ekstrakurikuler', 'tahun_ajaran_id')) {
            Schema::table('nilai_ekstrakurikuler', function (Blueprint $table) {
                $table->dropForeign(['tahun_ajaran_id']);
                $table->dropColumn('tahun_ajaran_id');
            });
        }

        if (Schema::hasColumn('absensis', 'tahun_ajaran_id')) {
            Schema::table('absensis', function (Blueprint $table) {
                $table->dropForeign(['tahun_ajaran_id']);
                $table->dropColumn('tahun_ajaran_id');
            });
        }

        if (Schema::hasColumn('kelas', 'tahun_ajaran_id')) {
            Schema::table('kelas', function (Blueprint $table) {
                $table->dropForeign(['tahun_ajaran_id']);
                $table->dropColumn('tahun_ajaran_id');
            });
        }

        if (Schema::hasColumn('siswas', 'tahun_ajaran_id')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->dropForeign(['tahun_ajaran_id']);
                $table->dropColumn('tahun_ajaran_id');
            });
        }

        if (Schema::hasColumn('mata_pelajarans', 'tahun_ajaran_id')) {
            Schema::table('mata_pelajarans', function (Blueprint $table) {
                $table->dropForeign(['tahun_ajaran_id']);
                $table->dropColumn('tahun_ajaran_id');
            });
        }
    }
};