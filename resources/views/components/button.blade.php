@props([
    'href' => null,
    'variant' => 'primary',
    'type' => 'button',
    'size' => 'md',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg font-semibold tracking-normal transition duration-300 hover:-translate-y-0.5 disabled:pointer-events-none disabled:opacity-60';
    $sizes = [
        'sm' => 'min-h-10 px-4 py-2 text-sm',
        'md' => 'min-h-11 px-5 py-2.5 text-[15px]',
        'lg' => 'min-h-12 px-6 py-3 text-[15px] sm:px-7 sm:text-base',
    ];
    $variants = [
        'primary' => 'bg-gold text-navy-deep shadow-[0_8px_18px_rgba(201,162,39,0.22)] hover:bg-gold-dark',
        'secondary' => 'bg-navy text-white hover:bg-navy-mid',
        'outline' => 'border border-navy/20 bg-transparent text-navy hover:border-navy hover:bg-navy hover:text-white',
        'ghost' => 'text-navy hover:text-gold-dark',
        'light' => 'border border-white/25 bg-transparent text-white hover:border-gold hover:text-gold',
        'whatsapp' => 'border border-navy/15 bg-white text-navy hover:border-[#25D366]',
    ];
    $classes = $base.' '.($sizes[$size] ?? $sizes['md']).' '.($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} @if (str_starts_with((string) $href, 'https://wa.me')) target="_blank" rel="noopener noreferrer" @endif>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
