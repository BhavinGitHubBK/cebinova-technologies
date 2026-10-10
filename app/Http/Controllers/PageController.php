<?php

namespace App\Http\Controllers;

use App\Models\Demo;
use App\Models\Industry;
use App\Models\Service;
use App\Models\ServiceSection;
use App\Models\ServiceSectionItem;
use App\Models\Solution;
use App\Support\WebsitePackages;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home');
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function services(): View
    {
        return view('pages.services.index', [
            'services' => $this->publicServices(),
        ]);
    }

    public function service(string $slug): View
    {
        $services = $this->publicServices();
        $service = collect($services)->firstWhere('slug', $slug);
        abort_unless($service, 404);

        $guide = config('cebinova.service_guides.'.$slug, []);
        $service['hero_title'] = $service['hero_title'] ?? $service['title'];
        $service['hero_subtitle'] = $service['hero_subtitle'] ?? ($service['short'] ?? '');
        $service['audience'] = $service['audience'] ?? ($guide['best_for'] ?? null);
        $service['outcome'] = $service['outcome'] ?? ($guide['outcome'] ?? null);
        $service['next_label'] = $service['next_label'] ?? ($guide['next_label'] ?? 'Get Free Consultation');
        $service['next_route'] = $service['next_route'] ?? ($guide['next_route'] ?? null);
        $service['next_param'] = $service['next_param'] ?? ($guide['next_param'] ?? null);
        $service['faq'] = $guide['faq'] ?? [];
        $service['sections'] = $this->serviceSections($slug);

        $relatedServices = collect($services)
            ->reject(fn (array $item) => $item['slug'] === $slug)
            ->take(3)
            ->values()
            ->all();

        return view('pages.services.show', compact('service', 'relatedServices'));
    }

    public function solutions(): View
    {
        return view('pages.solutions');
    }

    public function solution(string $slug): View
    {
        $solution = collect(config('cebinova.business_solutions'))->firstWhere('slug', $slug);
        $record = $this->activeSolution($slug);

        abort_unless($solution || $record, 404);

        if ($record) {
            $public = $record->toPublicArray();
            $solution = array_merge($solution ?? [
                'slug' => $slug,
                'title' => $public['title'],
                'heading' => $public['heading'] ?: $public['title'],
                'summary' => $public['summary'] ?: $record->short_description,
                'capabilities' => [],
                'demo' => null,
                'preview' => null,
                'source' => 'CEBINOVA Solutions',
                'business_type' => $public['business_type'] ?: $public['title'],
            ], array_filter([
                'title' => $public['title'],
                'heading' => $public['heading'] ?: null,
                'summary' => $public['summary'] ?: null,
                'demo' => $public['demo'] ?: null,
                'business_type' => $public['business_type'] ?: null,
                'audience' => $public['audience'] ?: null,
                'outcome' => $public['outcome'] ?: null,
            ], fn ($value) => filled($value)));

            if ($public['capabilities'] !== []) {
                $solution['capabilities'] = $public['capabilities'];
            }
            if (filled($record->description)) {
                $solution['problem'] = $record->description;
            }
            if (filled($public['preview']) && ! str_contains((string) $public['preview'], '://')) {
                $solution['preview'] = ltrim((string) $public['preview'], '/');
            }
            $solution['sections'] = collect($public['sections'])
                ->reject(fn (array $section) => $section['type'] === 'features')
                ->values()
                ->all();
            $solution['related_industries'] = collect($public['industries'])
                ->filter(fn (array $industry) => filled($industry['slug'] ?? null))
                ->values()
                ->all();
        }

        $solution['source'] = $solution['source'] ?? 'CEBINOVA Solutions';
        $solution['business_type'] = $solution['business_type'] ?? ($solution['title'] ?? 'Business');
        $solution['capabilities'] = $solution['capabilities'] ?? [];
        $solution['sections'] = $solution['sections'] ?? [];
        $solution['related_industries'] = $solution['related_industries'] ?? [];

        return view('pages.solutions.show', compact('solution'));
    }

    public function demos(): View
    {
        $demos = Demo::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->with(['industries', 'solutions'])
            ->get();

        return view('demos.index', compact('demos'));
    }

    public function industries(): View
    {
        return view('pages.industries');
    }

    public function industry(string $slug): View
    {
        $config = collect(config('cebinova.industries'))->firstWhere('slug', $slug);
        $record = $this->activeIndustry($slug);

        if ($record && ! $record->is_active) {
            abort(404);
        }

        abort_unless($record || $config, 404);

        $need = trim((string) ($record?->short_description ?: ($config['need'] ?? '')));
        $description = trim((string) ($record?->description ?: $need));
        abort_if($need === '' && $description === '', 404);

        $publicSolutions = ['retail', 'professional-services', 'ecommerce', 'business-management', 'ai-automation'];
        $solutions = $record
            ? $record->solutions->map(fn (Solution $solution) => [
                'title' => $solution->name,
                'slug' => in_array($solution->slug, $publicSolutions, true) ? $solution->slug : null,
            ])->all()
            : collect($config['solutions'] ?? [])->map(fn ($item) => ['title' => $item, 'slug' => null])->all();

        if ($solutions === [] && ! empty($config['solutions'])) {
            $solutions = collect($config['solutions'])->map(fn ($item) => ['title' => $item, 'slug' => null])->all();
        }

        $allowedDemos = ['kirana', 'professional-services', 'jewellery-retail'];
        $demos = $record
            ? $record->demos->map(function (Demo $demo) use ($allowedDemos) {
                $local = in_array($demo->local_demo, $allowedDemos, true) ? $demo->local_demo : null;

                return [
                    'name' => $demo->name,
                    'url' => $local ? route('demos.show', ['demo' => $local]) : ($demo->live_url ?: null),
                ];
            })->filter(fn (array $demo) => filled($demo['url']))->values()->all()
            : [];

        $industry = [
            'slug' => $slug,
            'title' => $record?->hero_title ?: $record?->name ?: $config['title'],
            'need' => $need,
            'description' => $description,
            'solutions' => $solutions,
            'demos' => $demos,
            'seo_title' => $record?->seo_title,
            'seo_description' => $record?->seo_description,
        ];

        return view('pages.industries.show', compact('industry'));
    }

    public function pricing(): View
    {
        return view('pages.pricing', [
            'kiranaPlans' => WebsitePackages::plans(),
            'kiranaAddons' => WebsitePackages::addons(),
            'pricingChoices' => WebsitePackages::commercialChoices(),
            'gstPercent' => (int) config('cebinova.pricing.gst_percent', 18),
        ]);
    }

    public function marketingPackages(): View
    {
        return view('pages.marketing-packages');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function privacy(): View
    {
        return view('pages.legal.privacy');
    }

    public function terms(): View
    {
        return view('pages.legal.terms');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function publicServices(): array
    {
        try {
            if (Schema::hasTable('services') && Service::query()->active()->exists()) {
                return Service::query()
                    ->active()
                    ->orderBy('sort_order')
                    ->get()
                    ->map(fn (Service $service) => $service->toPublicArray())
                    ->all();
            }
        } catch (\Throwable) {
            // fall through to config
        }

        return config('cebinova.services', []);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function serviceSections(string $slug): array
    {
        try {
            if (! Schema::hasTable('service_sections') || ! Schema::hasTable('services')) {
                return [];
            }

            $model = Service::query()
                ->where('slug', $slug)
                ->with(['sections' => fn ($query) => $query->active()->orderBy('sort_order'), 'sections.items'])
                ->first();
        } catch (\Throwable) {
            return [];
        }

        if (! $model) {
            return [];
        }

        return $model->sections->map(fn (ServiceSection $section) => [
            'type' => $section->type,
            'title' => $section->title,
            'subtitle' => $section->subtitle,
            'content' => $section->content,
            'items' => $section->items->where('is_active', true)->map(fn (ServiceSectionItem $item) => [
                'title' => $item->title,
                'description' => $item->description,
                'value' => $item->value,
            ])->values()->all(),
        ])->all();
    }

    private function activeSolution(string $slug): ?Solution
    {
        try {
            if (! Schema::hasTable('solutions')) {
                return null;
            }

            return Solution::query()
                ->active()
                ->where('slug', $slug)
                ->with(['sections.items', 'services', 'industries'])
                ->first();
        } catch (\Throwable) {
            return null;
        }
    }

    private function activeIndustry(string $slug): ?Industry
    {
        try {
            if (! Schema::hasTable('industries')) {
                return null;
            }

            return Industry::query()
                ->where('slug', $slug)
                ->with(['solutions' => fn ($query) => $query->active(), 'demos' => fn ($query) => $query->active()])
                ->first();
        } catch (\Throwable) {
            return null;
        }
    }
}
