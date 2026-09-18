<<<<<<< Updated upstream
﻿@php
=======
@php
>>>>>>> Stashed changes
    $items = [
        ['title' => 'Business first', 'text' => 'We start with how you sell, serve customers and run operations today.', 'icon' => 'briefcase'],
        ['title' => 'Right-sized technology', 'text' => 'No unnecessary features. Start with what you need now, then scale.', 'icon' => 'layers'],
        ['title' => 'One partner', 'text' => 'Web, commerce, software, AI and marketing stay connected as you grow.', 'icon' => 'nodes'],
    ];
@endphp

<section class="about-page-approach section-pad-lg bg-white" aria-label="Our approach">
    <div class="container-wide">
        <div class="about-page-head" data-reveal>
            <p class="about-page-kicker">
                <span class="about-page-dot" aria-hidden="true"></span>
                Our approach
            </p>
            <h2 class="section-title">How we decide what to build.</h2>
            <p class="section-support">Understand the work first. Then recommend the technology that fits.</p>
        </div>

        <div class="about-page-approach-grid" data-stagger>
            @foreach ($items as $item)
                <article class="about-page-card">
                    <span class="about-page-icon">
<<<<<<< Updated upstream
                        <x-mark :name="$item['icon']" class="h-5 w-5" />
=======
                        <x-icon :name="$item['icon']" class="h-5 w-5" />
>>>>>>> Stashed changes
                    </span>
                    <h3 class="about-page-card-title">{{ $item['title'] }}</h3>
                    <p class="about-page-card-text">{{ $item['text'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
