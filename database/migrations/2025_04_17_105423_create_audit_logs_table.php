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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user_type')->nullable(); // 'admin', 'guru', etc.
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action'); // 'login', 'create', 'update', 'delete', etc.
            $table->string('model_type')->nullable(); // The model being affected
            $table->unsignedBigInteger('model_id')->nullable(); // ID of the affected model
            $table->text('description')->nullable(); // Description of the action
            $table->json('old_values')->nullable(); // Previous values for updates
            $table->json('new_values')->nullable(); // New values for updates
            $table->string('ip_address')->nullable(); // User's IP address
            $table->string('user_agent')->nullable(); // User's browser information
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['user_type', 'user_id']);
            $table->index(['model_type', 'model_id']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};