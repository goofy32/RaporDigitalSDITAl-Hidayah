<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mata_pelajarans', function (Blueprint $table) {
            $table->boolean('allow_non_wali')->default(false)->after('is_muatan_lokal');
        });
    }

    public function down()
    {
        Schema::table('mata_pelajarans', function (Blueprint $table) {
            $table->dropColumn('allow_non_wali');
        });
    }
};