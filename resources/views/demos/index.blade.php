@extends('layouts.app')

@section('title', 'SARVIX Business Demos | SARVIX Technologies')
@section('description', 'Explore practical SARVIX solution demos designed for different industries and business requirements.')

@section('content')
    <x-page-hero
        :wrap="true"
        eyebrow="SARVIX SOLUTIONS"
        title="See What SARVIX Can Build for Your Business."
        text="Explore practical digital solution demos created for real business categories and requirements. SARVIX does not only talk about services - here are working demos of what we can build."
    >
        @include('sections.page.hero-ctas', [
            'consultHref' => consultation_url(),
            'extraHref' => route('solutions'),
            'extraLabel' => 'Explore All Solutions',
        ])
    </x-page-hero>

    <section class="section-pad section-soft">
        <div class="container-wide">
            <x-section-heading eyebrow="Live demos" title="Business Solutions Built for Real Businesses">
                These are live solution demos and concepts. They are not presented as live client case studies.
            </x-section-heading>

            <div class="mt-8 flex flex-wrap gap-2" data-filter-group="[data-demo-card]">
                <button type="button" class="sx-chip is-active" data-filter="all">All</button>
                @foreach (config('sarvix.business_solutions') as $item)
                    <button type="button" class="sx-chip" data-filter="{{ $item['slug'] }}">{{ $item['title'] }}</button>
                @endforeach
            </div>

            <div class="mt-11 grid gap-6 md:grid-cols-2 xl:grid-cols-3" data-stagger>
                @foreach (config('sarvix.business_solutions') as $item)
                    <article data-demo-card data-category="{{ $item['slug'] }}" class="demo-card lift-card card-surface flex h-full flex-col overflow-hidden">
                        <div class="relative overflow-hidden">
                            @if ($item['preview'])
                                <img src="{{ asset('assets/'.$item['preview']) }}" alt="" class="h-52 w-full object-cover" loading="lazy">
                            @else
                                <x-demo-preview :type="$item['slug'] === 'business-management' ? 'crm' : 'ai'" />
                            @endif
                            <span class="absolute left-3 top-3">
                                <x-badge :tone="$item['label'] === 'LIVE DEMO' ? 'gold' : 'grey'">{{ $item['label'] }}</x-badge>
                            </span>
                        </div>
                        <div class="flex flex-1 flex-col p-6 sm:p-7">
                            <p class="text-[12px] font-bold uppercase tracking-[0.14em] text-navy/40">{{ $item['industry'] }}</p>
                            <h2 class="mt-1 text-xl text-navy">SARVIX {{ $item['title'] }} Solution</h2>
                            <p class="mt-3 flex-1 text-[15.5px] leading-relaxed text-muted">{{ $item['summary'] }}</p>
                            <ul class="mt-4 flex flex-wrap gap-2">
                                @foreach ($item['capabilities'] as $capability)
                                    <li class="sx-chip">{{ $capability }}</li>
                                @endforeach
                            </ul>
                            <div class="mt-6 flex flex-wrap gap-3">
                                @if ($item['demo'])
                                    <x-button href="{{ demo_url($item['demo']) }}" size="sm" class="group">
                                        View Demo
                                        <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                                    </x-button>
                                @endif
                                <x-button href="{{ solution_page_url($item) }}" variant="outline" size="sm">View solution</x-button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    @include('sections.page.how-it-works')
    @include('sections.page.faq')
    @include('sections.home.cta')
@endsection
