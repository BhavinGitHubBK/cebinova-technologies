<section class="home-cta section-pad-lg" aria-label="Next step">
    @php
        $ctaSection = \App\Support\CmsContent::section('home', 'cta');
        $ctaKicker = $ctaSection?->subheading ?: 'Next step';
        $ctaHeading = $ctaSection?->heading ?: 'Ready to Take Your Business Forward?';
        $ctaLead = $ctaSection?->body ?: 'Start with the solution you need today. CEBINOVA can grow with you tomorrow.';
        $ctaPrimaryLabel = $ctaSection?->cta_label ?: 'Get Free Consultation';
        $ctaPrimaryUrl = $ctaSection?->cta_url ?: consultation_url();
        $ctaSecondaryLabel = $ctaSection?->secondary_cta_label ?: 'Talk to CEBINOVA';
        $ctaSecondaryUrl = $ctaSection?->secondary_cta_url ?: whatsapp_url();
        if ($ctaPrimaryUrl === '/contact') {
            $ctaPrimaryUrl = consultation_url();
        }
        if (in_array($ctaSecondaryUrl, ['/pricing', 'pricing'], true)) {
            $ctaSecondaryUrl = route('pricing');
        }
        if (in_array($ctaSecondaryUrl, ['/whatsapp', 'whatsapp'], true)) {
            $ctaSecondaryUrl = whatsapp_url();
        }
    @endphp
    <div class="pointer-events-none absolute inset-0 bg-dots-light"></div>
    <svg class="home-cta-lines" viewBox="0 0 1200 360" aria-hidden="true">
        <g fill="none" stroke="#FBB50B" stroke-opacity="0.28" stroke-width="1.1">
            <path d="M40 220C220 120 380 280 560 180C740 80 920 240 1160 140"/>
            <circle cx="560" cy="180" r="3.5" fill="#FBB50B"/>
            <circle cx="920" cy="240" r="3" fill="#FBB50B"/>
        </g>
    </svg>

    <div class="container-wide relative">
        <div class="home-cta-layout" data-stagger>
            <div class="home-cta-copy">
                <p class="home-cta-kicker">
                    <span class="home-cta-dot" aria-hidden="true"></span>
                    {{ $ctaKicker }}
                </p>
                <h2 class="section-title text-white">{{ $ctaHeading }}</h2>
                <p class="home-cta-support section-support">
                    {!! \Illuminate\Support\Str::of(e($ctaLead))->replace('CEBINOVA', '<strong>CEBINOVA</strong>') !!}
                </p>
                <ol class="home-cta-path" aria-label="Growth path">
                    <li>Website</li>
                    <li>Store</li>
                    <li>Software</li>
                    <li>Automation</li>
                    <li>Growth</li>
                </ol>
                <ul class="home-cta-proof">
                    <li>
                        <span class="home-cta-proof-value">6+</span>
                        <span class="home-cta-proof-label">Solution areas</span>
                    </li>
                    <li>
                        <span class="home-cta-proof-value">ONE</span>
                        <span class="home-cta-proof-label">Technology partner</span>
                    </li>
                    <li>
                        <span class="home-cta-proof-value">E2E</span>
                        <span class="home-cta-proof-label">Digital journey</span>
                    </li>
                </ul>
            </div>

            <aside class="home-cta-panel">
                <p class="home-cta-panel-kicker">Start here</p>
                <p class="home-cta-panel-note">Tell us how the business works today. We will map the first move.</p>
                <p class="home-cta-actions">
                    <x-button href="{{ $ctaPrimaryUrl }}" size="lg" class="group">
                        {{ $ctaPrimaryLabel }}
                        <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </x-button>
                    <x-button href="{{ $ctaSecondaryUrl }}" variant="light" size="lg">
                        @if (str_contains((string) $ctaSecondaryUrl, 'wa.me') || str_contains(strtolower((string) $ctaSecondaryLabel), 'whatsapp') || str_contains(strtolower((string) $ctaSecondaryLabel), 'talk to'))
                            <x-mark name="whatsapp" class="h-4 w-4" />
                        @endif
                        {{ $ctaSecondaryLabel }}
                    </x-button>
                </p>
                <ul class="home-cta-contacts">
                    <li>
                        <a href="tel:{{ preg_replace('/\s+/', '', (string) config('cebinova.contact.phone')) }}">
                            <x-mark name="phone" class="h-3.5 w-3.5" /> {{ config('cebinova.contact.phone') }}
                        </a>
                    </li>
                    <li>
                        <a href="mailto:{{ config('cebinova.contact.email') }}">
                            <x-mark name="mail" class="h-3.5 w-3.5" /> {{ config('cebinova.contact.email') }}
                        </a>
                    </li>
                </ul>
            </aside>
        </div>
    </div>
</section>
