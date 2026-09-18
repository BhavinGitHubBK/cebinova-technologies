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

<section class="demo-page-catalog section-pad-lg" aria-label="Live demos catalog">
    <div class="container-wide">
        <div class="demo-page-head" data-reveal>
            <p class="demo-page-kicker">
                <span class="demo-page-dot" aria-hidden="true"></span>
                Live demos
            </p>
            <h2 class="section-title">Business solutions built for real businesses.</h2>
            <p class="section-support">
                These are live solution demos and concepts. They are not presented as live client case studies.
            </p>
        </div>

        <div class="demo-page-filters" data-filter-group="[data-demo-card]" role="tablist" aria-label="Demo categories">
            <button type="button" class="demo-page-chip is-active" data-filter="all">All</button>
            @foreach ($solutions as $item)
                <button type="button" class="demo-page-chip" data-filter="{{ $item['slug'] }}">{{ $item['title'] }}</button>
            @endforeach
        </div>

        <div class="demo-page-grid" data-stagger>
            @foreach ($solutions as $item)
                @php
                    $isLive = $item['label'] === 'LIVE DEMO';
                    $isDemo = $item['label'] === 'SOLUTION DEMO';
                    $flagClass = $isLive ? ' is-live' : ($isDemo ? ' is-demo' : ' is-concept');
                @endphp
                <article
                    id="{{ $item['slug'] }}"
                    data-demo-card
                    data-category="{{ $item['slug'] }}"
                    class="demo-page-card scroll-mt-28{{ $isLive ? ' is-featured' : '' }}"
                >
                    <div class="demo-page-media">
                        @if ($item['preview'])
                            <img src="{{ asset('assets/'.$item['preview']) }}" alt="" class="demo-page-image" loading="lazy">
                        @else
                            <div class="demo-page-placeholder">
                                <x-demo-preview :type="$item['slug'] === 'business-management' ? 'crm' : 'ai'" />
                            </div>
                        @endif
                        <span class="demo-page-flag{{ $flagClass }}">{{ $item['label'] }}</span>
                    </div>

                    <div class="demo-page-body">
                        <div class="demo-page-card-top">
                            <span class="demo-page-icon">
<<<<<<< Updated upstream
                                <x-mark :name="$icons[$item['slug']] ?? 'layers'" class="h-5 w-5" />
=======
                                <x-icon :name="$icons[$item['slug']] ?? 'layers'" class="h-5 w-5" />
>>>>>>> Stashed changes
                            </span>
                            <p class="demo-page-industry">{{ $item['industry'] }}</p>
                        </div>

                        <h3 class="demo-page-card-title">CEBINOVA {{ $item['title'] }} Solution</h3>
                        <p class="demo-page-card-text">{{ $item['summary'] }}</p>

                        <ul class="demo-page-tags">
                            @foreach ($item['capabilities'] as $capability)
                                <li>{{ $capability }}</li>
                            @endforeach
                        </ul>

                        <div class="demo-page-actions">
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
