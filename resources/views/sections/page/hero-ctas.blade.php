@php
    $whatsappHref = $whatsappHref ?? whatsapp_url();
    $whatsappLabel = $whatsappLabel ?? 'Ask on WhatsApp';
    $consultLabel = $consultLabel ?? 'Get Free Consultation';
    $showConsult = $showConsult ?? true;
    $extraHref = $extraHref ?? null;
    $extraLabel = $extraLabel ?? null;
@endphp

<div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
    @if ($showConsult)
        <x-button href="{{ $consultHref }}" class="group">
            {{ $consultLabel }}
            <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
        </x-button>
    @endif
    <x-button href="{{ $whatsappHref }}" variant="outline">{{ $whatsappLabel }}</x-button>
    @if ($extraHref && $extraLabel)
        <x-button href="{{ $extraHref }}" variant="ghost">{{ $extraLabel }}</x-button>
    @endif
</div>
