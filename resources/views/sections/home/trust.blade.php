@php
    $items = [
        [
            'icon' => 'globe',
            'label' => 'Web Development',
            'category' => 'Presence',
            'text' => 'A site that explains you clearly and captures enquiry.',
            'href' => route('services.show', 'web-development'),
        ],
        [
            'icon' => 'store',
            'label' => 'eCommerce',
            'category' => 'Commerce',
            'text' => 'Catalogue, payments and orders in one place.',
            'href' => route('services.show', 'ecommerce'),
        ],
        [
            'icon' => 'layers',
            'label' => 'Custom Software',
            'category' => 'Systems',
            'text' => 'Tools built around how your team actually works.',
            'href' => route('services.show', 'custom-software'),
        ],
        [
            'icon' => 'device',
            'label' => 'Mobile Apps',
            'category' => 'Apps',
            'text' => 'Android and iOS for customers and your staff.',
            'href' => route('pricing'),
        ],
        [
            'icon' => 'spark',
            'label' => 'AI & Automation',
            'category' => 'Intelligence',
            'text' => 'WhatsApp, chatbots and workflows that keep moving.',
            'href' => route('services.show', 'ai-automation'),
            'tone' => 'ai',
            'badge' => 'AI',
        ],
        [
            'icon' => 'trend',
            'label' => 'Digital Growth',
            'category' => 'Marketing',
            'text' => 'SEO, campaigns and content that bring the next customer.',
            'href' => route('services.show', 'digital-growth'),
        ],
        [
            'icon' => 'nodes',
            'label' => 'Business Solutions',
            'category' => 'Ecosystem',
            'href' => route('solutions'),
            'tone' => 'umbrella',
            'text' => 'Complete solutions tailored to how your business works.',
            'chips' => ['Kirana', 'Retail', 'Professional'],
        ],
    ];
@endphp

<section class="trust-rail" aria-label="CEBINOVA capabilities">
    <div class="trust-rail-wrap">
        <div class="trust-rail-head">
            <p class="trust-rail-kicker">
                <span class="trust-rail-dot" aria-hidden="true"></span>
                One technology partner. Complete digital journey.
            </p>
            <p class="trust-rail-support">Everything your business needs - from digital presence to automation and growth.</p>
        </div>

        <ol class="trust-rail-list">
            @foreach ($items as $item)
                <li class="trust-rail-cell{{ ! empty($item['tone']) ? ' is-'.$item['tone'] : '' }}">
                    <a href="{{ $item['href'] }}" class="trust-rail-item">
                        <span class="trust-rail-icon">
<<<<<<< Updated upstream
                            <x-mark :name="$item['icon']" class="h-4 w-4" />
=======
                            <x-icon :name="$item['icon']" class="h-4 w-4" />
>>>>>>> Stashed changes
                        </span>
                        <span class="trust-rail-copy">
                            @if (! empty($item['badge']))
                                <x-badge tone="gold">{{ $item['badge'] }}</x-badge>
                            @else
                                <span class="trust-rail-cat">{{ $item['category'] }}</span>
                            @endif
                            <span class="trust-rail-label">{{ $item['label'] }}</span>
                            @if (! empty($item['text']))
                                <span class="trust-rail-note">{{ $item['text'] }}</span>
                            @endif
                            @if (! empty($item['chips']))
                                <span class="trust-rail-chips">
                                    @foreach ($item['chips'] as $chip)
                                        <span>{{ $chip }}</span>
                                    @endforeach
                                </span>
                            @endif
                        </span>
                        <span class="trust-rail-arrow" aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </span>
                    </a>
                </li>
            @endforeach
        </ol>

        <p class="trust-rail-cta">
            <x-button :href="route('services.index')" variant="outline" size="sm">
                Explore All Services
                <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </x-button>
        </p>
    </div>
</section>
