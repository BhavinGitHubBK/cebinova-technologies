<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('business_name')->nullable();
            $table->string('phone');
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('business_type')->nullable();
            $table->string('service')->nullable();
            $table->string('package_category')->nullable();
            $table->string('plan_duration')->nullable();
            $table->string('selected_price')->nullable();
            $table->string('city')->nullable();
            $table->string('budget')->nullable();
            $table->text('message')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('free_consultation')->default(false);
            $table->string('source')->default('website');
            $table->string('status')->default('New');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
