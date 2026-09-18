@props([
    'href' => null,
    'variant' => 'primary',
    'type' => 'button',
    'size' => 'md',
])

@php
    $base = 'cn-btn group inline-flex items-center justify-center gap-2 font-semibold disabled:pointer-events-none disabled:opacity-60';
    $sizes = [
        'sm' => 'cn-btn--sm',
        'md' => 'cn-btn--md',
        'lg' => 'cn-btn--lg',
    ];
    $variants = [
        'primary' => 'cn-btn--primary',
        'secondary' => 'cn-btn--secondary',
        'outline' => 'cn-btn--outline',
        'ghost' => 'cn-btn--ghost',
        'light' => 'cn-btn--light',
        'whatsapp' => 'cn-btn--whatsapp',
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
