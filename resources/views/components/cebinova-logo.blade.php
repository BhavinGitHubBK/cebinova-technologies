@props([
    'variant' => 'header',
    'tone' => 'light',
    'href' => null,
])

@php
    $variant = in_array($variant, ['header', 'full', 'compact'], true) ? $variant : 'header';
    $tone = $tone === 'dark' ? 'dark' : 'light';
    $showTagline = $variant !== 'compact';
    $href = $href ?? route('home');
    $iconFile = $tone === 'dark' && is_file(public_path('assets/brand/cebinova-c-icon-on-dark.png'))
        ? 'cebinova-c-icon-on-dark.png'
        : 'cebinova-c-icon.png';
    $logoVer = (int) @filemtime(public_path('assets/brand/'.$iconFile));
    $logoPng = asset('assets/brand/'.$iconFile).($logoVer ? '?v='.$logoVer : '');
    $logoWebp = $tone === 'dark' ? null : asset('assets/brand/cebinova-c-icon.webp').($logoVer ? '?v='.$logoVer : '');
    $logoSvg = $tone === 'dark' ? null : asset('assets/brand/cebinova-c-icon.svg').($logoVer ? '?v='.$logoVer : '');
@endphp

<a
    href="{{ $href }}"
    {{ $attributes->class(['cebinova-logo', 'cebinova-logo--'.$variant, 'cebinova-logo--'.$tone]) }}
    aria-label="CEBINOVA Technologies"
>
    <picture>
        @if ($logoWebp)
            <source type="image/webp" srcset="{{ $logoWebp }}">
        @endif
        @if ($logoSvg)
            <source type="image/svg+xml" srcset="{{ $logoSvg }}">
        @endif
        <source type="image/png" srcset="{{ $logoPng }}">
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
