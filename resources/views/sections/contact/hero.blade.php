<section class="contact-page-hero" aria-label="Contact overview">
    <div class="pointer-events-none absolute inset-0 bg-dots opacity-45"></div>
    <div class="container-wide relative">
        <div class="contact-page-hero-copy" data-reveal>
            <p class="contact-page-kicker">
                <span class="contact-page-dot" aria-hidden="true"></span>
                Contact
            </p>
            <h1 class="contact-page-title">Tell us about your business.</h1>
            <p class="contact-page-lead">
                Share where you are today. We will help you identify the right technology for the next stage of growth.
            </p>
            <ul class="contact-page-proof" aria-label="CEBINOVA contact highlights">
                <li>
                    <span class="contact-page-proof-value">FREE</span>
                    <span class="contact-page-proof-label">Consultation</span>
                </li>
                <li>
                    <span class="contact-page-proof-value">AHM</span>
                    <span class="contact-page-proof-label">Ahmedabad based</span>
                </li>
                <li>
                    <span class="contact-page-proof-value">WA</span>
                    <span class="contact-page-proof-label">Quick replies</span>
                </li>
                <li>
                    <span class="contact-page-proof-value">ONE</span>
                    <span class="contact-page-proof-label">Technology partner</span>
                </li>
            </ul>
            @include('sections.page.hero-ctas', [
                'consultHref' => '#enquiry-form',
                'whatsappHref' => \App\Support\MarketingPackages::isValid((string) request('package_category'), (string) request('plan_duration'))
                    ? package_whatsapp_url((string) request('package_category'), (string) request('plan_duration'))
                    : whatsapp_url(),
            ])
        </div>
    </div>
</section>
