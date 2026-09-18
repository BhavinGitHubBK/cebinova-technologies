@php
    $icons = [
        'kirana' => 'bag', 'dairy' => 'home', 'food' => 'store', 'retail' => 'cart',
        'manufacturing' => 'factory', 'wholesale' => 'layers', 'export' => 'globe',
        'professional' => 'briefcase', 'healthcare' => 'heart', 'education' => 'book',
        'real-estate' => 'building', 'startups' => 'rocket', 'services' => 'users',
    ];
    $preview = collect(config('cebinova.nav_solutions.industries'));
    $industries = collect(config('cebinova.industries'))->keyBy('slug');
@endphp

<section class="home-ind section-pad-lg" aria-label="Industries CEBINOVA serves">
    <div class="container-wide">
        <div class="home-ind-head" data-reveal>
            <p class="home-ind-kicker">
                <span class="home-ind-dot" aria-hidden="true"></span>
                Industries
            </p>
            <h2 class="section-title">Technology for Every Business.</h2>
            <p class="section-support">
                A concise look at the kinds of businesses CEBINOVA is built to help.
            </p>
        </div>

        <div class="home-ind-grid" data-stagger>
            @foreach ($preview as $item)
                @php $industry = $industries[$item['slug']] ?? null; @endphp
                @if ($industry)
                    <a href="{{ route('industries') }}#{{ $industry['slug'] }}" class="home-ind-card">
                        <span class="home-ind-icon">
                            <x-mark :name="$icons[$industry['slug']] ?? 'globe'" class="h-4 w-4" />
                        </span>
                        <span class="home-ind-copy">
                            <h3 class="home-ind-title">{{ $item['label'] }}</h3>
                            <p class="home-ind-note">{{ $industry['need'] }}</p>
                        </span>
                        <span class="home-ind-arrow" aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </span>
                    </a>
                @endif
            @endforeach

            <a href="{{ route('industries') }}" class="home-ind-more">
                <span class="home-ind-more-kicker">
                    <span class="home-ind-dot" aria-hidden="true"></span>
                    Your industry
                </span>
                <h3 class="home-ind-more-title">Don't see your business type?</h3>
                <p class="home-ind-more-note">Kirana, dairy, export and other trades are covered too. CEBINOVA still maps a first move around how you work.</p>
                <span class="home-ind-more-link">
                    View all industries
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </span>
            </a>
        </div>
    </div>
</section>
