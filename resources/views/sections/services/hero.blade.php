<section class="svc-page-hero" aria-label="Services overview">
    <div class="pointer-events-none absolute inset-0 bg-dots opacity-45"></div>
    <div class="container-wide relative">
        <div class="svc-page-hero-copy" data-reveal>
            <p class="svc-page-kicker">
                <span class="svc-page-dot" aria-hidden="true"></span>
                Services
            </p>
            <h1 class="svc-page-title">Complete technology services. One partner.</h1>
            <p class="svc-page-lead">
                Clear categories so you can start with the work that matters now - then add systems and growth support as the business moves forward.
            </p>
            <ul class="svc-page-proof" aria-label="CEBINOVA service highlights">
                @foreach (config('cebinova.capabilities') as $item)
                    <li>
                        <span class="svc-page-proof-value">{{ $item['value'] }}</span>
                        <span class="svc-page-proof-label">{{ $item['label'] }}</span>
                    </li>
                @endforeach
            </ul>
            @include('sections.page.hero-ctas', [
                'consultHref' => consultation_url(),
                'extraHref' => route('pricing'),
                'extraLabel' => 'Build Your Digital Store',
            ])
        </div>
    </div>
</section>
