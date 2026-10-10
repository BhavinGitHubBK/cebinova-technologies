<section class="price-page-hero" aria-label="Pricing overview">
    @php
        $copy = \App\Support\CmsContent::copy('pricing', 'hero', [
            'eyebrow' => 'Pricing',
            'heading' => 'Choose the Right Solution for Your Business',
            'lead' => 'Start with a clear website package, then add apps, domain, hosting and support only if you need them. See a real estimate before you talk to us.',
            'secondary_label' => 'Open Calculator',
            'secondary_url' => '#calculator',
        ]);
    @endphp
    <div class="pointer-events-none absolute inset-0 bg-dots opacity-45"></div>
    <div class="container-wide relative">
        <div class="price-page-hero-copy" data-reveal>
            <p class="price-page-kicker">
                <span class="price-page-dot" aria-hidden="true"></span>
                {{ $copy['eyebrow'] }}
            </p>
            <h1 class="price-page-title break-words">{{ $copy['heading'] }}</h1>
            <p class="price-page-lead break-words">{{ $copy['lead'] }}</p>
            <ul class="price-page-proof" aria-label="CEBINOVA pricing highlights">
                <li>
                    <span class="price-page-proof-value">₹14,999</span>
                    <span class="price-page-proof-label">Starting one-time</span>
                </li>
                <li>
                    <span class="price-page-proof-value">3</span>
                    <span class="price-page-proof-label">Website packages</span>
                </li>
                <li>
                    <span class="price-page-proof-value">2 min</span>
                    <span class="price-page-proof-label">Live estimate</span>
                </li>
                <li>
                    <span class="price-page-proof-value">0</span>
                    <span class="price-page-proof-label">Hidden charges</span>
                </li>
            </ul>
            @include('sections.page.hero-ctas', [
                'consultHref' => \App\Support\CmsContent::href($copy['cta_url'], consultation_url('Business Solution')),
                'consultLabel' => $copy['cta_label'],
                'extraHref' => \App\Support\CmsContent::href($copy['secondary_url'], '#calculator'),
                'extraLabel' => $copy['secondary_label'] ?: 'Open Calculator',
            ])
            <ol class="price-page-path" aria-label="How to get your price">
                <li><strong>1</strong> Choose website</li>
                <li><strong>2</strong> Choose Mobile App</li>
                <li><strong>3</strong> Choose Domain</li>
                <li><strong>4</strong> Choose Hosting</li>
                <li><strong>5</strong> Choose Support Plan</li>
                <li><strong>6</strong> See Final Price</li>
                <li><strong>7</strong> Book Free Consultation</li>
            </ol>
            <p class="price-page-hint">
                Recommended path is pre-selected: <strong>Business</strong> website, new domain, basic hosting. Change any step if you already have something.
            </p>
        </div>
    </div>
</section>
