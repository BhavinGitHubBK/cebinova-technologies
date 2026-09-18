<section class="demo-page-hero" aria-label="Demos overview">
    <div class="pointer-events-none absolute inset-0 bg-dots opacity-45"></div>
    <div class="container-wide relative">
        <div class="demo-page-hero-copy" data-reveal>
            <p class="demo-page-kicker">
                <span class="demo-page-dot" aria-hidden="true"></span>
                Demos
            </p>
            <h1 class="demo-page-title">See what CEBINOVA can build.</h1>
            <p class="demo-page-lead">
                Working digital solution demos for real business categories - not slide decks. Open a demo, then tell us what your business needs.
            </p>
            <ul class="demo-page-proof" aria-label="CEBINOVA demo highlights">
                <li>
                    <span class="demo-page-proof-value">3</span>
                    <span class="demo-page-proof-label">Live demos</span>
                </li>
                <li>
                    <span class="demo-page-proof-value">6</span>
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
                'consultHref' => consultation_url(),
                'extraHref' => route('solutions'),
                'extraLabel' => 'Explore All Solutions',
            ])
        </div>
    </div>
</section>
