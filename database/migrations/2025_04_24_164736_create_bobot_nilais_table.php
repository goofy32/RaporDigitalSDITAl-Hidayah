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
        Schema::create('bobot_nilais', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tahun_ajaran_id')->nullable();
            $table->float('bobot_tp', 5, 2)->default(0.25); // 25%
            $table->float('bobot_lm', 5, 2)->default(0.25); // 25%
            $table->float('bobot_as', 5, 2)->default(0.50); // 50%
            $table->timestamps();
            
            $table->foreign('tahun_ajaran_id')->references('id')->on('tahun_ajarans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bobot_nilais');
    }
};
