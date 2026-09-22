<?php

namespace App\Support;

use App\Models\Package;
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
                'key' => $package->key,
                'title' => $package->name,
                'price' => $plan?->price ?? ($config['price'] ?? 0),
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

    private static function tablesReady(): bool
    {
        try {
            return Schema::hasTable('packages');
        } catch (\Throwable) {
            return false;
        }
    }
}
