@php
    $items = config('cebinova.page.faq', []);
@endphp

@if (count($items))
    <section class="demo-page-faq section-pad-lg" id="page-faq" aria-label="Demos FAQ">
        <div class="container-wide">
            <div class="demo-page-head" data-reveal>
                <p class="demo-page-kicker">
                    <span class="demo-page-dot" aria-hidden="true"></span>
                    Simple answers
                </p>
                <h2 class="section-title">Questions people ask before they start.</h2>
                <p class="section-support">Short answers. No jargon.</p>
            </div>

            <div class="demo-page-faq-list" data-stagger>
                @foreach ($items as $item)
                    <details class="demo-page-faq-item">
                        <summary>
                            <span class="demo-page-faq-q">{{ $item['q'] }}</span>
                            <span class="demo-page-faq-toggle" aria-hidden="true">+</span>
                        </summary>
                        <p class="demo-page-faq-a">{{ $item['a'] }}</p>
                    </details>
                @endforeach
            </div>

            <div class="demo-page-faq-actions">
                <x-button href="{{ consultation_url() }}">Get Free Consultation</x-button>
                <x-button href="{{ whatsapp_url() }}" variant="outline">Ask on WhatsApp</x-button>
            </div>
        </div>
    </section>
@endif
