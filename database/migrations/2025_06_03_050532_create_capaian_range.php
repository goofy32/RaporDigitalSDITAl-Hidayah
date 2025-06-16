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
        Schema::create('capaian_range', function (Blueprint $table) {
            $table->id();
            $table->string('nama_range'); // 'Sangat Baik', 'Baik', 'Cukup', 'Perlu Bimbingan'
            $table->integer('nilai_min'); // Nilai minimum range
            $table->integer('nilai_max'); // Nilai maksimum range
            $table->text('template_text'); // Template kalimat untuk range ini
            $table->string('color_class')->nullable(); // Class CSS untuk warna
            $table->integer('urutan')->default(0); // Urutan tampilan
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('tahun_ajaran_id');
            $table->timestamps();

            $table->foreign('tahun_ajaran_id')->references('id')->on('tahun_ajarans')->onDelete('cascade');
            $table->index(['tahun_ajaran_id', 'is_active']);
            $table->index(['nilai_min', 'nilai_max']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capaian_kompetensi_range_templates');
    }
};