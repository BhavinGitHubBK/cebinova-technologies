<section class="section-pad section-soft">
    <div class="container-wide">
        <x-section-heading eyebrow="Optional add-ons" title="Not included - add only if needed.">
            Marketing plans cover design and content. These extras are quoted separately.
        </x-section-heading>
        <ul class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            @foreach (config('sarvix.marketing.addons') as $item)
                <li class="rounded-xl border border-line bg-white px-4 py-4 text-[15px] font-medium text-navy">{{ $item }}</li>
            @endforeach
        </ul>
        <p class="mt-8 text-sm text-muted">Start with the plan. Add extras later only if they help your business.</p>
    </div>
</section>
