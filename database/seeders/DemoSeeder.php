<?php

namespace Database\Seeders;

use App\Models\Demo;
use App\Models\Solution;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach (config('cebinova.business_solutions', []) as $order => $item) {
                if (Demo::withTrashed()->where('slug', $item['slug'])->exists()) {
                    continue;
                }
                $demo = Demo::create([
                    'name' => $item['title'], 'slug' => $item['slug'], 'short_description' => $item['summary'],
                    'thumbnail' => $item['preview'] ? 'assets/'.$item['preview'] : null,
                    'local_demo' => $item['demo'], 'badge' => $item['label'],
                    'features' => array_map(fn ($name) => ['name' => $name, 'description' => null, 'is_active' => true], $item['capabilities']),
                    'technologies' => [], 'is_active' => true, 'is_featured' => $item['label'] === 'LIVE DEMO', 'sort_order' => $order,
                ]);
                $solution = Solution::with(['services', 'industries'])->where('slug', $item['slug'])->first();
                if ($solution) {
                    $demo->solutions()->sync([$solution->id]);
                    $demo->services()->sync($solution->services->modelKeys());
                    $demo->industries()->sync($solution->industries->modelKeys());
                }
            }
        });
    }
}
