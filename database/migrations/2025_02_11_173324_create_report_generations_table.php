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
        Schema::create('report_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('report_template_id')->constrained()->onDelete('cascade');
            $table->string('generated_file');
            $table->enum('type', ['UTS', 'UAS']);
            $table->string('tahun_ajaran');
            $table->integer('semester');
            $table->timestamp('generated_at');
            $table->foreignId('generated_by')->constrained('gurus')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('report_generations');
    }
};
