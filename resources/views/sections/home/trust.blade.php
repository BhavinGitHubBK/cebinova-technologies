@php
    $items = [
        [
            'icon' => 'globe',
            'label' => 'Web Development',
            'category' => 'Presence',
            'href' => route('services.show', 'web-development'),
        ],
        [
            'icon' => 'store',
            'label' => 'eCommerce',
            'category' => 'Commerce',
            'href' => route('services.show', 'ecommerce'),
        ],
        [
            'icon' => 'layers',
            'label' => 'Custom Software',
            'category' => 'Systems',
            'href' => route('services.show', 'custom-software'),
        ],
        [
            'icon' => 'device',
            'label' => 'Mobile Apps',
            'category' => 'Apps',
            'href' => route('pricing'),
        ],
        [
            'icon' => 'spark',
            'label' => 'AI & Automation',
            'category' => 'Intelligence',
            'href' => route('services.show', 'ai-automation'),
            'tone' => 'ai',
            'badge' => 'AI',
        ],
        [
            'icon' => 'trend',
            'label' => 'Digital Growth',
            'category' => 'Marketing',
            'href' => route('services.show', 'digital-growth'),
        ],
        [
            'icon' => 'nodes',
            'label' => 'Business Solutions',
            'category' => 'Ecosystem',
            'href' => route('solutions'),
            'tone' => 'umbrella',
            'text' => 'Complete solutions tailored to how your business works.',
        ],
    ];
@endphp

<section class="trust-rail" aria-label="SARVIX capabilities">
    <div class="trust-rail-wrap">
        <div class="trust-rail-head">
            <p class="trust-rail-kicker">
                <span class="trust-rail-dot" aria-hidden="true"></span>
                One technology partner. Complete digital journey.
            </p>
            <p class="trust-rail-support">Everything your business needs - from digital presence to automation and growth.</p>
        </div>

        <ul class="trust-rail-list">
            @foreach ($items as $item)
                <li class="trust-rail-cell{{ ! empty($item['tone']) ? ' is-'.$item['tone'] : '' }}">
                    <a href="{{ $item['href'] }}" class="trust-rail-item">
                        <span class="trust-rail-top">
                            <span class="trust-rail-icon">
                                <x-icon :name="$item['icon']" class="h-4 w-4" />
                            </span>
                            @if (! empty($item['badge']))
                                <x-badge tone="gold">{{ $item['badge'] }}</x-badge>
                            @else
                                <span class="trust-rail-cat">{{ $item['category'] }}</span>
                            @endif
                        </span>
                        <span class="trust-rail-copy">
                            <span class="trust-rail-label">{{ $item['label'] }}</span>
                            @if (! empty($item['text']))
                                <span class="trust-rail-note">{{ $item['text'] }}</span>
                            @endif
                        </span>
                        <span class="trust-rail-arrow" aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>

        <p class="trust-rail-cta">
            <a href="{{ route('services.index') }}" class="trust-rail-link group">
                Explore All Services
                <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </p>
    </div>
</section>
