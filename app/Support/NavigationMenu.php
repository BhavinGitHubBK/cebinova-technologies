<?php

namespace App\Support;

use App\Models\Service;
use App\Services\Promotions\PromotionEngine;
use Illuminate\Support\Carbon;

class NavigationMenu
{
    /**
     * @return list<array{name: string, slug: string, icon: string, summary: string|null}>
     */
    public static function services(): array
    {
        try {
            return Service::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['name', 'slug', 'icon', 'short_description'])
                ->map(fn (Service $service) => [
                    'name' => $service->name,
                    'slug' => $service->slug,
                    'icon' => $service->icon ?: 'layers',
                    'summary' => $service->short_description,
                ])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return list<array{name: string, slug: string, discount: string, occasion: string|null, ends: string|null}>
     */
    public static function offers(): array
    {
        try {
            $offers = app(PromotionEngine::class)->surface()['offers'] ?? [];
        } catch (\Throwable) {
            return [];
        }

        return collect($offers)
            ->reject(fn (array $offer) => $offer['upcoming'] ?? false)
            ->sortBy('ends_at')
            ->map(fn (array $offer) => [
                'name' => $offer['name'],
                'slug' => $offer['slug'],
                'discount' => (string) ($offer['discount_label'] ?? ''),
                'occasion' => $offer['festival_name'] ?: ($offer['type_label'] ?? null),
                'ends' => $offer['ends_at'] ? Carbon::parse($offer['ends_at'])->format('j M') : null,
            ])
            ->values()
            ->all();
    }
}
