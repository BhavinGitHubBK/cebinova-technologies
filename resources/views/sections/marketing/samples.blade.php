<section class="mkt-page-samples section-pad-lg bg-white" id="sample-creatives">
    <div class="container-wide">
        <div class="mkt-page-head" data-reveal>
            <p class="mkt-page-kicker">
                <span class="mkt-page-dot" aria-hidden="true"></span>
                Sample creatives
            </p>
            <h2 class="section-title">This is the kind of work you receive.</h2>
            <p class="section-support">
                Ready-to-post designs for Instagram, Facebook and WhatsApp. Your logo and offer go on these layouts.
            </p>
        </div>
        <div class="mkt-page-samples-grid" data-stagger>
            @foreach (config('cebinova.marketing.samples') as $sample)
                <article class="sample-card sample-card--{{ $sample['tone'] }}">
                    <p class="sample-card-label">{{ $sample['label'] }}</p>
                    <h3 class="sample-card-title">{{ $sample['title'] }}</h3>
                    <p class="sample-card-meta">{{ $sample['meta'] }}</p>
                    <div class="sample-card-brand">CEBINOVA</div>
                </article>
            @endforeach
        </div>
        <p class="mkt-page-samples-note">These are style samples, not a live client campaign. Your creatives use your brand, products and language.</p>
        <div class="mkt-page-samples-actions">
            <x-button href="#marketing-plans">See plan prices</x-button>
        </div>
    </div>
</section>
