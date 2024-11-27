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
        Schema::table('format_rapors', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()->after('template_path');
            // Kita hapus preview_path karena akan digantikan pdf_path
            $table->dropColumn('preview_path');
        });
    }

    public function down()
    {
        Schema::table('format_rapors', function (Blueprint $table) {
            $table->dropColumn('pdf_path');
            $table->string('preview_path')->nullable();
        });
    }
};
