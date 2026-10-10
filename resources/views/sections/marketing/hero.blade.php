<section class="mkt-page-hero is-long" aria-label="Marketing packages overview">
    @php
        $copy = \App\Support\CmsContent::copy('marketing', 'hero', [
            'eyebrow' => 'Marketing Packages',
            'heading' => config('cebinova.marketing.hero_title'),
            'lead' => config('cebinova.marketing.hero_text'),
            'secondary_label' => 'See Plan Prices',
            'secondary_url' => '#marketing-plans',
        ]);
    @endphp
    <div class="pointer-events-none absolute inset-0 bg-dots opacity-45"></div>
    <div class="container-wide relative">
        <div class="mkt-page-hero-copy" data-reveal>
            <p class="mkt-page-kicker">
                <span class="mkt-page-dot" aria-hidden="true"></span>
                {{ $copy['eyebrow'] }}
            </p>
            <h1 class="mkt-page-title break-words">{{ $copy['heading'] }}</h1>
            <p class="mkt-page-lead break-words">{{ $copy['lead'] }}</p>
            <ul class="mkt-page-proof" aria-label="CEBINOVA marketing highlights">
                <li>
                    <span class="mkt-page-proof-value">{{ cebinova_inr(4999) }}</span>
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
                'consultHref' => \App\Support\CmsContent::href($copy['cta_url'], consultation_url('Digital Marketing')),
                'consultLabel' => $copy['cta_label'],
                'extraHref' => \App\Support\CmsContent::href($copy['secondary_url'], '#marketing-plans'),
                'extraLabel' => $copy['secondary_label'] ?: 'See Plan Prices',
            ])
        </div>
    </div>
</section>
