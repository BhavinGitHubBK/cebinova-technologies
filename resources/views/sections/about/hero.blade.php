<section class="about-page-hero" aria-label="About CEBINOVA">
    @php
        $copy = \App\Support\CmsContent::copy('about', 'hero', [
            'eyebrow' => 'About',
            'heading' => 'Technology built around your business.',
            'lead' => 'CEBINOVA Technologies is a complete technology partner for businesses that want to move from traditional operations to smart digital systems - without buying more than they need.',
            'secondary_label' => 'View Services',
            'secondary_url' => '/services',
        ]);
        $proof = \App\Support\CmsContent::slots($copy['proof'], [
            ['value' => 'ONE', 'label' => 'Technology partner'],
            ['value' => '5', 'label' => 'Growth stages'],
            ['value' => '6+', 'label' => 'Solution areas'],
            ['value' => 'E2E', 'label' => 'Digital journey'],
        ]);
    @endphp
    <div class="pointer-events-none absolute inset-0 bg-dots opacity-45"></div>
    <div class="container-wide relative">
        <div class="about-page-hero-copy" data-reveal>
            <p class="about-page-kicker">
                <span class="about-page-dot" aria-hidden="true"></span>
                {{ $copy['eyebrow'] }}
            </p>
            <h1 class="about-page-title break-words">{{ $copy['heading'] }}</h1>
            <p class="about-page-lead break-words">{{ $copy['lead'] }}</p>
            <ul class="about-page-proof" aria-label="CEBINOVA about highlights">
                @foreach ($proof as $item)
                    <li>
                        <span class="about-page-proof-value">{{ $item['value'] }}</span>
                        <span class="about-page-proof-label">{{ $item['label'] }}</span>
                    </li>
                @endforeach
            </ul>
            @include('sections.page.hero-ctas', [
                'consultHref' => \App\Support\CmsContent::href($copy['cta_url'], consultation_url()),
                'consultLabel' => $copy['cta_label'],
                'extraHref' => \App\Support\CmsContent::href($copy['secondary_url'], route('services.index')),
                'extraLabel' => $copy['secondary_label'] ?: 'View Services',
            ])
        </div>
    </div>
</section>
