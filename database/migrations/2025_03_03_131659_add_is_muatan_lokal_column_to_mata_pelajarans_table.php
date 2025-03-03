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
        Schema::table('mata_pelajarans', function (Blueprint $table) {
            $table->boolean('is_muatan_lokal')->default(false)->after('semester');
        });
    }
    
    public function down()
    {
        Schema::table('mata_pelajarans', function (Blueprint $table) {
            $table->dropColumn('is_muatan_lokal');
        });
    }
};
