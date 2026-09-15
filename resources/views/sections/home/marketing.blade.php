<section class="section-pad-lg bg-white" id="home-marketing">
    <div class="container-wide">
        <div data-reveal>
            <x-section-heading eyebrow="Marketing" title="Technology Builds the Business. Marketing Helps It Grow.">
                Three marketing solutions. Four subscription durations. Full launch plans live on Marketing Packages.
            </x-section-heading>
        </div>

        <div class="mt-12 grid gap-5 lg:grid-cols-3" data-stagger>
            <article class="lift-card card-surface flex h-full flex-col p-7 sm:p-8">
                <p class="text-[12px] font-bold uppercase tracking-[0.16em] text-navy/45">Always on</p>
                <h3 class="mt-2 text-[1.45rem] font-extrabold text-navy">Regular Marketing</h3>
                <p class="mt-3 flex-1 text-[15.5px] leading-relaxed text-muted">{{ config('sarvix.marketing.regular.teaser') }}</p>
            </article>
            <article class="lift-card card-surface flex h-full flex-col bg-mist p-7 sm:p-8">
                <p class="text-[12px] font-bold uppercase tracking-[0.16em] text-navy/45">Seasonal</p>
                <h3 class="mt-2 text-[1.45rem] font-extrabold text-navy">Festival Marketing</h3>
                <p class="mt-3 flex-1 text-[15.5px] leading-relaxed text-muted">{{ config('sarvix.marketing.festival.teaser') }}</p>
            </article>
            <article class="lift-card flex h-full flex-col rounded-[1.2rem] border border-gold/40 bg-navy p-7 text-white sm:p-8">
                <p class="inline-flex w-fit rounded-full bg-gold px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-navy-deep">Recommended</p>
                <h3 class="mt-3 text-[1.45rem] font-extrabold text-white">Complete Growth</h3>
                <p class="mt-3 flex-1 text-[15.5px] leading-relaxed text-white/72">{{ config('sarvix.marketing.growth.teaser') }}</p>
            </article>
        </div>

        <div class="mt-8 flex flex-col gap-5 border-t border-line pt-7 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[12px] font-semibold uppercase tracking-[0.16em] text-navy/45">Available durations</p>
                <p class="mt-2 text-[15.5px] font-semibold text-navy">{{ implode('  ·  ', config('sarvix.marketing.frequencies')) }}</p>
            </div>
            <x-button href="{{ route('marketing-packages') }}#marketing-plans">Explore Marketing Plans</x-button>
        </div>
    </div>
</section>
