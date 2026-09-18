<section class="sol-page-ind section-pad-lg" aria-label="Industries">
    <div class="container-wide">
        <div class="sol-page-head" data-reveal>
            <p class="sol-page-kicker">
                <span class="sol-page-dot" aria-hidden="true"></span>
                Industries
            </p>
            <h2 class="section-title">Built for the way different businesses operate.</h2>
            <p class="section-support">
                Detailed industry notes stay on the industries page. Start here, then tell us how you work.
            </p>
        </div>

        <div class="sol-page-ind-grid" data-stagger>
            @foreach (config('cebinova.nav_solutions.industries') as $item)
                <a href="{{ route('industries') }}#{{ $item['slug'] }}" class="sol-page-ind-chip">
                    <span class="sol-page-ind-dot" aria-hidden="true"></span>
                    {{ $item['label'] }}
                    <svg class="sol-page-ind-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            @endforeach
        </div>

        <p class="sol-page-ind-cta">
            <x-button href="{{ route('industries') }}" variant="outline" size="sm">
                View All Industries
                <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </x-button>
        </p>
    </div>
</section>
