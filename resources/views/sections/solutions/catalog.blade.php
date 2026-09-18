<<<<<<< Updated upstream
﻿@php
=======
@php
>>>>>>> Stashed changes
    $icons = [
        'kirana' => 'bag',
        'retail' => 'cart',
        'professional-services' => 'briefcase',
        'ecommerce' => 'store',
        'business-management' => 'layers',
        'ai-automation' => 'spark',
    ];
    $solutions = config('cebinova.business_solutions');
@endphp

<section class="sol-page-catalog section-pad-lg" aria-label="Business solutions">
    <div class="container-wide">
        <div class="sol-page-head" data-reveal>
            <p class="sol-page-kicker">
                <span class="sol-page-dot" aria-hidden="true"></span>
                Business solutions
            </p>
            <h2 class="section-title">Explore solutions by business type.</h2>
            <p class="section-support">
                Filter by category or browse all six areas - kirana, retail, services, commerce, operations and automation.
            </p>
        </div>

        <div class="sol-page-filters" data-filter-group="[data-solution-card]" role="tablist" aria-label="Solution categories">
            <button type="button" class="sol-page-chip is-active" data-filter="all">All</button>
            @foreach ($solutions as $item)
                <button type="button" class="sol-page-chip" data-filter="{{ $item['slug'] }}">{{ $item['title'] }}</button>
            @endforeach
        </div>

        <div class="sol-page-grid" data-stagger>
            @foreach ($solutions as $item)
                @php
                    $isLive = $item['label'] === 'LIVE DEMO';
                    $isDemo = $item['label'] === 'SOLUTION DEMO';
                    $flagClass = $isLive ? ' is-live' : ($isDemo ? ' is-demo' : ' is-concept');
                @endphp
                <article
                    id="{{ $item['slug'] }}"
                    data-solution-card
                    data-category="{{ $item['slug'] }}"
                    class="sol-page-card scroll-mt-28{{ $isLive ? ' is-featured' : '' }}"
                >
                    <div class="sol-page-card-top">
                        <span class="sol-page-icon">
<<<<<<< Updated upstream
                            <x-mark :name="$icons[$item['slug']] ?? 'layers'" class="h-5 w-5" />
=======
                            <x-icon :name="$icons[$item['slug']] ?? 'layers'" class="h-5 w-5" />
>>>>>>> Stashed changes
                        </span>
                        <span class="sol-page-flag{{ $flagClass }}">{{ $item['label'] }}</span>
                    </div>

                    <p class="sol-page-industry">{{ $item['industry'] }}</p>
                    <h3 class="sol-page-card-title">{{ $item['title'] }}</h3>
                    <p class="sol-page-card-text">{{ $item['summary'] }}</p>

                    <ul class="sol-page-list">
                        @foreach ($item['capabilities'] as $capability)
                            <li>{{ $capability }}</li>
                        @endforeach
                    </ul>

                    <div class="sol-page-actions">
                        <x-button href="{{ solution_page_url($item) }}" variant="outline" size="sm" class="group">
                            Explore
                            <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </x-button>
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
