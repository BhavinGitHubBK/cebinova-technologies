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
    $cats = ['Presence', 'Commerce', 'Operations', 'Intelligence', 'Growth'];
    $notes = [
        'Be findable and clear online.',
        'Take orders without extra admin.',
        'Run day-to-day work in one system.',
        'Cut repetitive follow-ups and tasks.',
        'Bring in the next set of customers.',
    ];

    $stages = array_merge(
        [[
            'label' => 'Start',
            'icon' => 'chat',
            'href' => consultation_url(),
            'tone' => 'start',
            'cat' => 'Brief',
            'note' => 'Map the need, then pick the first move.',
            'items' => ['Business conversation', 'Current process', 'Clear next step'],
        ]],
        collect(config('cebinova.journey'))->values()->map(function ($item, $index) use ($labels, $icons, $hrefs, $tones, $cats, $notes) {
            return [
                'label' => $labels[$index] ?? $item['title'],
                'icon' => $icons[$index] ?? 'layers',
                'href' => $hrefs[$index] ?? route('about'),
                'tone' => $tones[$index] ?? '',
                'cat' => $cats[$index] ?? 'Stage',
                'note' => $notes[$index] ?? '',
                'items' => $item['items'],
            ];
        })->all()
    );
@endphp

<section class="home-about section-pad-lg" id="journey" aria-label="How CEBINOVA works with your business">
    <div class="container-wide">
        <div class="home-about-head" data-reveal>
            <p class="home-about-kicker">
                <span class="home-about-dot" aria-hidden="true"></span>
                About CEBINOVA
            </p>
            <div class="home-about-intro">
                <div>
                    <h2 class="section-title">Technology Built Around Your Business.</h2>
                    <p class="home-about-lead">
                        CEBINOVA Technologies provides complete digital and technology solutions designed around real business needs.
                    </p>
                </div>
                <p class="home-about-copy">
                    We help businesses build their online presence, sell digitally, streamline operations, automate repetitive work and create scalable systems for future growth. We understand business first, then build the right technology.
                </p>
            </div>
        </div>

        <div class="home-journey-wrap">
            <div class="home-journey-line" aria-hidden="true">
                <span id="journey-progress"></span>
            </div>
            <ol class="home-journey" data-stagger>
                @foreach ($stages as $item)
                    <li class="home-journey-cell{{ $item['tone'] ? ' is-'.$item['tone'] : '' }}" data-journey-node>
                        <span class="home-journey-node" aria-hidden="true"></span>
                        <a href="{{ $item['href'] }}" class="home-journey-step">
                            <span class="home-journey-icon">
                                <x-mark :name="$item['icon']" class="h-4 w-4" />
                            </span>
                            <span class="home-journey-copy">
                                <span class="home-journey-cat">{{ $item['cat'] }}</span>
                                <h3 class="home-journey-title">{{ $item['label'] }}</h3>
                                @if ($item['note'] !== '')
                                    <span class="home-journey-note">{{ $item['note'] }}</span>
                                @endif
                                <ul class="home-journey-items">
                                    @foreach (array_slice($item['items'], 0, 3) as $line)
                                        <li>{{ $line }}</li>
                                    @endforeach
                                </ul>
                            </span>
                            <span class="home-journey-arrow" aria-hidden="true">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ol>
        </div>

        <p class="home-about-cta">
            <x-button :href="route('about')" variant="outline" size="sm">
                Discover CEBINOVA
                <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </x-button>
        </p>
    </div>
</section>
