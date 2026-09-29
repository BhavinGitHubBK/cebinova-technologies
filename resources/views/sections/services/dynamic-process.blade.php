<section class="section-pad bg-white" id="how-it-works">
    <div class="container-wide">
        <x-section-heading :wrap="true" eyebrow="How it works" :title="$section['title']">
            {{ $section['subtitle'] }}
        </x-section-heading>
        <ol class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ($section['items'] as $item)
                <li class="rounded-none border border-line bg-mist p-5">
                    <p class="text-[12px] font-bold uppercase tracking-[0.16em] text-gold-dark">{{ $item['value'] ?: str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                    <h3 class="mt-2 text-lg font-extrabold text-navy">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-[14.5px] leading-relaxed text-muted">{{ $item['description'] }}</p>
                </li>
            @endforeach
        </ol>
        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <x-button href="{{ service_enquiry_url($service) }}">Get Free Consultation</x-button>
            <x-button href="{{ page_whatsapp_url($topic) }}" variant="outline">Ask on WhatsApp</x-button>
        </div>
    </div>
</section>
