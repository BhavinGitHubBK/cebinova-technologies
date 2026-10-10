<?php

namespace App\Services;

use App\Models\Demo;
use App\Models\Industry;
use App\Models\Package;
use App\Models\PageSection;
use App\Models\Service;
use App\Models\Solution;
use App\Support\MarketingPackages;
use App\Support\WebsitePackages;
use Illuminate\Support\Facades\Schema;

class HomepageBuilder
{
    public static function homeData(): array
    {
        return [
            'sections' => self::orderedSections(),
            'services' => self::featuredServices(),
            'solutions' => self::featuredSolutions(),
            'industries' => self::featuredIndustries(),
            'demos' => self::featuredDemos(),
            'pricing' => self::pricingHighlights(),
            'marketing' => self::marketingHighlights(),
            'proof' => self::proofStats(),
            'problems' => self::problemOptions(),
            'packages' => self::featuredPackages(),
        ];
    }

    public static function orderedSections(): array
    {
        if (! self::hasTable('page_sections')) {
            return [];
        }

        return PageSection::query()
            ->where('page', 'home')
            ->visible()
            ->ordered()
            ->get()
            ->all();
    }

    public static function featuredServices(int $limit = 6): array
    {
        if (! self::hasTable('services')) {
            return collect(config('cebinova.services', []))->take($limit)->values()->all();
        }

        return Service::query()
            ->active()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(fn (Service $service) => $service->toPublicArray())
            ->all();
    }

    public static function featuredSolutions(int $limit = 4): array
    {
        if (! self::hasTable('solutions')) {
            return collect(config('cebinova.business_solutions', []))->take($limit)->values()->all();
        }

        return Solution::query()
            ->active()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit($limit)
            ->with(['sections' => fn ($query) => $query->active()->with(['items' => fn ($items) => $items->active()]), 'services' => fn ($query) => $query->active(), 'industries' => fn ($query) => $query->active()])
            ->get()
            ->map(fn (Solution $solution) => $solution->toPublicArray())
            ->all();
    }

    public static function featuredIndustries(int $limit = 6): array
    {
        if (! self::hasTable('industries')) {
            return collect(config('cebinova.industries', []))->take($limit)->values()->all();
        }

        return Industry::query()
            ->active()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit($limit)
            ->with('solutions')
            ->get()
            ->map(fn (Industry $industry) => $industry->toPublicArray(false))
            ->all();
    }

    public static function featuredDemos(int $limit = 4): array
    {
        if (! self::hasTable('demos')) {
            return collect(config('cebinova.business_solutions', []))->filter(fn ($item) => ! empty($item['demo']))->take($limit)->values()->all();
        }

        return Demo::query()
            ->active()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit($limit)
            ->with(['industries' => fn ($query) => $query->active(), 'solutions' => fn ($query) => $query->active(), 'services' => fn ($query) => $query->active()])
            ->get()
            ->map(function (Demo $demo): array {
                return [
                    'id' => $demo->id,
                    'slug' => $demo->slug,
                    'title' => $demo->name,
                    'summary' => $demo->short_description,
                    'badge' => $demo->badge,
                    'preview' => $demo->thumbnail,
                    'demo' => $demo->local_demo,
                    'live_url' => $demo->liveLink(),
                    'industry' => $demo->solutions->first()?->category ?: $demo->industries->first()?->name,
                    'label' => $demo->badge,
                    'capabilities' => collect($demo->features ?? [])
                        ->filter(fn ($feature) => is_string($feature) || (is_array($feature) && ($feature['is_active'] ?? false)))
                        ->map(fn ($feature) => is_string($feature) ? $feature : ($feature['name'] ?? ''))
                        ->filter()->values()->all(),
                    'services' => $demo->services->map(fn ($service) => $service->toPublicArray())->all(),
                    'solutions' => $demo->solutions->map(fn ($solution) => $solution->toPublicArray())->all(),
                    'industries' => $demo->industries->map(fn ($industry) => $industry->toPublicArray(false))->all(),
                ];
            })
            ->all();
    }

    public static function pricingHighlights(): array
    {
        return [
            'featured' => collect(config('cebinova.marketing', []))->all() ?: [],
        ];
    }

    public static function marketingHighlights(): array
    {
        $packages = [];

        foreach (['regular', 'festival', 'growth'] as $key) {
            $config = MarketingPackages::packageArray($key);
            if (is_array($config) && ! empty($config['title'])) {
                $packages[] = [
                    'key' => $key,
                    'title' => $config['title'],
                    'teaser' => $config['teaser'] ?? '',
                    'plans' => $config['plans'] ?? [],
                    'href' => route('marketing-packages').'#'.$key,
                ];
            }
        }

        return $packages;
    }

