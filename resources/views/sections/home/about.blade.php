@php
    $labels = ['Go Digital', 'Sell Online', 'Manage', 'Automate', 'Grow'];
    $icons = ['globe', 'cart', 'layers', 'spark', 'trend'];
    $hrefs = [
        route('services.show', 'web-development'),
        route('services.show', 'ecommerce'),
        route('services.show', 'custom-software'),
        route('services.show', 'ai-automation'),
        route('services.show', 'digital-growth'),
    ];
    $tones = ['', '', '', 'ai', 'growth'];

    $stages = array_merge(
        [[
            'step' => '00',
            'label' => 'Start',
            'icon' => 'chat',
            'href' => consultation_url(),
            'tone' => 'start',
            'items' => ['Business conversation', 'Current process', 'Clear next step'],
        ]],
        collect(config('sarvix.journey'))->values()->map(function ($item, $index) use ($labels, $icons, $hrefs, $tones) {
            return [
                'step' => $item['step'],
                'label' => $labels[$index] ?? $item['title'],
                'icon' => $icons[$index] ?? 'layers',
                'href' => $hrefs[$index] ?? route('about'),
                'tone' => $tones[$index] ?? '',
                'items' => $item['items'],
            ];
        })->all()
    );
@endphp

<section class="section-pad-lg bg-mist" id="journey">
    <div class="container-wide">
        <div class="grid items-end gap-8 lg:grid-cols-12" data-reveal>
            <div class="lg:col-span-7">
                <x-section-heading eyebrow="About SARVIX" title="Technology Built Around Your Business.">
                    SARVIX Technologies provides complete digital and technology solutions designed around real business needs.
                </x-section-heading>
            </div>
            <p class="max-w-xl text-[16px] leading-relaxed text-muted lg:col-span-5">
                We help businesses build their online presence, sell digitally, streamline operations, automate repetitive work and create scalable systems for future growth. We understand business first, then build the right technology.
            </p>
        </div>

        <div class="home-journey-wrap">
            <div class="home-journey-line" aria-hidden="true">
                <span id="journey-progress"></span>
            </div>
            <ol class="home-journey" data-stagger>
            @foreach ($stages as $item)
                <li>
                    <a
                        href="{{ $item['href'] }}"
                        class="home-journey-step{{ $item['tone'] ? ' is-'.$item['tone'] : '' }}"
                        data-journey-node
                    >
                        <span class="home-journey-top">
                            <span class="home-journey-dot">{{ $item['step'] }}</span>
                            <span class="home-journey-icon">
                                <x-icon :name="$item['icon']" class="h-4 w-4" />
                            </span>
                        </span>
                        <h3 class="home-journey-title">{{ $item['label'] }}</h3>
                        <ul class="home-journey-items">
                            @foreach (array_slice($item['items'], 0, 3) as $line)
                                <li>{{ $line }}</li>
                            @endforeach
                        </ul>
                    </a>
                </li>
            @endforeach
            </ol>
        </div>

        <p class="mt-8 text-center">
            <a href="{{ route('about') }}" class="trust-rail-link group">
                Discover SARVIX
                <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </p>
    </div>
</section>
