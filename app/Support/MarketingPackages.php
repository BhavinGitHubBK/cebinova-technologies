<?php

namespace App\Support;

use App\Models\MarketingPackage;
use App\Models\MarketingPlan;
use Illuminate\Support\Facades\Schema;

class MarketingPackages
{
    public const SERVICE = 'Digital Marketing';

    /**
     * @return array<string, string>
     */
    public static function categoryKeys(): array
    {
        if (! self::tablesReady()) {
            return self::configCategoryKeys();
        }

        return MarketingPackage::query()
            ->active()
            ->orderBy('sort_order')
            ->pluck('key', 'title')
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function categories(): array
    {
        return array_keys(self::categoryKeys());
    }

    /**
     * @return list<string>
     */
    public static function durations(): array
    {
        if (! self::tablesReady()) {
            return config('cebinova.marketing.frequencies', ['Monthly', 'Quarterly', 'Half-Yearly', 'Yearly']);
        }

        $labels = MarketingPlan::query()
            ->active()
            ->whereHas('package', fn ($q) => $q->active())
            ->pluck('label')
            ->unique()
            ->values()
            ->all();

        $preferred = ['Monthly', 'Quarterly', 'Half-Yearly', 'Yearly'];
        $ordered = [];

        foreach ($preferred as $label) {
            if (in_array($label, $labels, true)) {
                $ordered[] = $label;
            }
        }

        foreach ($labels as $label) {
            if (! in_array($label, $ordered, true)) {
                $ordered[] = $label;
            }
        }

        return $ordered !== []
            ? $ordered
            : config('cebinova.marketing.frequencies', ['Monthly', 'Quarterly', 'Half-Yearly', 'Yearly']);
    }

    public static function isValid(string $category, string $duration): bool
    {
        return self::priceAmount($category, $duration) !== null;
    }

    public static function priceAmount(?string $category, ?string $duration): ?int
    {
        if (! $category || ! $duration) {
            return null;
        }

        if (! self::tablesReady()) {
            return self::configPriceAmount($category, $duration);
        }

        $plan = MarketingPlan::query()
            ->active()
            ->where('label', $duration)
            ->whereHas('package', function ($query) use ($category) {
                $query->active()->where(function ($inner) use ($category) {
                    $inner->where('title', $category)->orWhere('service', $category);
                });
            })
            ->first();

        return $plan?->price;
    }

    public static function formattedPrice(?string $category, ?string $duration): ?string
    {
        $amount = self::priceAmount($category, $duration);

        return $amount !== null ? cebinova_inr($amount) : null;
    }

    public static function months(?string $duration): int
    {
        return match ($duration) {
            'Quarterly' => 3,
            'Half-Yearly' => 6,
            'Yearly' => 12,
            default => 1,
        };
    }

    public static function savingsVsMonthly(?string $category, ?string $duration): ?int
    {
        $monthly = self::priceAmount($category, 'Monthly');
        $price = self::priceAmount($category, $duration);
        $months = self::months($duration);

        if ($monthly === null || $price === null || $months <= 1) {
            return null;
        }

        $saved = ($monthly * $months) - $price;

        return $saved > 0 ? $saved : null;
    }

    public static function originalMonthlyTotal(?string $category, ?string $duration): ?int
    {
        $monthly = self::priceAmount($category, 'Monthly');
        $months = self::months($duration);

        if ($monthly === null || $months <= 1) {
            return null;
        }

        return $monthly * $months;
    }

    public static function effectiveMonthly(?string $category, ?string $duration): ?int
    {
        $price = self::priceAmount($category, $duration);
        $months = self::months($duration);

        if ($price === null || $months < 1) {
            return null;
        }

        return (int) round($price / $months);
    }

    public static function priceHeadline(?string $category, ?string $duration): ?string
    {
        $price = self::priceAmount($category, $duration);

        if ($price === null || ! $duration) {
            return null;
        }

        $period = match ($duration) {
            'Quarterly' => '/3 months',
            'Half-Yearly' => '/6 months',
            'Yearly' => '/year',
            default => '/month',
        };

        $headline = cebinova_inr($price).$period;
        $saved = self::savingsVsMonthly($category, $duration);
        $effective = self::effectiveMonthly($category, $duration);

        if ($saved && $effective && self::months($duration) > 1) {
            return $headline.' · Just '.cebinova_inr($effective).'/month · Save '.cebinova_inr($saved);
        }

        return $headline;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function priceMap(): array
    {
        $map = [];

        foreach (self::categoryKeys() as $label => $key) {
            foreach (self::packageArray($key)['plans'] ?? [] as $plan) {
                if (! isset($plan['label'], $plan['price'])) {
                    continue;
                }
                $map[$label][$plan['label']] = cebinova_inr((int) $plan['price']);
            }
        }

        return $map;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function packageArray(string $key): ?array
    {
        if (! self::tablesReady()) {
            $config = config('cebinova.marketing.'.$key);

            return is_array($config) ? $config : null;
        }

        $package = MarketingPackage::query()
            ->active()
            ->where('key', $key)
            ->with(['plans' => fn ($q) => $q->active()->orderBy('sort_order')])
            ->first();

        if (! $package) {
            return null;
        }

        $plans = [];

        foreach ($package->plans as $plan) {
            $plans[$plan->key] = [
                'label' => $plan->label,
                'duration' => $plan->duration,
                'price' => $plan->price,
                'period' => $plan->period,
                'badge' => $plan->badge,
                'cta' => $plan->cta,
                'note' => $plan->note,
                'includes' => $plan->includes ?? [],
                'monthly_pace' => $plan->monthly_pace,
            ];
        }

        return [
            'key' => $package->key,
            'title' => $package->title,
            'heading' => $package->heading,
            'subheading' => $package->subheading,
            'teaser' => $package->teaser,
            'best_if' => $package->best_if,
            'service' => $package->service,
            'badge' => $package->badge,
            'cta' => $package->cta,
            'secondary_cta' => $package->secondary_cta,
            'why' => $package->why,
            'includes' => $package->includes ?? [],
            'plans' => $plans,
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function catalog(): array
    {
        $catalog = [];

        foreach (self::categoryKeys() as $title => $key) {
            $array = self::packageArray($key);
            if ($array) {
                $catalog[$key] = $array;
            }
        }

        return $catalog;
    }

    private static function tablesReady(): bool
    {
        try {
            return Schema::hasTable('marketing_packages') && Schema::hasTable('marketing_plans');
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array<string, string>
     */
    private static function configCategoryKeys(): array
    {
        return [
            'Regular Marketing' => 'regular',
            'Festival Marketing' => 'festival',
            'Complete Growth' => 'growth',
        ];
    }

    private static function configPriceAmount(string $category, string $duration): ?int
    {
        $group = self::configCategoryKeys()[$category] ?? null;
        if (! $group) {
            return null;
        }

        foreach (config('cebinova.marketing.'.$group.'.plans', []) as $plan) {
            if (($plan['label'] ?? null) === $duration && isset($plan['price'])) {
                return (int) $plan['price'];
            }
        }

        return null;
    }
}
