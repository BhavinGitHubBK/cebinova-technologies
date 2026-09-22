<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('cebinova.services', []) as $index => $item) {
            Service::query()->updateOrCreate(
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
                ]
            );
        }
    }
}
