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
        Schema::create('gurus', function (Blueprint $table) {
            $table->id();
            $table->string('nuptk')->unique();
            $table->string('nama');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->date('tanggal_lahir');
            $table->string('no_handphone');
            $table->string('email')->unique();
            $table->text('alamat');
            $table->string('jabatan');
            $table->unsignedBigInteger('kelas_pengajar_id');
            $table->string('username')->unique();
            $table->string('password');
            $table->timestamps();
    
            $table->foreign('kelas_pengajar_id')->references('id')->on('kelas')->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mata_pelajarans');
    }
};
