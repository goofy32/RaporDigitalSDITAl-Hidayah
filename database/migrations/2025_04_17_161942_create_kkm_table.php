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
        Schema::create('kkm_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->onDelete('cascade');
            $table->decimal('nilai_kkm', 5, 2)->default(70.00);
            $table->decimal('bobot_tp', 5, 2)->default(1.00);
            $table->decimal('bobot_lm', 5, 2)->default(1.00);
            $table->decimal('bobot_as', 5, 2)->default(2.00);
            $table->text('keterangan')->nullable();
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->onDelete('set null');
            $table->timestamps();
            
            // Unique constraint untuk satu setting KKM per mata pelajaran per tahun ajaran
            $table->unique(['mata_pelajaran_id', 'tahun_ajaran_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kkm_settings');
    }
};