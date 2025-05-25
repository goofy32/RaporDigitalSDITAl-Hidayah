<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Database\Seeders\ReportPlaceholdersSeeder;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Jalankan seeder untuk update placeholder dengan KKM
        $seeder = new ReportPlaceholdersSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus placeholder KKM yang baru ditambahkan
        \DB::table('report_placeholders')
            ->where('placeholder_key', 'LIKE', 'kkm_%')
            ->delete();
    }
};
