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
        $formats = \App\Models\FormatRapor::all();
        foreach ($formats as $format) {
            if (!is_string($format->placeholders)) {
                $format->placeholders = json_encode($format->placeholders);
                $format->save();
            }
        }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
