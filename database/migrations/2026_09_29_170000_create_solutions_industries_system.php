<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->string('icon')->nullable();
            $table->string('badge')->nullable();
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('hero_title')->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->text('audience')->nullable();
            $table->text('outcome')->nullable();
            $table->string('image')->nullable();
            $table->string('demo_slug')->nullable();
            $table->string('business_type')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('next_route')->nullable();
            $table->string('next_param')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('solution_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solution_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('content')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['solution_id', 'is_active', 'sort_order']);
        });

        Schema::create('solution_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solution_section_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['solution_section_id', 'is_active', 'sort_order'], 'solution_section_items_lookup');
        });

        Schema::create('industries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('group')->nullable();
            $table->string('icon')->nullable();
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('hero_title')->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('solution_service', function (Blueprint $table) {
            $table->foreignId('solution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['solution_id', 'service_id']);
        });

        Schema::create('industry_solution', function (Blueprint $table) {
            $table->foreignId('industry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('solution_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['industry_id', 'solution_id']);
        });

        Schema::table('faqs', function (Blueprint $table) {
            $table->foreignId('solution_id')->nullable()->after('service_id')->constrained()->cascadeOnDelete();
            $table->index(['solution_id', 'is_active', 'sort_order']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('solution_id')->nullable()->after('service_snapshot')->constrained('solutions')->nullOnDelete();
            $table->json('solution_snapshot')->nullable()->after('solution_id');
            $table->foreignId('industry_id')->nullable()->after('solution_snapshot')->constrained('industries')->nullOnDelete();
            $table->json('industry_snapshot')->nullable()->after('industry_id');
        });

        $this->migrateContent();
    }

    private function migrateContent(): void
    {
        $now = now();
        $icons = ['kirana' => 'bag', 'retail' => 'cart', 'professional-services' => 'briefcase', 'ecommerce' => 'store', 'business-management' => 'layers', 'ai-automation' => 'spark'];
        foreach (config('cebinova.business_solutions', []) as $index => $item) {
            $guide = config('cebinova.solution_guides.'.$item['slug'], []);
            $id = DB::table('solutions')->insertGetId([
                'name' => $item['title'], 'slug' => $item['slug'], 'category' => $item['industry'] ?? null,
                'icon' => $icons[$item['slug']] ?? 'layers', 'badge' => $item['label'] ?? null,
                'short_description' => $item['summary'] ?? null, 'description' => $item['summary'] ?? null,
                'hero_title' => $item['heading'] ?? $item['title'], 'hero_subtitle' => $item['summary'] ?? null,
                'audience' => $guide['best_for'] ?? null, 'outcome' => $guide['outcome'] ?? null,
                'image' => $item['preview'] ?? null, 'demo_slug' => $item['demo'] ?? null,
                'business_type' => $item['business_type'] ?? null, 'cta_label' => $guide['next_label'] ?? 'Request this solution',
                'next_route' => $guide['next_route'] ?? null, 'next_param' => $guide['next_param'] ?? null,
                'is_featured' => ($item['label'] ?? null) === 'LIVE DEMO', 'is_active' => true, 'sort_order' => $index + 1,
                'seo_title' => 'CEBINOVA '.$item['title'].' Solution | CEBINOVA Technologies', 'seo_description' => $item['summary'] ?? null,
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $section = DB::table('solution_sections')->insertGetId([
                'solution_id' => $id, 'type' => 'features', 'title' => 'What this solution covers',
                'is_active' => true, 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now,
            ]);
            foreach (($item['capabilities'] ?? []) as $itemIndex => $capability) {
                DB::table('solution_section_items')->insert(['solution_section_id' => $section, 'title' => $capability, 'is_active' => true, 'sort_order' => $itemIndex, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        $groups = ['kirana' => 'retail-food', 'dairy' => 'retail-food', 'food' => 'retail-food', 'retail' => 'retail-food', 'manufacturing' => 'trade', 'wholesale' => 'trade', 'export' => 'trade', 'professional' => 'professional', 'healthcare' => 'professional', 'education' => 'professional', 'real-estate' => 'professional', 'services' => 'professional', 'startups' => 'startups'];
        $icons = ['kirana' => 'bag', 'dairy' => 'home', 'food' => 'store', 'retail' => 'cart', 'manufacturing' => 'factory', 'wholesale' => 'layers', 'export' => 'globe', 'professional' => 'briefcase', 'healthcare' => 'heart', 'education' => 'book', 'real-estate' => 'building', 'startups' => 'rocket', 'services' => 'users'];
        foreach (config('cebinova.industries', []) as $index => $item) {
            DB::table('industries')->insert([
                'name' => $item['title'], 'slug' => $item['slug'], 'group' => $groups[$item['slug']] ?? 'other', 'icon' => $icons[$item['slug']] ?? 'globe',
                'short_description' => $item['need'] ?? null, 'description' => $item['need'] ?? null,
                'is_featured' => in_array($item['slug'], ['kirana', 'food', 'startups'], true), 'is_active' => true, 'sort_order' => $index + 1,
                'seo_title' => $item['title'].' Solutions | CEBINOVA', 'seo_description' => $item['need'] ?? null,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $serviceMap = ['kirana' => ['web-development', 'ecommerce', 'custom-software'], 'retail' => ['web-development', 'ecommerce', 'digital-growth'], 'professional-services' => ['web-development', 'digital-growth'], 'ecommerce' => ['ecommerce', 'custom-software'], 'business-management' => ['custom-software', 'ai-automation'], 'ai-automation' => ['ai-automation', 'custom-software']];
        foreach ($serviceMap as $solutionSlug => $serviceSlugs) {
            $solutionId = DB::table('solutions')->where('slug', $solutionSlug)->value('id');
            foreach ($serviceSlugs as $order => $serviceSlug) {
                $serviceId = DB::table('services')->where('slug', $serviceSlug)->value('id');
                if ($solutionId && $serviceId) DB::table('solution_service')->insertOrIgnore(['solution_id' => $solutionId, 'service_id' => $serviceId, 'sort_order' => $order]);
            }
        }

        $industryMap = ['kirana' => ['kirana', 'ecommerce'], 'dairy' => ['business-management'], 'food' => ['ecommerce', 'retail'], 'retail' => ['retail', 'ecommerce'], 'manufacturing' => ['business-management'], 'wholesale' => ['business-management', 'ecommerce'], 'export' => ['business-management'], 'professional' => ['professional-services', 'business-management'], 'healthcare' => ['professional-services', 'business-management'], 'education' => ['professional-services', 'ai-automation'], 'real-estate' => ['professional-services', 'business-management'], 'startups' => ['ai-automation', 'business-management'], 'services' => ['professional-services', 'business-management', 'ai-automation']];
        foreach ($industryMap as $industrySlug => $solutionSlugs) {
            $industryId = DB::table('industries')->where('slug', $industrySlug)->value('id');
            foreach ($solutionSlugs as $order => $solutionSlug) {
                $solutionId = DB::table('solutions')->where('slug', $solutionSlug)->value('id');
                if ($industryId && $solutionId) DB::table('industry_solution')->insertOrIgnore(['industry_id' => $industryId, 'solution_id' => $solutionId, 'sort_order' => $order]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) { $table->dropConstrainedForeignId('solution_id'); $table->dropColumn('solution_snapshot'); $table->dropConstrainedForeignId('industry_id'); $table->dropColumn('industry_snapshot'); });
        Schema::table('faqs', fn (Blueprint $table) => $table->dropConstrainedForeignId('solution_id'));
        Schema::dropIfExists('industry_solution'); Schema::dropIfExists('solution_service'); Schema::dropIfExists('industries'); Schema::dropIfExists('solution_section_items'); Schema::dropIfExists('solution_sections'); Schema::dropIfExists('solutions');
    }
};
