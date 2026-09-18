@php
    $plans = [
        [
            'key' => 'regular',
            'cat' => 'Always on',
            'icon' => 'megaphone',
            'href' => route('marketing-packages').'#regular-marketing',
            'config' => config('cebinova.marketing.regular'),
        ],
        [
            'key' => 'festival',
            'cat' => 'Seasonal',
            'icon' => 'clock',
            'href' => route('marketing-packages').'#festival-marketing',
            'config' => config('cebinova.marketing.festival'),
        ],
        [
            'key' => 'growth',
            'cat' => 'Combined',
            'icon' => 'trend',
            'href' => route('marketing-packages').'#complete-growth',
            'config' => config('cebinova.marketing.growth'),
            'featured' => true,
        ],
    ];
@endphp

<section class="home-mkt section-pad-lg" id="home-marketing" aria-label="CEBINOVA marketing packages">
    <div class="container-wide">
        <div class="home-mkt-head" data-reveal>
            <p class="home-mkt-kicker">
                <span class="home-mkt-dot" aria-hidden="true"></span>
                Marketing
            </p>
            <h2 class="section-title">Technology Builds the Business. Marketing Helps It Grow.</h2>
            <p class="section-support">
                Three marketing solutions. Four subscription durations. Full launch plans live on Marketing Packages.
            </p>
        </div>

        <div class="home-mkt-grid" data-stagger>
            @foreach ($plans as $plan)
                <a href="{{ $plan['href'] }}" class="home-mkt-card{{ ! empty($plan['featured']) ? ' is-featured' : '' }}">
                    <span class="home-mkt-top">
                        <span class="home-mkt-icon">
                            <x-mark :name="$plan['icon']" class="h-4 w-4" />
                        </span>
                        @if (! empty($plan['featured']))
                            <span class="home-mkt-flag">Recommended</span>
                        @endif
                    </span>
                    <span class="home-mkt-cat">{{ $plan['cat'] }}</span>
                    <h3 class="home-mkt-title">{{ $plan['config']['title'] }}</h3>
                    <p class="home-mkt-note">{{ $plan['config']['teaser'] }}</p>
                    <p class="home-mkt-price">From {{ cebinova_inr($plan['config']['plans']['monthly']['price']) }} / month</p>
                    <span class="home-mkt-link">
                        View plans
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </span>
                </a>
            @endforeach
        </div>

        <div class="home-mkt-foot">
            <div>
                <p class="home-mkt-durations-label">Available durations</p>
                <ul class="home-mkt-durations">
                    @foreach (config('cebinova.marketing.frequencies') as $frequency)
                        <li>{{ $frequency }}</li>
                    @endforeach
                </ul>
            </div>
            <p class="home-mkt-cta">
                <x-button href="{{ route('marketing-packages') }}#marketing-plans" variant="primary">
                    Explore Marketing Plans
                    <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </x-button>
            </p>
        </div>
    </div>
</section>
