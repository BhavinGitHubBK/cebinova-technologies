<section class="section-pad section-soft" id="marketing-trust">
    <div class="container-wide">
        <x-section-heading eyebrow="Trust" title="Clear price. Clear work. Clear next step.">
            GST and ad spend are never hidden inside the package. You always know what is included.
        </x-section-heading>
        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (config('sarvix.marketing.trust') as $item)
                <article class="rounded-[1.15rem] border border-line bg-white p-6">
                    <h3 class="text-lg font-extrabold text-navy">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-[14.5px] leading-relaxed text-muted">{{ $item['text'] }}</p>
                </article>
            @endforeach
        </div>
        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <x-button href="{{ consultation_url('Digital Marketing') }}">Book a free consultation</x-button>
            <x-button href="{{ whatsapp_url() }}" variant="outline">Ask on WhatsApp</x-button>
        </div>
    </div>
</section>
