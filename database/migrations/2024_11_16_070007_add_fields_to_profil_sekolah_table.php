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
        Schema::table('profil_sekolah', function (Blueprint $table) {
            $table->string('email_sekolah')->after('jumlah_siswa');
            $table->string('tempat_terbit')->after('email_sekolah');
            $table->date('tanggal_terbit')->after('tempat_terbit');
            $table->string('website')->nullable()->after('tanggal_terbit');
        });
    }
    
    public function down()
    {
        Schema::table('profil_sekolah', function (Blueprint $table) {
            $table->dropColumn(['email_sekolah', 'tempat_terbit', 'tanggal_terbit', 'website']);
        });
    }
};
