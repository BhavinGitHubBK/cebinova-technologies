@php
    $items = config('cebinova.page.faq', []);
@endphp

@if (count($items))
    <section class="sol-page-faq section-pad-lg" id="page-faq" aria-label="Solutions FAQ">
        <div class="container-wide">
            <div class="sol-page-head" data-reveal>
                <p class="sol-page-kicker">
                    <span class="sol-page-dot" aria-hidden="true"></span>
                    Simple answers
                </p>
                <h2 class="section-title">Questions people ask before they start.</h2>
                <p class="section-support">Short answers. No jargon.</p>
            </div>

            <div class="sol-page-faq-list" data-stagger>
                @foreach ($items as $item)
                    <details class="sol-page-faq-item">
                        <summary>
                            <span class="sol-page-faq-q">{{ $item['q'] }}</span>
                            <span class="sol-page-faq-toggle" aria-hidden="true">+</span>
                        </summary>
                        <p class="sol-page-faq-a">{{ $item['a'] }}</p>
                    </details>
                @endforeach
            </div>

            <div class="sol-page-faq-actions">
                <x-button href="{{ consultation_url() }}">Get Free Consultation</x-button>
                <x-button href="{{ whatsapp_url() }}" variant="outline">Ask on WhatsApp</x-button>
            </div>
        </div>
    </section>
@endif
