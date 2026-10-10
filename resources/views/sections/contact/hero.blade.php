<section class="contact-page-hero" aria-label="Contact overview">
    @php
        $copy = \App\Support\CmsContent::copy('contact', 'hero', [
            'eyebrow' => 'Contact',
            'heading' => 'Tell us about your business.',
            'lead' => 'Share where you are today. We will help you identify the right technology for the next stage of growth.',
            'cta_url' => '#enquiry-form',
            'secondary_label' => 'Ask on WhatsApp',
        ]);
        $proof = \App\Support\CmsContent::slots($copy['proof'], [
            ['value' => 'FREE', 'label' => 'Consultation'],
            ['value' => 'AHM', 'label' => 'Ahmedabad based'],
            ['value' => 'WA', 'label' => 'Quick replies'],
            ['value' => 'ONE', 'label' => 'Technology partner'],
        ]);
        $whatsappHref = \App\Support\MarketingPackages::isValid((string) request('package_category'), (string) request('plan_duration'))
            ? package_whatsapp_url((string) request('package_category'), (string) request('plan_duration'))
            : \App\Support\CmsContent::href($copy['secondary_url'], whatsapp_url());
    @endphp
    <div class="pointer-events-none absolute inset-0 bg-dots opacity-45"></div>
    <div class="container-wide relative">
        <div class="contact-page-hero-copy" data-reveal>
            <p class="contact-page-kicker">
                <span class="contact-page-dot" aria-hidden="true"></span>
                {{ $copy['eyebrow'] }}
            </p>
            <h1 class="contact-page-title break-words">{{ $copy['heading'] }}</h1>
            <p class="contact-page-lead break-words">{{ $copy['lead'] }}</p>
            <ul class="contact-page-proof" aria-label="CEBINOVA contact highlights">
                @foreach ($proof as $item)
                    <li>
                        <span class="contact-page-proof-value">{{ $item['value'] }}</span>
                        <span class="contact-page-proof-label">{{ $item['label'] }}</span>
                    </li>
                @endforeach
            </ul>
            @include('sections.page.hero-ctas', [
                'showConsult' => false,
                'consultHref' => \App\Support\CmsContent::href($copy['cta_url'], '#enquiry-form'),
                'consultLabel' => $copy['cta_label'],
                'whatsappHref' => $whatsappHref,
                'whatsappLabel' => $copy['secondary_label'] ?: 'Ask on WhatsApp',
            ])
        </div>
    </div>
</section>
