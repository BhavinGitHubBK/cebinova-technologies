<?php

namespace App\Support;

use App\Models\Package;
use App\Models\PricingOption;
use App\Services\Promotions\PromotionEngine;
use Illuminate\Support\Facades\Schema;

class WebsitePackages
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function plans(): array
    {
        $configPlans = collect(config('cebinova.kirana_plans', []))->keyBy('key');

        if (! self::tablesReady()) {
            return $configPlans->values()->all();
        }

        $packages = Package::query()
            ->website()
            ->active()
            ->with(['plans' => fn ($q) => $q->active()->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        if ($packages->isEmpty()) {
            return $configPlans->values()->all();
        }

        return $packages->map(function (Package $package) use ($configPlans) {
            $plan = $package->plans->first();
            $config = $configPlans->get($package->key, []);

            return [
                'id' => $package->id,
                'key' => $package->key,
                'title' => $package->name,
                'price' => $plan?->price ?? ($config['price'] ?? 0),
                'promotion' => $plan ? self::offer('website_package', $package->id, $plan->price) : null,
                'period' => $plan?->period ?? ($config['period'] ?? 'One Time'),
                'badge' => $package->badge ?? $plan?->badge ?? ($config['badge'] ?? null),
                'for' => $package->short_description ?? ($config['for'] ?? null),
                'insight' => $package->teaser ?? ($config['insight'] ?? null),
                'features' => $plan?->features ?: ($package->includes ?? ($config['features'] ?? [])),
                'support' => $config['support'] ?? null,
                'training' => $config['training'] ?? null,
                'delivery' => $config['delivery'] ?? null,
                'ideal' => $config['ideal'] ?? [],
            ];
        })->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function addons(): array
    {
        if (! self::tablesReady()) {
            return config('cebinova.kirana_addons', []);
        }

        if (Schema::hasTable('pricing_options')) {
            $options = PricingOption::query()->active()->where('type', 'addon')->ordered()->get();
            if ($options->isNotEmpty()) {
                return $options->map(fn ($option) => [
                    'id' => $option->id,
                    'title' => $option->name,
                    'price' => (int) $option->price,
                    'note' => $option->description,
                    'promotion' => self::offer('pricing_option', $option->id, $option->price),
                ])->all();
            }
        }

        $packages = Package::query()
            ->whereIn('category', ['app', 'other'])
            ->active()
            ->with(['plans' => fn ($q) => $q->active()->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        if ($packages->isEmpty()) {
            return config('cebinova.kirana_addons', []);
        }

        return $packages->map(function (Package $package) {
            $plan = $package->plans->first();

            return [
                'title' => $package->name,
                'price' => $plan?->price ?? 0,
                'note' => $package->teaser ?? $plan?->period,
            ];
        })->all();
    }

    /**
     * Domain, hosting and support prices edited under Extra Charges.
     *
     * @return array{domain: list<array<string, mixed>>, hosting: list<array<string, mixed>>, support: list<array<string, mixed>>}
     */
    public static function commercialChoices(): array
    {
        $fallback = [
            'domain' => [
                ['name' => 'Use Existing Domain', 'price' => 0, 'recommended' => false],
                ['name' => 'Hostinger Domain', 'price' => 799, 'recommended' => true],
                ['name' => 'GoDaddy Domain', 'price' => 999, 'recommended' => false],
                ['name' => 'BigRock Domain', 'price' => 899, 'recommended' => false],
            ],
            'hosting' => [
                ['name' => 'Use Existing Hosting', 'price' => 0, 'recommended' => false],
                ['name' => 'Basic Hosting', 'price' => 3000, 'recommended' => false],
                ['name' => 'Premium Hosting', 'price' => 6000, 'recommended' => true],
            ],
            'support' => [],
        ];

        try {
            if (! Schema::hasTable('pricing_options')) {
                return $fallback;
            }

            $rows = PricingOption::query()->active()->ordered()->whereIn('type', ['domain', 'hosting', 'support'])->get();
        } catch (\Throwable) {
            return $fallback;
        }

        if ($rows->isEmpty()) {
            return $fallback;
        }

        $grouped = ['domain' => [], 'hosting' => [], 'support' => []];
        foreach ($rows as $row) {
            $grouped[$row->type][] = [
                'id' => $row->id,
                'name' => $row->name,
                'slug' => $row->slug,
                'price' => (int) $row->price,
                'recommended' => (bool) $row->is_recommended,
                'description' => $row->description,
            ];
        }

        foreach (['domain', 'hosting'] as $type) {
            if ($grouped[$type] === []) {
                $grouped[$type] = $fallback[$type];
            }
        }

        return $grouped;
    }

    public static function choicePrice(array $rows, string $needle, int $fallback): int
    {
        foreach ($rows as $row) {
            if (str_contains(strtolower((string) ($row['name'] ?? '')), strtolower($needle))) {
                return (int) $row['price'];
            }
        }

        return $fallback;
    }

    private static function tablesReady(): bool
    {
        try {
            return Schema::hasTable('packages');
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function offer(string $type, int $id, int|string $amount, ?int $packageId = null): ?array
    {
        try {
            if (! Schema::hasTable('promotion_campaigns')) {
                return null;
            }

            return app(PromotionEngine::class)->previewLine($type, $id, $amount, $packageId);
        } catch (\Throwable) {
            return null;
        }
    }
}
