@props([
    'eyebrow' => null,
    'title',
    'text' => null,
    'wrap' => false,
])

<section class="page-hero {{ $wrap ? 'is-long' : '' }}">
    <div class="pointer-events-none absolute inset-0 bg-dots opacity-50"></div>
    <div class="container-wide relative">
        @if ($eyebrow)
            <x-badge tone="blue">{{ $eyebrow }}</x-badge>
        @endif
        <h1 class="page-hero-title">{{ $title }}</h1>
        @if ($text)
            <p class="page-hero-text">{{ $text }}</p>
        @endif
        @if ($slot->isNotEmpty())
            <div class="mt-8">{{ $slot }}</div>
        @endif
    </div>
</section>
