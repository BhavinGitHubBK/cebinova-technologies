<section class="mkt-page-trust section-pad-lg bg-white" id="marketing-trust">
    <div class="container-wide">
        <div class="mkt-page-head" data-reveal>
            <p class="mkt-page-kicker">
                <span class="mkt-page-dot" aria-hidden="true"></span>
                Trust
            </p>
            <h2 class="section-title">Clear price. Clear work. Clear next step.</h2>
            <p class="section-support">
                GST and ad spend are never hidden inside the package. You always know what is included.
            </p>
        </div>
        <div class="mkt-page-trust-grid" data-stagger>
            @foreach (config('cebinova.marketing.trust') as $item)
                <article class="mkt-page-trust-card">
                    <h3 class="mkt-page-trust-title">{{ $item['title'] }}</h3>
                    <p class="mkt-page-trust-text">{{ $item['text'] }}</p>
                </article>
            @endforeach
        </div>
        <div class="mkt-page-trust-actions">
            <x-button href="{{ consultation_url('Digital Marketing') }}">Book a free consultation</x-button>
            <x-button href="{{ whatsapp_url() }}" variant="outline">Ask on WhatsApp</x-button>
        </div>
    </div>
</section>
