@php
    $isWhatsApp = str_starts_with(whatsapp_url(), 'https://wa.me');
@endphp
<a
    href="{{ whatsapp_url() }}"
    id="floatWhatsapp"
    class="fixed bottom-5 right-5 z-40 inline-flex items-center gap-2 rounded-full bg-navy px-4 py-3 text-sm font-semibold text-white shadow-[0_12px_30px_rgba(11,31,58,0.28)] transition hover:bg-navy-mid"
    aria-label="Talk to SARVIX"
    @if ($isWhatsApp) target="_blank" rel="noopener noreferrer" @endif
>
    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[#25D366] text-white">
        <x-icon name="whatsapp" class="h-4 w-4" />
    </span>
    <span class="hidden sm:inline">Talk to SARVIX</span>
</a>
