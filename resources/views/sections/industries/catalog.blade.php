@php
    $icons = [
        'kirana' => 'bag', 'dairy' => 'home', 'food' => 'store', 'retail' => 'cart',
        'manufacturing' => 'factory', 'wholesale' => 'layers', 'export' => 'globe',
        'professional' => 'briefcase', 'healthcare' => 'heart', 'education' => 'book',
        'real-estate' => 'building', 'startups' => 'rocket', 'services' => 'users',
    ];
    $groups = [
        'retail-food' => ['label' => 'Retail & Food', 'slugs' => ['kirana', 'dairy', 'food', 'retail']],
        'trade' => ['label' => 'Trade & Export', 'slugs' => ['manufacturing', 'wholesale', 'export']],
        'professional' => ['label' => 'Professional', 'slugs' => ['professional', 'healthcare', 'education', 'real-estate', 'services']],
        'startups' => ['label' => 'Startups', 'slugs' => ['startups']],
    ];
    $groupMap = [];
    foreach ($groups as $key => $group) {
        foreach ($group['slugs'] as $slug) {
            $groupMap[$slug] = $key;
        }
    }
    $featured = ['kirana', 'food', 'startups'];
@endphp

<section class="ind-page-catalog section-pad-lg" aria-label="Industries catalog">
    <div class="container-wide">
        <div class="ind-page-head" data-reveal>
            <p class="ind-page-kicker">
                <span class="ind-page-dot" aria-hidden="true"></span>
                Who we help
            </p>
            <h2 class="section-title">Built for the way different businesses operate.</h2>
            <p class="section-support">
                Filter by category or browse all industry types - from local retail and food to trade, professional services and startups.
            </p>
        </div>

        <div class="ind-page-filters" data-filter-group="[data-industry-card]" role="tablist" aria-label="Industry categories">
            <button type="button" class="ind-page-chip is-active" data-filter="all">All</button>
            @foreach ($groups as $key => $group)
                <button type="button" class="ind-page-chip" data-filter="{{ $key }}">{{ $group['label'] }}</button>
            @endforeach
        </div>

        <div class="ind-page-grid" data-stagger>
            @foreach (config('cebinova.industries') as $industry)
                <article
                    id="{{ $industry['slug'] }}"
                    data-industry-card
                    data-category="{{ $groupMap[$industry['slug']] ?? 'other' }}"
                    class="ind-page-card scroll-mt-28{{ in_array($industry['slug'], $featured, true) ? ' is-featured' : '' }}"
                >
                    <div class="ind-page-card-top">
                        <span class="ind-page-icon">
                            <x-mark :name="$icons[$industry['slug']] ?? 'globe'" class="h-5 w-5" />
                        </span>
                        @if (in_array($industry['slug'], $featured, true))
                            <span class="ind-page-flag">Common start</span>
                        @endif
                    </div>

                    <h3 class="ind-page-card-title">{{ $industry['title'] }}</h3>
                    <p class="ind-page-card-text">{{ $industry['need'] }}</p>

                    <p class="ind-page-solutions-label">Sample solutions</p>
                    <ul class="ind-page-tags">
                        @foreach ($industry['solutions'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </div>

        <div class="ind-page-foot">
            <div class="ind-page-foot-copy">
                <p class="ind-page-foot-kicker">
                    <span class="ind-page-dot" aria-hidden="true"></span>
                    Your industry
                </p>
                <h3 class="ind-page-foot-title">Don't see your business type?</h3>
                <p class="ind-page-foot-text">Tell us how you sell, serve customers and run operations. CEBINOVA maps a first move around your workflow.</p>
            </div>
            <div class="ind-page-foot-actions">
                <x-button href="{{ consultation_url() }}">Tell Us About Your Business</x-button>
                <x-button href="{{ whatsapp_url() }}" variant="outline">Ask on WhatsApp</x-button>
            </div>
        </div>
    </div>
</section>
