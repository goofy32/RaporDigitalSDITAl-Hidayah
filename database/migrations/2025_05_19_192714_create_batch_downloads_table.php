<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchDownloadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_downloads', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id')->unique();
            $table->unsignedBigInteger('guru_id');
            $table->string('zip_path');
            $table->integer('file_count')->default(0);
            $table->timestamps();
            
            // Add foreign key if guru table exists
            if (Schema::hasTable('gurus')) {
                $table->foreign('guru_id')->references('id')->on('gurus');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('batch_downloads');
    }
}
