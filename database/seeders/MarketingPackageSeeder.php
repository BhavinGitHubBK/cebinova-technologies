<?php

namespace Database\Seeders;

use App\Models\MarketingPackage;
use Illuminate\Database\Seeder;

class MarketingPackageSeeder extends Seeder
{
    public function run(): void
    {
        $sort = 0;

        foreach (['regular', 'festival', 'growth'] as $key) {
            $config = config('cebinova.marketing.'.$key);

            if (! is_array($config)) {
                continue;
            }

            $package = MarketingPackage::query()->updateOrCreate(
                ['key' => $config['key'] ?? $key],
                [
                    'title' => $config['title'] ?? $key,
                    'heading' => $config['heading'] ?? null,
                    'subheading' => $config['subheading'] ?? null,
                    'teaser' => $config['teaser'] ?? null,
                    'best_if' => $config['best_if'] ?? null,
                    'service' => $config['service'] ?? ($config['title'] ?? $key),
                    'badge' => $config['badge'] ?? null,
                    'cta' => $config['cta'] ?? null,
                    'secondary_cta' => $config['secondary_cta'] ?? null,
                    'why' => $config['why'] ?? null,
                    'includes' => $config['includes'] ?? null,
                    'sort_order' => $sort++,
                    'is_active' => true,
                ],
            );

            $planSort = 0;

            foreach ($config['plans'] ?? [] as $planKey => $plan) {
                if (! is_array($plan) || ! isset($plan['label'], $plan['price'])) {
                    continue;
                }

                $package->plans()->updateOrCreate(
                    ['key' => is_string($planKey) ? $planKey : ($plan['key'] ?? $plan['label'])],
                    [
                        'label' => $plan['label'],
                        'duration' => $plan['duration'] ?? null,
                        'price' => (int) $plan['price'],
                        'period' => $plan['period'] ?? null,
                        'badge' => $plan['badge'] ?? null,
                        'cta' => $plan['cta'] ?? null,
                        'note' => $plan['note'] ?? null,
                        'includes' => $plan['includes'] ?? [],
                        'monthly_pace' => $plan['monthly_pace'] ?? null,
                        'sort_order' => $planSort++,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
