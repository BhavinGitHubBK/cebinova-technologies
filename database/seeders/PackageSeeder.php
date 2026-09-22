<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $marketingMap = [
            'regular' => 'marketing_regular',
            'festival' => 'marketing_festival',
            'growth' => 'marketing_growth',
        ];

        $sort = 1;
        foreach ($marketingMap as $key => $category) {
            $config = config('cebinova.marketing.'.$key);
            if (! is_array($config)) {
                continue;
            }

            $package = Package::query()->updateOrCreate(
                ['slug' => Str::slug($config['title'] ?? $key)],
                [
                    'category' => $category,
                    'name' => $config['title'] ?? ucfirst($key),
                    'key' => $config['key'] ?? $key,
                    'heading' => $config['heading'] ?? null,
                    'short_description' => $config['subheading'] ?? null,
                    'subheading' => $config['subheading'] ?? null,
                    'teaser' => $config['teaser'] ?? null,
                    'best_if' => $config['best_if'] ?? null,
                    'service_label' => $config['service'] ?? null,
                    'badge' => $config['badge'] ?? null,
                    'cta_label' => $config['cta'] ?? 'Get This Plan',
                    'secondary_cta' => $config['secondary_cta'] ?? null,
                    'why' => $config['why'] ?? null,
                    'includes' => $config['includes'] ?? [],
                    'is_highlighted' => $key === 'growth',
                    'is_active' => true,
                    'sort_order' => $sort++,
                ]
            );

            $package->plans()->delete();
            $i = 0;
            foreach ($config['plans'] ?? [] as $planKey => $plan) {
                $package->plans()->create([
                    'key' => is_string($planKey) ? $planKey : Str::slug($plan['label'] ?? 'plan'),
                    'label' => $plan['label'] ?? 'Plan',
                    'duration' => $plan['duration'] ?? null,
                    'billing_duration' => $plan['label'] ?? null,
                    'price' => (int) ($plan['price'] ?? 0),
                    'original_price' => null,
                    'period' => $plan['period'] ?? null,
                    'badge' => $plan['badge'] ?? null,
                    'cta' => $plan['cta'] ?? null,
                    'note' => $plan['note'] ?? null,
                    'features' => $plan['includes'] ?? [],
                    'monthly_pace' => $plan['monthly_pace'] ?? null,
                    'is_active' => true,
                    'sort_order' => $i++,
                ]);
            }
        }

        foreach (config('cebinova.kirana_plans', []) as $index => $plan) {
            $package = Package::query()->updateOrCreate(
                ['slug' => 'website-'.($plan['key'] ?? Str::slug($plan['title']))],
                [
                    'category' => 'website',
                    'name' => $plan['title'],
                    'key' => $plan['key'] ?? null,
                    'heading' => $plan['title'],
                    'short_description' => $plan['for'] ?? null,
                    'teaser' => $plan['insight'] ?? null,
                    'badge' => $plan['badge'] ?? null,
                    'is_highlighted' => ($plan['key'] ?? '') === 'business',
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'includes' => $plan['features'] ?? [],
                ]
            );

            $package->plans()->delete();
            $package->plans()->create([
                'key' => 'one-time',
                'label' => 'One Time',
                'duration' => 'One Time',
                'billing_duration' => 'One Time',
                'price' => (int) ($plan['price'] ?? 0),
                'period' => $plan['period'] ?? 'One Time',
                'badge' => $plan['badge'] ?? null,
                'features' => $plan['features'] ?? [],
                'is_active' => true,
                'sort_order' => 0,
            ]);
        }

        foreach (config('cebinova.kirana_addons', []) as $index => $addon) {
            $slug = 'app-'.Str::slug($addon['title']);
            $package = Package::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'category' => str_contains(strtolower($addon['title']), 'app') ? 'app' : 'other',
                    'name' => $addon['title'],
                    'key' => Str::slug($addon['title']),
                    'teaser' => $addon['note'] ?? null,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
            $package->plans()->delete();
            $package->plans()->create([
                'key' => 'default',
                'label' => 'Standard',
                'price' => (int) ($addon['price'] ?? 0),
                'period' => $addon['note'] ?? null,
                'is_active' => true,
                'sort_order' => 0,
            ]);
        }
    }
}
