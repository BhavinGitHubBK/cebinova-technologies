<?php

namespace App\Http\Controllers;

use App\Models\Service;
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
        $service = collect($this->publicServices())->firstWhere('slug', $slug);
        abort_unless($service, 404);

        return view('pages.services.show', compact('service'));
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
        ]);
    }

    public function marketingPackages(): View
    {
        return view('pages.marketing-packages');
    }

    public function portfolio(): View
    {
        return view('pages.portfolio');
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
}
