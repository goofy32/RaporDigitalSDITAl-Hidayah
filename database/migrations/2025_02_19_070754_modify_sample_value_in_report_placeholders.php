<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifySampleValueInReportPlaceholders extends Migration
{
    public function up()
    {
        Schema::table('report_placeholders', function (Blueprint $table) {
            $table->string('sample_value')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('report_placeholders', function (Blueprint $table) {
            $table->string('sample_value')->nullable(false)->change();
        });
    }
}