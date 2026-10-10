<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demos', function (Blueprint $table) {
            $table->id();
            $table->string('name', 190);
            $table->string('slug', 190)->unique();
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('live_url', 2048)->nullable();
            $table->string('local_demo')->nullable();
            $table->string('badge')->default('CONCEPT');
            $table->json('features')->nullable();
            $table->json('technologies')->nullable();
            $table->json('screenshots')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['is_active', 'sort_order']);
        });

        foreach (['service', 'solution', 'industry'] as $related) {
            Schema::create('demo_'.$related, function (Blueprint $table) use ($related) {
                $table->foreignId('demo_id')->constrained('demos')->restrictOnDelete();
                $table->foreignId($related.'_id')->constrained($related === 'industry' ? 'industries' : $related.'s')->restrictOnDelete();
                $table->primary(['demo_id', $related.'_id']);
            });
        }

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('demo_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('source_page', 2048)->nullable();
        });
    }

    public function down(): void
    {
        // Deliberately preserve business records. Roll forward with a corrective migration.
    }
};
