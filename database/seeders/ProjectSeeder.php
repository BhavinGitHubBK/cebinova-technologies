<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('cebinova.portfolio', []) as $index => $item) {
            Project::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'client_name' => null,
                    'category' => $item['type'] ?? null,
                    'preview' => $item['preview'] ?? 'website',
                    'type' => $item['type'] ?? 'Project',
                    'technologies' => $item['tags'] ?? [],
                    'short_description' => $item['summary'] ?? null,
                    'full_case_study' => null,
                    'thumbnail' => null,
                    'gallery' => [],
                    'project_url' => null,
                    'completed_at' => null,
                    'is_featured' => false,
                    'sort_order' => $index + 1,
                    'status' => 'published',
                    'seo_title' => ($item['title'] ?? 'Project').' | CEBINOVA',
                    'seo_description' => $item['summary'] ?? null,
                ]
            );
        }
    }
}
