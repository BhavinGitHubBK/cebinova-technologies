<section class="svc-page-hero" aria-label="Services overview">
    @php
        $copy = \App\Support\CmsContent::copy('services', 'hero', [
            'eyebrow' => 'Services',
            'heading' => 'Complete technology services. One partner.',
            'lead' => 'Clear categories so you can start with the work that matters now - then add systems and growth support as the business moves forward.',
            'secondary_label' => 'See Solution Pricing',
            'secondary_url' => '/pricing',
        ]);
        $proof = \App\Support\CmsContent::slots($copy['proof'], collect(config('cebinova.capabilities'))->map(fn ($item) => ['value' => $item['value'], 'label' => $item['label']])->take(4)->all());
    @endphp
    <div class="pointer-events-none absolute inset-0 bg-dots opacity-45"></div>
    <div class="container-wide relative">
        <div class="svc-page-hero-copy" data-reveal>
            <p class="svc-page-kicker">
                <span class="svc-page-dot" aria-hidden="true"></span>
                {{ $copy['eyebrow'] }}
            </p>
            <h1 class="svc-page-title break-words">{{ $copy['heading'] }}</h1>
            <p class="svc-page-lead break-words">{{ $copy['lead'] }}</p>
            <ul class="svc-page-proof" aria-label="CEBINOVA service highlights">
                @foreach ($proof as $item)
                    <li>
                        <span class="svc-page-proof-value">{{ $item['value'] }}</span>
                        <span class="svc-page-proof-label">{{ $item['label'] }}</span>
                    </li>
                @endforeach
            </ul>
            @include('sections.page.hero-ctas', [
                'consultHref' => \App\Support\CmsContent::href($copy['cta_url'], consultation_url()),
                'consultLabel' => $copy['cta_label'],
                'extraHref' => \App\Support\CmsContent::href($copy['secondary_url'], route('pricing')),
                'extraLabel' => $copy['secondary_label'] ?: 'See Solution Pricing',
            ])
        </div>
    </div>
</section>
