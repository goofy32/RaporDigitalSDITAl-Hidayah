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
        Schema::table('absensis', function (Blueprint $table) {
            $table->tinyInteger('semester')->default(1)->after('tanpa_keterangan');
        });
    }
    
    public function down()
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropColumn('semester');
        });
    }
};
