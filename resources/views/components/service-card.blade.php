@props([
    'href',
    'icon',
    'title',
    'text',
    'tone' => 'navy',
])

@php
    $iconWrap = match ($tone) {
        'gold' => 'bg-gold text-navy-deep group-hover:bg-navy group-hover:text-gold',
        'mist' => 'bg-mist text-navy group-hover:bg-navy group-hover:text-gold',
        'soft' => 'bg-navy-mid text-gold group-hover:bg-gold group-hover:text-navy-deep',
        default => 'bg-navy text-gold group-hover:bg-gold group-hover:text-navy-deep',
    };
@endphp

<a href="{{ $href }}" class="group lift-card card-surface relative flex h-full flex-col overflow-hidden p-7 sm:p-8">
    <span class="absolute inset-x-0 top-0 h-0.5 bg-gold/0 transition duration-300 group-hover:bg-gold"></span>
    <span class="mb-5 inline-flex h-12 w-12 items-center justify-center rounded-xl {{ $iconWrap }} transition duration-300">
        <x-mark :name="$icon" class="h-5 w-5" />
    </span>
    <h3 class="text-xl font-bold tracking-tight text-navy">{{ $title }}</h3>
    <p class="mt-3 flex-1 text-[15.5px] leading-relaxed text-muted">{{ $text }}</p>
    <span class="mt-6 inline-flex items-center gap-2 text-[15px] font-semibold text-navy transition-colors duration-300 group-hover:text-gold-dark">
        Learn More
        <svg class="h-4 w-4 transition duration-300 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/>
        </svg>
    </span>
</a>
