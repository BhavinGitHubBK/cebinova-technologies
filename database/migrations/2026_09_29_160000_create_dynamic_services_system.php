<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('hero_title')->nullable()->after('featured_image');
            $table->text('hero_subtitle')->nullable()->after('hero_title');
            $table->string('badge')->nullable()->after('hero_subtitle');
            $table->text('audience')->nullable()->after('badge');
            $table->text('outcome')->nullable()->after('audience');
            $table->string('next_label')->nullable()->after('outcome');
            $table->string('next_route')->nullable()->after('next_label');
            $table->string('next_param')->nullable()->after('next_route');
        });

        Schema::create('service_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('content')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['service_id', 'is_active', 'sort_order']);
        });

        Schema::create('service_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_section_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('value')->nullable();
            $table->string('link')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['service_section_id', 'is_active', 'sort_order'], 'service_section_items_lookup');
        });

        Schema::table('faqs', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->index(['service_id', 'is_active', 'sort_order']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('service')->constrained('services')->nullOnDelete();
            $table->json('service_snapshot')->nullable()->after('service_id');
        });

        $now = now();
        foreach (config('cebinova.services', []) as $serviceData) {
            $service = DB::table('services')->where('slug', $serviceData['slug'])->first();
            if (! $service) {
                continue;
            }

            $guide = config('cebinova.service_guides.'.$serviceData['slug'], []);
            DB::table('services')->where('id', $service->id)->update([
                'hero_title' => $serviceData['title'],
                'hero_subtitle' => $serviceData['summary'] ?? null,
                'audience' => $guide['best_for'] ?? null,
                'outcome' => $guide['outcome'] ?? null,
                'next_label' => $guide['next_label'] ?? 'Get Free Consultation',
                'next_route' => $guide['next_route'] ?? null,
                'next_param' => $guide['next_param'] ?? null,
            ]);

            $sectionId = DB::table('service_sections')->insertGetId([
                'service_id' => $service->id,
                'type' => 'features',
                'title' => 'What this includes',
                'is_active' => true,
                'sort_order' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            foreach (($serviceData['includes'] ?? []) as $index => $feature) {
                DB::table('service_section_items')->insert([
                    'service_section_id' => $sectionId,
                    'title' => $feature,
                    'is_active' => true,
                    'sort_order' => $index,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $processId = DB::table('service_sections')->insertGetId([
                'service_id' => $service->id,
                'type' => 'process',
                'title' => config('cebinova.page.how_title'),
                'subtitle' => config('cebinova.page.how_text'),
                'is_active' => true,
                'sort_order' => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            foreach (config('cebinova.process', []) as $index => $step) {
                DB::table('service_section_items')->insert([
                    'service_section_id' => $processId,
                    'title' => $step['title'],
                    'description' => $step['text'],
                    'value' => $step['step'] ?? str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                    'is_active' => true,
                    'sort_order' => $index,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach (($guide['faq'] ?? []) as $index => $faq) {
                DB::table('faqs')->insert([
                    'service_id' => $service->id,
                    'page' => 'service',
                    'question' => $faq['q'],
                    'answer' => $faq['a'],
                    'is_active' => true,
                    'sort_order' => $index,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_id');
            $table->dropColumn('service_snapshot');
        });
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_id');
        });
        Schema::dropIfExists('service_section_items');
        Schema::dropIfExists('service_sections');
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['hero_title', 'hero_subtitle', 'badge', 'audience', 'outcome', 'next_label', 'next_route', 'next_param']);
        });
    }
};
