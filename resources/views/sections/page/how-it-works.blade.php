@php
    $topic = is_string($topic ?? null) ? $topic : null;
    $title = is_string($howTitle ?? null) ? $howTitle : config('sarvix.page.how_title');
    $text = is_string($howText ?? null) ? $howText : config('sarvix.page.how_text');
@endphp

<section class="section-pad bg-white" id="how-it-works">
    <div class="container-wide">
        <x-section-heading :wrap="true" eyebrow="How it works" :title="$title">
            {{ $text }}
        </x-section-heading>
        <ol class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            @foreach (config('sarvix.process') as $item)
                <li class="rounded-[1.15rem] border border-line bg-mist p-5">
                    <p class="text-[12px] font-bold uppercase tracking-[0.16em] text-gold-dark">{{ $item['step'] }}</p>
                    <h3 class="mt-2 text-lg font-extrabold text-navy">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-[14.5px] leading-relaxed text-muted">{{ $item['text'] }}</p>
                </li>
            @endforeach
        </ol>
        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <x-button href="{{ consultation_url($topic) }}">Get Free Consultation</x-button>
            <x-button href="{{ page_whatsapp_url($topic) }}" variant="outline">Ask on WhatsApp</x-button>
        </div>
    </div>
</section>
