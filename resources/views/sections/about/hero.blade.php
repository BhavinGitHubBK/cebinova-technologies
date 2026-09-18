<section class="about-page-hero" aria-label="About CEBINOVA">
    <div class="pointer-events-none absolute inset-0 bg-dots opacity-45"></div>
    <div class="container-wide relative">
        <div class="about-page-hero-copy" data-reveal>
            <p class="about-page-kicker">
                <span class="about-page-dot" aria-hidden="true"></span>
                About
            </p>
            <h1 class="about-page-title">Technology built around your business.</h1>
            <p class="about-page-lead">
                CEBINOVA Technologies is a complete technology partner for businesses that want to move from traditional operations to smart digital systems - without buying more than they need.
            </p>
            <ul class="about-page-proof" aria-label="CEBINOVA about highlights">
                <li>
                    <span class="about-page-proof-value">ONE</span>
                    <span class="about-page-proof-label">Technology partner</span>
                </li>
                <li>
                    <span class="about-page-proof-value">5</span>
                    <span class="about-page-proof-label">Growth stages</span>
                </li>
                <li>
                    <span class="about-page-proof-value">6+</span>
                    <span class="about-page-proof-label">Solution areas</span>
                </li>
                <li>
                    <span class="about-page-proof-value">E2E</span>
                    <span class="about-page-proof-label">Digital journey</span>
                </li>
            </ul>
            @include('sections.page.hero-ctas', [
                'consultHref' => consultation_url(),
                'extraHref' => route('services.index'),
                'extraLabel' => 'View Services',
            ])
        </div>
    </div>
</section>
