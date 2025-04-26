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
        Schema::table('siswas', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'lulus', 'pindah', 'dropout'])->default('aktif');
            $table->boolean('is_naik_kelas')->nullable();
            $table->unsignedBigInteger('kelas_tujuan_id')->nullable();
            $table->foreign('kelas_tujuan_id')->references('id')->on('kelas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
