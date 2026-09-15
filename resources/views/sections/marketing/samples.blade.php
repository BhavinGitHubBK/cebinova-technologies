<section class="section-pad bg-white" id="sample-creatives">
    <div class="container-wide">
        <x-section-heading eyebrow="Sample creatives" title="This is the kind of work you receive.">
            Ready-to-post designs for Instagram, Facebook and WhatsApp. Your logo and offer go on these layouts.
        </x-section-heading>
        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (config('sarvix.marketing.samples') as $sample)
                <article class="sample-card sample-card--{{ $sample['tone'] }}">
                    <p class="sample-card-label">{{ $sample['label'] }}</p>
                    <h3 class="sample-card-title">{{ $sample['title'] }}</h3>
                    <p class="sample-card-meta">{{ $sample['meta'] }}</p>
                    <div class="sample-card-brand">SARVIX</div>
                </article>
            @endforeach
        </div>
        <p class="mt-6 text-[14px] text-muted">These are style samples, not a live client campaign. Your creatives use your brand, products and language.</p>
        <div class="mt-6">
            <x-button href="#marketing-plans">See plan prices</x-button>
        </div>
    </div>
</section>
