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
        Schema::table('mata_pelajarans', function (Blueprint $table) {
            // Cek jika kolom belum ada
            if (!Schema::hasColumn('mata_pelajarans', 'allow_non_wali')) {
                $table->boolean('allow_non_wali')->default(false)->after('is_muatan_lokal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mata_pelajarans', function (Blueprint $table) {
            // Cek jika kolom ada sebelum dihapus
            if (Schema::hasColumn('mata_pelajarans', 'allow_non_wali')) {
                $table->dropColumn('allow_non_wali');
            }
        });
    }
};