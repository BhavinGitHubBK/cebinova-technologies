@php
    $items = config('cebinova.page.faq', []);
@endphp

@if (count($items))
    <section class="ind-page-faq section-pad-lg" id="page-faq" aria-label="Industries FAQ">
        <div class="container-wide">
            <div class="ind-page-head" data-reveal>
                <p class="ind-page-kicker">
                    <span class="ind-page-dot" aria-hidden="true"></span>
                    Simple answers
                </p>
                <h2 class="section-title">Questions people ask before they start.</h2>
                <p class="section-support">Short answers. No jargon.</p>
            </div>

            <div class="ind-page-faq-list" data-stagger>
                @foreach ($items as $item)
                    <details class="ind-page-faq-item">
                        <summary>
                            <span class="ind-page-faq-q">{{ $item['q'] }}</span>
                            <span class="ind-page-faq-toggle" aria-hidden="true">+</span>
                        </summary>
                        <p class="ind-page-faq-a">{{ $item['a'] }}</p>
                    </details>
                @endforeach
            </div>

            <div class="ind-page-faq-actions">
                <x-button href="{{ consultation_url() }}">Get Free Consultation</x-button>
                <x-button href="{{ whatsapp_url() }}" variant="outline">Ask on WhatsApp</x-button>
            </div>
        </div>
    </section>
@endif
