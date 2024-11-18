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
        Schema::table('kelas', function (Blueprint $table) {
            $table->integer('nomor_kelas')->after('id');
        });
    }
    
    public function down()
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn('nomor_kelas');
        });
    }
};
