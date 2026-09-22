<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // marketing_regular, marketing_festival, marketing_growth, website, app, other
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('key')->nullable()->index();
            $table->string('heading')->nullable();
            $table->text('short_description')->nullable();
            $table->text('subheading')->nullable();
            $table->string('teaser')->nullable();
            $table->string('best_if')->nullable();
            $table->string('service_label')->nullable();
            $table->string('badge')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('secondary_cta')->nullable();
            $table->text('why')->nullable();
            $table->json('includes')->nullable();
            $table->boolean('is_highlighted')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->date('active_from')->nullable();
            $table->date('active_until')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['category', 'is_active', 'sort_order']);
        });

        Schema::create('package_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->string('key')->nullable();
            $table->string('label');
            $table->string('duration')->nullable();
            $table->string('billing_duration')->nullable();
            $table->unsignedInteger('original_price')->nullable();
            $table->unsignedInteger('price');
            $table->unsignedTinyInteger('discount_percent')->nullable();
            $table->string('period')->nullable();
            $table->string('badge')->nullable();
            $table->string('cta')->nullable();
            $table->text('note')->nullable();
            $table->json('features')->nullable();
            $table->json('monthly_pace')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['package_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_plans');
        Schema::dropIfExists('packages');
    }
};
