@php
    $guide = $service;
    $nextHref = page_next_url($guide, $service['title']);
    $nextLabel = $service['next_label'] ?? 'Get Free Consultation';
    $whatsapp = page_whatsapp_url($service['title']);
    $process = collect($service['sections'] ?? [])->firstWhere('type', 'process');
@endphp

@extends('layouts.app')

@section('title', ($service['seo_title'] ?? null) ?: $service['title'].' | CEBINOVA Technologies')
@section('description', ($service['seo_description'] ?? null) ?: ($service['short'] ?? ''))

@section('content')
    <x-page-hero
        :wrap="true"
        :eyebrow="$service['category']"
        :title="$service['hero_title']"
        :text="$service['hero_subtitle']"
    >
        @include('sections.page.hero-ctas', [
            'consultHref' => service_enquiry_url($service),
            'whatsappHref' => $whatsapp,
            'extraHref' => ($guide['next_route'] ?? null) ? $nextHref : null,
            'extraLabel' => ($guide['next_route'] ?? null) ? $nextLabel : null,
        ])
    </x-page-hero>

    <section class="section-pad section-soft">
        <div class="container-wide grid gap-10 lg:grid-cols-12">
            <div class="lg:col-span-7">
                @if (! empty($service['audience']))
                    <p class="text-[12px] font-bold uppercase tracking-[0.16em] text-gold-dark">Who this is for</p>
                    <p class="mt-3 text-[17px] leading-relaxed text-navy">{{ $service['audience'] }}</p>
                @endif

                <h2 class="mt-10 text-2xl font-extrabold text-navy">
                    @if ($service['slug'] === 'web-development')
                        Types of websites
                    @elseif ($service['slug'] === 'custom-software')
                        Systems we build
                    @elseif ($service['slug'] === 'ai-automation')
                        Work we automate
                    @else
                        What this includes
                    @endif
                </h2>
                <ul class="mt-6 grid gap-3 sm:grid-cols-2">
                    @foreach ($service['includes'] as $item)
                        <li class="flex items-center gap-2.5 rounded-xl border border-line bg-white px-4 py-3.5 text-[15px] font-medium leading-none text-navy">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-gold"></span>
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <aside class="lg:col-span-5">
                <div class="card-surface p-7 sm:p-8">
                    <x-badge>Next step</x-badge>
                    <h2 class="mt-4 text-xl font-bold text-navy">{{ $service['outcome'] ?? 'We map this to your current stage.' }}</h2>
                    <p class="mt-3 text-[15.5px] leading-relaxed text-muted">Tell us how the business operates today. We will recommend the right starting point - not a larger stack than you need.</p>
                    <div class="mt-6 flex flex-col gap-3">
                        <x-button href="{{ $guide['next_route'] ? $nextHref : service_enquiry_url($service) }}" class="w-full">{{ $nextLabel }}</x-button>
                        <x-button href="{{ $whatsapp }}" variant="outline" class="w-full">Ask on WhatsApp</x-button>
                    </div>
                    <p class="mt-5 text-[13px] leading-relaxed text-muted">GST extra, if applicable. No payment required to start.</p>
                </div>
            </aside>
        </div>
    </section>

    @foreach (collect($service['sections'] ?? [])->whereIn('type', ['benefits', 'use_cases', 'technologies', 'custom']) as $section)
        @include('sections.services.dynamic-section', ['section' => $section])
    @endforeach

    @if ($process)
        @include('sections.services.dynamic-process', ['section' => $process, 'topic' => $service['title']])
    @endif

    @if ($service['slug'] === 'custom-software')
        <section class="section-pad bg-white" aria-label="How custom software changes the process">
            <div class="container-wide">
                <x-section-heading eyebrow="How it changes the work" title="From the current process to one system">
                    The software is scoped around the work the team already does.
                </x-section-heading>
                <ol class="mt-8 grid gap-4 md:grid-cols-3">
                    <li class="card-surface p-6">
                        <p class="text-[12px] font-bold uppercase tracking-[0.16em] text-gold-dark">Today</p>
                        <h3 class="mt-3 text-lg font-extrabold text-navy">Your current process</h3>
                        <p class="mt-2 text-[15px] leading-relaxed text-muted">Stock, billing, dealers and follow-ups live in sheets, calls and separate tools.</p>
                    </li>
                    <li class="card-surface p-6">
                        <p class="text-[12px] font-bold uppercase tracking-[0.16em] text-gold-dark">Build</p>
                        <h3 class="mt-3 text-lg font-extrabold text-navy">CEBINOVA software</h3>
                        <p class="mt-2 text-[15px] leading-relaxed text-muted">CRM, inventory, billing, orders, employee records, dealer portals or workflow — only the modules you need.</p>
                    </li>
                    <li class="card-surface p-6">
                        <p class="text-[12px] font-bold uppercase tracking-[0.16em] text-gold-dark">After</p>
                        <h3 class="mt-3 text-lg font-extrabold text-navy">A working process</h3>
                        <p class="mt-2 text-[15px] leading-relaxed text-muted">The same work happens in one place, with a record of what was done and what is still open.</p>
                    </li>
                </ol>
            </div>
        </section>
    @endif

    @if ($service['slug'] === 'ai-automation')
        <section class="section-pad bg-white" aria-label="Automation examples">
            <div class="container-wide">
                <x-section-heading eyebrow="Practical automation" title="What a lead workflow can look like">
                    These are working patterns, not a promise that every business needs every step.
                </x-section-heading>
                <ol class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
                        'Website enquiry is saved',
                        'It becomes a CRM lead',
                        'WhatsApp sends an acknowledgement',
                        'A salesperson is assigned',
                        'A follow-up reminder is set',
                        'Management gets a simple report',
                    ] as $step)
                        <li class="card-surface p-6">
                            <p class="text-[12px] font-bold uppercase tracking-[0.16em] text-gold-dark">Step {{ $loop->iteration }}</p>
                            <h3 class="mt-3 text-lg font-extrabold text-navy">{{ $step }}</h3>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    @if (! empty($service['faq']))
        <section class="section-pad section-soft" aria-label="Questions">
            <div class="container-wide max-w-3xl">
                <x-section-heading eyebrow="Questions" title="Before you start">
                    Straight answers for this service.
                </x-section-heading>
                <div class="mt-8 grid gap-4">
                    @foreach ($service['faq'] as $item)
                        <article class="card-surface p-6">
                            <h3 class="text-lg font-extrabold text-navy">{{ $item['q'] }}</h3>
                            <p class="mt-2 text-[15px] leading-relaxed text-muted">{{ $item['a'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
        @push('head')
            <script type="application/ld+json">
                {!! json_encode([
                    '@@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => collect($service['faq'])->map(fn ($item) => [
                        '@type' => 'Question',
                        'name' => $item['q'],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $item['a'],
                        ],
                    ])->values()->all(),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
            </script>
        @endpush
    @endif

    <section class="section-pad bg-white">
        <div class="container-wide">
            <x-section-heading eyebrow="Also useful" title="Related services">
                Start with this page. Add the next service when the business is ready.
            </x-section-heading>
            <div class="mt-8 grid gap-5 md:grid-cols-3">
                @foreach ($relatedServices as $related)
                    <x-service-card
                        :href="route('services.show', $related['slug'])"
                        :icon="$related['icon']"
                        :title="$related['title']"
                        :text="$related['short']"
                    />
                @endforeach
            </div>
        </div>
    </section>

    @include('sections.home.cta')
@endsection
