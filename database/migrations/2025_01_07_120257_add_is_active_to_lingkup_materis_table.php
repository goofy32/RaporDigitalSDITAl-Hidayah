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
        Schema::table('lingkup_materis', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('judul_lingkup_materi');
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('lingkup_materis', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropSoftDeletes();
        });
    }
};
