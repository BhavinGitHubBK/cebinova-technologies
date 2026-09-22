@php    $cats = [
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

<section class="home-sol section-pad-lg" aria-label="CEBINOVA solution paths">
    <div class="container-wide">
        <div class="home-sol-head" data-reveal>
            <p class="home-sol-kicker">
                <span class="home-sol-dot" aria-hidden="true"></span>
                Solutions
            </p>
            <h2 class="section-title">Start With What Your Business Needs Today.</h2>
            <p class="section-support">
                Choose a starting path. You can add selling, systems and automation when the business is ready.
            </p>
        </div>

        <div class="home-sol-grid" data-stagger>
            @foreach ($paths as $path)
                @php $highlight = $path['key'] === 'automate'; @endphp
                <a href="{{ route('solutions') }}#{{ $path['key'] }}" class="home-sol-card{{ $highlight ? ' is-featured' : '' }}">
                    <span class="home-sol-top">
                        <span class="home-sol-icon">
                            <x-mark :name="$icons[$path['key']] ?? 'layers'" class="h-4 w-4" />                        </span>
                        @if ($highlight)
                            <span class="home-sol-flag">Recommended</span>
                        @endif
                    </span>
                    <span class="home-sol-cat">{{ $cats[$path['key']] ?? 'Path' }}</span>
                    <h3 class="home-sol-title">{{ $path['title'] }}</h3>
                    <p class="home-sol-note">{{ $path['for'] }}</p>
                    <ul class="home-sol-list">
                        @foreach ($path['includes'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                    <span class="home-sol-link">
                        Explore path
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </span>
                </a>
            @endforeach
        </div>

        <p class="home-sol-foot">Website, app, domain and hosting prices are on the Pricing page. Marketing retainers are listed separately under Marketing Packages.</p>
    </div>
</section>
