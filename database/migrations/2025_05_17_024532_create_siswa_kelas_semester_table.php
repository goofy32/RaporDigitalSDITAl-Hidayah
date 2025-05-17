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
        Schema::create('siswa_kelas_semester', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('kelas_id');
            $table->unsignedBigInteger('tahun_ajaran_id');
            $table->tinyInteger('semester'); // 1 untuk ganjil, 2 untuk genap
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('siswa_id')->references('id')->on('siswas')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
            $table->foreign('tahun_ajaran_id')->references('id')->on('tahun_ajarans')->onDelete('cascade');
            
            // Unique constraint so a student can't be in multiple classes in the same semester+year
            $table->unique(['siswa_id', 'tahun_ajaran_id', 'semester']);
        });
        
        // Add a note to existing siswas table to maintain backward compatibility
        Schema::table('siswas', function (Blueprint $table) {
            $table->text('note')->nullable()->after('photo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('siswa_kelas_semester');
        
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
};
