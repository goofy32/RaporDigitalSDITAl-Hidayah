<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportTemplateKelasTable extends Migration
{
    public function up()
    {
        Schema::create('report_template_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_template_id')->constrained()->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Tambahkan indeks untuk performa
            $table->index(['report_template_id', 'kelas_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('report_template_kelas');
    }
}