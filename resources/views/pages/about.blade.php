@extends('layouts.app')

@section('title', 'About SARVIX Technologies | Technology for Every Business')
@section('description', 'SARVIX Technologies helps businesses of every size use technology to start, operate, automate and grow - as one technology partner for the complete business journey.')

@section('content')
    <x-page-hero
        :wrap="true"
        eyebrow="About"
        title="Technology Built Around Your Business."
        text="SARVIX Technologies is a complete technology partner for businesses that want to move from traditional operations to smart digital systems - without buying more than they need."
    >
        @include('sections.page.hero-ctas', [
            'consultHref' => consultation_url(),
        ])
    </x-page-hero>

    <section class="section-pad bg-white">
        <div class="container-wide grid gap-12 lg:grid-cols-12">
            <div class="lg:col-span-5" data-reveal>
                <x-section-heading eyebrow="Who we are" title="Who We Are">
                    We help businesses of every size use technology to start, operate, automate and grow.
                </x-section-heading>
            </div>
            <div class="space-y-5 text-base leading-relaxed text-muted lg:col-span-7" data-reveal>
                <p>SARVIX Technologies provides websites, eCommerce, custom software, AI automation, digital business systems and marketing support from one team.</p>
                <p>The idea is simple: <span class="font-semibold text-navy">Small Business → Digital Business → Growing Business</span>. You should not need a different vendor at every stage.</p>
                <p class="font-semibold text-navy">{{ config('sarvix.positioning') }}</p>
            </div>
        </div>
    </section>

    <section class="section-pad section-soft">
        <div class="container-wide">
            <x-section-heading eyebrow="Why SARVIX" title="Why businesses work with one technology partner." />
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-stagger>
                @php $whyIcons = ['nodes', 'layers', 'briefcase', 'trend', 'check', 'shield']; @endphp
                @foreach (config('sarvix.why') as $index => $item)
                    <article class="lift-card card-surface p-6 sm:p-7">
                        <span class="icon-tile mb-4">
                            <x-icon :name="$whyIcons[$index] ?? 'check'" class="h-5 w-5" />
                        </span>
                        <h3 class="text-navy">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-[15.5px] leading-relaxed text-muted">{{ $item['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section-pad bg-white">
        <div class="container-wide">
            <x-section-heading eyebrow="Our approach" title="Our Approach" />
            <div class="mt-10 grid gap-5 md:grid-cols-3" data-stagger>
                @foreach ([
                    ['title' => 'Business first', 'text' => 'We start with how you sell, serve customers and run operations today.'],
                    ['title' => 'Right-sized technology', 'text' => 'No unnecessary features. Start with what you need now, then scale.'],
                    ['title' => 'One partner', 'text' => 'Web, commerce, software, AI and marketing stay connected as you grow.'],
                ] as $item)
                    <article class="lift-card card-surface p-7">
                        <h3 class="text-navy">{{ $item['title'] }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-muted">{{ $item['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section-pad section-soft">
        <div class="container-wide">
            <x-section-heading eyebrow="Business journey" title="A connected path from presence to growth.">
                Five stages. One partner. You start where the business is today.
            </x-section-heading>
            <div class="mt-10 grid gap-4 md:grid-cols-5" data-stagger>
                @foreach (config('sarvix.journey') as $item)
                    <article class="lift-card card-surface p-5 sm:p-6">
                        <x-badge tone="blue">{{ $item['step'] }}</x-badge>
                        <h3 class="mt-4 text-[1.05rem] text-navy">{{ $item['title'] }}</h3>
                        <ul class="mt-3 space-y-1.5">
                            @foreach ($item['items'] as $line)
                                <li class="text-[13.5px] text-muted">{{ $line }}</li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section-pad bg-white">
        <div class="container-wide grid gap-6 md:grid-cols-2" data-stagger>
            <article class="lift-card card-surface p-8">
                <x-badge>Vision</x-badge>
                <h2 class="mt-4 text-navy">Our Vision</h2>
                <p class="mt-4 leading-relaxed text-muted">To become the technology partner businesses trust for their complete digital journey - and, over time, a connected SARVIX platform for commerce, operations, AI and automation.</p>
            </article>
            <article class="lift-card card-surface p-8">
                <x-badge tone="blue">Mission</x-badge>
                <h2 class="mt-4 text-navy">Our Mission</h2>
                <p class="mt-4 leading-relaxed text-muted">Understand the business first. Then build the right website, store, software or automation - so technology serves the work, not the other way around.</p>
            </article>
        </div>
    </section>

    <section class="section-pad section-soft">
        <div class="container-wide max-w-3xl" data-reveal>
            <x-section-heading eyebrow="Technology philosophy" title="Technology for Every Business.">
                From kirana stores and clinics to manufacturers and startups, the need is the same: a digital presence, working systems, and a path to grow. We recommend what the business can actually use - not a larger stack than it needs.
            </x-section-heading>
        </div>
    </section>

    <section class="section-pad bg-navy">
        <div class="container-wide">
            <x-section-heading :wrap="true" light eyebrow="Future vision" title="Future Vision">
                SARVIX Commerce, SARVIX Business, SARVIX AI, SARVIX Automate and SARVIX Cloud are the long-term ecosystem we are building toward - a connected home for the tools businesses already need.
            </x-section-heading>
        </div>
    </section>

    @include('sections.page.how-it-works')
    @include('sections.page.faq')
    @include('sections.home.cta')
@endsection
