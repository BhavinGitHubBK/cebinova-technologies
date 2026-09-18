<section class="ind-page-hero" aria-label="Industries overview">
    <div class="pointer-events-none absolute inset-0 bg-dots opacity-45"></div>
    <div class="container-wide relative">
        <div class="ind-page-hero-copy" data-reveal>
            <p class="ind-page-kicker">
                <span class="ind-page-dot" aria-hidden="true"></span>
                Industries
            </p>
            <h1 class="ind-page-title">Technology for every business.</h1>
            <p class="ind-page-lead">
                Sample starting solutions for the kinds of businesses we are built to help. If you do not see yours, tell us how you operate.
            </p>
            <ul class="ind-page-proof" aria-label="CEBINOVA industry highlights">
                <li>
                    <span class="ind-page-proof-value">13+</span>
                    <span class="ind-page-proof-label">Industry types</span>
                </li>
                <li>
                    <span class="ind-page-proof-value">ONE</span>
                    <span class="ind-page-proof-label">Technology partner</span>
                </li>
                <li>
                    <span class="ind-page-proof-value">6+</span>
                    <span class="ind-page-proof-label">Solution areas</span>
                </li>
                <li>
                    <span class="ind-page-proof-value">E2E</span>
                    <span class="ind-page-proof-label">Digital journey</span>
                </li>
            </ul>
            @include('sections.page.hero-ctas', [
                'consultHref' => consultation_url(),
                'extraHref' => route('solutions'),
                'extraLabel' => 'View Solutions',
            ])
        </div>
    </div>
</section>
