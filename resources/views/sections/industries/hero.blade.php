<section class="ind-page-hero" aria-label="Industries overview">
    @php
        $copy = \App\Support\CmsContent::copy('industries', 'hero', [
            'eyebrow' => 'Industries',
            'heading' => 'Technology for every business.',
            'lead' => 'Sample starting solutions for the kinds of businesses we are built to help. If you do not see yours, tell us how you operate.',
            'secondary_label' => 'View Solutions',
            'secondary_url' => '/solutions',
        ]);
        $proof = \App\Support\CmsContent::slots($copy['proof'], [
            ['value' => '13+', 'label' => 'Industry types'],
            ['value' => 'ONE', 'label' => 'Technology partner'],
            ['value' => '6+', 'label' => 'Solution areas'],
            ['value' => 'E2E', 'label' => 'Digital journey'],
        ]);
    @endphp
    <div class="pointer-events-none absolute inset-0 bg-dots opacity-45"></div>
    <div class="container-wide relative">
        <div class="ind-page-hero-copy" data-reveal>
            <p class="ind-page-kicker">
                <span class="ind-page-dot" aria-hidden="true"></span>
                {{ $copy['eyebrow'] }}
            </p>
            <h1 class="ind-page-title break-words">{{ $copy['heading'] }}</h1>
            <p class="ind-page-lead break-words">{{ $copy['lead'] }}</p>
            <ul class="ind-page-proof" aria-label="CEBINOVA industry highlights">
                @foreach ($proof as $item)
                    <li>
                        <span class="ind-page-proof-value">{{ $item['value'] }}</span>
                        <span class="ind-page-proof-label">{{ $item['label'] }}</span>
                    </li>
                @endforeach
            </ul>
            @include('sections.page.hero-ctas', [
                'consultHref' => \App\Support\CmsContent::href($copy['cta_url'], consultation_url()),
                'consultLabel' => $copy['cta_label'],
                'extraHref' => \App\Support\CmsContent::href($copy['secondary_url'], route('solutions')),
                'extraLabel' => $copy['secondary_label'] ?: 'View Solutions',
            ])
        </div>
    </div>
</section>
