@php
    $services = collect(config('cebinova.services'))->keyBy('slug');
    $layout = [
        [
            'slug' => 'web-development',
            'tone' => 'start',
            'flag' => 'Start here',
            'tags' => ['Business sites', 'Lead capture'],
            'note' => 'A site that explains you clearly and captures enquiry.',
        ],
        [
            'slug' => 'ecommerce',
            'tone' => '',
            'tags' => ['Catalogue', 'Payments'],
            'note' => 'Catalogue, payments and orders in one place.',
        ],
        [
            'slug' => 'custom-software',
            'tone' => '',
            'tags' => ['CRM', 'Inventory', 'Portals'],
            'note' => 'Software built around how your team actually works.',
        ],
        [
            'slug' => 'ai-automation',
            'tone' => 'ai',
            'flag' => 'AI',
            'tags' => ['Chatbots', 'WhatsApp', 'Workflows'],
            'note' => 'WhatsApp, chatbots and workflows that keep moving.',
        ],
        [
            'slug' => 'digital-business',
            'tone' => '',
            'tags' => ['Digitisation', 'Portals'],
            'note' => 'Move from paper and calls to a setup your team can use.',
        ],
        [
            'slug' => 'digital-growth',
            'tone' => 'growth',
            'flag' => 'Growth',
            'tags' => ['Marketing', 'SEO'],
            'note' => 'SEO, campaigns and content that bring the next customer.',
        ],
    ];
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
                        <x-mark :name="$service['icon']" class="h-4 w-4" />
                    </span>
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
