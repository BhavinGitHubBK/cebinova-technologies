<section class="demo-page-hero" aria-label="Demos overview">
    @php
        $copy = \App\Support\CmsContent::copy('demos', 'hero', [
            'eyebrow' => 'Demos',
            'heading' => 'See what CEBINOVA can build.',
            'lead' => 'Working digital solution demos for real business categories - not slide decks. Open a demo, then tell us what your business needs.',
            'secondary_label' => 'Explore All Solutions',
            'secondary_url' => '/solutions',
        ]);
    @endphp
    <div class="pointer-events-none absolute inset-0 bg-dots opacity-45"></div>
    <div class="container-wide relative">
        <div class="demo-page-hero-copy" data-reveal>
            <p class="demo-page-kicker">
                <span class="demo-page-dot" aria-hidden="true"></span>
                {{ $copy['eyebrow'] }}
            </p>
            <h1 class="demo-page-title break-words">{{ $copy['heading'] }}</h1>
            <p class="demo-page-lead break-words">{{ $copy['lead'] }}</p>
            <ul class="demo-page-proof" aria-label="CEBINOVA demo highlights">
                <li>
                    <span class="demo-page-proof-value">{{ $demos->map(fn ($demo) => $demo->liveLink())->filter()->unique()->count() }}</span>
                    <span class="demo-page-proof-label">Live demos</span>
                </li>
                <li>
                    <span class="demo-page-proof-value">{{ $demos->count() }}</span>
                    <span class="demo-page-proof-label">Solution areas</span>
                </li>
                <li>
                    <span class="demo-page-proof-value">LIVE</span>
                    <span class="demo-page-proof-label">Clickable previews</span>
                </li>
                <li>
                    <span class="demo-page-proof-value">ONE</span>
                    <span class="demo-page-proof-label">Technology partner</span>
                </li>
            </ul>
            @include('sections.page.hero-ctas', [
                'consultHref' => \App\Support\CmsContent::href($copy['cta_url'], consultation_url()),
                'consultLabel' => $copy['cta_label'],
                'extraHref' => \App\Support\CmsContent::href($copy['secondary_url'], route('solutions')),
                'extraLabel' => $copy['secondary_label'] ?: 'Explore All Solutions',
            ])
        </div>
    </div>
</section>
