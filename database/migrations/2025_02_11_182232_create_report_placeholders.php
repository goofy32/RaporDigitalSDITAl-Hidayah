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
        Schema::create('report_placeholders', function (Blueprint $table) {
            $table->id();
            $table->string('placeholder_key');
            $table->string('description');
            $table->string('category'); // Misal: 'siswa', 'nilai', 'absensi', dll
            $table->string('sample_value'); // Nilai contoh untuk preview
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('report_placeholders');
    }
};
