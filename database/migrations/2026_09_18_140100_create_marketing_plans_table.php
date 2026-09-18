<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketing_package_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('label');
            $table->string('duration')->nullable();
            $table->unsignedInteger('price');
            $table->string('period')->nullable();
            $table->string('badge')->nullable();
            $table->string('cta')->nullable();
            $table->json('includes')->nullable();
            $table->json('monthly_pace')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['marketing_package_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_plans');
    }
};
