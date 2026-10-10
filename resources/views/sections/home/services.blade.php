@php
    $services = collect($homeData['services'] ?? App\Services\HomepageBuilder::featuredServices())->keyBy('slug');
    $layout = $services->map(function ($service, $slug) {
        $tags = array_slice($service['includes'] ?? [], 0, 3);

        return [
            'slug' => $slug,
            'tone' => $slug === 'ai-automation' ? 'ai' : ($slug === 'digital-growth' ? 'growth' : ($slug === 'web-development' ? 'start' : '')),
            'flag' => $slug === 'web-development' ? 'Start here' : ($slug === 'ai-automation' ? 'AI' : ($slug === 'digital-growth' ? 'Growth' : null)),
            'tags' => $tags !== [] ? $tags : ['Business', 'Growth'],
            'note' => $service['short'] ?? $service['summary'] ?? '',
        ];
    })->values()->all();
@endphp

<section class="home-svc section-pad-lg" aria-label="CEBINOVA services">
    <div class="container-wide">
        <div class="home-svc-head" data-reveal>
            <p class="home-svc-kicker">
                <span class="home-svc-dot" aria-hidden="true"></span>
                Services
            </p>
            <h2 class="section-title">Everything Your Business Needs. Under One Roof.</h2>
            <p class="section-support">
                Six connected solution areas - so you do not need a different vendor for every stage of growth.
            </p>
        </div>

        <div class="home-svc-grid" data-stagger>
            @foreach ($layout as $cell)
                @php $service = $services[$cell['slug']]; @endphp
                <a href="{{ route('services.show', $service['slug']) }}" class="home-svc-card{{ $cell['tone'] ? ' is-'.$cell['tone'] : '' }}">
                    <span class="home-svc-icon">
                        <x-mark :name="$service['icon']" class="h-4 w-4" />                    </span>
                    <span class="home-svc-copy">
                        <span class="home-svc-meta">
                            <span class="home-svc-cat">{{ $service['category'] }}</span>
                            @if (! empty($cell['flag']))
                                <span class="home-svc-flag">{{ $cell['flag'] }}</span>
                            @endif
                        </span>
                        <h3 class="home-svc-title">{{ $service['title'] }}</h3>
                        <p class="home-svc-text">{{ $cell['note'] }}</p>
                        <span class="home-svc-tags">
                            @foreach ($cell['tags'] as $tag)
                                <span>{{ $tag }}</span>
                            @endforeach
                        </span>
                    </span>
                    <span class="home-svc-link">
                        Learn more
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </span>
                </a>
            @endforeach
        </div>

        <p class="home-svc-cta">
            <x-button :href="route('services.index')" variant="outline" size="sm">
                Explore All Services
                <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </x-button>
        </p>
    </div>
</section>
