<section class="section-pad section-soft" id="how-it-works">
    <div class="container-wide">
        <x-section-heading eyebrow="How it works" title="A simple path from chat to monthly report.">
            You always know what happens next. No long onboarding. No confusion.
        </x-section-heading>
        <ol class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            @foreach (config('sarvix.marketing.how_it_works') as $item)
                <li class="rounded-[1.15rem] border border-line bg-white p-5">
                    <p class="text-[12px] font-bold uppercase tracking-[0.16em] text-gold-dark">{{ $item['step'] }}</p>
                    <h3 class="mt-2 text-lg font-extrabold text-navy">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-[14.5px] leading-relaxed text-muted">{{ $item['text'] }}</p>
                </li>
            @endforeach
        </ol>
        <div class="mt-8">
            <x-button href="{{ consultation_url('Digital Marketing') }}">Get This Plan</x-button>
            <x-button href="{{ whatsapp_url() }}" variant="outline" class="ml-0 mt-3 sm:ml-3 sm:mt-0">Ask on WhatsApp</x-button>
        </div>
    </div>
</section>
