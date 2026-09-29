<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Support\MarketingPackages;
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
        if (Schema::hasTable('services') && Service::query()->exists()) {
            $record = Service::query()
                ->active()
                ->where('slug', $slug)
                ->with($this->publicServiceRelations())
                ->firstOrFail();

            $service = $record->toPublicArray();
            $relatedServices = Service::query()
                ->active()
                ->whereKeyNot($record->id)
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->limit(3)
                ->get()
                ->map(fn (Service $related) => $related->toPublicArray())
                ->all();
        } else {
            $services = config('cebinova.services', []);
            $service = collect($services)->firstWhere('slug', $slug);
            abort_unless($service, 404);
            $guide = config('cebinova.service_guides.'.$slug, []);
            $service = array_merge($service, $guide, [
                'hero_title' => $service['title'],
                'hero_subtitle' => $service['summary'],
                'audience' => $guide['best_for'] ?? null,
                'seo_title' => null,
                'seo_description' => null,
                'sections' => [[
                    'type' => 'process',
                    'title' => config('cebinova.page.how_title'),
                    'subtitle' => config('cebinova.page.how_text'),
                    'items' => collect(config('cebinova.process', []))->map(fn ($item) => [
                        'title' => $item['title'],
                        'description' => $item['text'],
                        'value' => $item['step'] ?? null,
                    ])->all(),
                ]],
                'faq' => $guide['faq'] ?? [],
            ]);
            $relatedServices = collect($services)->where('slug', '!=', $slug)->take(3)->values()->all();
        }

        return view('pages.services.show', compact('service', 'relatedServices'));
    }

    public function solutions(): View
    {
        return view('pages.solutions');
    }

    public function solution(string $slug): View
    {
        $solution = collect(config('cebinova.business_solutions'))->firstWhere('slug', $slug);
        abort_unless($solution, 404);

        return view('pages.solutions.show', compact('solution'));
    }

    public function demos(): View
    {
        return view('demos.index');
    }

    public function industries(): View
    {
        return view('pages.industries');
    }

    public function pricing(): View
    {
        return view('pages.pricing', [
            'kiranaPlans' => WebsitePackages::plans(),
            'kiranaAddons' => WebsitePackages::addons(),
            'pricingOptions' => WebsitePackages::options(),
        ]);
    }

    public function marketingPackages(): View
    {
        return view('pages.marketing-packages', [
            'marketingCatalog' => MarketingPackages::catalog(),
        ]);
    }

    public function contact(): View
    {
        $selectedServiceRecord = request()->filled('service_id')
            ? Service::query()->active()->find(request()->integer('service_id'))
            : null;

        return view('pages.contact', compact('selectedServiceRecord'));
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
            if (Schema::hasTable('services') && Service::query()->exists()) {
                return Service::query()
                    ->active()
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->with($this->publicServiceRelations())
                    ->get()
                    ->map(fn (Service $service) => $service->toPublicArray())
                    ->all();
            }
        } catch (\Throwable) {
            // fall through to config
        }

        return config('cebinova.services', []);
    }

    private function publicServiceRelations(): array
    {
        return [
            'sections' => fn ($query) => $query->active()->with(['items' => fn ($items) => $items->active()]),
            'faqs' => fn ($query) => $query->active(),
        ];
    }
}
