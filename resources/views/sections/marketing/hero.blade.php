<section class="mkt-page-hero is-long" aria-label="Marketing packages overview">
    <div class="pointer-events-none absolute inset-0 bg-dots opacity-45"></div>
    <div class="container-wide relative">
        <div class="mkt-page-hero-copy" data-reveal>
            <p class="mkt-page-kicker">
                <span class="mkt-page-dot" aria-hidden="true"></span>
                Marketing Packages
            </p>
            <h1 class="mkt-page-title">{{ config('cebinova.marketing.hero_title') }}</h1>
            <p class="mkt-page-lead">{{ config('cebinova.marketing.hero_text') }}</p>
            <ul class="mkt-page-proof" aria-label="CEBINOVA marketing highlights">
                <li>
                    <span class="mkt-page-proof-value">₹4,999</span>
                    <span class="mkt-page-proof-label">Starting monthly</span>
                </li>
                <li>
                    <span class="mkt-page-proof-value">3</span>
                    <span class="mkt-page-proof-label">Plan types</span>
                </li>
                <li>
                    <span class="mkt-page-proof-value">4</span>
                    <span class="mkt-page-proof-label">Billing cycles</span>
                </li>
                <li>
                    <span class="mkt-page-proof-value">E2E</span>
                    <span class="mkt-page-proof-label">Content team</span>
                </li>
            </ul>
            @include('sections.page.hero-ctas', [
                'consultHref' => consultation_url('Digital Marketing'),
                'extraHref' => '#marketing-plans',
                'extraLabel' => 'See Plan Prices',
            ])
        </div>
    </div>
</section>
