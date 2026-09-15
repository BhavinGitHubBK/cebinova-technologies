@php
    $services = collect(config('sarvix.services'))->keyBy('slug');
    $layout = [
        ['slug' => 'web-development', 'tone' => 'start', 'tags' => ['Business sites', 'Lead capture']],
        ['slug' => 'ecommerce', 'tone' => '', 'tags' => ['Catalogue', 'Payments']],
        ['slug' => 'custom-software', 'tone' => '', 'tags' => ['CRM', 'Inventory', 'Portals']],
        ['slug' => 'ai-automation', 'tone' => 'ai', 'tags' => ['Chatbots', 'WhatsApp', 'Workflows']],
        ['slug' => 'digital-business', 'tone' => '', 'tags' => ['Digitisation', 'Portals']],
        ['slug' => 'digital-growth', 'tone' => 'growth', 'tags' => ['Marketing', 'SEO']],
    ];
@endphp

<section class="section-pad-lg section-soft">
    <div class="container-wide">
        <div class="max-w-3xl" data-reveal>
            <x-section-heading eyebrow="Services" title="Everything Your Business Needs. Under One Roof.">
                Six connected solution areas - so you do not need a different vendor for every stage of growth.
            </x-section-heading>
        </div>

        <div class="home-svc-grid mt-11" data-stagger>
            @foreach ($layout as $cell)
                @php $service = $services[$cell['slug']]; @endphp
                <a href="{{ route('services.show', $service['slug']) }}" class="home-svc-card group{{ $cell['tone'] ? ' is-'.$cell['tone'] : '' }}">
                    <span class="home-svc-top">
                        <span class="home-svc-icon">
                            <x-icon :name="$service['icon']" class="h-4 w-4" />
                        </span>
                        @if ($cell['tone'] === 'start')
                            <x-badge>Start here</x-badge>
                        @elseif ($cell['tone'] === 'ai')
                            <x-badge tone="gold">AI</x-badge>
                        @elseif ($cell['tone'] === 'growth')
                            <x-badge tone="blue">Growth</x-badge>
                        @else
                            <span class="home-svc-cat">{{ $service['category'] }}</span>
                        @endif
                    </span>
                    <h3 class="home-svc-title">{{ $service['title'] }}</h3>
                    <p class="home-svc-text">{{ $service['short'] }}</p>
                    <span class="home-svc-tags">
                        @foreach ($cell['tags'] as $tag)
                            <span>{{ $tag }}</span>
                        @endforeach
                    </span>
                    <span class="home-svc-link">
                        Learn more
                        <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </span>
                </a>
            @endforeach
        </div>

        <p class="mt-8 text-center">
            <a href="{{ route('services.index') }}" class="trust-rail-link group">
                Explore All Services
                <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </p>
    </div>
</section>
