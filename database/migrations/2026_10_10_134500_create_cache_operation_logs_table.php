<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cache_operation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('cache_type', 32);
            $table->string('action', 16);
            $table->timestamp('cleared_at');
            $table->foreignId('cleared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('duration_ms')->default(0);
            $table->boolean('success')->default(false);
            $table->string('error_message', 190)->nullable();
            $table->timestamps();
            $table->index(['cache_type', 'success']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache_operation_logs');
    }
};
