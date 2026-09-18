<?php

namespace App\Support;

class MarketingPackages
{
    public const SERVICE = 'Digital Marketing';

    /**
     * @return array<string, string>
     */
    public static function categoryKeys(): array
    {
        return [
            'Regular Marketing' => 'regular',
            'Festival Marketing' => 'festival',
            'Complete Growth' => 'growth',
        ];
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
        return config('cebinova.marketing.frequencies', ['Monthly', 'Quarterly', 'Half-Yearly', 'Yearly']);
    }

    public static function isValid(string $category, string $duration): bool
    {
        return self::priceAmount($category, $duration) !== null;
    }

    public static function priceAmount(?string $category, ?string $duration): ?int
    {
        $group = self::categoryKeys()[$category] ?? null;
        if (! $group || ! $duration) {
            return null;
        }

        foreach (config('cebinova.marketing.'.$group.'.plans', []) as $plan) {
            if (($plan['label'] ?? null) === $duration && isset($plan['price'])) {
                return (int) $plan['price'];
            }
        }

        return null;
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
            foreach (config('cebinova.marketing.'.$key.'.plans', []) as $plan) {
                if (! isset($plan['label'], $plan['price'])) {
                    continue;
                }
                $map[$label][$plan['label']] = cebinova_inr((int) $plan['price']);
            }
        }

        return $map;
    }
}