    /**
     * @return list<array{key: string, value: string, label: string}>
     */
    public static function proofStats(): array
    {
        $admin = self::adminProof();
        $stats = [];

        foreach (['projects', 'industries', 'years', 'demos', 'support'] as $key) {
            if ($key === 'industries') {
                $count = self::hasTable('industries') ? Industry::query()->active()->count() : 0;
                if ($count > 0) {
                    $stats[] = ['key' => 'industries', 'value' => (string) $count, 'label' => 'Industries served'];
                }

                continue;
            }

            if ($key === 'demos') {
                $count = self::hasTable('demos') ? Demo::query()->active()->count() : 0;
                if ($count > 0) {
                    $stats[] = ['key' => 'demos', 'value' => (string) $count, 'label' => 'Demo projects'];
                }

                continue;
            }

            $value = trim((string) ($admin[$key]['value'] ?? ''));
            if ($value === '') {
                continue;
            }

            $stats[] = [
                'key' => $key,
                'value' => $value,
                'label' => $admin[$key]['label'],
            ];
        }

        return $stats;
    }

    /**
     * @return list<array{id: int, name: string, problem: string, outcome: string, href: string}>
     */
    public static function problemOptions(): array
    {
        if (! self::hasTable('solutions')) {
            return [];
        }

        $options = Solution::query()
            ->active()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (Solution $solution): ?array {
                $name = trim((string) $solution->name);
                $outcome = trim((string) $solution->outcome);
                $problem = trim((string) ($solution->audience ?: $solution->hero_subtitle ?: $solution->short_description));

                if ($name === '' || $outcome === '' || $problem === '' || blank($solution->slug)) {
                    return null;
                }

                return [
                    'id' => $solution->id,
                    'name' => $name,
                    'problem' => $problem,
                    'outcome' => $outcome,
                    'href' => route('solutions.show', $solution->slug),
                ];
            })
            ->filter()
            ->take(4)
            ->values();

        return $options->count() >= 2 ? $options->all() : [];
    }

    /**
     * @return list<array{title: string, summary: string, href: string}>
     */
    public static function featuredPackages(): array
    {
        $href = route('pricing').'#plans';

        if (self::hasTable('packages')) {
            $packages = Package::query()
                ->website()
                ->active()
                ->orderByDesc('is_highlighted')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(3)
                ->get();

            if ($packages->isNotEmpty()) {
                $packages->load(['plans' => fn ($query) => $query->active()->orderBy('sort_order')]);

                return $packages->map(function (Package $package) use ($href) {
                    $plan = $package->plans->first();
                    $features = collect($plan?->features ?: ($package->includes ?? []))
                        ->map(fn ($feature) => is_array($feature) ? (string) ($feature['name'] ?? $feature['title'] ?? '') : (string) $feature)
                        ->filter()
                        ->take(6)
                        ->values()
                        ->all();

                    return [
                        'title' => $package->name,
                        'summary' => trim((string) ($package->short_description ?: $package->teaser)),
                        'href' => $href,
                        'price' => (int) ($plan?->price ?? 0),
                        'period' => (string) ($plan?->period ?? ''),
                        'badge' => (string) ($package->badge ?: ($plan?->badge ?? '')),
                        'features' => $features,
                        'highlighted' => (bool) $package->is_highlighted,
                    ];
                })->all();
            }
        }

        return collect(WebsitePackages::plans())
            ->take(3)
            ->map(fn ($plan) => [
                'title' => (string) ($plan['title'] ?? ''),
                'summary' => trim((string) ($plan['for'] ?? ($plan['insight'] ?? ''))),
                'href' => $href,
                'price' => (int) ($plan['price'] ?? 0),
                'period' => (string) ($plan['period'] ?? ''),
                'badge' => (string) ($plan['badge'] ?? ''),
                'features' => array_slice(array_values(array_filter($plan['features'] ?? [], 'is_string')), 0, 6),
                'highlighted' => ($plan['badge'] ?? '') === 'Most Popular',
            ])
            ->filter(fn (array $plan) => $plan['title'] !== '')
            ->values()
            ->all();
    }

    /**
     * @return array{projects: array{value: string, label: string}, years: array{value: string, label: string}, support: array{value: string, label: string}}
     */
    private static function adminProof(): array
    {
        $defaults = [
            'projects' => ['value' => '', 'label' => 'Projects delivered'],
            'years' => ['value' => '', 'label' => 'Years of experience'],
            'support' => ['value' => '', 'label' => 'Support commitment'],
        ];

        if (! self::hasTable('page_sections')) {
            return $defaults;
        }

        $section = PageSection::query()->visible()->where('page', 'home')->where('key', 'proof')->first();

        foreach ($section?->settings['proof'] ?? [] as $item) {
            if (! is_array($item) || ! isset($defaults[$item['key'] ?? ''])) {
                continue;
            }

            $key = (string) $item['key'];
            $label = trim((string) ($item['label'] ?? ''));
            $defaults[$key] = [
                'value' => trim((string) ($item['value'] ?? '')),
                'label' => $label !== '' ? $label : $defaults[$key]['label'],
            ];
        }

        return $defaults;
    }

    private static function hasTable(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
