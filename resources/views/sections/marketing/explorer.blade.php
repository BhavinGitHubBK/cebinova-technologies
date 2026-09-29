@php
    $catalog = $marketingCatalog ?? \App\Support\MarketingPackages::catalog();
    $categories = collect(['regular', 'festival'])->mapWithKeys(fn ($key) => isset($catalog[$key]) ? [$key => $catalog[$key]] : [])->all();
    $growth = $catalog['growth'] ?? null;
    $defaultCategory = array_key_first($categories);
@endphp

<section id="marketing-plans" class="mkt-page-explorer scroll-mt-28 section-pad-lg" aria-label="Marketing plan prices">
    <div class="container-wide">
        <div class="mkt-page-head" data-reveal>
            <p class="mkt-page-kicker">
                <span class="mkt-page-dot" aria-hidden="true"></span>
                Prices
            </p>
            <h2 class="section-title">See the exact price and what you get.</h2>
            <p class="section-support">
                Tap a plan. Then pick Monthly, Quarterly, Half-Yearly or Yearly. Yearly costs less every month.
            </p>
        </div>

        @if ($categories !== [])
        <div class="js-pack-explorer mkt-page-explorer-shell" data-default-cat="{{ $defaultCategory }}" data-default-duration="yearly">
            <div class="mkt-page-cat-grid" role="tablist" aria-label="Marketing package categories">
                @foreach ($categories as $key => $category)
                    <button
                        type="button"
                        class="pack-cat js-pack-cat scroll-mt-28 {{ $key === $defaultCategory ? 'is-active' : '' }}"
                        id="{{ $key }}-marketing"
                        data-cat="{{ $key }}"
                        role="tab"
                        aria-selected="{{ $key === $defaultCategory ? 'true' : 'false' }}"
                    >
                        <span class="pack-cat-title">{{ $category['title'] }}</span>
                        <span class="pack-cat-text">{{ $category['teaser'] }}</span>
                    </button>
                @endforeach
            </div>

            @if ($growth && ! empty($growth['plans']))
            <a href="#complete-growth" class="mkt-page-growth-banner">
                <span class="mkt-page-growth-banner-copy">
                    <span class="mkt-page-growth-banner-title">
                        {{ $growth['title'] }}
                        <span class="mkt-page-growth-banner-badge">Most Popular</span>
                    </span>
                    <span class="mkt-page-growth-banner-text">{{ $growth['teaser'] }} From {{ cebinova_inr($growth['plans']['monthly']['price']) }} / month - only ₹1,000 more than Regular.</span>
                </span>
                <span class="mkt-page-growth-banner-link">View →</span>
            </a>
            @endif

            <div class="mkt-page-panels">
                @foreach ($categories as $category)
                    <div class="js-pack-cat-panel {{ $category['key'] === $defaultCategory ? '' : 'hidden' }}" data-cat="{{ $category['key'] }}" @if ($category['key'] !== $defaultCategory) hidden @endif>
                        <div class="mkt-page-panel-head">
                            <p class="mkt-page-kicker">
                                <span class="mkt-page-dot" aria-hidden="true"></span>
                                {{ $category['title'] }}
                            </p>
                            <h3 class="section-title">{{ $category['heading'] }}</h3>
                            <p class="section-support">{{ $category['subheading'] }}</p>
                            @if (! empty($category['best_if']))
                                <p class="mkt-page-best-if">{{ $category['best_if'] }}</p>
                            @endif
                        </div>

                        <div class="mkt-page-duration-grid" role="tablist" aria-label="{{ $category['title'] }} durations">
                            @foreach ($category['plans'] as $durationKey => $plan)
                                <button
                                    type="button"
                                    class="pack-duration js-pack-duration {{ $durationKey === 'yearly' ? 'is-active' : '' }}"
                                    data-cat="{{ $category['key'] }}"
                                    data-duration="{{ $durationKey }}"
                                    data-sticky-name="{{ $category['service'] }} · {{ $plan['label'] }}"
                                    data-sticky-price="{{ \App\Support\MarketingPackages::priceHeadline($category['service'], $plan['label']) }}"
                                    data-sticky-cta="{{ $plan['enquiry_url'] ?? package_enquiry_url($category['service'], $plan['label']) }}"
                                    data-sticky-wa="{{ package_whatsapp_url($category['service'], $plan['label']) }}"
                                    role="tab"
                                    aria-selected="{{ $durationKey === 'yearly' ? 'true' : 'false' }}"
                                >
                                    <span class="pack-duration-label">{{ $plan['label'] }}</span>
                                    <span class="pack-duration-price">{{ cebinova_inr($plan['price']) }}</span>
                                    @if ($plan['badge'])
                                        <span class="pack-duration-badge">{{ $plan['badge'] }}</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>

                        <div class="mkt-page-plan-stack">
                            @foreach ($category['plans'] as $durationKey => $plan)
                                <article class="js-pack-plan mkt-page-plan-card {{ $durationKey === 'yearly' ? '' : 'hidden' }}" data-cat="{{ $category['key'] }}" data-duration="{{ $durationKey }}" @if ($durationKey !== 'yearly') hidden @endif>
                                    <div class="mkt-page-plan-grid">
                                        <div class="mkt-page-plan-pricing">
                                            @if ($plan['badge'])
                                                <p class="mkt-page-plan-badge">{{ $plan['badge'] }}</p>
                                            @endif
                                            <h4 class="mkt-page-plan-label">{{ $plan['label'] }}</h4>
                                            <p class="mkt-page-plan-duration">Duration: {{ $plan['duration'] }}</p>
                                            @include('sections.marketing.price-stack', ['service' => $category['service'], 'plan' => $plan, 'tone' => 'light'])
                                            @if ($category['key'] === 'regular' && $durationKey === 'monthly')
                                                <p class="mkt-page-plan-note">Need festivals too? Complete Growth is only ₹1,000 more and includes them.</p>
                                            @endif
                                            <div class="mkt-page-plan-actions">
                                                <x-button href="{{ $plan['enquiry_url'] ?? package_enquiry_url($category['service'], $plan['label']) }}" size="lg" class="w-full">Get This Plan</x-button>
                                                <x-button href="{{ package_whatsapp_url($category['service'], $plan['label']) }}" variant="outline" size="lg" class="w-full">Ask on WhatsApp</x-button>
                                            </div>
                                        </div>
                                        <div class="mkt-page-plan-details">
                                            @if (! empty($plan['monthly_pace']))
                                                <p class="mkt-page-plan-section-label">What you get every month</p>
                                                <ul class="mkt-page-plan-list mkt-page-plan-list--pace">
                                                    @foreach ($plan['monthly_pace'] as $item)
                                                        <li>{{ $item }}</li>
                                                    @endforeach
                                                </ul>
                                                <p class="mkt-page-plan-section-label mkt-page-plan-section-label--muted">Full {{ strtolower($plan['label']) }} total</p>
                                            @else
                                                <p class="mkt-page-plan-section-label">What you get</p>
                                            @endif
                                            <ul class="mkt-page-plan-list">
                                                @foreach ($plan['includes'] as $item)
                                                    <li>{{ $item }}</li>
                                                @endforeach
                                            </ul>
                                            <div class="mkt-page-plan-notes">
                                                <p>{{ config('cebinova.marketing.notes.tax') }}</p>
                                                <p>{{ config('cebinova.marketing.notes.timeline') }}</p>
                                                <p>{{ config('cebinova.marketing.notes.payment') }}</p>
                                                <p>{{ config('cebinova.marketing.notes.support') }}</p>
                                                <p>{{ config('cebinova.marketing.notes.reels') }}</p>
                                                @if ($category['key'] === 'festival')
                                                    <p>{{ config('cebinova.marketing.notes.festival') }}</p>
                                                    <p>{{ config('cebinova.marketing.notes.delivery') }}</p>
                                                @endif
                                                <p>{{ config('cebinova.marketing.notes.third_party') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @else
            <div class="card-surface p-8 text-center text-muted">Marketing packages are being updated. Please check back shortly.</div>
        @endif
    </div>
</section>
