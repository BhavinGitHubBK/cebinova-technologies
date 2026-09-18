<section class="mkt-page-faq section-pad-lg" id="marketing-faq">
    <div class="container-wide">
        <div class="mkt-page-head" data-reveal>
            <p class="mkt-page-kicker">
                <span class="mkt-page-dot" aria-hidden="true"></span>
                Simple answers
            </p>
            <h2 class="section-title">Questions clients ask before they start.</h2>
            <p class="section-support">Short answers. No jargon.</p>
        </div>
        <div class="mkt-page-faq-list" data-stagger>
            @foreach (config('cebinova.marketing.faq', []) as $item)
                <details class="mkt-page-faq-item">
                    <summary>
                        <span class="mkt-page-faq-q">{{ $item['q'] }}</span>
                        <span class="mkt-page-faq-toggle" aria-hidden="true">+</span>
                    </summary>
                    <p class="mkt-page-faq-a">{{ $item['a'] }}</p>
                </details>
            @endforeach
        </div>
        <div class="mkt-page-faq-actions">
            <x-button href="{{ consultation_url('Digital Marketing') }}">Get This Plan</x-button>
            <x-button href="{{ whatsapp_url() }}" variant="outline">Ask on WhatsApp</x-button>
        </div>
    </div>
</section>
