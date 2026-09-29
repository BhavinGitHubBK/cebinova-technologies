<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('cebinova.services', []) as $index => $item) {
            $service = Service::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name' => $item['title'],
                    'category' => $item['category'] ?? null,
                    'icon' => $item['icon'] ?? null,
                    'short_description' => $item['short'] ?? null,
                    'full_description' => $item['summary'] ?? null,
                    'features' => $item['includes'] ?? [],
                    'cta_label' => 'Enquire now',
                    'cta_url' => '/contact?service='.urlencode($item['title']),
                    'sort_order' => $index + 1,
                    'is_featured' => false,
                    'is_active' => true,
                    'seo_title' => $item['title'].' | CEBINOVA',
                    'seo_description' => $item['short'] ?? null,
                    'hero_title' => $item['title'],
                    'hero_subtitle' => $item['summary'] ?? null,
                    'audience' => config('cebinova.service_guides.'.$item['slug'].'.best_for'),
                    'outcome' => config('cebinova.service_guides.'.$item['slug'].'.outcome'),
                    'next_label' => config('cebinova.service_guides.'.$item['slug'].'.next_label'),
                    'next_route' => config('cebinova.service_guides.'.$item['slug'].'.next_route'),
                    'next_param' => config('cebinova.service_guides.'.$item['slug'].'.next_param'),
                ]
            );

            if (! Schema::hasTable('service_sections')) {
                continue;
            }

            $features = $service->sections()->firstOrCreate(['type' => 'features'], ['title' => 'What this includes', 'sort_order' => 10]);
            $features->items()->delete();
            foreach (($item['includes'] ?? []) as $featureIndex => $feature) {
                $features->items()->create(['title' => $feature, 'is_active' => true, 'sort_order' => $featureIndex]);
            }

            $process = $service->sections()->firstOrCreate(['type' => 'process'], [
                'title' => config('cebinova.page.how_title'),
                'subtitle' => config('cebinova.page.how_text'),
                'sort_order' => 20,
            ]);
            $process->items()->delete();
            foreach (config('cebinova.process', []) as $processIndex => $step) {
                $process->items()->create([
                    'title' => $step['title'],
                    'description' => $step['text'],
                    'value' => $step['step'] ?? str_pad((string) ($processIndex + 1), 2, '0', STR_PAD_LEFT),
                    'is_active' => true,
                    'sort_order' => $processIndex,
                ]);
            }

            $service->faqs()->delete();
            foreach (config('cebinova.service_guides.'.$item['slug'].'.faq', []) as $faqIndex => $faq) {
                $service->faqs()->create([
                    'page' => 'service',
                    'question' => $faq['q'],
                    'answer' => $faq['a'],
                    'is_active' => true,
                    'sort_order' => $faqIndex,
                ]);
            }
        }
    }
}
