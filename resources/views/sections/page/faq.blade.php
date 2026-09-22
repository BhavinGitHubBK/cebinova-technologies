@php
    use App\Support\CmsContent;

    $topic = is_string($topic ?? null) ? $topic : null;
    if (isset($faqItems) && is_array($faqItems) && isset($faqItems[0]['q'])) {
        $items = $faqItems;
    } elseif (isset($items) && is_array($items) && isset($items[0]['q'])) {
        $items = $items;
    } else {
        $items = CmsContent::faqs($faqPage ?? 'general');
    }
@endphp

@if (count($items))
    <section class="section-pad section-soft" id="page-faq">
        <div class="container-wide">
            <x-section-heading :wrap="true" eyebrow="Simple answers" title="Questions people ask before they start.">
                Short answers. No jargon.
            </x-section-heading>
            <div class="mt-10 divide-y divide-line overflow-hidden rounded-none border border-line bg-white">
                @foreach ($items as $item)
                    <details class="group">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-6 px-5 py-5 text-[16px] font-extrabold leading-snug text-navy marker:content-none hover:bg-mist sm:px-8 [&::-webkit-details-marker]:hidden">
                            <span>{{ $item['q'] }}</span>
                            <span class="shrink-0 text-lg leading-none text-navy/35 transition group-open:rotate-45" aria-hidden="true">+</span>
                        </summary>
                        <p class="px-5 pb-5 text-[15.5px] leading-relaxed text-muted sm:px-8">{{ $item['a'] }}</p>
                    </details>
                @endforeach
            </div>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <x-button href="{{ consultation_url($topic) }}">Get Free Consultation</x-button>
                <x-button href="{{ page_whatsapp_url($topic) }}" variant="outline">Ask on WhatsApp</x-button>
            </div>
        </div>
    </section>
@endif
