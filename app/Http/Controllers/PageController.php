<?php

namespace App\Http\Controllers;

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
            'services' => config('sarvix.services'),
        ]);
    }

    public function service(string $slug): View
    {
        $service = collect(config('sarvix.services'))->firstWhere('slug', $slug);
        abort_unless($service, 404);

        return view('pages.services.show', compact('service'));
    }

    public function solutions(): View
    {
        return view('pages.solutions');
    }

    public function solution(string $slug): View
    {
        $solution = collect(config('sarvix.business_solutions'))->firstWhere('slug', $slug);
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
        return view('pages.pricing');
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
}
