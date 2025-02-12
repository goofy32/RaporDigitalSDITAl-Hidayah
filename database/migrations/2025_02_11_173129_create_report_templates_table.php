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
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('path');
            $table->enum('type', ['UTS', 'UAS']);
            $table->boolean('is_active')->default(false);
            $table->string('tahun_ajaran')->nullable();
            $table->integer('semester')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('report_templates');
    }
};
