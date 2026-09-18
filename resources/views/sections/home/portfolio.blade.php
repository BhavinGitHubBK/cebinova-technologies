@php
    $slugs = ['kirana', 'retail', 'professional-services', 'ecommerce'];
    $items = collect(config('cebinova.business_solutions'))->keyBy('slug');
    $flags = [
        'kirana' => 'Start here',
        'retail' => null,
        'professional-services' => null,
        'ecommerce' => 'Commerce',
    ];
@endphp

<section class="home-port section-pad-lg" id="home-demos" aria-label="Working business demos">
    <div class="container-wide">
        <div class="home-port-head" data-reveal>
            <p class="home-port-kicker">
                <span class="home-port-dot" aria-hidden="true"></span>
                Solutions
            </p>
            <h2 class="section-title">Solutions Built for Real Businesses.</h2>
            <p class="section-support">
                CEBINOVA does not only talk about services. These working demos show what we can build for different businesses.
            </p>
        </div>

        <div class="home-port-grid" data-stagger>
            @foreach ($slugs as $slug)
                @php $item = $items[$slug]; @endphp
                <a href="{{ $item['demo'] ? demo_url($item['demo']) : solution_page_url($item) }}" class="home-port-card{{ $slug === 'kirana' ? ' is-featured' : '' }}">
                    <span class="home-port-chrome" aria-hidden="true">
                        <span class="home-port-traffic">
                            <span></span>
                            <span></span>
                            <span></span>
                        </span>
                        <span class="home-port-url">{{ $item['title'] }}</span>
                    </span>
                    <span class="home-port-shot-wrap">
                        @if ($item['preview'])
                            <img src="{{ asset('assets/'.$item['preview']) }}" alt="" class="home-port-shot" loading="lazy">
                        @else
                            <span class="home-port-empty">Preview coming soon</span>
                        @endif
                        <span class="home-port-badge">{{ $item['label'] }}</span>
                    </span>
                    <span class="home-port-body">
                        <span class="home-port-meta">
                            <span class="home-port-cat">{{ $item['industry'] }}</span>
                            @if (! empty($flags[$slug]))
                                <span class="home-port-flag">{{ $flags[$slug] }}</span>
                            @endif
                        </span>
                        <h3 class="home-port-title">{{ $item['title'] }}</h3>
                        <p class="home-port-text">{{ $item['summary'] }}</p>
                        <span class="home-port-tags">
                            @foreach (array_slice($item['capabilities'], 0, 3) as $capability)
                                <span>{{ $capability }}</span>
                            @endforeach
                        </span>
                        <span class="home-port-link">
                            {{ $item['demo'] ? 'View Demo' : 'Explore solution' }}
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </span>
                    </span>
                </a>
            @endforeach
        </div>

        <p class="home-port-cta">
            <x-button href="{{ route('solutions') }}" size="sm">
                Explore All Solutions
                <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </x-button>
            <x-button href="{{ route('demos') }}" variant="outline" size="sm">
                View Demos
                <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </x-button>
        </p>
    </div>
</section>
