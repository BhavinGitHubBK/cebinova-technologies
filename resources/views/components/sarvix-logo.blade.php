@props([
    'variant' => 'header',
    'tone' => 'light',
])

@php
    $variant = in_array($variant, ['header', 'full', 'compact'], true) ? $variant : 'header';
    $tone = $tone === 'dark' ? 'dark' : 'light';
    $showTagline = $variant !== 'compact';
@endphp

<a
    href="{{ route('home') }}"
    {{ $attributes->class(['sarvix-logo', 'sarvix-logo--'.$variant, 'sarvix-logo--'.$tone]) }}
    aria-label="SARVIX Technologies home"
>
    <picture>
        <source type="image/webp" srcset="{{ asset('assets/brand/sarvix-s-icon.webp') }}">
        <img
            src="{{ asset('assets/brand/sarvix-s-icon.png') }}"
            alt=""
            class="sarvix-logo-icon"
            width="835"
            height="981"
        >
    </picture>
    <span class="sarvix-logo-content">
        <span class="sarvix-brand-row">
            <span class="sarvix-brand-name">SARVI<span class="sarvix-accent">X</span></span>
            <span class="sarvix-brand-tech">TECHNOLOGIES</span>
        </span>
        @if ($showTagline)
            <span class="sarvix-tagline">{{ config('sarvix.tagline') }}</span>
        @endif
    </span>
</a>
