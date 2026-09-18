<section class="mkt-page-steps section-pad-lg" id="how-it-works">
    <div class="container-wide">
        <div class="mkt-page-head" data-reveal>
            <p class="mkt-page-kicker">
                <span class="mkt-page-dot" aria-hidden="true"></span>
                How it works
            </p>
            <h2 class="section-title">A simple path from chat to monthly report.</h2>
            <p class="section-support">
                You always know what happens next. No long onboarding. No confusion.
            </p>
        </div>
        <ol class="mkt-page-steps-grid" data-stagger>
            @foreach (config('cebinova.marketing.how_it_works') as $item)
                <li class="mkt-page-step-card">
                    <p class="mkt-page-step-num">{{ $item['step'] }}</p>
                    <h3 class="mkt-page-step-title">{{ $item['title'] }}</h3>
                    <p class="mkt-page-step-text">{{ $item['text'] }}</p>
                </li>
            @endforeach
        </ol>
        <div class="mkt-page-steps-actions">
            <x-button href="{{ consultation_url('Digital Marketing') }}">Get This Plan</x-button>
            <x-button href="{{ whatsapp_url() }}" variant="outline">Ask on WhatsApp</x-button>
        </div>
    </div>
</section>
