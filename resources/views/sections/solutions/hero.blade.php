<section class="sol-page-hero" aria-label="Solutions overview">
    <div class="pointer-events-none absolute inset-0 bg-dots opacity-45"></div>
    <div class="container-wide relative">
        <div class="sol-page-hero-copy" data-reveal>
            <p class="sol-page-kicker">
                <span class="sol-page-dot" aria-hidden="true"></span>
                Solutions
            </p>
            <h1 class="sol-page-title">Business solutions built for real operations.</h1>
            <p class="sol-page-lead">
                Practical digital solutions for different businesses - with working demos that show what CEBINOVA can build.
            </p>
            <ul class="sol-page-proof" aria-label="CEBINOVA solution highlights">
                <li>
                    <span class="sol-page-proof-value">3</span>
                    <span class="sol-page-proof-label">Live demos</span>
                </li>
                <li>
                    <span class="sol-page-proof-value">6</span>
                    <span class="sol-page-proof-label">Solution areas</span>
                </li>
                <li>
                    <span class="sol-page-proof-value">9+</span>
                    <span class="sol-page-proof-label">Industries</span>
                </li>
                <li>
                    <span class="sol-page-proof-value">E2E</span>
                    <span class="sol-page-proof-label">Growth path</span>
                </li>
            </ul>
            @include('sections.page.hero-ctas', [
                'consultHref' => consultation_url(),
                'extraHref' => route('demos'),
                'extraLabel' => 'View Demos',
            ])
        </div>
    </div>
</section>
