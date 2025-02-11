<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Hapus tabel jika sudah ada
        Schema::dropIfExists('guru_kelas');

        // Buat tabel baru
        Schema::create('guru_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')
                  ->constrained('gurus')
                  ->onDelete('cascade');
            $table->foreignId('kelas_id')
                  ->constrained('kelas')
                  ->onDelete('cascade');
            $table->boolean('is_wali_kelas')
                  ->default(false);
            $table->enum('role', ['pengajar', 'wali_kelas']);
            $table->timestamps();
            
            // Unique constraint untuk mencegah guru memiliki multiple role untuk kelas yang sama
            $table->unique(['guru_id', 'kelas_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guru_kelas');
    }
};
