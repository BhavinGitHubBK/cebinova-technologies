@php
    $items = config('cebinova.page.faq', []);
@endphp

@if (count($items))
    <section class="about-page-faq section-pad-lg" id="page-faq" aria-label="About FAQ">
        <div class="container-wide">
            <div class="about-page-head" data-reveal>
                <p class="about-page-kicker">
                    <span class="about-page-dot" aria-hidden="true"></span>
                    Simple answers
                </p>
                <h2 class="section-title">Questions people ask before they start.</h2>
                <p class="section-support">Short answers. No jargon.</p>
            </div>

            <div class="about-page-faq-list" data-stagger>
                @foreach ($items as $item)
                    <details class="about-page-faq-item">
                        <summary>
                            <span class="about-page-faq-q">{{ $item['q'] }}</span>
                            <span class="about-page-faq-toggle" aria-hidden="true">+</span>
                        </summary>
                        <p class="about-page-faq-a">{{ $item['a'] }}</p>
                    </details>
                @endforeach
            </div>

            <div class="about-page-faq-actions">
                <x-button href="{{ consultation_url() }}">Get Free Consultation</x-button>
                <x-button href="{{ whatsapp_url() }}" variant="outline">Ask on WhatsApp</x-button>
            </div>
        </div>
    </section>
@endif
