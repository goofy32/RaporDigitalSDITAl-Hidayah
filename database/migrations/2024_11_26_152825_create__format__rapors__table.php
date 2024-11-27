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
        Schema::create('format_rapors', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // UTS/UAS
            $table->string('title');
            $table->string('template_path');
            $table->string('preview_path')->nullable();
            $table->string('tahun_ajaran');
            $table->boolean('is_active')->default(false);
            $table->json('placeholders')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('format_rapors');
    }
};
