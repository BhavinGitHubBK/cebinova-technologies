@props([
    'eyebrow' => null,
    'title',
    'align' => 'left',
    'light' => false,
    'wrap' => false,
])

@php
    $alignClass = $align === 'center' ? 'mx-auto text-center' : '';
    $titleClass = $light ? 'text-white' : 'text-navy';
    $textClass = $light ? 'text-white/72' : 'text-muted';
@endphp

<div {{ $attributes->merge(['class' => $alignClass]) }}>
    @if ($eyebrow)
        <x-badge :tone="$light ? 'gold' : 'blue'" class="mb-3">{{ $eyebrow }}</x-badge>
    @endif
    <h2 class="section-title {{ $titleClass }} {{ $wrap ? 'is-long' : '' }}">{{ $title }}</h2>
    @if ($slot->isNotEmpty())
        <div class="mt-4 max-w-[40rem] text-[16px] leading-relaxed sm:text-[17px] {{ $textClass }}">{{ $slot }}</div>
    @endif
</div>
