<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSemesterSnapshotsTable extends Migration
{
    public function up()
    {
        Schema::create('semester_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tahun_ajaran_id');
            $table->tinyInteger('semester');
            $table->datetime('snapshot_date');
            $table->json('data')->nullable();
            $table->timestamps();
            
            $table->foreign('tahun_ajaran_id')
                  ->references('id')
                  ->on('tahun_ajarans')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('semester_snapshots');
    }
}