<section class="price-page-hero" aria-label="Pricing overview">
    <div class="pointer-events-none absolute inset-0 bg-dots opacity-45"></div>
    <div class="container-wide relative">
        <div class="price-page-hero-copy" data-reveal>
            <p class="price-page-kicker">
                <span class="price-page-dot" aria-hidden="true"></span>
                Pricing
            </p>
            <h1 class="price-page-title">Build Your Digital Store</h1>
            <p class="price-page-lead">
                3 website packages. Add apps only if you need them. See your exact total in 2 minutes.
            </p>
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
                'consultHref' => consultation_url('Business Solution'),
                'extraHref' => '#calculator',
                'extraLabel' => 'Open Calculator',
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
