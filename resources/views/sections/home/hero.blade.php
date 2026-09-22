<section class="hero-section relative overflow-hidden bg-paper">
    @php
        $heroSection = \App\Support\CmsContent::section('home', 'hero');
        $heroEyebrow = $heroSection?->subheading ?: config('cebinova.positioning');
        $heroHeading = $heroSection?->heading ?: 'Technology That Helps Every Business Grow.';
        $heroLead = $heroSection?->body ?: 'From your first website to eCommerce, custom software and AI-powered automation, CEBINOVA gives your business the technology it needs to start, operate and grow.';
        $heroCtaLabel = $heroSection?->cta_label ?: 'Get Free Consultation';
        $heroCtaUrl = $heroSection?->cta_url ?: consultation_url();
        $heroSecondaryLabel = $heroSection?->secondary_cta_label ?: 'Explore Our Solutions';
        $heroSecondaryUrl = $heroSection?->secondary_cta_url ?: route('solutions');
        if ($heroCtaUrl === '/contact') {
            $heroCtaUrl = consultation_url();
        }
        if ($heroSecondaryUrl === '/solutions') {
            $heroSecondaryUrl = route('solutions');
        }
        // Split heading for existing visual style when using default/seeded copy
        $headingParts = preg_split('/\s+Every\s+/i', $heroHeading, 2);
        $headingPrimary = $headingParts[0] ?? $heroHeading;
        $headingSecondary = isset($headingParts[1]) ? 'Every '.$headingParts[1] : null;
    @endphp
    <div class="pointer-events-none absolute inset-0 bg-dots opacity-70"></div>
    <div class="hero-mesh pointer-events-none absolute inset-0"></div>
    <div class="hero-lines pointer-events-none absolute inset-0"></div>

    <div class="container-wide relative hero-shell">
        <div class="hero-copy">
            <p class="hero-eyebrow" data-hero-item>
                <span class="hero-eyebrow-dot" aria-hidden="true"></span>
                {{ $heroEyebrow }}
            </p>

            <h1 class="hero-heading" data-hero-item>
                @if ($headingSecondary)
                    <span class="hero-heading-navy">{{ $headingPrimary }}</span> <span class="hero-heading-line">{{ $headingSecondary }}</span>
                @else
                    <span class="hero-heading-navy">{{ $heroHeading }}</span>
                @endif
            </h1>

            <p class="hero-lead" data-hero-item>
                {!! \Illuminate\Support\Str::of(e($heroLead))->replace('CEBINOVA', '<strong>CEBINOVA</strong>') !!}
            </p>

            <div class="hero-actions" data-hero-item>
                <x-button href="{{ $heroCtaUrl }}" class="hero-btn hero-btn-primary group">
                    {{ $heroCtaLabel }}
                    <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </x-button>
                <x-button href="{{ $heroSecondaryUrl }}" variant="outline" class="hero-btn hero-btn-secondary group">
                    {{ $heroSecondaryLabel }}
                    <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </x-button>
            </div>

            @php
                $heroCaps = [
                    ['label' => 'Web', 'icon' => 'globe', 'href' => route('services.show', 'web-development')],
                    ['label' => 'eCommerce', 'icon' => 'store', 'href' => route('services.show', 'ecommerce')],
                    ['label' => 'Software', 'icon' => 'layers', 'href' => route('services.show', 'custom-software')],
                    ['label' => 'Mobile', 'icon' => 'device', 'href' => route('pricing')],
                    ['label' => 'AI', 'icon' => 'spark', 'href' => route('services.show', 'ai-automation')],
                    ['label' => 'Automation', 'icon' => 'nodes', 'href' => route('services.show', 'ai-automation')],
                    ['label' => 'Marketing', 'icon' => 'megaphone', 'href' => route('marketing-packages')],
                    ['label' => 'Growth', 'icon' => 'trend', 'href' => route('services.show', 'digital-growth')],
                ];
            @endphp
            <ul class="hero-caps" data-hero-item aria-label="What CEBINOVA builds">
                @foreach ($heroCaps as $item)
                    <li>
                        <a href="{{ $item['href'] }}" class="hero-cap{{ ! empty($item['accent']) ? ' is-accent' : '' }}" data-hero-cap>
                            <span class="hero-cap-icon-wrap">
                                <x-mark :name="$item['icon']" class="hero-cap-icon" />                            </span>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="hero-visual" data-hero-visual>
            <x-hero-visual />
        </div>

        <div class="hero-advantage" data-hero-item>
            <p class="hero-advantage-kicker">CEBINOVA Advantage</p>
            <ul class="hero-advantage-list">
                @foreach (config('cebinova.capabilities') as $item)
                    <li class="hero-advantage-item">
                        <span class="hero-advantage-value">{{ $item['value'] }}</span>
                        <span class="hero-advantage-label">{{ $item['label'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
