<<<<<<< Updated upstream
﻿@php
=======
@php
>>>>>>> Stashed changes
    $services = config('cebinova.services');
    $tones = [
        'web-development' => ['tone' => 'start', 'flag' => 'Start here'],
        'ai-automation' => ['tone' => 'ai', 'flag' => 'AI'],
        'digital-growth' => ['tone' => 'growth', 'flag' => 'Growth'],
    ];
@endphp

<section class="svc-page-catalog section-pad-lg" aria-label="Service categories">
    <div class="container-wide">
        <div class="svc-page-head" data-reveal>
            <p class="svc-page-kicker">
                <span class="svc-page-dot" aria-hidden="true"></span>
                Explore services
            </p>
            <h2 class="section-title">Pick the work that matches your stage.</h2>
            <p class="section-support">
                Filter by category or browse all six areas - web, commerce, software, automation, digitisation and growth.
            </p>
        </div>

        <div class="svc-page-filters" data-filter-group="[data-service-card]" role="tablist" aria-label="Service categories">
            <button type="button" class="svc-page-chip is-active" data-filter="all">All</button>
            @foreach ($services as $service)
                <button type="button" class="svc-page-chip" data-filter="{{ $service['slug'] }}">{{ $service['category'] }}</button>
            @endforeach
        </div>

        <div class="svc-page-grid" data-stagger>
            @foreach ($services as $service)
                @php
                    $meta = $tones[$service['slug']] ?? ['tone' => '', 'flag' => ''];
                    $tone = $meta['tone'];
                    $flag = $meta['flag'];
                @endphp
                <article
                    id="{{ $service['slug'] }}"
                    data-service-card
                    data-category="{{ $service['slug'] }}"
                    class="svc-page-card{{ $tone ? ' is-'.$tone : '' }}"
                >
                    <div class="svc-page-card-top">
                        <span class="svc-page-icon">
<<<<<<< Updated upstream
                            <x-mark :name="$service['icon']" class="h-5 w-5" />
=======
                            <x-icon :name="$service['icon']" class="h-5 w-5" />
>>>>>>> Stashed changes
                        </span>
                        @if ($flag)
                            <span class="svc-page-flag">{{ $flag }}</span>
                        @else
                            <span class="svc-page-flag is-muted">{{ $service['category'] }}</span>
                        @endif
                    </div>

                    <p class="svc-page-cat">{{ $service['category'] }}</p>
                    <h3 class="svc-page-card-title">{{ $service['title'] }}</h3>
                    <p class="svc-page-card-text">{{ $service['summary'] }}</p>

                    <div class="svc-page-tags" aria-label="Key inclusions">
                        @foreach (array_slice($service['includes'], 0, 4) as $tag)
                            <span>{{ $tag }}</span>
                        @endforeach
                    </div>

                    <ul class="svc-page-list">
                        @foreach ($service['includes'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>

                    <div class="svc-page-actions">
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
