<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('career_applications', function (Blueprint $table) {
            $table->string('location_city', 80)->nullable();
            $table->string('location_region', 80)->nullable();
            $table->string('location_country', 80)->nullable();
            $table->string('location_source', 30)->nullable();
            $table->string('location_permission', 30)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('location_accuracy')->nullable();
            $table->timestamp('location_captured_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('career_applications', fn (Blueprint $table) => $table->dropColumn(['location_city', 'location_region', 'location_country', 'location_source', 'location_permission', 'latitude', 'longitude', 'location_accuracy', 'location_captured_at']));
    }
};
