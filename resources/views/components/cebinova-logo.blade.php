@props([
    'variant' => 'header',
    'tone' => 'light',
])

@php
    $variant = in_array($variant, ['header', 'full', 'compact'], true) ? $variant : 'header';
    $tone = $tone === 'dark' ? 'dark' : 'light';
    $showTagline = $variant !== 'compact';
    $logoVer = (int) @filemtime(public_path('assets/brand/cebinova-c-icon.png'));
    $logoPng = asset('assets/brand/cebinova-c-icon.png').($logoVer ? '?v='.$logoVer : '');
    $logoWebp = asset('assets/brand/cebinova-c-icon.webp').($logoVer ? '?v='.$logoVer : '');
    $logoSvg = asset('assets/brand/cebinova-c-icon.svg').($logoVer ? '?v='.$logoVer : '');
@endphp

<a
    href="{{ route('home') }}"
    {{ $attributes->class(['cebinova-logo', 'cebinova-logo--'.$variant, 'cebinova-logo--'.$tone]) }}
    aria-label="CEBINOVA Technologies home"
>
    <picture>
        <source type="image/png" srcset="{{ $logoPng }}">
        <source type="image/webp" srcset="{{ $logoWebp }}">
        <source type="image/svg+xml" srcset="{{ $logoSvg }}">
        <img
            src="{{ $logoPng }}"
            alt=""
            class="cebinova-logo-icon"
            width="425"
            height="356"
            decoding="async"
        >
    </picture>
    <span class="cebinova-logo-content">
        <span class="cebinova-brand-row">
            <span class="cebinova-brand-name">CEBI<span class="cebinova-accent">NOVA</span></span>
            <span class="cebinova-brand-tech">TECHNOLOGIES</span>
        </span>
        <span class="cebinova-brand-line" aria-hidden="true">
            <span class="cebinova-brand-line-sheen"></span>
            <span class="cebinova-brand-line-node"></span>
        </span>
        @if ($showTagline)
            <span class="cebinova-tagline">{{ config('cebinova.tagline') }}</span>
        @endif
    </span>
</a>
