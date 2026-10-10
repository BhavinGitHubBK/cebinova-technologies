<?php

namespace App\Http\Controllers;

use App\Models\Demo;
use App\Models\PromotionCampaign;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [
            route('home'),
            route('about'),
            route('services.index'),
            route('solutions'),
            route('industries'),
            route('marketing-packages'),
            route('pricing'),
            route('offers.index'),
            route('demos'),
            route('contact'),
            route('privacy'),
            route('terms'),
        ];

        foreach ([
            'web-development',
            'ecommerce',
            'custom-software',
            'ai-automation',
            'digital-business',
            'digital-growth',
        ] as $slug) {
            $urls[] = route('services.show', $slug);
        }

        foreach ([
            'retail',
            'professional-services',
            'ecommerce',
            'business-management',
            'ai-automation',
        ] as $slug) {
            $urls[] = route('solutions.show', $slug);
        }

        foreach (config('cebinova.industries', []) as $industry) {
            if (filled($industry['slug'] ?? null) && filled($industry['need'] ?? null)) {
                $urls[] = route('industries.show', $industry['slug']);
            }
        }

        try {
            if (Schema::hasTable('demos')) {
                $localDemos = Demo::query()
                    ->active()
                    ->whereIn('local_demo', ['kirana', 'professional-services', 'jewellery-retail'])
                    ->orderBy('sort_order')
                    ->pluck('local_demo');

                foreach ($localDemos as $demo) {
                    $urls[] = route('demos.show', ['demo' => $demo]);
                }
            }

            if (Schema::hasTable('promotion_campaigns')) {
                $offers = PromotionCampaign::query()
                    ->where('publication_status', 'published')
                    ->get();
                foreach ($offers as $offer) {
                    $preview = $offer->show_before_start && $offer->starts_at && now()->gte($offer->starts_at->copy()->subDays((int) $offer->preview_days)) && now()->lt($offer->starts_at);
                    if ($offer->state() === 'active' || ($offer->state() === 'scheduled' && $preview)) {
                        $urls[] = route('offers.show', $offer->slug);
                    }
                }
            }
        } catch (\Throwable) {
            // Public pages above still belong in the sitemap.
        }

        return response()
            ->view('sitemap', ['urls' => array_values(array_unique($urls))])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
