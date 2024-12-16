<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('nilai_ekstrakurikuler', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('ekstrakurikuler_id');
            $table->string('predikat');
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->foreign('siswa_id')->references('id')->on('siswas')->onDelete('cascade');
            $table->foreign('ekstrakurikuler_id')->references('id')->on('ekstrakurikulers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('nilai_ekstrakurikuler');
    }
};
