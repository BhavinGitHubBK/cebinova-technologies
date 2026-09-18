@php
    $items = config('cebinova.page.faq', []);
@endphp

@if (count($items))
    <section class="svc-page-faq section-pad-lg" id="page-faq" aria-label="Services FAQ">
        <div class="container-wide">
            <div class="svc-page-head" data-reveal>
                <p class="svc-page-kicker">
                    <span class="svc-page-dot" aria-hidden="true"></span>
                    Simple answers
                </p>
                <h2 class="section-title">Questions people ask before they start.</h2>
                <p class="section-support">Short answers. No jargon.</p>
            </div>

            <div class="svc-page-faq-list" data-stagger>
                @foreach ($items as $item)
                    <details class="svc-page-faq-item">
                        <summary>
                            <span class="svc-page-faq-q">{{ $item['q'] }}</span>
                            <span class="svc-page-faq-toggle" aria-hidden="true">+</span>
                        </summary>
                        <p class="svc-page-faq-a">{{ $item['a'] }}</p>
                    </details>
                @endforeach
            </div>

            <div class="svc-page-faq-actions">
                <x-button href="{{ consultation_url() }}">Get Free Consultation</x-button>
                <x-button href="{{ whatsapp_url() }}" variant="outline">Ask on WhatsApp</x-button>
            </div>
        </div>
    </section>
@endif
