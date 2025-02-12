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
        Schema::create('report_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_template_id')->constrained()->onDelete('cascade');
            $table->string('placeholder_key');
            $table->string('data_source'); // Contoh: 'siswa.nama', 'nilai.nilai_akhir'
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('report_mappings');
    }
};
