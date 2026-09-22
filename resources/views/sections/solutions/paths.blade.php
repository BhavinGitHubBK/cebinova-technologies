@php
    $cats = [
        'starter' => 'Presence',
        'sell' => 'Commerce',
        'manage' => 'Operations',
        'automate' => 'Growth',
    ];
    $icons = [
        'starter' => 'globe',
        'sell' => 'cart',
        'manage' => 'layers',
        'automate' => 'spark',
    ];
    $paths = config('cebinova.solution_paths');
@endphp

<section class="sol-page-paths section-pad-lg" aria-label="Growth paths">
    <div class="container-wide">
        <div class="sol-page-head" data-reveal>
            <p class="sol-page-kicker">
                <span class="sol-page-dot" aria-hidden="true"></span>
                Growth path
            </p>
            <h2 class="section-title">Start with what your business needs today.</h2>
            <p class="section-support">
                These paths stay available as a growth sequence. Business solutions above are how that work looks in practice.
            </p>
            <p class="sol-page-path-track">Starter → Sell Online → Manage → Automate &amp; Scale</p>
        </div>

        <div class="sol-page-path-grid" data-stagger>
            @foreach ($paths as $path)
                @php $highlight = $path['key'] === 'automate'; @endphp
                <article id="{{ $path['key'] }}" class="sol-page-path-card scroll-mt-28{{ $highlight ? ' is-featured' : '' }}">
                    <span class="sol-page-path-icon">
                        <x-mark :name="$icons[$path['key']] ?? 'layers'" class="h-4 w-4" />
                    </span>
                    <span class="sol-page-path-meta">
                        <span class="sol-page-path-cat">{{ $cats[$path['key']] ?? 'Path' }}</span>
                        @if ($highlight)
                            <span class="sol-page-path-flag">Recommended</span>
                        @endif
                    </span>
                    <h3 class="sol-page-path-title">{{ $path['title'] }}</h3>
                    <p class="sol-page-path-text">{{ $path['for'] }}</p>
                    <ul class="sol-page-path-list">
                        @foreach ($path['includes'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                    <p class="sol-page-path-service">{{ $path['service'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
