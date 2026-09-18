<<<<<<< Updated upstream
﻿@php
=======
@php
>>>>>>> Stashed changes
    $points = [
        [
            'icon' => 'nodes',
            'cat' => 'Partner',
            'href' => route('about'),
            'cta' => 'About CEBINOVA',
        ],
        [
            'icon' => 'layers',
            'cat' => 'Coverage',
            'href' => route('services.index'),
            'cta' => 'View services',
        ],
        [
            'icon' => 'briefcase',
            'cat' => 'Method',
            'href' => route('about'),
            'cta' => 'How we work',
        ],
        [
            'icon' => 'trend',
            'cat' => 'Scale',
            'href' => route('solutions'),
            'cta' => 'View solutions',
        ],
        [
            'icon' => 'check',
            'cat' => 'Path',
            'href' => route('pricing'),
            'cta' => 'View pricing',
        ],
        [
            'icon' => 'shield',
            'cat' => 'Support',
            'href' => route('contact'),
            'cta' => 'Talk to CEBINOVA',
        ],
    ];
    $why = config('cebinova.why');
@endphp

<section class="home-why section-pad-lg" aria-label="Why CEBINOVA">
    <div class="container-wide">
        <div class="home-why-layout" data-stagger>
            <div class="home-why-aside">
                <article class="home-why-intro">
                    <p class="home-why-kicker">
                        <span class="home-why-dot" aria-hidden="true"></span>
                        Why CEBINOVA
                    </p>
                    <p class="home-why-from">From first website</p>
                    <h2 class="section-title text-white">To complete automation</h2>
                    <p class="home-why-position">{{ config('cebinova.positioning') }}</p>
                    <p class="home-why-copy">CEBINOVA grows with your business. You don't need different vendors for your website, store, software, automation and digital growth.</p>
                    <ol class="home-why-path" aria-hidden="true">
                        <li>Website</li>
                        <li>Store</li>
                        <li>Software</li>
                        <li>Automation</li>
                        <li>Growth</li>
                    </ol>
                    <p class="home-why-cta">
                        <x-button href="{{ route('about') }}" variant="light" size="sm">
                            Discover CEBINOVA
                            <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </x-button>
                    </p>
                </article>

                <aside class="home-why-proof">
                    <p class="home-why-proof-kicker">What you get</p>
                    <ul class="home-why-proof-list">
                        <li>
                            <span class="home-why-proof-value">6+</span>
                            <span class="home-why-proof-label">Solution areas</span>
                        </li>
                        <li>
                            <span class="home-why-proof-value">ONE</span>
                            <span class="home-why-proof-label">Technology partner</span>
                        </li>
                        <li>
                            <span class="home-why-proof-value">E2E</span>
                            <span class="home-why-proof-label">Digital journey</span>
                        </li>
                    </ul>
                    <p class="home-why-proof-note">Tell us how the business works today. We will map the first move.</p>
                    <p class="home-why-proof-cta">
                        <x-button href="{{ consultation_url() }}" size="sm">
                            Get Free Consultation
                            <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </x-button>
                    </p>
                </aside>
            </div>

            <div class="home-why-grid">
                @foreach ($why as $index => $item)
                    @php $point = $points[$index] ?? ['icon' => 'check', 'cat' => 'Reason', 'href' => route('about'), 'cta' => 'Learn more']; @endphp
                    <a href="{{ $point['href'] }}" class="home-why-card">
                        <span class="home-why-icon">
<<<<<<< Updated upstream
                            <x-mark :name="$point['icon']" class="h-4 w-4" />
=======
                            <x-icon :name="$point['icon']" class="h-4 w-4" />
>>>>>>> Stashed changes
                        </span>
                        <span class="home-why-cat">{{ $point['cat'] }}</span>
                        <h3 class="home-why-title">{{ $item['title'] }}</h3>
                        <p class="home-why-note">{{ $item['text'] }}</p>
                        <span class="home-why-link">
                            {{ $point['cta'] }}
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
