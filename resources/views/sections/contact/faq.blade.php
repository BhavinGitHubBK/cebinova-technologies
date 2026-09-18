@php
    $items = config('cebinova.page.faq', []);
@endphp

@if (count($items))
    <section class="contact-page-faq section-pad-lg bg-white" id="page-faq" aria-label="Contact FAQ">
        <div class="container-wide">
            <div class="contact-page-head" data-reveal>
                <p class="contact-page-kicker">
                    <span class="contact-page-dot" aria-hidden="true"></span>
                    Simple answers
                </p>
                <h2 class="section-title">Questions people ask before they start.</h2>
                <p class="section-support">Short answers. No jargon.</p>
            </div>

            <div class="contact-page-faq-list" data-stagger>
                @foreach ($items as $item)
                    <details class="contact-page-faq-item">
                        <summary>
                            <span class="contact-page-faq-q">{{ $item['q'] }}</span>
                            <span class="contact-page-faq-toggle" aria-hidden="true">+</span>
                        </summary>
                        <p class="contact-page-faq-a">{{ $item['a'] }}</p>
                    </details>
                @endforeach
            </div>

            <div class="contact-page-faq-actions">
                <x-button href="#enquiry-form">Get Free Consultation</x-button>
                <x-button href="{{ whatsapp_url() }}" variant="outline">Ask on WhatsApp</x-button>
            </div>
        </div>
    </section>
@endif
