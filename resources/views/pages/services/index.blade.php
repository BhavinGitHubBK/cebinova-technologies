@extends('layouts.app')

@section('title', 'Services | SARVIX Technologies')
@section('description', 'Websites, eCommerce, custom software, AI automation, digital transformation and marketing - complete technology services from SARVIX.')

@php
    $services = config('sarvix.services');
    $tones = [
        'web-development' => 'start',
        'ai-automation' => 'ai',
        'digital-growth' => 'growth',
    ];
@endphp

@section('content')
    <x-page-hero
        :wrap="true"
        eyebrow="Services"
        title="Complete technology services. One partner."
        text="Clear categories so you can start with the work that matters now - then add systems and growth support as the business moves forward."
    >
        @include('sections.page.hero-ctas', [
            'consultHref' => consultation_url(),
            'extraHref' => route('pricing'),
            'extraLabel' => 'Build Your Digital Store',
        ])
    </x-page-hero>

    <section class="section-pad section-soft">
        <div class="container-wide">
            <div class="mb-8 flex flex-wrap gap-2" data-filter-group="[data-service-card]" role="tablist" aria-label="Service categories">
                <button type="button" class="sx-chip is-active" data-filter="all">All</button>
                @foreach ($services as $service)
                    <button type="button" class="sx-chip" data-filter="{{ $service['slug'] }}">{{ $service['category'] }}</button>
                @endforeach
            </div>

            <div class="svc-grid" data-stagger>
                @foreach ($services as $service)
                    @php $tone = $tones[$service['slug']] ?? null; @endphp
                    <article
                        id="{{ $service['slug'] }}"
                        data-service-card
                        data-category="{{ $service['slug'] }}"
                        class="svc-card{{ $tone ? ' is-'.$tone : '' }}"
                    >
                        <div class="svc-card-top">
                            <span class="svc-card-icon">
                                <x-icon :name="$service['icon']" class="h-5 w-5" />
                            </span>
                            @if ($tone === 'start')
                                <x-badge>Start here</x-badge>
                            @elseif ($tone === 'ai')
                                <x-badge tone="gold">AI</x-badge>
                            @elseif ($tone === 'growth')
                                <x-badge tone="blue">Growth</x-badge>
                            @else
                                <x-badge tone="grey">{{ $service['category'] }}</x-badge>
                            @endif
                        </div>

                        <p class="svc-card-cat">{{ $service['category'] }}</p>
                        <h2 class="svc-card-title">{{ $service['title'] }}</h2>
                        <p class="svc-card-text">{{ $service['summary'] }}</p>

                        <ul class="svc-card-list">
                            @foreach ($service['includes'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>

                        <div class="svc-card-actions">
                            <x-button href="{{ route('services.show', $service['slug']) }}" class="group">
                                View {{ $service['title'] }}
                                <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </x-button>
                            @if ($service['slug'] === 'digital-growth')
                                <x-button href="{{ route('marketing-packages') }}" variant="outline">Marketing plans</x-button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section-pad bg-white">
        <div class="container-wide">
            <x-section-heading eyebrow="How we start" title="Right-sized technology. One connected journey.">
                You do not need every service on day one. We start with the work that matches how the business operates today.
            </x-section-heading>
            <div class="svc-path" data-stagger>
                @foreach ([
                    ['step' => '01', 'title' => 'Presence', 'text' => 'Website, catalogue and a clear way for customers to enquire.'],
                    ['step' => '02', 'title' => 'Sell & manage', 'text' => 'eCommerce, software and operations tools when the process needs them.'],
                    ['step' => '03', 'title' => 'Automate & grow', 'text' => 'AI, workflows and marketing once the foundation is in place.'],
                ] as $item)
                    <article class="lift-card card-surface p-6 sm:p-7">
                        <x-badge tone="blue">{{ $item['step'] }}</x-badge>
                        <h3 class="mt-4 text-navy">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-[15px] leading-relaxed text-muted">{{ $item['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    @include('sections.page.how-it-works')
    @include('sections.page.faq')
    @include('sections.home.cta')
@endsection
