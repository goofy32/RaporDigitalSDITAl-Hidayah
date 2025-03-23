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
        Schema::create('tahun_ajarans', function (Blueprint $table) {
            $table->id();
            $table->string('tahun_ajaran'); // Format: "2024/2025"
            $table->boolean('is_active')->default(false);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('semester')->default(1); // 1: Ganjil, 2: Genap
            $table->string('deskripsi')->nullable();
            $table->timestamps();
        });

        // Tambahkan kolom tahun_ajaran_id ke tabel yang diperlukan
        Schema::table('kelas', function (Blueprint $table) {
            $table->foreignId('tahun_ajaran_id')->nullable()->after('nama_kelas')
                  ->constrained('tahun_ajarans')->nullOnDelete();
        });

        Schema::table('mata_pelajarans', function (Blueprint $table) {
            $table->foreignId('tahun_ajaran_id')->nullable()->after('kelas_id')
                  ->constrained('tahun_ajarans')->nullOnDelete();
        });

        Schema::table('report_templates', function (Blueprint $table) {
            $table->foreignId('tahun_ajaran_id')->nullable()->after('semester')
                  ->constrained('tahun_ajarans')->nullOnDelete();
            
            // Ubah kolom tahun_ajaran yang ada menjadi string jika masih array
            if (Schema::hasColumn('report_templates', 'tahun_ajaran')) {
                $table->string('tahun_ajaran_text')->nullable()->after('tahun_ajaran');
                // Migrasi data setelah tabel diubah
            }
        });

        Schema::table('report_generations', function (Blueprint $table) {
            $table->foreignId('tahun_ajaran_id')->nullable()->after('generated_by')
                  ->constrained('tahun_ajarans')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus foreign key constraints
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropForeign(['tahun_ajaran_id']);
            $table->dropColumn('tahun_ajaran_id');
        });

        Schema::table('mata_pelajarans', function (Blueprint $table) {
            $table->dropForeign(['tahun_ajaran_id']);
            $table->dropColumn('tahun_ajaran_id');
        });

        Schema::table('report_templates', function (Blueprint $table) {
            $table->dropForeign(['tahun_ajaran_id']);
            $table->dropColumn('tahun_ajaran_id');
            if (Schema::hasColumn('report_templates', 'tahun_ajaran_text')) {
                $table->dropColumn('tahun_ajaran_text');
            }
        });

        Schema::table('report_generations', function (Blueprint $table) {
            $table->dropForeign(['tahun_ajaran_id']);
            $table->dropColumn('tahun_ajaran_id');
        });

        Schema::dropIfExists('tahun_ajarans');
    }
};