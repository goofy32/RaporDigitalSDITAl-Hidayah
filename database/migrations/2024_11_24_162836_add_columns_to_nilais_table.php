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
        Schema::table('nilais', function (Blueprint $table) {
            $table->float('na_tp')->nullable(); // Nilai rata-rata tujuan pembelajaran
            $table->float('na_lm')->nullable(); // Nilai rata-rata lingkup materi
            $table->integer('tp_number')->nullable(); // Nomor tujuan pembelajaran
        });
    }

    public function down()
    {
        Schema::table('nilais', function (Blueprint $table) {
            $table->dropColumn(['na_tp', 'na_lm', 'tp_number']);
        });
    }
};
