<section class="sol-page-hero" aria-label="Solutions overview">
    @php
        $copy = \App\Support\CmsContent::copy('solutions', 'hero', [
            'eyebrow' => 'Solutions',
            'heading' => 'Business solutions built for real operations.',
            'lead' => 'Practical digital solutions for different businesses - with working demos that show what CEBINOVA can build.',
            'secondary_label' => 'View Demos',
            'secondary_url' => '/demos',
        ]);
        $proof = \App\Support\CmsContent::slots($copy['proof'], [
            ['value' => '3', 'label' => 'Live demos'],
            ['value' => '6', 'label' => 'Solution areas'],
            ['value' => '9+', 'label' => 'Industries'],
            ['value' => 'E2E', 'label' => 'Growth path'],
        ]);
    @endphp
    <div class="pointer-events-none absolute inset-0 bg-dots opacity-45"></div>
    <div class="container-wide relative">
        <div class="sol-page-hero-copy" data-reveal>
            <p class="sol-page-kicker">
                <span class="sol-page-dot" aria-hidden="true"></span>
                {{ $copy['eyebrow'] }}
            </p>
            <h1 class="sol-page-title break-words">{{ $copy['heading'] }}</h1>
            <p class="sol-page-lead break-words">{{ $copy['lead'] }}</p>
            <ul class="sol-page-proof" aria-label="CEBINOVA solution highlights">
                @foreach ($proof as $item)
                    <li>
                        <span class="sol-page-proof-value">{{ $item['value'] }}</span>
                        <span class="sol-page-proof-label">{{ $item['label'] }}</span>
                    </li>
                @endforeach
            </ul>
            @include('sections.page.hero-ctas', [
                'consultHref' => \App\Support\CmsContent::href($copy['cta_url'], consultation_url()),
                'consultLabel' => $copy['cta_label'],
                'extraHref' => \App\Support\CmsContent::href($copy['secondary_url'], route('demos')),
                'extraLabel' => $copy['secondary_label'] ?: 'View Demos',
            ])
        </div>
    </div>
</section>
