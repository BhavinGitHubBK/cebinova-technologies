<<<<<<< Updated upstream
﻿@php
=======
@php
>>>>>>> Stashed changes
    $modules = [
        ['icon' => 'cart', 'cat' => 'Selling', 'href' => route('services.show', 'ecommerce')],
        ['icon' => 'briefcase', 'cat' => 'Operations', 'href' => route('services.show', 'custom-software')],
        ['icon' => 'spark', 'cat' => 'Intelligence', 'href' => route('services.show', 'ai-automation')],
        ['icon' => 'nodes', 'cat' => 'Workflows', 'href' => route('services.show', 'ai-automation')],
        ['icon' => 'layers', 'cat' => 'Platform', 'href' => route('solutions')],
    ];
    $path = ['Commerce', 'Business', 'AI', 'Automate', 'Cloud'];
@endphp

<section class="home-eco section-pad-lg" aria-label="Future CEBINOVA ecosystem">
    <div class="container-wide">
        <div class="home-eco-head" data-reveal>
            <p class="home-eco-kicker">
                <span class="home-eco-dot" aria-hidden="true"></span>
                Future ecosystem
            </p>
            <h2 class="section-title">More Than a Service Company.</h2>
            <p class="section-support">
                IT services today. A connected technology platform tomorrow.
            </p>
            <p class="home-eco-copy">
                Our vision is to build a connected CEBINOVA ecosystem where businesses can manage their websites, marketing, support, software and automation from one platform.
            </p>
            <ol class="home-eco-path" aria-label="Ecosystem path">
                @foreach ($path as $step)
                    <li>{{ $step }}</li>
                @endforeach
            </ol>
        </div>

        <div class="home-eco-grid" data-stagger>
            @foreach (config('cebinova.ecosystem') as $index => $item)
                @php $module = $modules[$index] ?? ['icon' => 'layers', 'cat' => 'Platform', 'href' => route('solutions')]; @endphp
                <a href="{{ $module['href'] }}" class="home-eco-card{{ $loop->last ? ' is-last' : '' }}">
                    <span class="home-eco-top">
                        <span class="home-eco-icon">
<<<<<<< Updated upstream
                            <x-mark :name="$module['icon']" class="h-4 w-4" />
=======
                            <x-icon :name="$module['icon']" class="h-4 w-4" />
>>>>>>> Stashed changes
                        </span>
                        <span class="home-eco-flag">In vision</span>
                    </span>
                    <span class="home-eco-cat">{{ $module['cat'] }}</span>
                    <h3 class="home-eco-title">{{ $item['name'] }}</h3>
                    <p class="home-eco-text">{{ $item['text'] }}</p>
                    <span class="home-eco-link">
                        Explore today
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </span>
                </a>
            @endforeach
        </div>

        <p class="home-eco-cta">
            <x-button :href="consultation_url()" size="sm">
                Get Free Consultation
                <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </x-button>
            <x-button :href="route('about')" variant="outline" size="sm">
                Discover CEBINOVA
                <svg class="arrow-shift h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </x-button>
        </p>
    </div>
</section>
