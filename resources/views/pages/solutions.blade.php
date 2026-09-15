@extends('layouts.app')

@section('title', 'Solutions | SARVIX Technologies')
@section('description', 'SARVIX business solutions for kirana stores, retail, professional services, eCommerce, operations and automation.')

@section('content')
    <x-page-hero
        :wrap="true"
        eyebrow="Solutions"
        title="Business Solutions"
        text="Practical digital solutions for different businesses - with working demos that show what SARVIX can build."
    >
        @include('sections.page.hero-ctas', [
            'consultHref' => consultation_url(),
            'extraHref' => route('demos'),
            'extraLabel' => 'View Demos',
        ])
    </x-page-hero>

    <section class="section-pad section-soft">
        <div class="container-wide">
            <div class="mb-8 flex flex-wrap gap-2" data-filter-group="[data-solution-card]" role="tablist" aria-label="Solution categories">
                <button type="button" class="sx-chip is-active" data-filter="all">All</button>
                @foreach (config('sarvix.business_solutions') as $item)
                    <button type="button" class="sx-chip" data-filter="{{ $item['slug'] }}">{{ $item['title'] }}</button>
                @endforeach
            </div>

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3" data-stagger>
                @foreach (config('sarvix.business_solutions') as $item)
                    <article id="{{ $item['slug'] }}" data-solution-card data-category="{{ $item['slug'] }}" class="lift-card card-surface flex h-full scroll-mt-28 flex-col p-7">
                        <x-badge :tone="$item['label'] === 'LIVE DEMO' ? 'gold' : 'blue'">{{ $item['label'] }}</x-badge>
                        <h2 class="mt-3 text-navy">{{ $item['title'] }}</h2>
                        <p class="mt-3 flex-1 text-[15.5px] leading-relaxed text-muted">{{ $item['summary'] }}</p>
                        <ul class="mt-5 space-y-2">
                            @foreach ($item['capabilities'] as $capability)
                                <li class="text-[15px] text-navy/80 before:mr-2 before:text-gold before:content-['•']">{{ $capability }}</li>
                            @endforeach
                        </ul>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <x-button href="{{ solution_page_url($item) }}" variant="outline" size="sm">Explore</x-button>
                            @if ($item['demo'])
                                <x-button href="{{ demo_url($item['demo']) }}" size="sm" class="group">
                                    View Demo
                                    <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                                </x-button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section-pad bg-white">
        <div class="container-wide space-y-6">
            <x-section-heading eyebrow="Growth path" title="Start With What Your Business Needs Today.">
                These paths stay available as a growth sequence. Business solutions above are how that work looks in practice.
            </x-section-heading>
            <p class="text-[15px] font-semibold text-navy/65">Starter → Sell Online → Manage → Automate &amp; Scale</p>
            @foreach (config('sarvix.solution_paths') as $index => $path)
                @php $highlight = $path['key'] === 'automate'; @endphp
                <article id="{{ $path['key'] }}" class="lift-card card-surface scroll-mt-28 p-8 lg:p-10 {{ $highlight ? 'border-gold/50' : '' }}">
                    <div class="grid gap-8 lg:grid-cols-12">
                        <div class="lg:col-span-4">
                            <div class="flex items-center gap-3">
                                <span class="flex h-11 w-11 items-center justify-center rounded-full border-2 {{ $highlight ? 'border-gold bg-gold text-navy-deep' : 'border-gold bg-white text-navy' }} text-sm font-extrabold">0{{ $index + 1 }}</span>
                            </div>
                            <h2 class="mt-4 text-navy">{{ $path['title'] }}</h2>
                            <p class="mt-4 text-[16px] leading-relaxed text-muted">{{ $path['for'] }}</p>
                        </div>
                        <ul class="grid gap-3 sm:grid-cols-2 lg:col-span-8">
                            @foreach ($path['includes'] as $item)
                                <li class="rounded-xl border border-line bg-mist px-4 py-4 text-[15px] font-medium text-navy">{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="section-pad section-soft">
        <div class="container-wide">
            <x-section-heading eyebrow="Industries" title="Built for the way different businesses operate.">
                Detailed industry notes stay on the industries page. Start here, then tell us how you work.
            </x-section-heading>
            <div class="mt-8 flex flex-wrap gap-2.5">
                @foreach (config('sarvix.nav_solutions.industries') as $item)
                    <a href="{{ route('industries') }}#{{ $item['slug'] }}" class="sx-chip">{{ $item['label'] }}</a>
                @endforeach
            </div>
            <div class="mt-8">
                <x-button href="{{ route('industries') }}" variant="outline">View All Industries</x-button>
            </div>
        </div>
    </section>

    @include('sections.page.how-it-works')
    @include('sections.page.faq')
    @include('sections.home.cta')
@endsection
